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

        $empresaId = get_empresa_id();
        $locacoes = $locacaoModel
            ->builderWithJoins()
            ->where('locacoes.loc_empresa_id', $empresaId)
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
                ->where('locacoes.loc_empresa_id', get_empresa_id())
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
                ->where('locacoes.loc_empresa_id', get_empresa_id())
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
            
            // Remover campos que não devem ser processados
            unset($payload['locacao_id']); // Não está no allowedFields
            unset($payload['loc_cli_display']); // Campo apenas para exibição
            unset($payload['loc_vei_display']); // Campo apenas para exibição
            unset($payload['loc_tempo_minimo']); // Campo apenas para cálculo

            $data = $this->normalizeLocacaoPayload($payload);
            $validationError = $this->validateLocacaoPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['loc_empresa_id'] = get_empresa_id();

            // calcular total (fallback simples)
            if (!array_key_exists('loc_valor_total', $data) || $data['loc_valor_total'] === null) {
                $data['loc_valor_total'] = (float) ($data['loc_valor_locacao'] ?? 0);
            }

            $locacaoModel = new LocacaoModel();
            
            // Tentar inserir
            $id = $locacaoModel->insert($data, true);
            
            if (!$id) {
                $errors = $locacaoModel->errors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Não foi possível cadastrar a locação.';
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors,
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locação cadastrada com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            // Em desenvolvimento, retornar mensagem de erro detalhada
            $errorMessage = 'Erro ao cadastrar locação.';
            if (ENVIRONMENT !== 'production') {
                $errorMessage .= ' ' . $e->getMessage();
            }
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $errorMessage,
                'error' => ENVIRONMENT !== 'production' ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
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

            $data['loc_empresa_id'] = get_empresa_id();

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

        // Normalizar valores monetários - garantir que sejam float ou null
        $valorLocacao = $this->parseMoney($payload['loc_valor_locacao'] ?? null);
        $valorCaucao = $this->parseMoney($payload['loc_valor_caucao'] ?? null);
        $valorTotal = $this->parseMoney($payload['loc_valor_total'] ?? null);
        $taxaJuros = $this->parseMoney($payload['loc_taxa_juros'] ?? null);
        $taxaMulta = $this->parseMoney($payload['loc_taxa_multa'] ?? null);

        return [
            'loc_cli_id' => (int) ($payload['loc_cli_id'] ?? 0),
            'loc_vei_id' => (int) ($payload['loc_vei_id'] ?? 0),
            'loc_data_inicio' => (string) ($payload['loc_data_inicio'] ?? ''),
            'loc_data_fim_prevista' => (string) ($payload['loc_data_fim_prevista'] ?? ''),
            'loc_data_fim_real' => (!empty($payload['loc_data_fim_real']) ? (string) $payload['loc_data_fim_real'] : null),
            'loc_status' => $status,
            'loc_valor_locacao' => $valorLocacao !== null ? (float) $valorLocacao : 0.0,
            'loc_valor_caucao' => $valorCaucao !== null ? (float) $valorCaucao : null,
            'loc_valor_total' => $valorTotal !== null ? (float) $valorTotal : null,
            'loc_recorrencia_pagamento' => $rec !== '' ? $rec : null,
            'loc_data_inicio_pagamento' => (!empty($payload['loc_data_inicio_pagamento']) ? (string) $payload['loc_data_inicio_pagamento'] : null),
            'loc_taxa_juros' => $taxaJuros !== null ? (float) $taxaJuros : null,
            'loc_taxa_multa' => $taxaMulta !== null ? (float) $taxaMulta : null,
            'loc_km_retirada' => (!empty($payload['loc_km_retirada']) && $payload['loc_km_retirada'] !== null)
                ? (int) preg_replace('/\D/', '', (string) $payload['loc_km_retirada'])
                : null,
            'loc_km_devolucao' => null, // Sempre null na criação
            'loc_responsavel_entrega' => null, // Sempre null na criação
            'loc_responsavel_devolucao' => null, // Sempre null na criação
            'loc_obs_operacionais' => (!empty($payload['loc_obs_operacionais']) ? trim((string) $payload['loc_obs_operacionais']) : null),
            'loc_obs_financeiras' => (!empty($payload['loc_obs_financeiras']) ? trim((string) $payload['loc_obs_financeiras']) : null),
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

    /**
     * Buscar veículos que já foram locados por um cliente específico
     * Retorna veículos únicos ordenados por data mais recente
     */
    public function veiculosPorCliente($cliId)
    {
        try {
            $locacaoModel = new LocacaoModel();
            $veiculoModel = new VeiculoModel();
            
            $empresaId = get_empresa_id();
            $cliId = (int) $cliId;
            
            if ($cliId <= 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [],
                ]);
            }

            // Buscar todas as locações do cliente usando builder
            $db = \Config\Database::connect();
            $locacoes = $db->table('locacoes')
                ->select('loc_vei_id, MAX(created_at) as ultima_locacao')
                ->where('loc_empresa_id', $empresaId)
                ->where('loc_cli_id', $cliId)
                ->groupBy('loc_vei_id')
                ->orderBy('ultima_locacao', 'DESC')
                ->get()
                ->getResultArray();

            if (empty($locacoes)) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [],
                ]);
            }

            // Extrair IDs dos veículos mantendo a ordem
            $veiIds = [];
            $ordemVeiculos = [];
            foreach ($locacoes as $index => $loc) {
                $veiId = (int) $loc['loc_vei_id'];
                $veiIds[] = $veiId;
                $ordemVeiculos[$veiId] = $index;
            }

            // Buscar dados completos dos veículos usando builder
            $veiculos = [];
            if (!empty($veiIds)) {
                $veiculosRaw = $db->table('veiculos')
                    ->where('vei_empresa_id', $empresaId)
                    ->whereIn('id', $veiIds)
                    ->get()
                    ->getResultArray();

                // Manter ordem baseada na última locação
                foreach ($veiIds as $veiId) {
                    foreach ($veiculosRaw as $veiculo) {
                        if ((int) $veiculo['id'] === $veiId) {
                            $veiculos[] = $veiculo;
                            break;
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $veiculos,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar veículos do cliente.',
            ]);
        }
    }

    /**
     * Buscar cliente que mais recentemente locou um veículo específico
     */
    public function clientePorVeiculo($veiId)
    {
        try {
            $locacaoModel = new LocacaoModel();
            $clienteModel = new ClienteModel();
            
            $empresaId = get_empresa_id();
            $veiId = (int) $veiId;
            
            if ($veiId <= 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null,
                ]);
            }

            // Buscar última locação do veículo
            $locacao = $locacaoModel
                ->where('loc_empresa_id', $empresaId)
                ->where('loc_vei_id', $veiId)
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$locacao || !isset($locacao['loc_cli_id'])) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null,
                ]);
            }

            // Buscar dados do cliente
            $cliente = $clienteModel->find((int) $locacao['loc_cli_id']);

            if (!$cliente) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => null,
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $cliente,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar cliente do veículo.',
            ]);
        }
    }
}
