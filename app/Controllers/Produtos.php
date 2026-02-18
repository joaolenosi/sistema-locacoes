<?php

namespace App\Controllers;

use App\Models\ProdutoModel;

class Produtos extends BaseController
{
    public function index(): string
    {
        $produtoModel = new ProdutoModel();

        $produtos = $produtoModel
            ->where('pro_empresa_id', get_empresa_id())
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Listagem de Produtos',
            'produtos' => $produtos,
        ];
        
        try {
            return view('admin/produtos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $produtoModel = new ProdutoModel();
            $produtos = $produtoModel
                ->where('pro_empresa_id', get_empresa_id())
                ->orderBy('created_at', 'DESC')
                ->findAll();
            return $this->response->setJSON(['success' => true, 'data' => $produtos]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao listar produtos.']);
        }
    }

    public function editar($id)
    {
        try {
            $produtoModel = new ProdutoModel();
            $produto = $produtoModel->find((int) $id);
            if (!$produto) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Produto não encontrado.']);
            }
            return $this->response->setJSON(['success' => true, 'data' => $produto]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar produto.']);
        }
    }

    public function criar()
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida. Faça login novamente.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeProdutoPayload($payload);
            $err = $this->validateProdutoPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $data['pro_empresa_id'] = $empresaId;

            $produtoModel = new ProdutoModel();
            $id = $produtoModel->insert($data, true);
            if (!$id) {
                $errors = $produtoModel->errors();
                if ($errors) {
                    log_message('error', 'Erros de validação ao cadastrar produto: ' . json_encode($errors));
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Erro de validação: ' . implode(', ', $errors)]);
                }
                log_message('error', 'Falha ao inserir produto no banco de dados. Dados: ' . json_encode($data));
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível cadastrar o produto.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Produto cadastrado com sucesso.', 'id' => $id]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao cadastrar produto: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao cadastrar produto.']);
        }
    }

    public function atualizar($id)
    {
        try {
            $produtoModel = new ProdutoModel();
            $existing = $produtoModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Produto não encontrado.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeProdutoPayload($payload);
            $err = $this->validateProdutoPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $data['pro_empresa_id'] = get_empresa_id();

            $ok = $produtoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível atualizar o produto.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Produto atualizado com sucesso.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao atualizar produto.']);
        }
    }

    private function normalizeProdutoPayload(array $payload): array
    {
        $toIntOrNull = static function ($v) {
            if ($v === '' || $v === null) return null;
            return (int) preg_replace('/\D/', '', (string) $v);
        };

        $toBoolInt = static function ($v) {
            if ($v === '' || $v === null) return 0;
            return ((string) $v === '1' || (string) $v === 'true' || (string) $v === 'on') ? 1 : 0;
        };

        $toMoneyOrNull = static function ($v) {
            if ($v === '' || $v === null) return null;
            $raw = trim((string) $v);
            $raw = str_replace([' ', 'R$', 'r$'], '', $raw);
            if (strpos($raw, ',') !== false) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            }
            $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
            return $raw !== '' ? (float) $raw : null;
        };

        $ativo = $toBoolInt($payload['pro_ativo'] ?? 1);

        return [
            'pro_nome' => trim((string) ($payload['pro_nome'] ?? '')),
            'pro_categoria' => trim((string) ($payload['pro_categoria'] ?? '')) ?: null,
            'pro_marca' => trim((string) ($payload['pro_marca'] ?? '')) ?: null,
            'pro_sku' => trim((string) ($payload['pro_sku'] ?? '')) ?: null,
            'pro_preco_custo' => $toMoneyOrNull($payload['pro_preco_custo'] ?? null),
            'pro_preco_venda' => $toMoneyOrNull($payload['pro_preco_venda'] ?? null),
            'pro_estoque_atual' => $toIntOrNull($payload['pro_estoque_atual'] ?? null),
            'pro_estoque_minimo' => $toIntOrNull($payload['pro_estoque_minimo'] ?? null),
            'pro_controlado' => $toBoolInt($payload['pro_controlado'] ?? 0),
            'pro_intervalo_km' => $toIntOrNull($payload['pro_intervalo_km'] ?? null),
            'pro_ativo' => $ativo,
        ];
    }

    private function validateProdutoPayload(array $data): ?string
    {
        if (($data['pro_nome'] ?? '') === '') return 'Informe o nome.';
        // preço venda é essencial no plano (mas pode ser nulo se usuário quiser); validamos se vier preenchido
        return null;
    }
}
