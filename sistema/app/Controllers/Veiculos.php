<?php

namespace App\Controllers;

use App\Models\VeiculoModel;

class Veiculos extends BaseController
{
    public function index(): string
    {
        $veiculoModel = new VeiculoModel();

        // Listagem (mais recente primeiro)
        $veiculos = $veiculoModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Cards (dinâmico)
        $totalVeiculos = count($veiculos);
        $veiculosOcupados = 0;
        foreach ($veiculos as $v) {
            if (($v['vei_status'] ?? '') === 'locado') {
                $veiculosOcupados++;
            }
        }

        $data = [
            'title' => 'Listagem de Veículos',
            'total_veiculos' => $totalVeiculos,
            'veiculos_livres' => $totalVeiculos - $veiculosOcupados,
            'veiculos_ocupados' => $veiculosOcupados,
            // Dados iniciais (primeira renderização, sem consulta na view)
            'veiculos' => $veiculos,
        ];
        
        try {
            return view('admin/veiculos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $veiculoModel = new VeiculoModel();
            $veiculos = $veiculoModel->orderBy('created_at', 'DESC')->findAll();
            return $this->response->setJSON([
                'success' => true,
                'data' => $veiculos,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar veículos.',
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $veiculoModel = new VeiculoModel();
            $veiculo = $veiculoModel->find((int) $id);
            if (!$veiculo) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Veículo não encontrado.',
                ]);
            }
            return $this->response->setJSON([
                'success' => true,
                'data' => $veiculo,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar veículo.',
            ]);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();

            $data = $this->normalizeVeiculoPayload($payload);
            $validationError = $this->validateVeiculoPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['vei_empresa_id'] = 1; // fixo (por enquanto)

            $veiculoModel = new VeiculoModel();
            $id = $veiculoModel->insert($data, true);

            if (!$id) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível cadastrar o veículo.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Veículo cadastrado com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao cadastrar veículo.',
            ]);
        }
    }

    public function atualizar($id)
    {
        try {
            $veiculoModel = new VeiculoModel();
            $existing = $veiculoModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Veículo não encontrado.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeVeiculoPayload($payload);
            $validationError = $this->validateVeiculoPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            // manter empresa fixa
            $data['vei_empresa_id'] = 1;

            $ok = $veiculoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar o veículo.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Veículo atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar veículo.',
            ]);
        }
    }

    private function normalizeVeiculoPayload(array $payload): array
    {
        $placa = strtoupper(trim((string) ($payload['vei_placa'] ?? '')));
        $placa = preg_replace('/[^A-Z0-9-]/', '', $placa ?? '') ?? '';

        $status = (string) ($payload['vei_status'] ?? 'disponivel');
        $allowedStatus = ['disponivel', 'locado', 'manutencao', 'inativo'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'disponivel';
        }

        return [
            'vei_tipo' => trim((string) ($payload['vei_tipo'] ?? '')),
            'vei_marca' => trim((string) ($payload['vei_marca'] ?? '')),
            'vei_modelo' => trim((string) ($payload['vei_modelo'] ?? '')),
            'vei_ano' => trim((string) ($payload['vei_ano'] ?? '')),
            'vei_placa' => $placa,
            'vei_cor' => trim((string) ($payload['vei_cor'] ?? '')) ?: null,
            'vei_renavam' => preg_replace('/\D/', '', (string) ($payload['vei_renavam'] ?? '')) ?: null,
            'vei_chassi' => preg_replace('/[^A-Za-z0-9]/', '', (string) ($payload['vei_chassi'] ?? '')) ?: null,
            'vei_data_licenciamento' => $payload['vei_data_licenciamento'] ?: null,
            'vei_km_atual' => ($payload['vei_km_atual'] !== '' && $payload['vei_km_atual'] !== null)
                ? (int) preg_replace('/\D/', '', (string) $payload['vei_km_atual'])
                : null,
            'vei_data_compra' => $payload['vei_data_compra'] ?: null,
            'vei_valor_compra' => ($payload['vei_valor_compra'] !== '' && $payload['vei_valor_compra'] !== null)
                ? (float) $payload['vei_valor_compra']
                : null,
            'vei_status' => $status,
        ];
    }

    private function validateVeiculoPayload(array $data): ?string
    {
        if (($data['vei_tipo'] ?? '') === '') return 'Informe o tipo.';
        if (($data['vei_marca'] ?? '') === '') return 'Informe a marca.';
        if (($data['vei_modelo'] ?? '') === '') return 'Informe o modelo.';
        if (($data['vei_ano'] ?? '') === '') return 'Informe o ano.';
        if (($data['vei_placa'] ?? '') === '') return 'Informe a placa.';
        if (($data['vei_status'] ?? '') === '') return 'Informe o status.';
        return null;
    }
}
