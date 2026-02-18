<?php

namespace App\Controllers;

use App\Models\ManutencaoModel;
use App\Models\VeiculoControleModel;
use App\Models\VeiculoModel;
use App\Models\ServicoModel;
use App\Models\ProdutoModel;

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
                $kmAtual = (int) ($man['vei_km_atual'] ?? 0);
                $kmPrevisto = (int) ($man['man_km'] ?? 0);
                $dataPrevista = $man['man_data'] ?? $hoje;

                // Calcular status
                $status = 'agendada';
                if ($dataPrevista < $hoje || ($kmPrevisto > 0 && $kmPrevisto < $kmAtual)) {
                    $status = 'atrasada';
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
                // Origem = manutencao: apenas finalizar o registro
                $ok = $manutencaoModel->update($id, [
                    'man_status' => 'finalizada',
                    'man_data' => $dataRealizacao,
                    'man_km' => $kmAtual,
                ]);
                if (!$ok) {
                    return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível marcar a manutenção como realizada.']);
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

        return [
            'man_veiculo_id' => $toIntOrNull($payload['man_veiculo_id'] ?? null),
            'man_data' => trim((string) ($payload['man_data'] ?? '')),
            'man_km' => $toIntOrNull($payload['man_km'] ?? null),
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
}
