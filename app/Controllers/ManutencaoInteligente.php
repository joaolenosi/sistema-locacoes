<?php

namespace App\Controllers;

use App\Models\ManutencaoModel;
use App\Models\ManutencaoFotoModel;
use App\Models\VeiculoControleModel;
use App\Models\VeiculoModel;
use App\Models\ServicoModel;
use App\Models\ProdutoModel;
use App\Models\LocacaoModel;
use App\Models\EmpresaModel;

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
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel
                ->select('manutencoes.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_marca, veiculos.vei_ano, veiculos.vei_cor, veiculos.vei_km_atual')
                ->join('veiculos', 'veiculos.id = manutencoes.man_veiculo_id', 'left')
                ->where('manutencoes.id', (int) $id)
                ->where('manutencoes.man_empresa_id', $empresaId)
                ->first();

            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }

            $fotoModel = new ManutencaoFotoModel();
            $fotos = $fotoModel->findByManutencao((int) $id, $empresaId);
            $manutencao['fotos'] = $fotos;

            $itens = $this->buscarItensManutencao((int) $id, $empresaId);
            $manutencao['itens'] = $itens;

            return $this->response->setJSON(['success' => true, 'data' => $manutencao]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao carregar detalhes da manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao carregar detalhes da manutenção.']);
        }
    }

    /**
     * Exibe a página de detalhes da manutenção (com fotos e botão PDF).
     */
    public function detalhesView($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return redirect()->to(base_url('login'))->with('error', 'Sessão inválida.');
        }
        $manutencaoModel = new ManutencaoModel();
        $manutencao = $manutencaoModel
            ->select('manutencoes.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_marca, veiculos.vei_ano, veiculos.vei_cor, veiculos.vei_km_atual')
            ->join('veiculos', 'veiculos.id = manutencoes.man_veiculo_id', 'left')
            ->where('manutencoes.id', (int) $id)
            ->where('manutencoes.man_empresa_id', $empresaId)
            ->first();
        if (!$manutencao) {
            return redirect()->to(base_url('admin/manutencao'))->with('error', 'Manutenção não encontrada.');
        }
        $fotoModel = new ManutencaoFotoModel();
        $manutencao['fotos'] = $fotoModel->findByManutencao((int) $id, $empresaId);
        $manutencao['itens'] = $this->buscarItensManutencao((int) $id, $empresaId);

        $produtoModel = new ProdutoModel();
        $servicoModel = new ServicoModel();
        $data = [
            'title' => 'Detalhes da Manutenção',
            'manutencao' => $manutencao,
            'produtos' => $produtoModel->where('pro_empresa_id', $empresaId)->where('pro_ativo', 1)->orderBy('pro_nome')->findAll(),
            'servicos' => $servicoModel->where('ser_empresa_id', $empresaId)->where('ser_ativo', 1)->orderBy('ser_nome')->findAll(),
        ];
        return view('admin/manutencoes/detalhes', $data);
    }

    /**
     * Busca os itens (produtos/serviços) de uma manutenção.
     */
    private function buscarItensManutencao(int $manutencaoId, int $empresaId): array
    {
        $db = \Config\Database::connect();
        return $db->table('manutencoes_itens')
            ->where('mai_manutencao_id', $manutencaoId)
            ->where('mai_empresa_id', $empresaId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Recalcula e atualiza man_total da manutenção (soma dos itens).
     */
    private function recalcularManTotal(int $manutencaoId, int $empresaId): void
    {
        $db = \Config\Database::connect();
        $soma = $db->table('manutencoes_itens')
            ->selectSum('mai_valor_total')
            ->where('mai_manutencao_id', $manutencaoId)
            ->where('mai_empresa_id', $empresaId)
            ->get()
            ->getRow();
        $total = $soma && isset($soma->mai_valor_total) ? (float) $soma->mai_valor_total : 0.00;
        $manutencaoModel = new ManutencaoModel();
        $manutencaoModel->update($manutencaoId, ['man_total' => $total]);
    }

    /**
     * POST: adiciona um item (produto ou serviço) à manutenção.
     */
    public function adicionarItem($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }
            $id = (int) $id;
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel->where('id', $id)->where('man_empresa_id', $empresaId)->first();
            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }
            $statusPermitidos = ['aberta', 'rascunho'];
            if (!in_array($manutencao['man_status'] ?? '', $statusPermitidos, true)) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Só é possível adicionar itens em manutenções abertas ou em rascunho.']);
            }

            $payload = (array) ($this->request->getJSON(true) ?? $this->request->getPost());
            $tipoItem = strtolower(trim((string) ($payload['tipo_item'] ?? '')));
            if (!in_array($tipoItem, ['produto', 'servico'], true)) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Informe o tipo: produto ou servico.']);
            }
            $quantidade = max(1, (int) ($payload['quantidade'] ?? 1));

            $descricao = '';
            $valorUnitario = 0.00;
            $produtoId = null;
            $servicoId = null;

            if ($tipoItem === 'produto') {
                $produtoId = (int) ($payload['produto_id'] ?? 0);
                if ($produtoId < 1) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Selecione um produto.']);
                }
                $produtoModel = new ProdutoModel();
                $produto = $produtoModel->where('id', $produtoId)->where('pro_empresa_id', $empresaId)->first();
                if (!$produto) {
                    return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Produto não encontrado.']);
                }
                $descricao = $produto['pro_nome'] ?? 'Produto';
                $valorUnitario = (float) ($produto['pro_preco_venda'] ?? $produto['pro_preco_custo'] ?? 0);
            } else {
                $servicoId = (int) ($payload['servico_id'] ?? 0);
                if ($servicoId < 1) {
                    return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Selecione um serviço.']);
                }
                $servicoModel = new ServicoModel();
                $servico = $servicoModel->where('id', $servicoId)->where('ser_empresa_id', $empresaId)->first();
                if (!$servico) {
                    return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Serviço não encontrado.']);
                }
                $descricao = $servico['ser_nome'] ?? 'Serviço';
                $valorUnitario = (float) ($servico['ser_preco_padrao'] ?? 0);
            }

            $valorTotal = round($quantidade * $valorUnitario, 2);

            $db = \Config\Database::connect();
            $db->table('manutencoes_itens')->insert([
                'mai_empresa_id' => $empresaId,
                'mai_manutencao_id' => $id,
                'mai_tipo_item' => $tipoItem,
                'mai_produto_id' => $produtoId,
                'mai_servico_id' => $servicoId,
                'mai_descricao' => $descricao,
                'mai_quantidade' => $quantidade,
                'mai_valor_unitario' => $valorUnitario,
                'mai_valor_total' => $valorTotal,
            ]);

            $itemId = $db->insertID();
            $this->recalcularManTotal($id, $empresaId);

            $item = [
                'id' => $itemId,
                'mai_descricao' => $descricao,
                'mai_tipo_item' => $tipoItem,
                'mai_quantidade' => $quantidade,
                'mai_valor_unitario' => $valorUnitario,
                'mai_valor_total' => $valorTotal,
            ];

            $manutencaoAtual = $manutencaoModel->find($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Item adicionado.',
                'item' => $item,
                'man_total' => (float) ($manutencaoAtual['man_total'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao adicionar item na manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao adicionar item.']);
        }
    }

    /**
     * POST: remove um item da manutenção.
     */
    public function removerItem($itemId)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }
            $itemId = (int) $itemId;
            $db = \Config\Database::connect();
            $item = $db->table('manutencoes_itens')
                ->where('id', $itemId)
                ->where('mai_empresa_id', $empresaId)
                ->get()
                ->getRowArray();
            if (!$item) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Item não encontrado.']);
            }
            $manutencaoId = (int) $item['mai_manutencao_id'];
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel->where('id', $manutencaoId)->where('man_empresa_id', $empresaId)->first();
            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }
            $statusPermitidos = ['aberta', 'rascunho'];
            if (!in_array($manutencao['man_status'] ?? '', $statusPermitidos, true)) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Só é possível remover itens de manutenções abertas ou em rascunho.']);
            }

            $db->table('manutencoes_itens')->where('id', $itemId)->delete();
            $this->recalcularManTotal($manutencaoId, $empresaId);
            $manutencaoAtual = $manutencaoModel->find($manutencaoId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Item removido.',
                'man_total' => (float) ($manutencaoAtual['man_total'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao remover item da manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao remover item.']);
        }
    }

    private const FOTO_MAX_SIZE = 5 * 1024 * 1024; // 5MB
    private const FOTO_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

    /**
     * Upload de múltiplas fotos para uma manutenção.
     */
    public function uploadFoto($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }
            $id = (int) $id;
            $manutencaoModel = new ManutencaoModel();
            $manutencao = $manutencaoModel->where('id', $id)->where('man_empresa_id', $empresaId)->first();
            if (!$manutencao) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Manutenção não encontrada.']);
            }

            $files = $this->request->getFileMultiple('fotos');
            if (empty($files) || (count($files) === 1 && ($files[0]->getError() === UPLOAD_ERR_NO_FILE || ($files[0]->getSize() === 0 && $files[0]->getError() === 0)))) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
            }

            $writable = WRITEPATH . 'uploads/manutencoes/' . $empresaId . '/' . $id . '/';
            if (!is_dir($writable)) {
                if (!@mkdir($writable, 0755, true)) {
                    return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível criar o diretório de uploads.']);
                }
                $indexHtml = $writable . 'index.html';
                if (!file_exists($indexHtml)) {
                    file_put_contents($indexHtml, '<!DOCTYPE html><html><head><title>403</title></head><body><p>Forbidden</p></body></html>');
                }
            }

            $fotoModel = new ManutencaoFotoModel();
            $uploaded = [];
            $ordem = (int) $fotoModel->where('maf_manutencao_id', $id)->countAllResults();

            foreach ($files as $file) {
                if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($file->getSize() > self::FOTO_MAX_SIZE) {
                    continue;
                }
                $mime = $file->getMimeType();
                if (!in_array($mime, self::FOTO_ALLOWED_TYPES, true)) {
                    continue;
                }
                $ext = $file->getClientExtension() ?: 'jpg';
                $nomeSeguro = bin2hex(random_bytes(8)) . '_' . time() . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
                $file->move($writable, $nomeSeguro);
                $caminhoRelativo = 'uploads/manutencoes/' . $empresaId . '/' . $id . '/' . $nomeSeguro;
                $fotoModel->insert([
                    'maf_empresa_id' => $empresaId,
                    'maf_manutencao_id' => $id,
                    'maf_nome_arquivo' => $file->getClientName(),
                    'maf_caminho' => $caminhoRelativo,
                    'maf_tamanho' => $file->getSize(),
                    'maf_tipo' => $mime,
                    'maf_ordem' => $ordem++,
                ], true);
                $uploaded[] = ['caminho' => $caminhoRelativo, 'nome' => $file->getClientName()];
            }

            $fotos = $fotoModel->findByManutencao($id, $empresaId);
            return $this->response->setJSON(['success' => true, 'message' => 'Fotos enviadas.', 'fotos' => $fotos]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao enviar fotos da manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao enviar fotos.']);
        }
    }

    /**
     * Deleta uma foto da manutenção (por ID da foto).
     */
    public function deletarFoto($fotoId)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }
            $fotoId = (int) $fotoId;
            $fotoModel = new ManutencaoFotoModel();
            $foto = $fotoModel->where('id', $fotoId)->where('maf_empresa_id', $empresaId)->first();
            if (!$foto) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Foto não encontrada.']);
            }
            $path = WRITEPATH . $foto['maf_caminho'];
            if (is_file($path)) {
                @unlink($path);
            }
            $fotoModel->delete($fotoId);
            return $this->response->setJSON(['success' => true, 'message' => 'Foto removida.']);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao deletar foto: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao remover foto.']);
        }
    }

    /**
     * Serve o arquivo de uma foto (para exibir em img src).
     */
    public function foto($fotoId)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403);
        }
        $fotoId = (int) $fotoId;
        $fotoModel = new ManutencaoFotoModel();
        $foto = $fotoModel->where('id', $fotoId)->where('maf_empresa_id', $empresaId)->first();
        if (!$foto) {
            return $this->response->setStatusCode(404);
        }
        $path = WRITEPATH . $foto['maf_caminho'];
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }
        $mime = $foto['maf_tipo'] ?: 'image/jpeg';
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setBody(file_get_contents($path));
    }

    /**
     * GET: gera e baixa PDF da manutenção (cabeçalho empresa, dados veículo, itens, fotos).
     */
    public function pdf($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403);
        }
        $id = (int) $id;
        $manutencaoModel = new ManutencaoModel();
        $manutencao = $manutencaoModel
            ->select('manutencoes.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_marca, veiculos.vei_ano, veiculos.vei_cor, veiculos.vei_km_atual, veiculos.vei_chassi, veiculos.vei_renavam')
            ->join('veiculos', 'veiculos.id = manutencoes.man_veiculo_id', 'left')
            ->where('manutencoes.id', $id)
            ->where('manutencoes.man_empresa_id', $empresaId)
            ->first();
        if (!$manutencao) {
            return $this->response->setStatusCode(404);
        }
        $empresa = (new EmpresaModel())->find($empresaId);
        $fotoModel = new ManutencaoFotoModel();
        $fotos = $fotoModel->findByManutencao($id, $empresaId);
        $db = \Config\Database::connect();
        $itens = $db->table('manutencoes_itens')
            ->where('mai_manutencao_id', $id)
            ->where('mai_empresa_id', $empresaId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $html = $this->buildManutencaoPdfHtml($manutencao, $empresa ?: [], $itens, $fotos);
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->getOptions()->setIsRemoteEnabled(true);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
        } catch (\Throwable $e) {
            log_message('error', 'Dompdf manutenção: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Erro ao gerar PDF.');
        }
        $placa = preg_replace('/\s+/', '', $manutencao['vei_placa'] ?? '');
        $filename = 'manutencao-' . $id . '-' . ($placa ?: 'veiculo') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdfOutput);
    }

    private function buildManutencaoPdfHtml(array $manutencao, array $empresa, array $itens, array $fotos): string
    {
        $empNome = htmlspecialchars($empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? 'Empresa', ENT_QUOTES, 'UTF-8');
        $empCnpj = htmlspecialchars($empresa['emp_cpf_cnpj'] ?? '', ENT_QUOTES, 'UTF-8');
        $empEnd = trim(($empresa['emp_rua'] ?? '') . ', ' . ($empresa['emp_numero'] ?? '') . ($empresa['emp_complemento'] ? ' - ' . $empresa['emp_complemento'] : '') . ' - ' . ($empresa['emp_cidade'] ?? '') . '/' . ($empresa['emp_estado'] ?? ''));
        $empEnd = htmlspecialchars($empEnd, ENT_QUOTES, 'UTF-8');
        $empTel = htmlspecialchars($empresa['emp_telefone'] ?? $empresa['emp_email'] ?? '', ENT_QUOTES, 'UTF-8');

        $dataMan = $manutencao['man_data'] ?? '';
        if ($dataMan && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $dataMan, $m)) {
            $dataMan = $m[3] . '/' . $m[2] . '/' . $m[1];
        }
        $tipo = $manutencao['man_tipo'] === 'preventiva' ? 'Preventiva' : 'Corretiva';
        $status = $manutencao['man_status'] ?? '';
        $statusLabel = ['aberta' => 'Aberta', 'finalizada' => 'Finalizada', 'rascunho' => 'Rascunho', 'cancelada' => 'Cancelada'][$status] ?? $status;
        $veiPlaca = htmlspecialchars($manutencao['vei_placa'] ?? '-', ENT_QUOTES, 'UTF-8');
        $veiModelo = htmlspecialchars($manutencao['vei_modelo'] ?? '-', ENT_QUOTES, 'UTF-8');
        $veiMarca = htmlspecialchars($manutencao['vei_marca'] ?? '-', ENT_QUOTES, 'UTF-8');
        $veiAno = htmlspecialchars($manutencao['vei_ano'] ?? '-', ENT_QUOTES, 'UTF-8');
        $veiCor = htmlspecialchars($manutencao['vei_cor'] ?? '-', ENT_QUOTES, 'UTF-8');
        $veiKm = isset($manutencao['man_km']) ? (int) $manutencao['man_km'] : null;
        $veiKmStr = $veiKm !== null ? number_format($veiKm, 0, ',', '.') . ' km' : '-';
        $obs = htmlspecialchars(trim($manutencao['man_obs'] ?? ''), ENT_QUOTES, 'UTF-8');
        $total = isset($manutencao['man_total']) ? number_format((float) $manutencao['man_total'], 2, ',', '.') : '0,00';

        $header = '
        <div class="cabecalho" style="border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:15px;">
            <h1 style="margin:0 0 5px 0; font-size:16pt;">' . $empNome . '</h1>
            <p style="margin:0; font-size:9pt; color:#555;">CNPJ/CPF: ' . $empCnpj . '</p>
            <p style="margin:0; font-size:9pt; color:#555;">' . $empEnd . '</p>
            <p style="margin:0; font-size:9pt; color:#555;">Contato: ' . $empTel . '</p>
        </div>
        <h2 style="font-size:14pt; margin:0 0 12px 0; text-align:center;">Relatório de Manutenção</h2>';

        $veiculoBlock = '
        <table class="veiculo" style="width:100%; border-collapse:collapse; margin-bottom:15px; font-size:10pt;">
            <tr style="background:#f0f0f0;"><th colspan="2" style="text-align:left; padding:8px;">Dados do Veículo</th></tr>
            <tr><td style="padding:6px; width:30%;"><strong>Placa</strong></td><td style="padding:6px;">' . $veiPlaca . '</td></tr>
            <tr><td style="padding:6px;"><strong>Marca / Modelo</strong></td><td style="padding:6px;">' . $veiMarca . ' / ' . $veiModelo . '</td></tr>
            <tr><td style="padding:6px;"><strong>Ano / Cor</strong></td><td style="padding:6px;">' . $veiAno . ' / ' . $veiCor . '</td></tr>
            <tr><td style="padding:6px;"><strong>Quilometragem</strong></td><td style="padding:6px;">' . $veiKmStr . '</td></tr>
        </table>';

        $manBlock = '
        <table class="manutencao" style="width:100%; border-collapse:collapse; margin-bottom:15px; font-size:10pt;">
            <tr style="background:#f0f0f0;"><th colspan="2" style="text-align:left; padding:8px;">Dados da Manutenção</th></tr>
            <tr><td style="padding:6px; width:30%;"><strong>Data</strong></td><td style="padding:6px;">' . $dataMan . '</td></tr>
            <tr><td style="padding:6px;"><strong>Tipo</strong></td><td style="padding:6px;">' . $tipo . '</td></tr>
            <tr><td style="padding:6px;"><strong>Status</strong></td><td style="padding:6px;">' . $statusLabel . '</td></tr>
            <tr><td style="padding:6px;"><strong>Valor total</strong></td><td style="padding:6px;">R$ ' . $total . '</td></tr>
            ' . ($obs ? '<tr><td style="padding:6px;"><strong>Observações</strong></td><td style="padding:6px;">' . $obs . '</td></tr>' : '') . '
        </table>';

        $itensHtml = '';
        if (!empty($itens)) {
            $itensHtml = '<table style="width:100%; border-collapse:collapse; margin-bottom:15px; font-size:9pt;">';
            $itensHtml .= '<tr style="background:#f0f0f0;"><th style="text-align:left; padding:6px;">Descrição</th><th style="padding:6px;">Qtd</th><th style="text-align:right; padding:6px;">Valor unit.</th><th style="text-align:right; padding:6px;">Total</th></tr>';
            foreach ($itens as $item) {
                $desc = htmlspecialchars($item['mai_descricao'] ?? '-', ENT_QUOTES, 'UTF-8');
                $qtd = (int) ($item['mai_quantidade'] ?? 1);
                $vu = number_format((float) ($item['mai_valor_unitario'] ?? 0), 2, ',', '.');
                $vt = number_format((float) ($item['mai_valor_total'] ?? 0), 2, ',', '.');
                $itensHtml .= '<tr><td style="padding:5px;">' . $desc . '</td><td style="padding:5px;">' . $qtd . '</td><td style="text-align:right; padding:5px;">R$ ' . $vu . '</td><td style="text-align:right; padding:5px;">R$ ' . $vt . '</td></tr>';
            }
            $itensHtml .= '</table>';
        }

        $fotosHtml = '';
        if (!empty($fotos)) {
            $fotosHtml = '<p style="font-weight:bold; margin:12px 0 6px 0;">Fotos anexadas</p><div style="margin-bottom:15px;">';
            $count = 0;
            foreach ($fotos as $f) {
                $path = WRITEPATH . ($f['maf_caminho'] ?? '');
                if (!is_file($path)) {
                    continue;
                }
                $count++;
                $bin = @file_get_contents($path);
                if ($bin === false) {
                    continue;
                }
                $b64 = base64_encode($bin);
                $mime = $f['maf_tipo'] ?? 'image/jpeg';
                $fotosHtml .= '<img src="data:' . $mime . ';base64,' . $b64 . '" alt="Foto ' . $count . '" style="max-width:48%; height:auto; margin:4px; vertical-align:top;" />';
            }
            $fotosHtml .= '</div>';
        }

        $footer = '<p style="font-size:8pt; color:#666; margin-top:20px; text-align:right;">Documento gerado em ' . date('d/m/Y H:i') . '</p>';

        $css = '
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.4; margin: 20px; }
        .cabecalho h1 { font-size: 16pt; }
        table { page-break-inside: avoid; }
        img { max-width: 100%; }
        ';
        $body = $header . $veiculoBlock . $manBlock . $itensHtml . $fotosHtml . $footer;
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>' . $body . '</body></html>';
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
