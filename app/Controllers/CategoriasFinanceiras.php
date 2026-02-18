<?php

namespace App\Controllers;

use App\Models\CategoriaFinanceiraModel;

class CategoriasFinanceiras extends BaseController
{
    public function index(): string
    {
        $categoriaModel = new CategoriaFinanceiraModel();

        $categorias = $categoriaModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Listagem de Categorias Financeiras',
            'categorias' => $categorias,
        ];
        
        try {
            return view('admin/categorias-financeiras/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $categoriaModel = new CategoriaFinanceiraModel();
            $categorias = $categoriaModel
                ->orderBy('created_at', 'DESC')
                ->findAll();
            return $this->response->setJSON(['success' => true, 'data' => $categorias]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao listar categorias financeiras.']);
        }
    }

    public function editar($id)
    {
        try {
            $categoriaModel = new CategoriaFinanceiraModel();
            $categoria = $categoriaModel->find((int) $id);
            if (!$categoria) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Categoria não encontrada.']);
            }
            return $this->response->setJSON(['success' => true, 'data' => $categoria]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar categoria.']);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();
            $data = $this->normalizeCategoriaPayload($payload);
            $err = $this->validateCategoriaPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $categoriaModel = new CategoriaFinanceiraModel();
            $id = $categoriaModel->insert($data, true);
            if (!$id) {
                $errors = $categoriaModel->errors();
                if ($errors) {
                    log_message('error', 'Erros de validação ao cadastrar categoria: ' . json_encode($errors));
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Erro de validação: ' . implode(', ', $errors)]);
                }
                log_message('error', 'Falha ao inserir categoria no banco de dados. Dados: ' . json_encode($data));
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível cadastrar a categoria.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Categoria cadastrada com sucesso.', 'id' => $id]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao cadastrar categoria: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao cadastrar categoria.']);
        }
    }

    public function atualizar($id)
    {
        try {
            $categoriaModel = new CategoriaFinanceiraModel();
            $existing = $categoriaModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Categoria não encontrada.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeCategoriaPayload($payload);
            $err = $this->validateCategoriaPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $ok = $categoriaModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível atualizar a categoria.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Categoria atualizada com sucesso.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao atualizar categoria.']);
        }
    }

    private function normalizeCategoriaPayload(array $payload): array
    {
        $toBoolInt = static function ($v) {
            if ($v === '' || $v === null) return 0;
            return ((string) $v === '1' || (string) $v === 'true' || (string) $v === 'on') ? 1 : 0;
        };

        return [
            'cat_nome' => trim((string) ($payload['cat_nome'] ?? '')),
            'cat_tipo' => strtolower(trim((string) ($payload['cat_tipo'] ?? ''))),
            'cat_padrao' => $toBoolInt($payload['cat_padrao'] ?? 0),
        ];
    }

    private function validateCategoriaPayload(array $data): ?string
    {
        if (($data['cat_nome'] ?? '') === '') return 'Informe o nome da categoria.';
        if (!in_array($data['cat_tipo'] ?? '', ['receita', 'despesa'], true)) {
            return 'Informe um tipo válido (receita ou despesa).';
        }
        return null;
    }
}
