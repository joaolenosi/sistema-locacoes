<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\LocacaoModel;
use App\Models\VeiculoModel;

class Locoes extends BaseController
{
    public function index(): string
    {
        $locacaoModel = new LocacaoModel();

        $locacoes = $locacaoModel
            ->builderWithJoins()
            ->where('locacoes.loc_empresa_id', 1)
            ->orderBy('locacoes.created_at', 'DESC')
            ->get()
            ->getResultArray();

        [$entradas, $saidas, $emAtraso] = $this->calcularKpis($locacoes);

        $data = [
            'title' => 'Listagem de Locações',
            // KPIs (dinâmico, sem consulta em view)
            'entradas' => $entradas,
            'saidas' => $saidas,
            'em_atraso' => $emAtraso,

            // Dados iniciais (primeira renderização, sem consulta na view)
            'locacoes' => $locacoes,
        ];
        
        try {
            return view('admin/locacoes/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $locacaoModel = new LocacaoModel();
            $rows = $locacaoModel
                ->builderWithJoins()
                ->where('locacoes.loc_empresa_id', 1)
                ->orderBy('locacoes.created_at', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar locações.',
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $locacaoModel = new LocacaoModel();
            $row = $locacaoModel
                ->builderWithJoins()
                ->where('locacoes.loc_empresa_id', 1)
                ->where('locacoes.id', (int) $id)
                ->get()
                ->getRowArray();

            if (!$row) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locação não encontrada.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $row,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar locação.',
            ]);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();

            $data = $this->normalizeLocacaoPayload($payload);
            $validationError = $this->validateLocacaoPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['loc_empresa_id'] = 1; // fixo (por enquanto)

            // calcular total (fallback simples)
            if (!array_key_exists('loc_valor_total', $data) || $data['loc_valor_total'] === null) {
                $data['loc_valor_total'] = (float) ($data['loc_valor_locacao'] ?? 0);
            }

            $locacaoModel = new LocacaoModel();
            $id = $locacaoModel->insert($data, true);
            if (!$id) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível cadastrar a locação.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locação cadastrada com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao cadastrar locação.',
            ]);
        }
    }

    public function atualizar($id)
    {
        try {
            $locacaoModel = new LocacaoModel();
            $existing = $locacaoModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locação não encontrada.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeLocacaoPayload($payload);
            $validationError = $this->validateLocacaoPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['loc_empresa_id'] = 1; // manter empresa fixa

            if (!array_key_exists('loc_valor_total', $data) || $data['loc_valor_total'] === null) {
                $data['loc_valor_total'] = (float) ($data['loc_valor_locacao'] ?? 0);
            }

            $ok = $locacaoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar a locação.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locação atualizada com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar locação.',
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $locacoes
     * @return array{0:int, 1:int, 2:int}
     */
    private function calcularKpis(array $locacoes): array
    {
        $entradas = 0;
        $saidas = 0;
        $emAtraso = 0;

        foreach ($locacoes as $l) {
            $status = (string) ($l['loc_status'] ?? 'reservada');
            if (in_array($status, ['reservada', 'ativa'], true)) {
                $entradas++;
            }
            if (in_array($status, ['finalizada', 'cancelada'], true)) {
                $saidas++;
            }
            if (in_array($status, ['atrasada', 'inadimplente'], true)) {
                $emAtraso++;
            }
        }

        return [$entradas, $saidas, $emAtraso];
    }

    private function normalizeLocacaoPayload(array $payload): array
    {
        $status = strtolower(trim((string) ($payload['loc_status'] ?? 'reservada')));
        $allowedStatus = ['reservada', 'ativa', 'atrasada', 'finalizada', 'cancelada', 'inadimplente'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'reservada';
        }

        $rec = (string) ($payload['loc_recorrencia_pagamento'] ?? '');
        $allowedRec = ['diaria', 'semanal', 'quinzenal', 'mensal'];
        if ($rec !== '' && !in_array($rec, $allowedRec, true)) {
            $rec = '';
        }

        $valoresRecebidos = 0;
        if (array_key_exists('loc_valores_recebidos', $payload)) {
            $raw = $payload['loc_valores_recebidos'];
            $valoresRecebidos = ($raw === 'on' || $raw === '1' || $raw === 1 || $raw === true) ? 1 : 0;
        }

        return [
            'loc_cli_id' => (int) ($payload['loc_cli_id'] ?? 0),
            'loc_vei_id' => (int) ($payload['loc_vei_id'] ?? 0),
            'loc_data_inicio' => (string) ($payload['loc_data_inicio'] ?? ''),
            'loc_data_fim_prevista' => (string) ($payload['loc_data_fim_prevista'] ?? ''),
            'loc_data_fim_real' => ($payload['loc_data_fim_real'] ?? '') ?: null,
            'loc_status' => $status,
            'loc_valor_locacao' => $this->parseMoney($payload['loc_valor_locacao'] ?? null) ?? 0,
            'loc_valor_caucao' => $this->parseMoney($payload['loc_valor_caucao'] ?? null),
            'loc_valor_total' => $this->parseMoney($payload['loc_valor_total'] ?? null),
            'loc_recorrencia_pagamento' => $rec !== '' ? $rec : null,
            'loc_data_inicio_pagamento' => ($payload['loc_data_inicio_pagamento'] ?? '') ?: null,
            'loc_taxa_juros' => $this->parseMoney($payload['loc_taxa_juros'] ?? null),
            'loc_taxa_multa' => $this->parseMoney($payload['loc_taxa_multa'] ?? null),
            'loc_km_retirada' => ($payload['loc_km_retirada'] !== '' && $payload['loc_km_retirada'] !== null)
                ? (int) preg_replace('/\D/', '', (string) $payload['loc_km_retirada'])
                : null,
            'loc_obs_operacionais' => trim((string) ($payload['loc_obs_operacionais'] ?? '')) ?: null,
            'loc_obs_financeiras' => trim((string) ($payload['loc_obs_financeiras'] ?? '')) ?: null,
            'loc_valores_recebidos' => $valoresRecebidos,
        ];
    }

    private function validateLocacaoPayload(array $data): ?string
    {
        if ((int) ($data['loc_cli_id'] ?? 0) <= 0) return 'Selecione o locatário.';
        if ((int) ($data['loc_vei_id'] ?? 0) <= 0) return 'Selecione o veículo.';
        if (($data['loc_data_inicio'] ?? '') === '') return 'Informe a data de início.';
        if (($data['loc_data_fim_prevista'] ?? '') === '') return 'Informe a data fim prevista.';
        if (!isset($data['loc_valor_locacao']) || (float) $data['loc_valor_locacao'] <= 0) return 'Informe o valor da locação.';

        // Cliente existe?
        $clienteModel = new ClienteModel();
        $cli = $clienteModel->find((int) $data['loc_cli_id']);
        if (!$cli) return 'Locatário inválido.';

        // Veículo existe?
        $veiModel = new VeiculoModel();
        $vei = $veiModel->find((int) $data['loc_vei_id']);
        if (!$vei) return 'Veículo inválido.';

        return null;
    }

    private function parseMoney($value): ?float
    {
        if ($value === null) return null;
        if ($value === '') return null;

        $raw = trim((string) $value);
        $raw = str_replace([' ', 'R$', 'r$'], '', $raw);

        // Aceita tanto \"1234.56\" quanto \"1.234,56\"
        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
        if ($raw === '') return null;

        return (float) $raw;
    }
}
