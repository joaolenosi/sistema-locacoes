<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class Locatarios extends BaseController
{
    public function index(): string
    {
        $clienteModel = new ClienteModel();

        // Listagem (mais recente primeiro)
        $locatarios = $clienteModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Listagem de Locatários',
            // Dados iniciais (primeira renderização, sem consulta na view)
            'locatarios' => $locatarios,
        ];

        try {
            return view('admin/locatarios/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $clienteModel = new ClienteModel();
            $locatarios = $clienteModel->orderBy('created_at', 'DESC')->findAll();
            return $this->response->setJSON([
                'success' => true,
                'data' => $locatarios,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar locatários.',
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $clienteModel = new ClienteModel();
            $locatario = $clienteModel->find((int) $id);
            if (!$locatario) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locatário não encontrado.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $locatario,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar locatário.',
            ]);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();

            $data = $this->normalizeClientePayload($payload);
            $validationError = $this->validateClientePayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['cli_empresa_id'] = 1; // fixo (por enquanto)
            if (!array_key_exists('cli_ativo', $data) || $data['cli_ativo'] === null || $data['cli_ativo'] === '') {
                $data['cli_ativo'] = 1;
            }

            $clienteModel = new ClienteModel();
            $id = $clienteModel->insert($data, true);

            if (!$id) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível cadastrar o locatário.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locatário cadastrado com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao cadastrar locatário.',
            ]);
        }
    }

    public function atualizar($id)
    {
        try {
            $clienteModel = new ClienteModel();
            $existing = $clienteModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locatário não encontrado.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeClientePayload($payload);
            $validationError = $this->validateClientePayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            // manter empresa fixa
            $data['cli_empresa_id'] = 1;

            $ok = $clienteModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar o locatário.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locatário atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar locatário.',
            ]);
        }
    }

    private function normalizeClientePayload(array $payload): array
    {
        $tipo = (string) ($payload['cli_tipo_pessoa'] ?? 'fisica');
        $allowedTipos = ['fisica', 'juridica', 'estrangeiro'];
        if (!in_array($tipo, $allowedTipos, true)) {
            $tipo = 'fisica';
        }

        $cpfCnpj = null;
        if (array_key_exists('cli_cpf_cnpj', $payload) && $payload['cli_cpf_cnpj'] !== '' && $payload['cli_cpf_cnpj'] !== null) {
            $cpfCnpj = preg_replace('/\D/', '', (string) $payload['cli_cpf_cnpj']) ?: null;
        }

        $email = null;
        if (array_key_exists('cli_email', $payload) && $payload['cli_email'] !== '' && $payload['cli_email'] !== null) {
            $email = trim((string) $payload['cli_email']);
        }

        $ativo = null;
        if (array_key_exists('cli_ativo', $payload)) {
            $raw = $payload['cli_ativo'];
            // checkbox: "on" / "1" / 1
            $ativo = ($raw === 'on' || $raw === '1' || $raw === 1 || $raw === true) ? 1 : 0;
        }

        return [
            'cli_tipo_pessoa' => $tipo,
            'cli_nome' => trim((string) ($payload['cli_nome'] ?? '')),
            'cli_cpf_cnpj' => $cpfCnpj,
            'cli_data_nascimento' => ($payload['cli_data_nascimento'] ?? '') ?: null,
            'cli_email' => $email,
            'cli_telefone' => preg_replace('/\D/', '', (string) ($payload['cli_telefone'] ?? '')) ?: null,
            'cli_whatsapp' => preg_replace('/\D/', '', (string) ($payload['cli_whatsapp'] ?? '')) ?: null,
            'cli_cnh_numero' => preg_replace('/\D/', '', (string) ($payload['cli_cnh_numero'] ?? '')) ?: null,
            'cli_cnh_validade' => ($payload['cli_cnh_validade'] ?? '') ?: null,
            'cli_cep' => preg_replace('/\D/', '', (string) ($payload['cli_cep'] ?? '')) ?: null,
            'cli_estado' => trim((string) ($payload['cli_estado'] ?? '')) ?: null,
            'cli_cidade' => trim((string) ($payload['cli_cidade'] ?? '')) ?: null,
            'cli_bairro' => trim((string) ($payload['cli_bairro'] ?? '')) ?: null,
            'cli_rua' => trim((string) ($payload['cli_rua'] ?? '')) ?: null,
            'cli_numero' => trim((string) ($payload['cli_numero'] ?? '')) ?: null,
            'cli_complemento' => trim((string) ($payload['cli_complemento'] ?? '')) ?: null,
            'cli_obs' => trim((string) ($payload['cli_obs'] ?? '')) ?: null,
            'cli_ativo' => $ativo,
        ];
    }

    private function validateClientePayload(array $data): ?string
    {
        if (($data['cli_tipo_pessoa'] ?? '') === '') return 'Informe o tipo de pessoa.';
        if (($data['cli_nome'] ?? '') === '') return 'Informe o nome.';

        $tipo = (string) ($data['cli_tipo_pessoa'] ?? 'fisica');
        $cpfCnpj = (string) ($data['cli_cpf_cnpj'] ?? '');

        // Estrangeiro pode não ter CPF/CNPJ
        if ($tipo !== 'estrangeiro') {
            if ($cpfCnpj === '') return 'Informe o CPF/CNPJ.';
            $len = strlen($cpfCnpj);
            if ($len !== 11 && $len !== 14) return 'CPF/CNPJ inválido.';
        } else {
            // se preencher, valida tamanho também
            if ($cpfCnpj !== '') {
                $len = strlen($cpfCnpj);
                if ($len !== 11 && $len !== 14) return 'CPF/CNPJ inválido.';
            }
        }

        $email = (string) ($data['cli_email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido.';
        }

        return null;
    }
}
