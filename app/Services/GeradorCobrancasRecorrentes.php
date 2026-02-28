<?php

namespace App\Services;

use App\Models\CategoriaFinanceiraModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\LocacaoModel;

/**
 * Gera cobranças recorrentes a partir das locações ativas.
 * Usa loc_recorrencia_pagamento e loc_data_inicio_pagamento para calcular os períodos.
 */
class GeradorCobrancasRecorrentes
{
    private LancamentoFinanceiroModel $lancamentoModel;
    private LocacaoModel $locacaoModel;
    private CategoriaFinanceiraModel $categoriaModel;

    /** @var array<string, int> Dias a adicionar por recorrência */
    private const DIAS_POR_RECORRENCIA = [
        'diaria'    => 1,
        'semanal'   => 7,
        'quinzenal' => 15,
        'mensal'    => 0, // Tratamento especial
    ];

    public function __construct()
    {
        $this->lancamentoModel  = new LancamentoFinanceiroModel();
        $this->locacaoModel     = new LocacaoModel();
        $this->categoriaModel   = new CategoriaFinanceiraModel();
    }

    /**
     * Executa a geração de cobranças para a empresa logada.
     *
     * @return int Quantidade de cobranças criadas
     */
    public function executar(int $empresaId): int
    {
        if ($empresaId < 1) {
            return 0;
        }

        $locacoes = $this->buscarLocacoesComRecorrencia($empresaId);
        $categoriaId = $this->criarOuBuscarCategoriaLocacao();
        $criadas = 0;

        foreach ($locacoes as $loc) {
            $criadas += $this->gerarCobrancasParaLocacao($loc, $categoriaId, $empresaId);
        }

        return $criadas;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buscarLocacoesComRecorrencia(int $empresaId): array
    {
        $recorrencias = array_keys(self::DIAS_POR_RECORRENCIA);
        $statusPermitidos = ['ativa', 'atrasada', 'reservada', 'inadimplente'];

        return $this->locacaoModel
            ->where('loc_empresa_id', $empresaId)
            ->whereIn('loc_recorrencia_pagamento', $recorrencias)
            ->where('loc_recorrencia_pagamento IS NOT NULL', null, false)
            ->whereIn('loc_status', $statusPermitidos)
            ->findAll();
    }

    private function criarOuBuscarCategoriaLocacao(): int
    {
        $categoria = $this->categoriaModel
            ->where('cat_nome', 'Locação')
            ->where('cat_tipo', 'receita')
            ->first();

        if ($categoria && isset($categoria['id'])) {
            return (int) $categoria['id'];
        }

        $data = [
            'cat_nome'   => 'Locação',
            'cat_tipo'   => 'receita',
            'cat_padrao' => 0,
        ];
        $id = $this->categoriaModel->insert($data, true);
        if (!$id) {
            throw new \RuntimeException('Não foi possível criar a categoria Locação.');
        }
        return (int) $id;
    }

    /**
     * @param array<string, mixed> $loc
     */
    private function gerarCobrancasParaLocacao(array $loc, int $categoriaId, int $empresaId): int
    {
        $locId = (int) ($loc['id'] ?? 0);
        $dataInicio = $loc['loc_data_inicio_pagamento'] ?? $loc['loc_data_inicio'] ?? null;
        if (!$dataInicio) {
            return 0;
        }

        $hoje = date('Y-m-d');
        $dataFim = $loc['loc_data_fim_real'] ?? $loc['loc_data_fim_prevista'] ?? $hoje;
        if ($dataFim > $hoje) {
            $dataFim = $hoje;
        }

        $recorrencia = (string) ($loc['loc_recorrencia_pagamento'] ?? 'mensal');
        $valor = (float) ($loc['loc_valor_locacao'] ?? 0);
        $veiId = (int) ($loc['loc_vei_id'] ?? 0);

        $periodos = $this->calcularPeriodos($dataInicio, $dataFim, $recorrencia);
        $criadas = 0;

        foreach ($periodos as $dataVencimento) {
            if ($this->cobrancaJaExiste($locId, $dataVencimento, $empresaId)) {
                continue;
            }
            if ($this->criarLancamento($locId, $dataVencimento, $valor, $categoriaId, $empresaId, $veiId)) {
                $criadas++;
            }
        }

        return $criadas;
    }

    /**
     * @return list<string> Datas de vencimento no formato Y-m-d
     */
    private function calcularPeriodos(string $dataInicio, string $dataFim, string $recorrencia): array
    {
        $periodos = [];
        $atual = \DateTime::createFromFormat('Y-m-d', $dataInicio);
        $fim = \DateTime::createFromFormat('Y-m-d', $dataFim);

        if (!$atual || !$fim || $atual > $fim) {
            return [];
        }

        $dias = self::DIAS_POR_RECORRENCIA[$recorrencia] ?? 30;

        if ($recorrencia === 'mensal') {
            while ($atual <= $fim) {
                $periodos[] = $atual->format('Y-m-d');
                $diaOriginal = (int) $atual->format('d');
                $atual->modify('first day of next month');
                $ultimoDia = (int) $atual->format('t');
                $dia = min($diaOriginal, $ultimoDia);
                $atual->setDate((int) $atual->format('Y'), (int) $atual->format('m'), $dia);
            }
        } else {
            while ($atual <= $fim) {
                $periodos[] = $atual->format('Y-m-d');
                $atual->modify("+{$dias} days");
            }
        }

        return $periodos;
    }

    private function cobrancaJaExiste(int $locacaoId, string $dataVencimento, int $empresaId): bool
    {
        $count = $this->lancamentoModel
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_locacao_id', $locacaoId)
            ->where('lan_data_vencimento', $dataVencimento)
            ->where('lan_tipo', 'receita')
            ->countAllResults();

        return $count > 0;
    }

    private function criarLancamento(
        int $locacaoId,
        string $dataVencimento,
        float $valor,
        int $categoriaId,
        int $empresaId,
        int $veiId
    ): bool {
        $competencia = $this->formatarCompetencia($dataVencimento);
        $descricao = "Locação #{$locacaoId} - {$competencia}";

        $data = [
            'lan_empresa_id'      => $empresaId,
            'lan_tipo'            => 'receita',
            'lan_categoria_id'    => $categoriaId,
            'lan_descricao'       => $descricao,
            'lan_data_lancamento' => date('Y-m-d'),
            'lan_data_vencimento' => $dataVencimento,
            'lan_valor'           => $valor,
            'lan_status'          => 'pendente',
            'lan_locacao_id'      => $locacaoId,
            'lan_veiculo_id'      => $veiId > 0 ? $veiId : null,
            'lan_forma_pagamento' => null,
            'lan_referencia'      => null,
            'lan_obs'             => null,
        ];

        $id = $this->lancamentoModel->insert($data, true);
        return $id > 0;
    }

    private function formatarCompetencia(string $dataVencimento): string
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $dataVencimento);
        if (!$dt) {
            return $dataVencimento;
        }
        return $dt->format('d/m/Y');
    }
}
