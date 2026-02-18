<?php

namespace App\Controllers;

use App\Models\ManutencaoModel;
use App\Models\VeiculoControleModel;
use App\Models\VeiculoModel;
use App\Models\ServicoModel;
use App\Models\ProdutoModel;
use App\Models\LocacaoModel;

class ManutencaoInteligente extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Manutenção Inteligente',
            'subtitle' => 'Gerencie peças, serviços e prazos com eficiência. Alertas automáticos ajudam você a manter seus veículos sempre em dia, sem surpresas.',
        ];
        
        try {
            return view('admin/manutencao-inteligente/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $db = \Config\Database::connect();
            $resultados = [];

            // 1. Buscar manutenções abertas/agendadas
            $manutencaoModel = new ManutencaoModel();
            $manutencoes = $manutencaoModel
                ->select('manutencoes.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_km_atual')
                ->join('veiculos', 'veiculos.id = manutencoes.man_veiculo_id', 'left')
                ->where('manutencoes.man_empresa_id', $empresaId)
                ->where('manutencoes.man_status', 'aberta')
                ->findAll();

            foreach ($manutencoes as $man) {
                $hoje = date('Y-m-d');
                $kmPrevisto = (int) ($man['man_km'] ?? 0);
                $dataPrevista = $man['man_data'] ?? $hoje;
                $triggerTipo = $man['man_trigger_tipo'] ?? 'qualquer';
                
                // Buscar KM atual: usar man_km_atual se existir, senão buscar do histórico
                $kmAtual = null;
                if (!empty($man['man_km_atual'])) {
                    $kmAtual = (int) $man['man_km_atual'];
                } else {
                    $kmAtual = $this->obterUltimaKmVeiculo($man['man_veiculo_id']);
                }

                // Calcular status baseado no trigger_tipo
                $status = 'agendada';
                $atrasadaPorData = ($dataPrevista < $hoje);
                $atrasadaPorKm = ($kmPrevisto > 0 && $kmAtual > 0 && $kmAtual >= $kmPrevisto);
                
                if ($triggerTipo === 'data') {
                    $status = $atrasadaPorData ? 'atrasada' : 'agendada';
                } elseif ($triggerTipo === 'km') {
                    $status = $atrasadaPorKm ? 'atrasada' : 'agendada';
                } else { // 'qualquer' (padrão)
                    $status = ($atrasadaPorData || $atrasadaPorKm) ? 'atrasada' : 'agendada';
                }

                $resultados[] = [
                    'id' => $man['id'],
                    'origem' => 'manutencao',
                    'veiculo_placa' => $man['vei_placa'] ?? '-',
                    'veiculo_modelo' => $man['vei_modelo'] ?? '-',
                    'veiculo_id' => $man['man_veiculo_id'],
                    'tipo' => $man['man_tipo'] ?? 'corretiva',
                    'servico_nome' => 'Manutenção agendada',
                    'data_prevista' => $dataPrevista,
                    'km_previsto' => $kmPrevisto,
                    'km_atual' => $kmAtual,
                    'status' => $status,
                    'observacoes' => $man['man_obs'] ?? '',
                ];
            }

            // 2. Buscar controles de veículos próximos do prazo
            $veiculoControleModel = new VeiculoControleModel();
            $controles = $veiculoControleModel
                ->select('veiculo_controles.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_km_atual, servicos.ser_nome as servico_nome, produtos.pro_nome as produto_nome')
                ->join('veiculos', 'veiculos.id = veiculo_controles.vec_veiculo_id', 'left')
                ->join('servicos', 'servicos.id = veiculo_controles.vec_servico_id AND veiculo_controles.vec_tipo_item = "servico"', 'left')
                ->join('produtos', 'produtos.id = veiculo_controles.vec_produto_id AND veiculo_controles.vec_tipo_item = "produto"', 'left')
                ->where('veiculo_controles.vec_empresa_id', $empresaId)
                ->where('veiculo_controles.vec_status', 'ativo')
                ->findAll();

            $hoje = date('Y-m-d');
            $margemKm = 1000; // Alertar quando estiver a 1000km do prazo

            foreach ($controles as $ctrl) {
                $kmAtual = (int) ($ctrl['vei_km_atual'] ?? 0);
                $proximoKm = (int) ($ctrl['vec_proximo_km'] ?? 0);
                
                // Só incluir se estiver próximo do prazo
                if ($proximoKm > 0 && $proximoKm <= ($kmAtual + $margemKm)) {
                    $nomeItem = $ctrl['servico_nome'] ?? $ctrl['produto_nome'] ?? 'Item de manutenção';
                    
                    // Calcular status
                    $status = 'agendada';
                    if ($proximoKm < $kmAtual) {
                        $status = 'atrasada';
                    }

                    $resultados[] = [
                        'id' => $ctrl['id'],
                        'origem' => 'controle',
                        'veiculo_placa' => $ctrl['vei_placa'] ?? '-',
                        'veiculo_modelo' => $ctrl['vei_modelo'] ?? '-',
                        'veiculo_id' => $ctrl['vec_veiculo_id'],
                        'tipo' => 'preventiva',
                        'servico_nome' => $nomeItem,
                        'data_prevista' => null, // Controles não têm data, apenas KM
                        'km_previsto' => $proximoKm,
                        'km_atual' => $kmAtual,
                        'status' => $status,
                        'observacoes' => '',
                    ];
                }
            }

            // Ordenar por urgência: atrasadas primeiro, depois por data/KM
            usort($resultados, function($a, $b) {
                if ($a['status'] === 'atrasada' && $b['status'] !== 'atrasada') return -1;
                if ($a['status'] !== 'atrasada' && $b['status'] === 'atrasada') return 1;
                
                // Se ambas têm data, ordenar por data
                if ($a['data_prevista'] && $b['data_prevista']) {
                    return strcmp($a['data_prevista'], $b['data_prevista']);
                }
                
                // Se ambas têm KM, ordenar por KM
                if ($a['km_previsto'] > 0 && $b['km_previsto'] > 0) {
                    return $a['km_previsto'] <=> $b['km_previsto'];
                }
                
                return 0;
            });

            // Resumo para os cards (atrasadas, agendadas, total)
            $atrasadas = 0;
            $agendadas = 0;
            foreach ($resultados as $r) {
                if (($r['status'] ?? '') === 'atrasada') {
                    $atrasadas++;
                } else {
                    $agendadas++;
                }
            }
            $resumo = [
                'atrasadas' => $atrasadas,
                'agendadas' => $agendadas,
                'total' => count($resultados),
            ];

            return $this->response->setJSON(['success' => true, 'data' => $resultados, 'resumo' => $resumo]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao listar manutenções inteligentes: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao listar manutenções inteligentes.']);
        }
    }

    public function editar($id)
    {
        try {
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel->find((int) $id);
            
            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }

            // Verificar se pertence à empresa
            if ($manutencao['man_empresa_id'] != get_empresa_id()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acesso negado.']);
            }

            return $this->response->setJSON(['success' => true, 'data' => $manutencao]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao carregar manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar manutenção.']);
        }
    }

    public function criar()
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeManutencaoPayload($payload);
            $err = $this->validateManutencaoPayload($data);
            
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            // Se man_km_atual não foi informado, buscar automaticamente do histórico
            if (empty($data['man_km_atual']) && !empty($data['man_veiculo_id'])) {
                $data['man_km_atual'] = $this->obterUltimaKmVeiculo($data['man_veiculo_id']);
            }

            $data['man_empresa_id'] = $empresaId;
            $data['man_status'] = 'aberta';

            $manutencaoModel = new ManutencaoModel();
            $id = $manutencaoModel->insert($data, true);
            
            if (!$id) {
                $errors = $manutencaoModel->errors();
                if ($errors) {
                    log_message('error', 'Erros de validação ao cadastrar manutenção: ' . json_encode($errors));
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Erro de validação: ' . implode(', ', $errors)]);
                }
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível cadastrar a manutenção.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Manutenção cadastrada com sucesso.', 'id' => $id]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao cadastrar manutenção: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao cadastrar manutenção.']);
        }
    }

    public function atualizar($id)
    {
        try {
            $manutencaoModel = new ManutencaoModel();
            $existing = $manutencaoModel->find((int) $id);
            
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }

            // Verificar se pertence à empresa
            if ($existing['man_empresa_id'] != get_empresa_id()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acesso negado.']);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeManutencaoPayload($payload);
            $err = $this->validateManutencaoPayload($data);
            
            if ($err) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $err]);
            }

            // Se man_km_atual não foi informado, buscar automaticamente do histórico
            if (empty($data['man_km_atual']) && !empty($data['man_veiculo_id'])) {
                $data['man_km_atual'] = $this->obterUltimaKmVeiculo($data['man_veiculo_id']);
            }

            $ok = $manutencaoModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível atualizar a manutenção.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Manutenção atualizada com sucesso.']);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao atualizar manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao atualizar manutenção.']);
        }
    }

    public function detalhes($id)
    {
        try {
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel
                ->select('manutencoes.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_km_atual')
                ->join('veiculos', 'veiculos.id = manutencoes.man_veiculo_id', 'left')
                ->where('manutencoes.id', (int) $id)
                ->where('manutencoes.man_empresa_id', get_empresa_id())
                ->first();

            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }

            return $this->response->setJSON(['success' => true, 'data' => $manutencao]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao carregar detalhes da manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar detalhes da manutenção.']);
        }
    }

    public function completar($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $id = (int) $id;
            $payload = $this->request->getJSON(true) ?? $this->request->getPost();
            $payload = is_array($payload) ? $payload : [];

            $dataRealizacao = trim((string) ($payload['data_realizacao'] ?? ''));
            $kmAtual = isset($payload['km_atual']) ? (int) preg_replace('/\D/', '', (string) $payload['km_atual']) : null;
            $atualizarProxima = (int) ($payload['atualizar_proxima'] ?? 0);

            if ($dataRealizacao === '') {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Informe a data da realização.']);
            }
            if ($kmAtual === null || $kmAtual < 0) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Informe o KM atual.']);
            }

            $manutencaoModel = new ManutencaoModel();
            $veiculoControleModel = new VeiculoControleModel();

            // Resolver origem: primeiro tenta manutenção aberta, depois controle
            $manutencao = $manutencaoModel
                ->where('id', $id)
                ->where('man_empresa_id', $empresaId)
                ->where('man_status', 'aberta')
                ->first();

            if ($manutencao) {
                // Origem = manutencao: finalizar o registro e, se solicitado, criar a próxima manutenção
                $ok = $manutencaoModel->update($id, [
                    'man_status' => 'finalizada',
                    'man_data' => $dataRealizacao,
                    'man_km' => $kmAtual,
                ]);
                if (!$ok) {
                    return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível marcar a manutenção como realizada.']);
                }

                // Se marcou "agendar a próxima", criar nova manutenção aberta
                if ($atualizarProxima === 1) {
                    $intervaloKm = 10000; // padrão 10.000 km para próxima revisão
                    $proximoKm = $kmAtual + $intervaloKm;
                    $dataRealizacaoObj = new \DateTime($dataRealizacao);
                    $dataRealizacaoObj->modify('+1 year');
                    $proximaDataPrevista = $dataRealizacaoObj->format('Y-m-d');

                    $novaManutencao = [
                        'man_empresa_id' => $empresaId,
                        'man_veiculo_id' => $manutencao['man_veiculo_id'],
                        'man_data' => $proximaDataPrevista,
                        'man_km' => $proximoKm,
                        'man_km_atual' => $kmAtual,
                        'man_trigger_tipo' => $manutencao['man_trigger_tipo'] ?? 'qualquer',
                        'man_tipo' => $manutencao['man_tipo'] ?? 'preventiva',
                        'man_status' => 'aberta',
                        'man_obs' => 'Agendada automaticamente ao completar manutenção anterior.',
                    ];
                    $manutencaoModel->insert($novaManutencao);
                }

                return $this->response->setJSON(['success' => true, 'message' => 'Manutenção marcada como realizada.']);
            }

            $controle = $veiculoControleModel
                ->where('id', $id)
                ->where('vec_empresa_id', $empresaId)
                ->first();

            if (!$controle) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Registro não encontrado.']);
            }

            // Origem = controle: criar manutenção finalizada e opcionalmente atualizar controle
            $manId = $manutencaoModel->insert([
                'man_empresa_id' => $empresaId,
                'man_veiculo_id' => $controle['vec_veiculo_id'],
                'man_data' => $dataRealizacao,
                'man_km' => $kmAtual,
                'man_tipo' => 'preventiva',
                'man_status' => 'finalizada',
            ], true);

            if (!$manId) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível registrar a manutenção.']);
            }

            if ($atualizarProxima === 1) {
                $intervalo = (int) ($controle['vec_intervalo_km'] ?? 0);
                $proximoKm = $intervalo > 0 ? $kmAtual + $intervalo : null;
                $veiculoControleModel->update($id, [
                    'vec_ultimo_km' => $kmAtual,
                    'vec_proximo_km' => $proximoKm,
                    'vec_ultima_manutencao_id' => $manId,
                ]);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Manutenção marcada como realizada.']);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao completar manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao marcar manutenção como realizada.']);
        }
    }

    private function normalizeManutencaoPayload(array $payload): array
    {
        $toIntOrNull = static function ($v) {
            if ($v === '' || $v === null) return null;
            return (int) preg_replace('/\D/', '', (string) $v);
        };

        $triggerTipo = strtolower(trim((string) ($payload['man_trigger_tipo'] ?? 'qualquer')));
        if (!in_array($triggerTipo, ['data', 'km', 'qualquer'], true)) {
            $triggerTipo = 'qualquer';
        }

        return [
            'man_veiculo_id' => $toIntOrNull($payload['man_veiculo_id'] ?? null),
            'man_data' => trim((string) ($payload['man_data'] ?? '')),
            'man_km' => $toIntOrNull($payload['man_km'] ?? null),
            'man_km_atual' => $toIntOrNull($payload['man_km_atual'] ?? null),
            'man_trigger_tipo' => $triggerTipo,
            'man_tipo' => strtolower(trim((string) ($payload['man_tipo'] ?? 'corretiva'))),
            'man_obs' => trim((string) ($payload['man_obs'] ?? '')) ?: null,
        ];
    }

    private function validateManutencaoPayload(array $data): ?string
    {
        if (($data['man_veiculo_id'] ?? null) === null || $data['man_veiculo_id'] < 1) {
            return 'Selecione um veículo.';
        }
        if (($data['man_data'] ?? '') === '') {
            return 'Informe a data prevista.';
        }
        if (!in_array($data['man_tipo'] ?? '', ['preventiva', 'corretiva'], true)) {
            return 'Informe um tipo válido (preventiva ou corretiva).';
        }
        return null;
    }

    /**
     * Busca a última KM do veículo do histórico de locações
     * Prioriza loc_km_devolucao (última devolução), senão loc_km_retirada (última retirada)
     * Se não houver locações, retorna vei_km_atual da tabela veiculos
     */
    private function obterUltimaKmVeiculo(int $veiculoId): ?int
    {
        try {
            $locacaoModel = new LocacaoModel();
            
            // Buscar última devolução (mais precisa)
            $ultimaDevolucao = $locacaoModel
                ->where('loc_vei_id', $veiculoId)
                ->where('loc_km_devolucao IS NOT NULL')
                ->where('loc_km_devolucao >', 0)
                ->orderBy('loc_data_fim_real', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->first();
            
            if ($ultimaDevolucao && !empty($ultimaDevolucao['loc_km_devolucao'])) {
                return (int) $ultimaDevolucao['loc_km_devolucao'];
            }
            
            // Se não houver devolução, buscar última retirada
            $ultimaRetirada = $locacaoModel
                ->where('loc_vei_id', $veiculoId)
                ->where('loc_km_retirada IS NOT NULL')
                ->where('loc_km_retirada >', 0)
                ->orderBy('loc_data_inicio', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->first();
            
            if ($ultimaRetirada && !empty($ultimaRetirada['loc_km_retirada'])) {
                return (int) $ultimaRetirada['loc_km_retirada'];
            }
            
            // Se não houver locações, buscar KM atual do veículo
            $veiculoModel = new VeiculoModel();
            $veiculo = $veiculoModel->find($veiculoId);
            
            if ($veiculo && !empty($veiculo['vei_km_atual'])) {
                return (int) $veiculo['vei_km_atual'];
            }
            
            return null;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao obter última KM do veículo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Endpoint para buscar KM atual do veículo via AJAX
     */
    public function kmAtual($veiculoId)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $veiculoId = (int) $veiculoId;
            if ($veiculoId < 1) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'ID do veículo inválido.']);
            }

            // Verificar se o veículo pertence à empresa
            $veiculoModel = new VeiculoModel();
            $veiculo = $veiculoModel
                ->where('id', $veiculoId)
                ->where('vei_empresa_id', $empresaId)
                ->first();

            if (!$veiculo) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Veículo não encontrado.']);
            }

            $kmAtual = $this->obterUltimaKmVeiculo($veiculoId);

            return $this->response->setJSON([
                'success' => true,
                'km_atual' => $kmAtual,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao buscar KM atual do veículo: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao buscar KM atual do veículo.']);
        }
    }
}
