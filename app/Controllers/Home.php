<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\ManutencaoModel;
use App\Models\VeiculoModel;

class Home extends BaseController
{
    public function index(): string
    {
        $empresaId = get_empresa_id();

        [$receitasMes, $despesasMes] = $this->getReceitasEDespesasMesAtual($empresaId);

        $data = [
            'title' => 'Dashboard',
            'faturamento_mes_atual'   => $receitasMes, // faturamento = receitas recebidas no mês
            'faturamento_mes_anterior' => $this->getFaturamentoMesAnterior($empresaId),
            'crescimento_percentual' => 0, // filled below
            'caixa_total'            => $this->getCaixaTotal($empresaId),
            'receitas_mes_atual'     => $receitasMes,
            'despesas_mes_atual'     => $despesasMes,
            'lucro_mes_atual'        => $receitasMes - $despesasMes,
            'veiculos_disponiveis'   => $this->getVeiculosDisponiveis($empresaId),
            'total_veiculos'         => $this->getTotalVeiculos($empresaId),
            'cobrancas_atraso'       => $this->getCobrancasAtraso($empresaId),
            'precisa_manutencao'     => $this->getPrecisaManutencao($empresaId),
            'cnhs_vencidas'          => $this->getCnhsVencidas($empresaId),
            'fluxo_caixa'            => $this->getFluxoCaixa12Meses($empresaId),
            'tipos_movimentacao'     => $this->getTiposMovimentacao($empresaId),
            'veiculos_status'        => $this->getVeiculosPorStatus($empresaId),
        ];

        $atual   = $data['faturamento_mes_atual'];
        $anterior = $data['faturamento_mes_anterior'];
        $data['crescimento_percentual'] = $anterior > 0
            ? round((($atual - $anterior) / $anterior) * 100, 0)
            : ($atual > 0 ? 100 : 0);

        try {
            return view('admin/dashboard', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Faturamento (receitas pagas) em um mês/ano.
     * Data do fato: COALESCE(lan_data_pagamento, lan_data_lancamento).
     */
    private function getFaturamentoMes(int $empresaId, int $mes, int $ano): float
    {
        $db = \Config\Database::connect();
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim   = date('Y-m-t', strtotime($inicio));

        $row = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pago')
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) >=', $inicio, false)
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) <=', $fim, false)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    private function getFaturamentoMesAnterior(int $empresaId): float
    {
        $mes = (int) date('n');
        $ano = (int) date('Y');
        if ($mes === 1) {
            $mes = 12;
            $ano--;
        } else {
            $mes--;
        }
        return $this->getFaturamentoMes($empresaId, $mes, $ano);
    }

    /**
     * Caixa total = soma receitas pagas - soma despesas pagas (todos os tempos).
     */
    private function getCaixaTotal(int $empresaId): float
    {
        $db = \Config\Database::connect();

        $receitas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pago')
            ->get()
            ->getRow();
        $totalReceitas = (float) ($receitas->total ?? 0);

        $despesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->get()
            ->getRow();
        $totalDespesas = (float) ($despesas->total ?? 0);

        return $totalReceitas - $totalDespesas;
    }

    /**
     * Receitas e despesas do mês atual (recebidas/pagas no mês).
     * Faturamento = receitas (o que você recebeu). Lucro = receitas - despesas.
     *
     * @return array{0: float, 1: float} [receitas, despesas]
     */
    private function getReceitasEDespesasMesAtual(int $empresaId): array
    {
        $db = \Config\Database::connect();
        $inicio = date('Y-m-01');
        $fim   = date('Y-m-t');

        $receitas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pago')
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) >=', $inicio, false)
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) <=', $fim, false)
            ->get()
            ->getRow();
        $totalReceitas = (float) ($receitas->total ?? 0);

        $despesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) >=', $inicio, false)
            ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) <=', $fim, false)
            ->get()
            ->getRow();
        $totalDespesas = (float) ($despesas->total ?? 0);

        return [$totalReceitas, $totalDespesas];
    }

    /**
     * Cobranças em atraso: receitas pendentes com vencimento &lt; hoje.
     */
    private function getCobrancasAtraso(int $empresaId): int
    {
        $model = new LancamentoFinanceiroModel();
        return (int) $model
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pendente')
            ->where('lan_data_vencimento <', date('Y-m-d'))
            ->countAllResults();
    }

    /**
     * Quantidade de manutenções em aberto.
     */
    private function getPrecisaManutencao(int $empresaId): int
    {
        $model = new ManutencaoModel();
        return (int) $model
            ->where('man_empresa_id', $empresaId)
            ->where('man_status', 'aberta')
            ->countAllResults();
    }

    /**
     * Veículos disponíveis: vei_status = 'disponivel' ou vazio.
     */
    private function getVeiculosDisponiveis(int $empresaId): int
    {
        $model = new VeiculoModel();
        return (int) $model
            ->where('vei_empresa_id', $empresaId)
            ->groupStart()
                ->where('vei_status', 'disponivel')
                ->orWhere('vei_status', '')
            ->groupEnd()
            ->countAllResults();
    }

    private function getTotalVeiculos(int $empresaId): int
    {
        $model = new VeiculoModel();
        return (int) $model->where('vei_empresa_id', $empresaId)->countAllResults();
    }

    /**
     * Clientes com CNH vencida (cli_cnh_validade &lt; hoje).
     */
    private function getCnhsVencidas(int $empresaId): int
    {
        $model = new ClienteModel();
        return (int) $model
            ->where('cli_empresa_id', $empresaId)
            ->where('cli_cnh_validade IS NOT NULL', null, false)
            ->where('cli_cnh_validade <', date('Y-m-d'))
            ->countAllResults();
    }

    /**
     * Fluxo de caixa: últimos 12 meses, valor = receitas pagas - despesas pagas por mês.
     */
    private function getFluxoCaixa12Meses(int $empresaId): array
    {
        $db = \Config\Database::connect();
        $result = [];
        $mesesLabel = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        for ($i = 11; $i >= 0; $i--) {
            $dt = new \DateTime();
            $dt->modify("-{$i} months");
            $ano  = (int) $dt->format('Y');
            $mes  = (int) $dt->format('n');
            $inicio = $dt->format('Y-m-01');
            $fim   = $dt->format('Y-m-t');

            $receitas = $db->table('lancamentos_financeiros')
                ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
                ->where('lan_empresa_id', $empresaId)
                ->where('lan_tipo', 'receita')
                ->where('lan_status', 'pago')
                ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) >=', $inicio, false)
                ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) <=', $fim, false)
                ->get()
                ->getRow();
            $totalReceitas = (float) ($receitas->total ?? 0);

            $despesas = $db->table('lancamentos_financeiros')
                ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
                ->where('lan_empresa_id', $empresaId)
                ->where('lan_tipo', 'despesa')
                ->where('lan_status', 'pago')
                ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) >=', $inicio, false)
                ->where('COALESCE(lan_data_pagamento, lan_data_lancamento) <=', $fim, false)
                ->get()
                ->getRow();
            $totalDespesas = (float) ($despesas->total ?? 0);

            $label = $mesesLabel[$mes - 1] . '/' . substr((string) $ano, -2);
            $result[] = ['mes' => $label, 'valor' => round($totalReceitas - $totalDespesas, 2)];
        }

        return $result;
    }

    /**
     * Tipos de movimentação: percentual Entrada (receitas) vs Saída (despesas) para gráfico pizza.
     */
    private function getTiposMovimentacao(int $empresaId): array
    {
        $db = \Config\Database::connect();

        $receitas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pago')
            ->get()
            ->getRow();
        $totalReceitas = (float) ($receitas->total ?? 0);

        $despesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->get()
            ->getRow();
        $totalDespesas = (float) ($despesas->total ?? 0);

        $total = $totalReceitas + $totalDespesas;
        if ($total <= 0) {
            return [
                ['tipo' => 'Receitas', 'valor' => 50],
                ['tipo' => 'Despesas', 'valor' => 50],
            ];
        }
        return [
            ['tipo' => 'Receitas', 'valor' => round(($totalReceitas / $total) * 100, 0)],
            ['tipo' => 'Despesas', 'valor' => round(($totalDespesas / $total) * 100, 0)],
        ];
    }

    /**
     * Veículos por status para gráfico: Ocupados (locado), Livres (disponivel + vazio), Manutenção, Inativo.
     */
    private function getVeiculosPorStatus(int $empresaId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('veiculos')
            ->select('vei_status, COUNT(*) as quantidade', false)
            ->where('vei_empresa_id', $empresaId)
            ->groupBy('vei_status')
            ->get()
            ->getResultArray();

        $map = [
            'locado'     => 'Ocupados',
            'disponivel' => 'Livres',
            'manutencao' => 'Manutenção',
            'inativo'    => 'Inativo',
        ];
        $counts = [
            'Ocupados'    => 0,
            'Livres'     => 0,
            'Manutenção' => 0,
            'Inativo'    => 0,
        ];
        foreach ($rows as $r) {
            $status = trim((string) ($r['vei_status'] ?? ''));
            $label = $map[$status] ?? 'Livres';
            if ($status === '') {
                $label = 'Livres';
            }
            $counts[$label] = $counts[$label] + (int) ($r['quantidade'] ?? 0);
        }

        return [
            ['status' => 'Ocupados', 'quantidade' => $counts['Ocupados']],
            ['status' => 'Livres', 'quantidade' => $counts['Livres']],
            ['status' => 'Manutenção', 'quantidade' => $counts['Manutenção']],
            ['status' => 'Inativo', 'quantidade' => $counts['Inativo']],
        ];
    }
}
