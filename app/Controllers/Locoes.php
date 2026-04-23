<?php

namespace App\Controllers;

use App\Models\CategoriaFinanceiraModel;
use App\Models\ClienteModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\LocacaoModel;
use App\Models\VeiculoModel;
use CodeIgniter\Database\BaseBuilder;

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

            // Atualizar status do veículo para locado
            try {
                $veiId = (int) ($data['loc_vei_id'] ?? 0);
                if ($veiId > 0) {
                    $veiculoModel = new VeiculoModel();
                    $veiculoModel->update($veiId, ['vei_status' => 'locado']);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Erro ao atualizar status do veículo após criar locação: ' . $e->getMessage());
            }

            // Criar lançamento de receita para caução se necessário
            try {
                $this->criarLancamentoReceitaCaucao((int) $id, $data);
            } catch (\Throwable $e) {
                // Logar erro mas não impedir o salvamento da locação
                log_message('error', 'Erro ao criar lançamento de receita após criar locação: ' . $e->getMessage());
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

            // Atualizar status do veículo conforme status da locação
            try {
                $veiId = (int) ($data['loc_vei_id'] ?? $existing['loc_vei_id'] ?? 0);
                if ($veiId > 0) {
                    $veiculoModel = new VeiculoModel();
                    $novoStatus = (string) ($data['loc_status'] ?? $existing['loc_status'] ?? 'reservada');
                    $veiStatus = in_array($novoStatus, ['finalizada', 'cancelada'], true) ? 'disponivel' : 'locado';
                    $veiculoModel->update($veiId, ['vei_status' => $veiStatus]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Erro ao atualizar status do veículo após atualizar locação: ' . $e->getMessage());
            }

            // Verificar se precisa criar lançamento de receita para caução
            $valoresRecebidos = (int) ($data['loc_valores_recebidos'] ?? 0);
            $valorCaucao = $data['loc_valor_caucao'] ?? null;
            
            if ($valoresRecebidos === 1 && $valorCaucao !== null && (float) $valorCaucao > 0) {
                try {
                    // Verificar se já existe lançamento para esta locação com categoria "Pagamento Caução"
                    $lancamentoModel = new LancamentoFinanceiroModel();
                    $categoriaModel = new CategoriaFinanceiraModel();
                    
                    // Buscar categoria "Pagamento Caução"
                    $categoria = $categoriaModel
                        ->where('cat_nome', 'Pagamento Caução')
                        ->where('cat_tipo', 'receita')
                        ->first();
                    
                    if ($categoria && isset($categoria['id'])) {
                        $categoriaId = (int) $categoria['id'];
                        
                        // Verificar se já existe lançamento vinculado a esta locação com esta categoria
                        $lancamentoExistente = $lancamentoModel
                            ->where('lan_locacao_id', (int) $id)
                            ->where('lan_categoria_id', $categoriaId)
                            ->where('lan_tipo', 'receita')
                            ->first();
                        
                        // Só criar se não existir
                        if (!$lancamentoExistente) {
                            $this->criarLancamentoReceitaCaucao((int) $id, $data);
                        }
                    } else {
                        // Se categoria não existe, criar categoria e lançamento
                        $this->criarLancamentoReceitaCaucao((int) $id, $data);
                    }
                } catch (\Throwable $e) {
                    // Logar erro mas não impedir a atualização da locação
                    log_message('error', 'Erro ao criar lançamento de receita após atualizar locação: ' . $e->getMessage());
                }
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

    public function excluir($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Sessão inválida.',
                ]);
            }

            $locacaoModel = new LocacaoModel();
            $locacao = $locacaoModel
                ->where('loc_empresa_id', $empresaId)
                ->where('id', (int) $id)
                ->first();

            if (!$locacao) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locação não encontrada.',
                ]);
            }

            try {
                if (!$locacaoModel->delete((int) $id)) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => 'Não foi possível excluir a locação.',
                    ]);
                }
            } catch (\Throwable $e) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível excluir a locação. Verifique se não há registros financeiros ou contratos vinculados.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locação excluída com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao excluir locação.',
            ]);
        }
    }

    public function finalizar($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Sessão inválida.',
                ]);
            }

            $locacaoModel = new LocacaoModel();
            $locacao = $locacaoModel
                ->where('loc_empresa_id', $empresaId)
                ->where('id', (int) $id)
                ->first();

            if (!$locacao) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locação não encontrada.',
                ]);
            }

            $statusAtual = (string) ($locacao['loc_status'] ?? 'reservada');
            if (in_array($statusAtual, ['finalizada', 'cancelada'], true)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Esta locação já está finalizada ou cancelada.',
                ]);
            }

            // Verificar cobranças pendentes desta locação
            $lancamentoModel = new LancamentoFinanceiroModel();
            $pendentesCount = $lancamentoModel
                ->where('lan_empresa_id', $empresaId)
                ->where('lan_locacao_id', (int) $id)
                ->where('lan_tipo', 'receita')
                ->where('lan_status', 'pendente')
                ->countAllResults();

            $acaoCobrancas = (string) ($this->request->getPost('acao_cobrancas') ?? '');
            $acaoCobrancas = trim($acaoCobrancas);

            // Se houver cobranças pendentes e o frontend ainda não escolheu o que fazer
            if ($pendentesCount > 0 && $acaoCobrancas !== 'quitar_pendentes') {
                return $this->response->setJSON([
                    'success' => false,
                    'requiresAction' => true,
                    'pendentes_count' => $pendentesCount,
                    'message' => 'Existem cobranças pendentes para esta locação.',
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // Se usuário escolheu quitar pendentes, marcar todas como pagas
            if ($pendentesCount > 0 && $acaoCobrancas === 'quitar_pendentes') {
                $hoje = date('Y-m-d');
                $builder = $lancamentoModel
                    ->where('lan_empresa_id', $empresaId)
                    ->where('lan_locacao_id', (int) $id)
                    ->where('lan_tipo', 'receita')
                    ->where('lan_status', 'pendente')
                    ->set('lan_status', 'pago')
                    ->set('lan_data_pagamento', $hoje)
                    ->set('lan_valor_pago', 'lan_valor', false);
                $builder->update();
            }

            // Finalizar locação
            $hoje = date('Y-m-d');
            $updateData = [
                'loc_status' => 'finalizada',
            ];
            if (empty($locacao['loc_data_fim_real'])) {
                $updateData['loc_data_fim_real'] = $hoje;
            }
            $locacaoModel->update((int) $id, $updateData);

            // Liberar veículo (marcar como disponível)
            $veiId = (int) ($locacao['loc_vei_id'] ?? 0);
            if ($veiId > 0) {
                $veiculoModel = new VeiculoModel();
                $veiculoModel->update($veiId, ['vei_status' => 'disponivel']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Erro ao finalizar locação.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locação finalizada com sucesso.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao finalizar locação: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao finalizar locação.',
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
        if (strpos($raw, ',') !== false) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        $raw = preg_replace('/[^0-9.]/', '', $raw) ?? '';
        if ($raw === '') return null;

        return (float) $raw;
    }

    /**
     * Busca ou cria a categoria "Pagamento Caução" do tipo receita
     * @return int ID da categoria
     */
    private function criarOuBuscarCategoriaPagamentoCaucao(): int
    {
        try {
            $categoriaModel = new CategoriaFinanceiraModel();
            
            // Buscar categoria existente
            $categoria = $categoriaModel
                ->where('cat_nome', 'Pagamento Caução')
                ->where('cat_tipo', 'receita')
                ->first();
            
            if ($categoria && isset($categoria['id'])) {
                return (int) $categoria['id'];
            }
            
            // Criar nova categoria se não existir
            $data = [
                'cat_nome' => 'Pagamento Caução',
                'cat_tipo' => 'receita',
                'cat_padrao' => 0,
            ];
            
            $id = $categoriaModel->insert($data, true);
            if (!$id) {
                log_message('error', 'Erro ao criar categoria "Pagamento Caução": ' . json_encode($categoriaModel->errors()));
                throw new \Exception('Não foi possível criar a categoria.');
            }
            
            return (int) $id;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao buscar/criar categoria Pagamento Caução: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cria um lançamento de receita para o caução da locação
     * @param int $locacaoId ID da locação
     * @param array $data Dados da locação normalizados
     * @return int|null ID do lançamento criado ou null em caso de erro
     */
    private function criarLancamentoReceitaCaucao(int $locacaoId, array $data): ?int
    {
        try {
            // Verificar se deve criar o lançamento
            $valoresRecebidos = (int) ($data['loc_valores_recebidos'] ?? 0);
            $valorCaucao = $data['loc_valor_caucao'] ?? null;
            
            if ($valoresRecebidos !== 1 || $valorCaucao === null || (float) $valorCaucao <= 0) {
                return null;
            }
            
            // Buscar ou criar categoria
            $categoriaId = $this->criarOuBuscarCategoriaPagamentoCaucao();
            
            // Preparar dados do lançamento
            $dataLancamento = date('Y-m-d');
            $dataVencimento = !empty($data['loc_data_inicio']) ? $data['loc_data_inicio'] : $dataLancamento;
            
            $lancamentoData = [
                'lan_empresa_id' => get_empresa_id(),
                'lan_tipo' => 'receita',
                'lan_categoria_id' => $categoriaId,
                'lan_descricao' => 'Lançamento automático feito pelo módulo de locação.',
                'lan_data_lancamento' => $dataLancamento,
                'lan_data_vencimento' => $dataVencimento,
                'lan_data_pagamento' => $dataLancamento,
                'lan_valor' => (float) $valorCaucao,
                'lan_valor_pago' => (float) $valorCaucao,
                'lan_status' => 'pago',
                'lan_locacao_id' => $locacaoId,
                'lan_veiculo_id' => (int) ($data['loc_vei_id'] ?? 0) > 0 ? (int) $data['loc_vei_id'] : null,
                'lan_forma_pagamento' => null,
                'lan_referencia' => null,
                'lan_obs' => null,
            ];
            
            $lancamentoModel = new LancamentoFinanceiroModel();
            $lancamentoId = $lancamentoModel->insert($lancamentoData, true);
            
            if (!$lancamentoId) {
                $errors = $lancamentoModel->errors();
                log_message('error', 'Erro ao criar lançamento de receita para caução: ' . json_encode($errors));
                return null;
            }
            
            return (int) $lancamentoId;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao criar lançamento de receita para caução: ' . $e->getMessage());
            return null;
        }
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
