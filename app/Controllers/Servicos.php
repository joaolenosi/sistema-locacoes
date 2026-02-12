<?php

namespace App\Controllers;

use App\Models\ServicoModel;

class Servicos extends BaseController
{
    public function index(): string
    {
        $servicoModel = new ServicoModel();

        $servicos = $servicoModel
            ->where('ser_empresa_id', get_empresa_id())
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Listagem de Serviços',
            'servicos' => $servicos,
        ];
        
        try {
            return view('admin/servicos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $servicoModel = new ServicoModel();
            $servicos = $servicoModel
                ->where('ser_empresa_id', get_empresa_id())
                ->orderBy('created_at', 'DESC')
                ->findAll();
            return $this->response->setJSON(['success' => true, 'data' => $servicos]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao listar serviços.']);
        }
    }

    public function editar($id)
    {
        try {
            $servicoModel = new ServicoModel();
            $servico = $servicoModel->find((int) $id);
            if (!$servico) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Serviço não encontrado.']);
            }
            return $this->response->setJSON(['success' => true, 'data' => $servico]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar serviço.']);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();
            $data = $this->normalizeServicoPayload($payload);
            $err = $this->validateServicoPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $data['ser_empresa_id'] = get_empresa_id();

            $servicoModel = new ServicoModel();
            $id = $servicoModel->insert($data, true);
            if (!$id) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível cadastrar o serviço.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Serviço cadastrado com sucesso.', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao cadastrar serviço.']);
        }
    }

    public function atualizar($id)
    {
        try {
            $servicoModel = new ServicoModel();
            $existing = $servicoModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Serviço não encontrado.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeServicoPayload($payload);
            $err = $this->validateServicoPayload($data);
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            $data['ser_empresa_id'] = get_empresa_id();

            $ok = $servicoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível atualizar o serviço.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Serviço atualizado com sucesso.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao atualizar serviço.']);
        }
    }

    private function normalizeServicoPayload(array $payload): array
    {
        $toBoolInt = static function ($v) {
            if ($v === '' || $v === null) return 0;
            return ((string) $v === '1' || (string) $v === 'true' || (string) $v === 'on') ? 1 : 0;
        };

        $toIntOrNull = static function ($v) {
            if ($v === '' || $v === null) return null;
            return (int) preg_replace('/\D/', '', (string) $v);
        };

        $toMoneyOrNull = static function ($v) {
            if ($v === '' || $v === null) return null;
            $raw = trim((string) $v);
            $raw = str_replace([' ', 'R$', 'r$'], '', $raw);
            if (str_contains($raw, ',')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            }
            $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
            return $raw !== '' ? (float) $raw : null;
        };

        return [
            'ser_nome' => trim((string) ($payload['ser_nome'] ?? '')),
            'ser_categoria' => trim((string) ($payload['ser_categoria'] ?? '')) ?: null,
            'ser_descricao' => trim((string) ($payload['ser_descricao'] ?? '')) ?: null,
            'ser_preco_padrao' => $toMoneyOrNull($payload['ser_preco_padrao'] ?? null),
            'ser_controlado' => $toBoolInt($payload['ser_controlado'] ?? 0),
            'ser_intervalo_km' => $toIntOrNull($payload['ser_intervalo_km'] ?? null),
            'ser_ativo' => $toBoolInt($payload['ser_ativo'] ?? 1),
        ];
    }

    private function validateServicoPayload(array $data): ?string
    {
        if (($data['ser_nome'] ?? '') === '') return 'Informe o nome.';
        return null;
    }
}
