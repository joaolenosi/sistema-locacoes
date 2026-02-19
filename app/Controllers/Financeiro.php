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

        [$receitasMesAtual, $despesasMesAtual] = $this->getReceitasEDespesasMesAtual($empresaId);

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
        $categoriaModel = new CategoriaFinanceiraModel();
        $catReceita = $categoriaModel->getByTipo('receita');
        $catDespesa = $categoriaModel->getByTipo('despesa');

        $data = [
            'title' => 'Movimentações',
            'categorias_receita' => array_map(static function ($c) {
                return ['id' => (int) $c['id'], 'nome' => $c['cat_nome'] ?? $c['nome'] ?? ''];
            }, $catReceita),
            'categorias_despesa' => array_map(static function ($c) {
                return ['id' => (int) $c['id'], 'nome' => $c['cat_nome'] ?? $c['nome'] ?? ''];
            }, $catDespesa),
            'locacoes' => array_map(static function ($l) {
                return ['id' => (int) $l['id'], 'nome' => $l['label'] ?? ('Locação #' . ($l['id'] ?? ''))];
            }, $this->getLocacoesBasicas()),
            'formas_pagamento' => $this->getFormasPagamento(),
        ];

        try {
            return view('admin/financeiro/movimentacoes', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Lista de formas de pagamento (enum do banco – única fonte de verdade para selects).
     * @return array<int, array{id:string, nome:string}>
     */
    private function getFormasPagamento(): array
    {
        return [
            ['id' => 'dinheiro', 'nome' => 'Dinheiro'],
            ['id' => 'pix', 'nome' => 'PIX'],
            ['id' => 'cartao_credito', 'nome' => 'Cartão de Crédito'],
            ['id' => 'cartao_debito', 'nome' => 'Cartão de Débito'],
            ['id' => 'boleto', 'nome' => 'Boleto'],
            ['id' => 'transferencia', 'nome' => 'Transferência'],
        ];
    }

    /**
     * Receitas e despesas do mês atual.
     * Mês do lançamento: data pagamento, ou (se nula) data vencimento, ou data lançamento.
     * Assim, registro pago com vencimento 18/02 e data pagamento nula entra em fevereiro.
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
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >= ?', [$inicio])
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <= ?', [$fim])
            ->get()
            ->getRow();
        $totalReceitas = (float) ($receitas->total ?? 0);

        $despesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >= ?', [$inicio])
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <= ?', [$fim])
            ->get()
            ->getRow();
        $totalDespesas = (float) ($despesas->total ?? 0);

        return [$totalReceitas, $totalDespesas];
    }

    /**
     * DEBUG: Retorna os SELECTs SQL usados nos cards e na listagem.
     * Acesse: /admin/financeiro/debug-queries
     */
    public function debugQueries()
    {
        $empresaId = get_empresa_id();
        $db = \Config\Database::connect();
        $inicio = date('Y-m-01');
        $fim   = date('Y-m-t');

        // Query para receitas do mês (cards)
        $builderReceitas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pago')
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >= ?', [$inicio])
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <= ?', [$fim]);
        $sqlReceitas = $builderReceitas->getCompiledSelect(false);

        // Query para despesas do mês (cards)
        $builderDespesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >= ?', [$inicio])
            ->whereRaw('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <= ?', [$fim]);
        $sqlDespesas = $builderDespesas->getCompiledSelect(false);

        // Query para listagem completa
        $lancamentoModel = new LancamentoFinanceiroModel();
        $builderListagem = $lancamentoModel
            ->builderWithCategoria()
            ->where('lancamentos_financeiros.lan_empresa_id', $empresaId)
            ->orderBy('lancamentos_financeiros.created_at', 'DESC');
        $sqlListagem = $builderListagem->getCompiledSelect(false);

        // Executar e pegar resultados
        $resultReceitas = $builderReceitas->get()->getRow();
        $resultDespesas = $builderDespesas->get()->getRow();
        $resultListagem = $builderListagem->get()->getResultArray();

        // Filtrar registros de fevereiro para análise
        $registrosFev = [];
        foreach ($resultListagem as $r) {
            $dataPag = $r['lan_data_pagamento'] ?? null;
            $dataVenc = $r['lan_data_vencimento'] ?? null;
            $dataLanc = $r['lan_data_lancamento'] ?? null;
            $data = $dataPag ?: ($dataVenc ?: $dataLanc);
            if ($data) {
                $data = substr($data, 0, 10);
                if ($data >= $inicio && $data <= $fim) {
                    $registrosFev[] = [
                        'id' => $r['id'],
                        'tipo' => $r['lan_tipo'],
                        'status' => $r['lan_status'],
                        'data_pagamento' => $dataPag,
                        'data_vencimento' => $dataVenc,
                        'data_lancamento' => $dataLanc,
                        'data_usada' => $data,
                        'valor' => $r['lan_valor'],
                        'valor_pago' => $r['lan_valor_pago'],
                        'descricao' => $r['lan_descricao'],
                    ];
                }
            }
        }

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Debug Financeiro - Queries SQL</title>';
        $html .= '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}';
        $html .= '.query-box{background:white;padding:15px;margin:15px 0;border-left:4px solid #0d6efd;border-radius:4px;}';
        $html .= '.result-box{background:#e7f3ff;padding:15px;margin:15px 0;border-left:4px solid #22c55e;border-radius:4px;}';
        $html .= 'pre{background:#f8f9fa;padding:10px;border-radius:4px;overflow-x:auto;}';
        $html .= 'h2{color:#333;border-bottom:2px solid #0d6efd;padding-bottom:5px;}';
        $html .= 'table{border-collapse:collapse;width:100%;margin:15px 0;}';
        $html .= 'th,td{padding:8px;text-align:left;border:1px solid #ddd;}';
        $html .= 'th{background:#0d6efd;color:white;}</style></head><body>';
        $html .= '<h1>🔍 Debug Financeiro - Queries SQL</h1>';
        
        $html .= '<div class="result-box"><strong>Empresa ID:</strong> ' . $empresaId . '<br>';
        $html .= '<strong>Mês atual:</strong> ' . date('m/Y') . ' (início: ' . $inicio . ', fim: ' . $fim . ')</div>';

        $html .= '<h2>1. SELECT para Receitas do Mês (Cards)</h2>';
        $html .= '<div class="query-box"><pre>' . htmlspecialchars($sqlReceitas) . '</pre></div>';
        $html .= '<div class="result-box"><strong>Resultado:</strong> R$ ' . number_format((float) ($resultReceitas->total ?? 0), 2, ',', '.') . '</div>';

        $html .= '<h2>2. SELECT para Despesas do Mês (Cards)</h2>';
        $html .= '<div class="query-box"><pre>' . htmlspecialchars($sqlDespesas) . '</pre></div>';
        $html .= '<div class="result-box"><strong>Resultado:</strong> R$ ' . number_format((float) ($resultDespesas->total ?? 0), 2, ',', '.') . '</div>';

        $html .= '<h2>3. SELECT para Listagem Completa</h2>';
        $html .= '<div class="query-box"><pre>' . htmlspecialchars($sqlListagem) . '</pre></div>';
        $html .= '<div class="result-box"><strong>Total de registros:</strong> ' . count($resultListagem) . '</div>';

        $html .= '<h2>4. Registros de Fevereiro (Filtrados)</h2>';
        $html .= '<div class="result-box"><strong>Total encontrados:</strong> ' . count($registrosFev) . '</div>';
        if (count($registrosFev) > 0) {
            $html .= '<table><tr><th>ID</th><th>Tipo</th><th>Status</th><th>Data Pagamento</th><th>Data Vencimento</th><th>Data Lançamento</th><th>Data Usada</th><th>Valor</th><th>Valor Pago</th><th>Descrição</th></tr>';
            foreach ($registrosFev as $r) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($r['id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($r['tipo']) . '</td>';
                $html .= '<td>' . htmlspecialchars($r['status']) . '</td>';
                $html .= '<td>' . htmlspecialchars($r['data_pagamento'] ?? '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($r['data_vencimento'] ?? '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($r['data_lancamento'] ?? '-') . '</td>';
                $html .= '<td><strong>' . htmlspecialchars($r['data_usada']) . '</strong></td>';
                $html .= '<td>R$ ' . number_format((float) ($r['valor'] ?? 0), 2, ',', '.') . '</td>';
                $html .= '<td>R$ ' . number_format((float) ($r['valor_pago'] ?? 0), 2, ',', '.') . '</td>';
                $html .= '<td>' . htmlspecialchars($r['descricao'] ?? '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p style="color:red;">⚠️ Nenhum registro encontrado para fevereiro com status "pago"!</p>';
        }

        $html .= '</body></html>';
        return $html;
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
        if (strpos($raw, ',') !== false) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
        if ($raw === '') return null;

        return (float) $raw;
    }
}
