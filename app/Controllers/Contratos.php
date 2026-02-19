<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\ContratoModeloModel;
use App\Models\ContratoVariavelModel;
use App\Models\EmpresaModel;
use App\Models\LocacaoModel;

class Contratos extends BaseController
{
    public function index(): string
    {
        // Aba "Meus contratos" - buscar da tabela contratos (ou fallback locações)
        $meusContratos = $this->buscarMeusContratos();

        $modeloPadrao = null;
        $variaveis = [];
        $dbWarning = null;

        // Aba "Modelos de contratos": tenta DB remoto (fallback para dados do dump se falhar)
        try {
            $modeloModel = new ContratoModeloModel();
            $variavelModel = new ContratoVariavelModel();

            $modeloPadrao = $modeloModel
                ->where('con_padrao', 1)
                ->where('con_ativo', 1)
                ->first();

            if (!$modeloPadrao) {
                $modeloPadrao = $modeloModel->where('con_ativo', 1)->first();
            }

            $variaveis = $variavelModel
                ->where('cov_ativo', 1)
                ->orderBy('cov_entidade', 'ASC')
                ->orderBy('cov_chave', 'ASC')
                ->findAll();

            $modelosList = $modeloModel->where('con_ativo', 1)->orderBy('con_nome', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $modelosList = [];
            $dbWarning = 'Não foi possível carregar do banco remoto agora. Exibindo dados padrão para testes.';

            // Fallback mínimo baseado no dump fornecido (contratos_modelos / contratos_variaveis)
            $modeloPadrao = [
                'id' => 1,
                'con_nome' => 'Contrato de Locação de Veículo',
                'con_descricao' => 'Modelo padrão de contrato de locação de veículo automotor, com campos dinâmicos.',
                'con_conteudo' => "CONTRATO DE LOCAÇÃO DE VEÍCULO AUTOMOTOR\n\nPelo presente instrumento particular, de um lado {{locadora.nome_completo}}, inscrita no CPF/CNPJ sob o nº {{locadora.cpf_cnpj}}, com endereço à {{locadora.endereco}}, nº {{locadora.numero}}, {{locadora.complemento}}, bairro {{locadora.bairro}}, {{locadora.cidade}} – {{locadora.estado}}, CEP {{locadora.cep}}, doravante denominada LOCADORA;\n\nE, de outro lado, {{locatario.nome_completo}}, inscrito no CPF/CNPJ sob o nº {{locatario.cpf_cnpj}}, portador da CNH nº {{locatario.cnh_numero}}, com vencimento em {{locatario.cnh_vencimento}}, residente e domiciliado à {{locatario.endereco}}, nº {{locatario.numero}}, {{locatario.complemento}}, bairro {{locatario.bairro}}, {{locatario.cidade}} – {{locatario.estado}}, CEP {{locatario.cep}}, telefone {{locatario.telefone}}, WhatsApp {{locatario.whatsapp}}, doravante denominado LOCATÁRIO, têm entre si justo e contratado o que segue:\n\nCLÁUSULA 1ª – DO OBJETO\nO presente contrato tem como objeto a locação do veículo automotor abaixo descrito:\nMarca: {{veiculo.marca}}\nModelo: {{veiculo.modelo}}\nAno: {{veiculo.ano}}\nCor: {{veiculo.cor}}\nPlaca: {{veiculo.placa}}\nChassi: {{veiculo.chassi}}\nRenavam: {{veiculo.renavam}}\nTipo: {{veiculo.tipo}}\n\nCLÁUSULA 2ª – DO PRAZO\nA locação terá início em {{locacao.data_inicio}}, pelo período de {{locacao.tempo}}, conforme condições acordadas entre as partes.\n\nCLÁUSULA 3ª – DO VALOR E FORMA DE PAGAMENTO\nPela locação, o LOCATÁRIO pagará à LOCADORA o valor total de {{locacao.valor}}, conforme a recorrência de pagamento definida em {{locacao.recorrencia_pagamento}}, com início em {{locacao.inicio_pagamento}}.\nEm caso de atraso, incidirá multa no valor de {{locacao.taxa_multa}} e juros de {{locacao.taxa_juros}}, calculados conforme legislação vigente.\n\nCLÁUSULA 8ª – DO FORO\nPara dirimir quaisquer controvérsias oriundas deste contrato, as partes elegem o foro da comarca de {{locadora.cidade}} – {{locadora.estado}}, renunciando a qualquer outro, por mais privilegiado que seja.\n\nE, por estarem assim justas e contratadas, firmam o presente instrumento na data de {{data_de_hoje}}.",
            ];

            $variaveis = [
                ['cov_chave' => 'data_de_hoje', 'cov_label' => 'Data de Hoje', 'cov_entidade' => 'global'],
                ['cov_chave' => 'locacao.data_inicio', 'cov_label' => 'Data de Início da Locação', 'cov_entidade' => 'locacao'],
                ['cov_chave' => 'locacao.recorrencia_pagamento', 'cov_label' => 'Recorrência de Pagamento', 'cov_entidade' => 'locacao'],
                ['cov_chave' => 'locacao.inicio_pagamento', 'cov_label' => 'Início do Pagamento', 'cov_entidade' => 'locacao'],
                ['cov_chave' => 'locacao.valor', 'cov_label' => 'Valor da Locação', 'cov_entidade' => 'locacao'],
                ['cov_chave' => 'locadora.nome_completo', 'cov_label' => 'Nome da Locadora', 'cov_entidade' => 'locadora'],
                ['cov_chave' => 'locadora.cpf_cnpj', 'cov_label' => 'CPF/CNPJ da Locadora', 'cov_entidade' => 'locadora'],
                ['cov_chave' => 'locatario.nome_completo', 'cov_label' => 'Nome do Locatário', 'cov_entidade' => 'locatario'],
                ['cov_chave' => 'locatario.cpf_cnpj', 'cov_label' => 'CPF/CNPJ do Locatário', 'cov_entidade' => 'locatario'],
                ['cov_chave' => 'locatario.cnh_numero', 'cov_label' => 'Número da CNH', 'cov_entidade' => 'locatario'],
                ['cov_chave' => 'locatario.cnh_vencimento', 'cov_label' => 'Vencimento da CNH', 'cov_entidade' => 'locatario'],
                ['cov_chave' => 'veiculo.marca', 'cov_label' => 'Marca do Veículo', 'cov_entidade' => 'veiculo'],
                ['cov_chave' => 'veiculo.modelo', 'cov_label' => 'Modelo do Veículo', 'cov_entidade' => 'veiculo'],
                ['cov_chave' => 'veiculo.placa', 'cov_label' => 'Placa do Veículo', 'cov_entidade' => 'veiculo'],
            ];
            $modelosList = [['id' => 1, 'con_nome' => 'Contrato de Locação de Veículo']];
        }

        if (empty($modelosList)) {
            $modelosList = $modeloPadrao ? [['id' => $modeloPadrao['id'], 'con_nome' => $modeloPadrao['con_nome'] ?? 'Modelo padrão']] : [];
        }

        // Para o editor: passar o conteúdo original (HTML ou texto) para o Quill renderizar corretamente
        // O Quill vai detectar se é HTML e renderizar com formatação, ou se é texto puro
        if ($modeloPadrao !== null && array_key_exists('con_conteudo', $modeloPadrao)) {
            $conteudoOriginal = $modeloPadrao['con_conteudo'] ?? '';
            $modeloPadrao['con_conteudo_editor'] = $conteudoOriginal; // Passa o conteúdo original (HTML ou texto)
        }

        $data = [
            'title' => 'Contratos',
            'meus_contratos' => $meusContratos,
            'modelo_padrao' => $modeloPadrao,
            'modelos_list' => $modelosList ?? [],
            'variaveis' => $variaveis,
            'db_warning' => $dbWarning,
        ];
        
        try {
            return view('admin/contratos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Busca "Meus contratos" da tabela contratos (ou fallback nas locações se tabela não existir)
     */
    private function buscarMeusContratos(): array
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return [];
        }

        try {
            $contratoModel = new ContratoModel();
            $rows = $contratoModel
                ->builderWithJoins()
                ->where('contratos.con_empresa_id', $empresaId)
                ->orderBy('contratos.created_at', 'DESC')
                ->get()
                ->getResultArray();

            $contratos = [];
            foreach ($rows as $row) {
                $contratos[] = [
                    'id' => (int) $row['id'],
                    'numero' => $row['con_numero'] ?? '-',
                    'locatario' => $row['cli_nome'] ?? '-',
                    'veiculo' => $row['vei_placa'] ?? '-',
                    'inicio' => formatarDataBR($row['loc_data_inicio'] ?? ''),
                    'termino' => formatarDataBR($row['loc_data_fim_prevista'] ?? ''),
                    'valor_total' => formatarMoedaBR($row['loc_valor_total'] ?? $row['loc_valor_locacao'] ?? 0),
                    'status' => ($row['con_status'] ?? '') === 'gerado' ? 'Gerado' : 'Rascunho',
                    'tipo' => 'contrato',
                    'ver_id' => (int) $row['id'],
                ];
            }
            return $contratos;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao buscar contratos: ' . $e->getMessage());
            return $this->buscarContratosDasLocacoes();
        }
    }

    /**
     * Busca contratos baseados nas locações (fallback quando tabela contratos não existe)
     */
    private function buscarContratosDasLocacoes(): array
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return [];
            }

            $locacaoModel = new LocacaoModel();
            $locacoes = $locacaoModel
                ->builderWithJoins()
                ->where('locacoes.loc_empresa_id', $empresaId)
                ->whereIn('locacoes.loc_status', ['reservada', 'ativa', 'finalizada'])
                ->orderBy('locacoes.created_at', 'DESC')
                ->get()
                ->getResultArray();

            $contratos = [];
            foreach ($locacoes as $locacao) {
                $contrato = $this->formatarDadosContratoLocacao($locacao);
                if ($contrato) {
                    $contratos[] = $contrato;
                }
            }

            return $contratos;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao buscar contratos das locações: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Formata dados de uma locação para formato de contrato (listagem fallback)
     */
    private function formatarDadosContratoLocacao(array $locacao): ?array
    {
        // Validar se tem dados mínimos necessários
        if (empty($locacao['cli_nome']) || empty($locacao['vei_placa'])) {
            return null;
        }

        $statusMap = [
            'reservada' => 'Reservado',
            'ativa' => 'Ativo',
            'atrasada' => 'Atrasado',
            'finalizada' => 'Encerrado',
            'cancelada' => 'Cancelado',
            'inadimplente' => 'Inadimplente',
        ];

        return [
            'id' => (int) $locacao['id'],
            'numero' => $this->gerarNumeroContratoFromLocacaoId($locacao['id']),
            'locatario' => $locacao['cli_nome'] ?? '-',
            'veiculo' => $locacao['vei_placa'] ?? '-',
            'inicio' => formatarDataBR($locacao['loc_data_inicio'] ?? ''),
            'termino' => formatarDataBR($locacao['loc_data_fim_prevista'] ?? ''),
            'valor_total' => formatarMoedaBR($locacao['loc_valor_total'] ?? $locacao['loc_valor_locacao'] ?? 0),
            'status' => $statusMap[$locacao['loc_status']] ?? $locacao['loc_status'] ?? 'Desconhecido',
            'tipo' => 'locacao',
            'ver_id' => (int) $locacao['id'],
        ];
    }

    /**
     * Gera número de contrato no formato C-000001-1 (ano-id-sequencial)
     */
    private function gerarNumeroContrato(int $contratoId): string
    {
        $ano = date('Y');
        $seq = str_pad((string) $contratoId, 6, '0', STR_PAD_LEFT);
        return "C-{$seq}-1";
    }

    private function gerarNumeroContratoFromLocacaoId(int $locacaoId): string
    {
        $ano = date('Y');
        $sequencial = str_pad((string) $locacaoId, 3, '0', STR_PAD_LEFT);
        return "CT-{$ano}-{$sequencial}";
    }

    /**
     * Garante que o helper de contrato está carregado (substituirVariaveisContrato).
     * Carrega via require_once se helper() não tiver carregado (ex.: servidor case-sensitive).
     */
    private function ensureContratoHelper(): void
    {
        if (function_exists('substituirVariaveisContrato')) {
            return;
        }
        $path = APPPATH . 'Helpers/Contrato_helper.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
        $pathLower = APPPATH . 'Helpers/contrato_helper.php';
        if (file_exists($pathLower)) {
            require_once $pathLower;
            return;
        }
        helper('contrato');
    }

    /**
     * API: listar contratos para consumo via AJAX (tabela contratos ou fallback locações)
     */
    public function listar()
    {
        try {
            $contratos = $this->buscarMeusContratos();
            return $this->response->setJSON([
                'success' => true,
                'data' => $contratos,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao listar contratos: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar contratos.',
            ]);
        }
    }

    /**
     * API Select2: locações disponíveis (reservada, ativa) para o modal Criar contrato
     */
    public function locacoesDisponiveis()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setJSON(['results' => []]);
        }

        $q = $this->request->getGet('q');
        $builder = (new LocacaoModel())->builderWithJoins()
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->whereIn('locacoes.loc_status', ['reservada', 'ativa']);

        if (!empty($q) && is_string($q)) {
            $term = '%' . $q . '%';
            $builder->groupStart();
            if (is_numeric($q)) {
                $builder->where('locacoes.id', (int) $q)->orLike('veiculos.vei_placa', $term)->orLike('clientes.cli_nome', $term);
            } else {
                $builder->like('veiculos.vei_placa', $term)->orLike('clientes.cli_nome', $term);
            }
            $builder->groupEnd();
        }

        $locacoes = $builder->orderBy('locacoes.created_at', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($locacoes as $loc) {
            $numero = str_pad((string) ($loc['id'] ?? 0), 6, '0', STR_PAD_LEFT);
            $placa = $loc['vei_placa'] ?? '';
            $nome = $loc['cli_nome'] ?? '';
            $text = "#{$numero} - {$placa} | " . strtoupper($nome);
            $results[] = ['id' => (int) $loc['id'], 'text' => $text];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    /**
     * POST: criar documento de contrato (rascunho) e redirecionar para ver
     */
    public function criar()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Empresa não identificada.',
            ]);
        }

        $locacaoId = (int) $this->request->getPost('locacao_id');
        $modeloId = (int) $this->request->getPost('modelo_id');
        if ($locacaoId < 1 || $modeloId < 1) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Selecione a locação e o modelo do contrato.',
            ]);
        }

        $locacaoModel = new LocacaoModel();
        $locacao = $locacaoModel->builderWithJoins()
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->where('locacoes.id', $locacaoId)
            ->get()->getRowArray();
        if (!$locacao) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Locação não encontrada.',
            ]);
        }

        $modeloModel = new ContratoModeloModel();
        $modelo = $modeloModel->where('con_ativo', 1)->find($modeloId);
        if (!$modelo) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Modelo de contrato não encontrado.',
            ]);
        }

        $contratoModel = new ContratoModel();
        $contratoModel->insert([
            'con_empresa_id' => $empresaId,
            'con_locacao_id' => $locacaoId,
            'con_modelo_id' => $modeloId,
            'con_numero' => '', // será preenchido após insert com id
            'con_status' => 'rascunho',
        ]);
        $contratoId = (int) $contratoModel->getInsertID();
        if ($contratoId < 1) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao criar contrato.',
            ]);
        }

        $numero = $this->gerarNumeroContrato($contratoId);
        $contratoModel->update($contratoId, ['con_numero' => $numero]);

        $redirect = base_url('admin/contratos/ver/' . $contratoId);
        return $this->response->setJSON([
            'success' => true,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Tela de visualização do contrato (Dados + Visualização + PDF)
     */
    public function ver(int $id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return redirect()->to(base_url('admin/contratos'));
        }

        $contratoModel = new ContratoModel();
        $contrato = $contratoModel->where('con_empresa_id', $empresaId)->find($id);
        if (!$contrato) {
            return redirect()->to(base_url('admin/contratos'));
        }

        $locacaoModel = new LocacaoModel();
        $locacao = $locacaoModel->builderWithJoins()
            ->where('locacoes.id', $contrato['con_locacao_id'])
            ->get()->getRowArray();
        if (!$locacao) {
            return redirect()->to(base_url('admin/contratos'));
        }

        // Carregar cliente e veículo completos (helper precisa de todos os campos)
        $db = \Config\Database::connect();
        $cliente = $db->table('clientes')->where('id', $locacao['loc_cli_id'])->get()->getRowArray();
        $veiculo = $db->table('veiculos')->where('id', $locacao['loc_vei_id'])->get()->getRowArray();
        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->find($empresaId);
        $modeloModel = new ContratoModeloModel();
        $modelo = $modeloModel->find($contrato['con_modelo_id']);

        $locacaoArray = is_array($locacao) ? $locacao : [];
        $clienteArray = $cliente ?: [];
        $veiculoArray = $veiculo ?: [];
        $empresaArray = $empresa ?: [];
        $conteudoSubstituido = '';
        if ($modelo && !empty($modelo['con_conteudo'])) {
            $this->ensureContratoHelper();
            $conteudoSubstituido = \substituirVariaveisContrato(
                $modelo['con_conteudo'],
                $locacaoArray,
                $clienteArray,
                $veiculoArray,
                $empresaArray
            );
        }

        $data = [
            'title' => 'Contrato ' . ($contrato['con_numero'] ?? ''),
            'contrato' => $contrato,
            'locacao' => $locacaoArray,
            'cliente' => $clienteArray,
            'veiculo' => $veiculoArray,
            'empresa' => $empresaArray,
            'modelo' => $modelo ?: [],
            'conteudo_substituido' => $conteudoSubstituido,
        ];

        return view('admin/contratos/ver', $data);
    }

    /**
     * Converte HTML do modelo (banco/Quill) em texto puro para exibir no editor.
     * Assim o usuário vê o contrato legível e pode formatar de novo com o editor.
     */
    private function htmlParaTextoEditor(string $conteudo): string
    {
        $conteudo = trim($conteudo);
        if ($conteudo === '') {
            return '';
        }
        // Se não parece HTML, devolve como está (já é texto puro)
        if (strpos($conteudo, '<') === false || strpos($conteudo, '>') === false) {
            return $conteudo;
        }
        $s = $conteudo;
        $s = preg_replace('/<br\s*\/?>/i', "\n", $s);
        $s = preg_replace('/<\/p>\s*<p>/i', "\n\n", $s);
        $s = preg_replace('/<\/p>/i', "\n", $s);
        $s = preg_replace('/<p>/i', '', $s);
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace("/\n{3,}/", "\n\n", $s));
    }

    /**
     * Converte o conteúdo do contrato (já com variáveis substituídas) para HTML do PDF.
     * Se já for HTML (contém tags), usa como está. Se for texto puro, converte: título e
     * linhas "CLÁUSULA Xª" em negrito, demais linhas em parágrafos.
     */
    private function conteudoContratoParaPdfHtml(string $conteudo): string
    {
        $conteudo = trim($conteudo);
        if ($conteudo === '') {
            return '';
        }
        // Conteúdo já em HTML (ex.: do editor Quill ou do UPDATE no banco)
        if (strpos($conteudo, '<') !== false && strpos($conteudo, '>') !== false) {
            return $conteudo;
        }
        // Fallback: texto puro -> HTML com título e cláusulas em destaque
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $out = '';
        $primeira = true;
        foreach ($linhas as $linha) {
            $trim = trim($linha);
            if ($trim === '') {
                $out .= '<p>&nbsp;</p>';
                continue;
            }
            if (stripos($trim, 'CLÁUSULA') === 0) {
                $out .= '<p class="clausula"><strong>' . htmlspecialchars($trim, ENT_QUOTES, 'UTF-8') . '</strong></p>';
            } elseif ($primeira && (stripos($trim, 'CONTRATO') !== false || strlen($trim) > 40)) {
                $out .= '<p class="titulo-contrato">' . htmlspecialchars($trim, ENT_QUOTES, 'UTF-8') . '</p>';
                $primeira = false;
            } else {
                $out .= '<p>' . htmlspecialchars($trim, ENT_QUOTES, 'UTF-8') . '</p>';
                $primeira = false;
            }
        }
        return $out;
    }

    /**
     * GET: gerar e baixar PDF do contrato
     */
    public function pdf(int $id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403);
        }

        $contrato = (new ContratoModel())->where('con_empresa_id', $empresaId)->find($id);
        if (!$contrato) {
            return $this->response->setStatusCode(404);
        }

        $locacaoModel = new LocacaoModel();
        $locacao = $locacaoModel->builderWithJoins()
            ->where('locacoes.id', $contrato['con_locacao_id'])
            ->get()->getRowArray();
        if (!$locacao) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $cliente = $db->table('clientes')->where('id', $locacao['loc_cli_id'])->get()->getRowArray();
        $veiculo = $db->table('veiculos')->where('id', $locacao['loc_vei_id'])->get()->getRowArray();
        $empresa = (new EmpresaModel())->find($empresaId);
        $modelo = (new ContratoModeloModel())->find($contrato['con_modelo_id']);

        $conteudoSubstituido = '';
        if ($modelo && !empty($modelo['con_conteudo'])) {
            $this->ensureContratoHelper();
            $conteudoSubstituido = \substituirVariaveisContrato(
                $modelo['con_conteudo'],
                $locacao ?: [],
                $cliente ?: [],
                $veiculo ?: [],
                $empresa ?: []
            );
        }

        $bodyContent = $this->conteudoContratoParaPdfHtml($conteudoSubstituido);
        // Garantir que tags HTML não estejam como entidades (evitar que o PDF mostre literalmente <p>, <strong>, etc.)
        $bodyContent = html_entity_decode($bodyContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        body { font-family: DejaVu Serif, serif; font-size: 11pt; line-height: 1.5; margin: 24px; }
        .titulo-contrato { font-size: 14pt; font-weight: bold; margin-top: 1em; margin-bottom: 0.6em; text-align: center; }
        .clausula { font-weight: bold; margin-top: 0.8em; margin-bottom: 0.3em; }
        p { margin: 0.4em 0; }
        </style></head><body>' . $bodyContent . '</body></html>';

        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();
        } catch (\Throwable $e) {
            log_message('error', 'Dompdf: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Erro ao gerar PDF.');
        }

        $filename = 'contrato-' . ($contrato['con_numero'] ?? $id) . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdfOutput);
    }

    /**
     * POST: atualizar modelo de contrato (nome, descrição, conteúdo).
     * Aceita apenas modelos de empresa logada ou modelo padrão (con_empresa_id NULL).
     */
    public function atualizarModelo(int $id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acesso negado.']);
        }

        $modeloModel = new ContratoModeloModel();
        $modelo = $modeloModel->find($id);
        if (!$modelo) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Modelo não encontrado.']);
        }

        $modeloEmpresaId = isset($modelo['con_empresa_id']) ? (int) $modelo['con_empresa_id'] : null;
        if ($modeloEmpresaId !== null && $modeloEmpresaId !== $empresaId) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Modelo não encontrado.']);
        }

        $conNome = $this->request->getPost('con_nome');
        $conDescricao = $this->request->getPost('con_descricao');
        $conConteudo = $this->request->getPost('con_conteudo');

        $conNome = is_string($conNome) ? trim($conNome) : '';
        $conDescricao = is_string($conDescricao) ? trim($conDescricao) : '';
        $conConteudo = is_string($conConteudo) ? $conConteudo : '';

        if ($conNome === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Nome do modelo é obrigatório.']);
        }

        // Permitir apenas tags seguras (Quill: p, strong, br, ul, li, etc.)
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><span><h1><h2><h3><h4><blockquote><code><pre>';
        $conConteudo = strip_tags($conConteudo, $allowedTags);

        $modeloModel->update($id, [
            'con_nome'       => $conNome,
            'con_descricao'  => $conDescricao,
            'con_conteudo'   => $conConteudo,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * POST: marcar contrato como gerado e gerar token para envio
     */
    public function marcarGerado(int $id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        $contrato = (new ContratoModel())->where('con_empresa_id', $empresaId)->find($id);
        if (!$contrato) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false]);
        }

        $token = bin2hex(random_bytes(32));
        (new ContratoModel())->update($id, [
            'con_status' => 'gerado',
            'con_token' => $token,
        ]);

        $linkEnvio = base_url('contrato/ver/' . $token); // rota pública futura, por enquanto só o token
        return $this->response->setJSON([
            'success' => true,
            'link' => $linkEnvio,
            'token' => $token,
        ]);
    }
}
