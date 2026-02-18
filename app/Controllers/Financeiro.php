<?php

namespace App\Controllers;

use App\Models\CategoriaFinanceiraModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\VeiculoModel;
use CodeIgniter\Database\BaseConnection;

class Financeiro extends BaseController
{
    public function index(): string
    {
        $categoriaModel = new CategoriaFinanceiraModel();
        $lancamentoModel = new LancamentoFinanceiroModel();

        $empresaId = get_empresa_id();
        $lancamentos = $lancamentoModel
            ->builderWithCategoria()
            ->where('lancamentos_financeiros.lan_empresa_id', $empresaId)
            ->orderBy('lancamentos_financeiros.created_at', 'DESC')
            ->get()
            ->getResultArray();

        [$receitasMesAtual, $despesasMesAtual] = $this->calcularTotaisMesAtual($lancamentos);

        $data = [
            'title' => 'Listagem Financeira',
            // Cards (dinâmico, sem consulta em view)
            'receitas_mes_atual' => $receitasMesAtual,
            'despesas_mes_atual' => $despesasMesAtual,
            'lucro_mes_atual' => $receitasMesAtual - $despesasMesAtual,

            // Dados para primeira renderização (evitar consultas na view)
            'lancamentos' => $lancamentos,
            'categorias_receita' => $categoriaModel->getByTipo('receita'),
            'categorias_despesa' => $categoriaModel->getByTipo('despesa'),
            'locacoes' => $this->getLocacoesBasicas(),
        ];
        
        try {
            return view('admin/financeiro/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $lancamentoModel = new LancamentoFinanceiroModel();
            $rows = $lancamentoModel
                ->builderWithCategoria()
                ->where('lancamentos_financeiros.lan_empresa_id', get_empresa_id())
                ->orderBy('lancamentos_financeiros.created_at', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar lançamentos.',
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $lancamentoModel = new LancamentoFinanceiroModel();
            $row = $lancamentoModel
                ->builderWithCategoria()
                ->where('lancamentos_financeiros.lan_empresa_id', get_empresa_id())
                ->where('lancamentos_financeiros.id', (int) $id)
                ->get()
                ->getRowArray();

            if (!$row) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Lançamento não encontrado.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $row,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar lançamento.',
            ]);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();

            $data = $this->normalizeLancamentoPayload($payload);
            $validationError = $this->validateLancamentoPayload($data, $payload);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $empresaId = get_empresa_id();
            if ($empresaId <= 0) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Sessão inválida. Faça login novamente.',
                ]);
            }

            $data['lan_empresa_id'] = $empresaId;

            $lancamentoModel = new LancamentoFinanceiroModel();
            $id = $lancamentoModel->insert($data, true);
            if (!$id) {
                $errors = $lancamentoModel->errors();
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível cadastrar o lançamento.',
                    'errors' => $errors,
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lançamento cadastrado com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao cadastrar lançamento: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $errorMessage = ENVIRONMENT === 'development' 
                ? 'Erro ao cadastrar lançamento: ' . $e->getMessage()
                : 'Erro ao cadastrar lançamento. Verifique os logs para mais detalhes.';
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }
    }

    public function atualizar($id)
    {
        try {
            $lancamentoModel = new LancamentoFinanceiroModel();
            $existing = $lancamentoModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Lançamento não encontrado.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeLancamentoPayload($payload);
            $validationError = $this->validateLancamentoPayload($data, $payload);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['lan_empresa_id'] = get_empresa_id();

            $ok = $lancamentoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar o lançamento.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lançamento atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar lançamento.',
            ]);
        }
    }

    public function getCategorias($tipo)
    {
        try {
            $categoriaModel = new CategoriaFinanceiraModel();
            $cats = $categoriaModel->getByTipo((string) $tipo);
            return $this->response->setJSON([
                'success' => true,
                'data' => $cats,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar categorias.',
            ]);
        }
    }

    public function movimentacoes(): string
    {
        $data = [
            'title' => 'Movimentações',
            // Dados simulados para testes (sem consulta em view)
            'categorias_receita' => [
                ['id' => 1, 'nome' => 'Locação de veículos'],
                ['id' => 2, 'nome' => 'Caução'],
                ['id' => 3, 'nome' => 'Multa por atraso'],
                ['id' => 4, 'nome' => 'Taxa administrativa'],
                ['id' => 5, 'nome' => 'Serviços adicionais'],
                ['id' => 6, 'nome' => 'Venda de serviços'],
            ],
            'categorias_despesa' => [
                ['id' => 7, 'nome' => 'Combustível'],
                ['id' => 8, 'nome' => 'Manutenção de veículos'],
                ['id' => 9, 'nome' => 'Peças e acessórios'],
                ['id' => 10, 'nome' => 'Seguro'],
                ['id' => 11, 'nome' => 'IPVA'],
                ['id' => 12, 'nome' => 'Licenciamento'],
                ['id' => 13, 'nome' => 'Multas de trânsito'],
                ['id' => 14, 'nome' => 'Internet'],
                ['id' => 15, 'nome' => 'Aluguel'],
                ['id' => 16, 'nome' => 'Energia elétrica'],
                ['id' => 17, 'nome' => 'Água'],
                ['id' => 18, 'nome' => 'Folha de pagamento'],
            ],
            'locacoes' => [
                ['id' => 1, 'nome' => 'Locação #001 - João Silva'],
                ['id' => 2, 'nome' => 'Locação #002 - Maria Santos'],
                ['id' => 3, 'nome' => 'Locação #003 - Pedro Oliveira'],
            ],
            'formas_pagamento' => [
                ['id' => 'dinheiro', 'nome' => 'Dinheiro'],
                ['id' => 'pix', 'nome' => 'PIX'],
                ['id' => 'cartao_credito', 'nome' => 'Cartão de Crédito'],
                ['id' => 'cartao_debito', 'nome' => 'Cartão de Débito'],
                ['id' => 'boleto', 'nome' => 'Boleto'],
                ['id' => 'transferencia', 'nome' => 'Transferência'],
            ],
        ];

        try {
            return view('admin/financeiro/movimentacoes', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $lancamentos
     * @return array{0: float, 1: float}
     */
    private function calcularTotaisMesAtual(array $lancamentos): array
    {
        $mes = (int) date('m');
        $ano = (int) date('Y');

        $receitas = 0.0;
        $despesas = 0.0;

        foreach ($lancamentos as $l) {
            $status = (string) ($l['lan_status'] ?? 'pendente');
            if ($status !== 'pago') {
                continue;
            }

            $dataBase = (string) ($l['lan_data_pagamento'] ?? '');
            if ($dataBase === '') {
                $dataBase = (string) ($l['lan_data_lancamento'] ?? '');
            }
            if ($dataBase === '') {
                continue;
            }

            $ts = strtotime($dataBase);
            if (!$ts) {
                continue;
            }

            if ((int) date('m', $ts) !== $mes || (int) date('Y', $ts) !== $ano) {
                continue;
            }

            $valor = $l['lan_valor_pago'] ?? null;
            if ($valor === null || $valor === '') {
                $valor = $l['lan_valor'] ?? 0;
            }
            $valor = (float) $valor;

            $tipo = (string) ($l['lan_tipo'] ?? '');
            if ($tipo === 'receita') {
                $receitas += $valor;
            } elseif ($tipo === 'despesa') {
                $despesas += $valor;
            }
        }

        return [$receitas, $despesas];
    }

    /**
     * Retorna locações básicas para popular select (sem joins pesados).
     * @return array<int, array{id:int, label:string}>
     */
    private function getLocacoesBasicas(): array
    {
        try {
            /** @var BaseConnection $db */
            $db = db_connect();
            $rows = $db->table('locacoes')
                ->select('id')
                ->where('loc_empresa_id', get_empresa_id())
                ->orderBy('id', 'DESC')
                ->limit(200)
                ->get()
                ->getResultArray();

            $out = [];
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $out[] = ['id' => $id, 'label' => 'Locação #' . $id];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function normalizeLancamentoPayload(array $payload): array
    {
        $tipo = strtolower(trim((string) ($payload['lan_tipo'] ?? '')));
        if (!in_array($tipo, ['receita', 'despesa'], true)) {
            $tipo = '';
        }

        $status = strtolower(trim((string) ($payload['lan_status'] ?? 'pendente')));
        $allowedStatus = ['pendente', 'pago', 'cancelado'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'pendente';
        }

        $forma = (string) ($payload['lan_forma_pagamento'] ?? '');
        $allowedForma = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'];
        if ($forma !== '' && !in_array($forma, $allowedForma, true)) {
            $forma = '';
        }

        $valor = $this->parseMoney($payload['lan_valor'] ?? null);
        $valorPago = $this->parseMoney($payload['lan_valor_pago'] ?? null);

        $dataLancamento = (string) ($payload['lan_data_lancamento'] ?? '');
        $dataLancamento = $dataLancamento !== '' ? $dataLancamento : date('Y-m-d');

        $dataPagamento = (string) ($payload['lan_data_pagamento'] ?? '');
        $dataPagamento = $dataPagamento !== '' ? $dataPagamento : null;

        $locacaoId = null;
        if (array_key_exists('lan_locacao_id', $payload) && $payload['lan_locacao_id'] !== '' && $payload['lan_locacao_id'] !== null) {
            $locacaoId = (int) preg_replace('/\D/', '', (string) $payload['lan_locacao_id']);
            if ($locacaoId <= 0) $locacaoId = null;
        }

        $veiculoId = null;
        if (array_key_exists('lan_veiculo_id', $payload) && $payload['lan_veiculo_id'] !== '' && $payload['lan_veiculo_id'] !== null) {
            $veiculoId = (int) preg_replace('/\D/', '', (string) $payload['lan_veiculo_id']);
            if ($veiculoId <= 0) $veiculoId = null;
        }

        // Se veio placa (texto), tentamos resolver para ID (sem travar caso não encontre; validação cuida)
        $placa = trim((string) ($payload['lan_veiculo_placa'] ?? ''));
        if ($veiculoId === null && $placa !== '') {
            $veiculoId = $this->resolveVeiculoIdByPlaca($placa);
        }

        // Checkbox "marcar como pago/recebido" (aceitar on/1/true)
        $marcarPago = false;
        if (array_key_exists('marcar_recebida', $payload)) {
            $v = (string) $payload['marcar_recebida'];
            $marcarPago = ($v === 'on' || $v === '1' || $v === 'true');
        }
        if (array_key_exists('marcar_paga', $payload)) {
            $v = (string) $payload['marcar_paga'];
            $marcarPago = $marcarPago || ($v === 'on' || $v === '1' || $v === 'true');
        }

        if ($marcarPago && $status !== 'cancelado') {
            $status = 'pago';
            if ($dataPagamento === null) {
                $dataPagamento = date('Y-m-d');
            }
        }

        return [
            'lan_tipo' => $tipo,
            'lan_categoria_id' => (int) ($payload['lan_categoria_id'] ?? 0),
            'lan_descricao' => trim((string) ($payload['lan_descricao'] ?? '')),
            'lan_data_lancamento' => $dataLancamento,
            'lan_data_vencimento' => (string) ($payload['lan_data_vencimento'] ?? ''),
            'lan_data_pagamento' => $dataPagamento,
            'lan_valor' => $valor,
            'lan_valor_pago' => $valorPago,
            'lan_status' => $status,
            'lan_forma_pagamento' => $forma !== '' ? $forma : null,
            'lan_referencia' => trim((string) ($payload['lan_referencia'] ?? '')) ?: null,
            'lan_locacao_id' => $locacaoId,
            'lan_veiculo_id' => $veiculoId,
            'lan_obs' => trim((string) ($payload['lan_obs'] ?? '')) ?: null,
        ];
    }

    private function validateLancamentoPayload(array $data, array $rawPayload): ?string
    {
        if (($data['lan_tipo'] ?? '') === '') return 'Informe o tipo.';
        if ((int) ($data['lan_categoria_id'] ?? 0) <= 0) return 'Informe a categoria.';
        if (($data['lan_descricao'] ?? '') === '') return 'Informe a descrição.';
        if (($data['lan_data_vencimento'] ?? '') === '') return 'Informe a data de vencimento.';
        if (($data['lan_data_lancamento'] ?? '') === '') return 'Informe a data de lançamento.';
        if (!isset($data['lan_valor']) || (float) $data['lan_valor'] <= 0) return 'Informe o valor.';

        // Categoria precisa existir e corresponder ao tipo
        $categoriaModel = new CategoriaFinanceiraModel();
        $cat = $categoriaModel->find((int) $data['lan_categoria_id']);
        if (!$cat) return 'Categoria inválida.';
        if (($cat['cat_tipo'] ?? '') !== ($data['lan_tipo'] ?? '')) return 'Categoria não corresponde ao tipo.';

        // Resolver placa -> veiculo_id (se informado)
        $placa = trim((string) ($rawPayload['lan_veiculo_placa'] ?? ''));
        if ($placa !== '' && (!$data['lan_veiculo_id'])) {
            return 'Veículo não encontrado pela placa informada.';
        }

        return null;
    }

    private function resolveVeiculoIdByPlaca(string $placa): ?int
    {
        $placa = strtoupper(trim($placa));
        $placa = preg_replace('/[^A-Z0-9]/', '', $placa ?? '') ?? '';
        if (strlen($placa) < 7) return null;

        $withHyphen = substr($placa, 0, 3) . '-' . substr($placa, 3);

        try {
            $veiculoModel = new VeiculoModel();
            $row = $veiculoModel->where('vei_placa', $withHyphen)->first();
            if (!$row) {
                $row = $veiculoModel->where('vei_placa', $placa)->first();
            }
            if (!$row) return null;
            $id = (int) ($row['id'] ?? 0);
            return $id > 0 ? $id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseMoney($value): ?float
    {
        if ($value === null) return null;
        if ($value === '') return null;

        $raw = trim((string) $value);
        $raw = str_replace([' ', 'R$', 'r$'], '', $raw);

        // Aceita tanto "1234.56" quanto "1.234,56"
        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
        if ($raw === '') return null;

        return (float) $raw;
    }
}
