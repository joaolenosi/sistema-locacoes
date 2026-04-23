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

    public function efetuarPagamento($id)
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

            // Verificar se pertence à empresa
            if ((int) ($existing['lan_empresa_id'] ?? 0) !== get_empresa_id()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ]);
            }

            // Verificar se já está pago
            if (($existing['lan_status'] ?? '') === 'pago') {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Este lançamento já está pago.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $dataPagamento = trim((string) ($payload['lan_data_pagamento'] ?? ''));
            
            if ($dataPagamento === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Informe a data do pagamento.',
                ]);
            }

            // Validar formato da data
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPagamento)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Data inválida.',
                ]);
            }

            $valorPago = $this->parseMoney($payload['lan_valor_pago'] ?? null);
            if ($valorPago === null) {
                $valorPago = (float) ($existing['lan_valor'] ?? 0);
            }

            $updateData = [
                'lan_status' => 'pago',
                'lan_data_pagamento' => $dataPagamento,
                'lan_valor_pago' => $valorPago,
            ];

            // Opcional: forma de pagamento e referência
            if (isset($payload['lan_forma_pagamento']) && $payload['lan_forma_pagamento'] !== '') {
                $allowedForma = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'];
                if (in_array($payload['lan_forma_pagamento'], $allowedForma, true)) {
                    $updateData['lan_forma_pagamento'] = $payload['lan_forma_pagamento'];
                }
            }

            if (isset($payload['lan_referencia']) && trim($payload['lan_referencia']) !== '') {
                $updateData['lan_referencia'] = trim($payload['lan_referencia']);
            }

            $ok = $lancamentoModel->update((int) $id, $updateData);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível efetuar o pagamento.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pagamento efetuado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao efetuar pagamento: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao efetuar pagamento.',
            ]);
        }
    }

    public function fatura($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId <= 0) {
                return $this->response->setStatusCode(401)->setBody('Sessão inválida.');
            }

            $lancamentoModel = new LancamentoFinanceiroModel();
            $lancamento = $lancamentoModel->find((int) $id);
            if (!$lancamento) {
                return $this->response->setStatusCode(404)->setBody('Lançamento não encontrado.');
            }

            if ((int) ($lancamento['lan_empresa_id'] ?? 0) !== $empresaId) {
                return $this->response->setStatusCode(403)->setBody('Acesso negado.');
            }

            if (($lancamento['lan_status'] ?? '') !== 'pago') {
                return $this->response->setStatusCode(422)->setBody('A fatura só pode ser emitida para lançamentos pagos.');
            }

            $locacaoId = (int) ($lancamento['lan_locacao_id'] ?? 0);
            if ($locacaoId <= 0) {
                return $this->response->setStatusCode(422)->setBody('Lançamento sem locação vinculada.');
            }

            $db = \Config\Database::connect();
            $row = $db->table('locacoes')
                ->select('locacoes.*')
                ->select('clientes.*')
                ->select('veiculos.*')
                ->join('clientes', 'clientes.id = locacoes.loc_cli_id', 'left')
                ->join('veiculos', 'veiculos.id = locacoes.loc_vei_id', 'left')
                ->where('locacoes.id', $locacaoId)
                ->where('locacoes.loc_empresa_id', $empresaId)
                ->get()
                ->getRowArray();

            if (!$row) {
                return $this->response->setStatusCode(404)->setBody('Locação vinculada não encontrada.');
            }

            $empresa = $db->table('empresas')->where('id', $empresaId)->get()->getRowArray() ?: [];

            $html = $this->buildFaturaPdfHtml($lancamento, $row, $empresa);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            $numero = str_pad((string) ((int) $lancamento['id']), 6, '0', STR_PAD_LEFT);
            $filename = 'fatura-locacao-' . $numero . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->setBody($pdfOutput);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao gerar fatura de locação: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Erro ao gerar fatura.');
        }
    }

    public function excluir($id)
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

            if ((int) ($existing['lan_empresa_id'] ?? 0) !== get_empresa_id()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Acesso negado.',
                ]);
            }

            $ok = $lancamentoModel->delete((int) $id);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível excluir o lançamento.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lançamento excluído com sucesso.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao excluir lançamento: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao excluir lançamento.',
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
     * Receitas e despesas do mês atual (para calcular Lucro = receitas - despesas).
     * Faturamento usa apenas receitas (sem subtrair despesas).
     * Mês determinado por: COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento).
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
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >=', $db->escape($inicio), false)
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <=', $db->escape($fim), false)
            ->get()
            ->getRow();
        $totalReceitas = (float) ($receitas->total ?? 0);

        $despesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >=', $db->escape($inicio), false)
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <=', $db->escape($fim), false)
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
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >=', $db->escape($inicio), false)
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <=', $db->escape($fim), false);
        $sqlReceitas = $builderReceitas->getCompiledSelect(false);

        // Query para despesas do mês (cards)
        $builderDespesas = $db->table('lancamentos_financeiros')
            ->select('SUM(COALESCE(lan_valor_pago, lan_valor)) as total', false)
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'despesa')
            ->where('lan_status', 'pago')
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) >=', $db->escape($inicio), false)
            ->where('COALESCE(lan_data_pagamento, lan_data_vencimento, lan_data_lancamento) <=', $db->escape($fim), false);
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

    private function buildFaturaPdfHtml(array $lancamento, array $locacaoJoin, array $empresa): string
    {
        $numeroFatura = str_pad((string) ((int) ($lancamento['id'] ?? 0)), 6, '0', STR_PAD_LEFT);
        $valorTotal = (float) (($lancamento['lan_valor_pago'] ?? null) !== null && $lancamento['lan_valor_pago'] !== ''
            ? $lancamento['lan_valor_pago']
            : ($lancamento['lan_valor'] ?? 0));

        $emissao = $this->formatDateBR((string) ($lancamento['lan_data_pagamento'] ?? date('Y-m-d')));
        $inicio = $this->formatDateBR((string) ($locacaoJoin['loc_data_inicio'] ?? ''));
        $fim = $this->formatDateBR((string) ($locacaoJoin['loc_data_fim_prevista'] ?? ''));
        $placa = strtoupper((string) ($locacaoJoin['vei_placa'] ?? ''));
        $marca = strtoupper((string) ($locacaoJoin['vei_marca'] ?? ''));
        $modelo = strtoupper((string) ($locacaoJoin['vei_modelo'] ?? ''));

        $empresaNome = $this->esc((string) ($empresa['emp_nome'] ?? $empresa['emp_fantasia'] ?? 'EMPRESA'));
        $empresaCpfCnpj = $this->esc((string) ($empresa['emp_cpf_cnpj'] ?? ''));
        $empresaTelefone = $this->esc((string) ($empresa['emp_telefone'] ?? ''));
        $empresaEndereco = $this->esc(trim((string) ($empresa['emp_rua'] ?? '')));
        $empresaNumero = $this->esc(trim((string) ($empresa['emp_numero'] ?? '')));
        $empresaBairro = $this->esc(trim((string) ($empresa['emp_bairro'] ?? '')));
        $empresaCidadeUf = $this->esc(trim((string) (($empresa['emp_cidade'] ?? '') . '/' . ($empresa['emp_estado'] ?? ''))));
        $empresaCep = $this->esc((string) ($empresa['emp_cep'] ?? ''));

        $clienteNome = $this->esc((string) ($locacaoJoin['cli_nome'] ?? ''));
        $clienteCpfCnpj = $this->esc((string) ($locacaoJoin['cli_cpf_cnpj'] ?? ''));
        $clienteEndereco = $this->esc(trim((string) ($locacaoJoin['cli_endereco'] ?? '')));
        $clienteBairro = $this->esc((string) ($locacaoJoin['cli_bairro'] ?? ''));
        $clienteCep = $this->esc((string) ($locacaoJoin['cli_cep'] ?? ''));
        $clienteUf = $this->esc((string) ($locacaoJoin['cli_estado'] ?? ''));
        $clienteCidade = $this->esc((string) ($locacaoJoin['cli_cidade'] ?? ''));
        $clienteTelefone = $this->esc((string) ($locacaoJoin['cli_telefone'] ?? ''));
        $logoHtml = $this->buildEmpresaLogoHtml($empresa);

        $descricaoLocacao = 'LOCAÇÃO DE AUTOMÓVEIS TIPO ' . trim($marca . '/ ' . $modelo)
            . ' - PLACA: ' . $placa
            . ' - REF. AO PERÍODO (' . $inicio . ' À ' . $fim . ')';

        $valorExtenso = strtoupper($this->valorPorExtenso($valorTotal));
        $valorFormatado = $this->formatMoneyBR($valorTotal);

        return '<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    .sheet { width: 100%; }
    .title { text-align: right; font-weight: bold; font-size: 20px; margin-bottom: 8px; }
    .line { border-bottom: 1px solid #000; margin: 6px 0 8px 0; }
    .row { width: 100%; }
    .small { font-size: 10px; }
    .mb4 { margin-bottom: 4px; }
    .mb8 { margin-bottom: 8px; }
    .mb12 { margin-bottom: 12px; }
    .bold { font-weight: bold; }
    .box-title { font-weight: bold; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; }
    td, th { vertical-align: top; padding: 2px 4px; }
    .right { text-align: right; }
    .center { text-align: center; }
    .border { border: 1px solid #000; }
    .top-space { margin-top: 14px; }
    .canhoto { margin-top: 18px; border-top: 1px dashed #000; padding-top: 10px; }
  </style>
</head>
<body>
  <div class="sheet">
    <table class="mb4">
      <tr>
        <td style="width: 70%;">
          <div class="mb8">' . $logoHtml . '</div>
          <div class="bold" style="font-size:13px;">' . $empresaNome . '</div>
          <div>' . $empresaEndereco . ', ' . $empresaNumero . ' ' . $empresaBairro . '</div>
          <div>' . $empresaCidadeUf . '</div>
          <div>CEP: ' . $empresaCep . '</div>
          <div>CNPJ: ' . $empresaCpfCnpj . '</div>
          <div>TELEFONE: ' . $empresaTelefone . '</div>
        </td>
        <td style="width: 30%;">
          <div class="title">FATURA DE LOCAÇÃO</div>
          <div class="border" style="padding:8px;">
            <div><span class="bold">Nº:</span> ' . $numeroFatura . '</div>
            <div><span class="bold">Emissão:</span> ' . $emissao . '</div>
          </div>
        </td>
      </tr>
    </table>

    <div class="line"></div>
    <div class="box-title">DESTINATÁRIO</div>
    <table class="border mb8">
      <tr>
        <td style="width:70%;"><span class="small">Razão Social / Nome Cliente</span><br><span class="bold">' . $clienteNome . '</span></td>
        <td style="width:30%;"><span class="small">CNPJ / CPF</span><br><span class="bold">' . $clienteCpfCnpj . '</span></td>
      </tr>
      <tr>
        <td><span class="small">Endereço</span><br>' . $clienteEndereco . '</td>
        <td><span class="small">Bairro</span><br>' . $clienteBairro . '</td>
      </tr>
      <tr>
        <td><span class="small">Cidade</span><br>' . $clienteCidade . '</td>
        <td><span class="small">CEP / UF / Telefone</span><br>' . $clienteCep . ' - ' . $clienteUf . ' - ' . $clienteTelefone . '</td>
      </tr>
    </table>

    <div class="mb4"><span class="bold">CONDIÇÃO DE PAGAMENTO:</span> À VISTA</div>
    <div class="mb8"><span class="bold">OBSERVAÇÃO</span> ' . $this->esc((string) ($lancamento['lan_obs'] ?? '')) . '</div>

    <div class="box-title">DADOS DA LOCAÇÃO</div>
    <table class="border mb8">
      <tr>
        <th style="width:12%;">Código</th>
        <th style="width:58%;">Descrição / Configuração</th>
        <th style="width:10%;" class="center">Quantidade</th>
        <th style="width:10%;" class="right">Valor Unitário</th>
        <th style="width:10%;" class="right">Valor Total</th>
      </tr>
      <tr>
        <td>' . $numeroFatura . '</td>
        <td>' . $this->esc($descricaoLocacao) . '</td>
        <td class="center">1</td>
        <td class="right">R$ ' . $valorFormatado . '</td>
        <td class="right">R$ ' . $valorFormatado . '</td>
      </tr>
    </table>

    <div class="mb12 bold">Valor Total da Fatura: R$' . $valorFormatado . ' ( ' . $valorExtenso . ' )</div>

    <div class="canhoto small">
      <div>RECEBI(EMOS) DE ' . $empresaNome . ' AS LOCAÇÕES CONSTANTES NESSA FATURA INDICADA AO LADO.</div>
      <table style="margin-top:10px;">
        <tr>
          <td style="width:60%;">
            <div style="border-top:1px solid #000; padding-top:3px;">DATA DO RECEBIMENTO</div>
          </td>
          <td style="width:40%;">
            <div style="border-top:1px solid #000; padding-top:3px;">IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR</div>
          </td>
        </tr>
      </table>
      <div class="right bold">FATURA DE LOCAÇÃO Nº: ' . $numeroFatura . '</div>
    </div>
  </div>
</body>
</html>';
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function formatDateBR(string $date): string
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $m)) {
            return $m[3] . '/' . $m[2] . '/' . $m[1];
        }
        return $date;
    }

    private function formatMoneyBR(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function valorPorExtenso(float $value): string
    {
        $inteiro = (int) floor($value);
        $centavos = (int) round(($value - $inteiro) * 100);

        $textoInteiro = $this->numeroPorExtenso($inteiro);
        $saida = $textoInteiro . ' ' . ($inteiro === 1 ? 'real' : 'reais');

        if ($centavos > 0) {
            $saida .= ' e ' . $this->numeroPorExtenso($centavos) . ' ' . ($centavos === 1 ? 'centavo' : 'centavos');
        }

        return $saida;
    }

    private function numeroPorExtenso(int $numero): string
    {
        if ($numero === 0) {
            return 'zero';
        }

        $unidades = [
            0 => '',
            1 => 'um',
            2 => 'dois',
            3 => 'três',
            4 => 'quatro',
            5 => 'cinco',
            6 => 'seis',
            7 => 'sete',
            8 => 'oito',
            9 => 'nove',
            10 => 'dez',
            11 => 'onze',
            12 => 'doze',
            13 => 'treze',
            14 => 'quatorze',
            15 => 'quinze',
            16 => 'dezesseis',
            17 => 'dezessete',
            18 => 'dezoito',
            19 => 'dezenove',
        ];

        $dezenas = [
            2 => 'vinte',
            3 => 'trinta',
            4 => 'quarenta',
            5 => 'cinquenta',
            6 => 'sessenta',
            7 => 'setenta',
            8 => 'oitenta',
            9 => 'noventa',
        ];

        $centenas = [
            1 => 'cento',
            2 => 'duzentos',
            3 => 'trezentos',
            4 => 'quatrocentos',
            5 => 'quinhentos',
            6 => 'seiscentos',
            7 => 'setecentos',
            8 => 'oitocentos',
            9 => 'novecentos',
        ];

        if ($numero < 20) {
            return $unidades[$numero];
        }

        if ($numero < 100) {
            $dezena = (int) floor($numero / 10);
            $resto = $numero % 10;
            return $dezenas[$dezena] . ($resto ? ' e ' . $unidades[$resto] : '');
        }

        if ($numero === 100) {
            return 'cem';
        }

        if ($numero < 1000) {
            $centena = (int) floor($numero / 100);
            $resto = $numero % 100;
            return $centenas[$centena] . ($resto ? ' e ' . $this->numeroPorExtenso($resto) : '');
        }

        if ($numero < 1000000) {
            $milhar = (int) floor($numero / 1000);
            $resto = $numero % 1000;
            $prefixo = $milhar === 1 ? 'mil' : $this->numeroPorExtenso($milhar) . ' mil';
            if ($resto === 0) {
                return $prefixo;
            }
            return $prefixo . ($resto < 100 ? ' e ' : ', ') . $this->numeroPorExtenso($resto);
        }

        $milhoes = (int) floor($numero / 1000000);
        $resto = $numero % 1000000;
        $prefixo = $milhoes === 1 ? 'um milhão' : $this->numeroPorExtenso($milhoes) . ' milhões';
        if ($resto === 0) {
            return $prefixo;
        }
        return $prefixo . ($resto < 100 ? ' e ' : ', ') . $this->numeroPorExtenso($resto);
    }

    private function buildEmpresaLogoHtml(array $empresa): string
    {
        $logoRelPath = trim((string) ($empresa['emp_logo'] ?? ''));
        if ($logoRelPath === '') {
            return '';
        }

        $logoPath = WRITEPATH . $logoRelPath;
        if (!is_file($logoPath)) {
            return '';
        }

        $bin = @file_get_contents($logoPath);
        if ($bin === false) {
            return '';
        }

        $mime = mime_content_type($logoPath) ?: 'image/png';
        $b64 = base64_encode($bin);
        return '<img src="data:' . $mime . ';base64,' . $b64 . '" alt="Logo" style="height:48px; max-width:220px;">';
    }
}
