<?php

namespace App\Controllers;

use App\Models\ContratoModeloModel;
use App\Models\ContratoVariavelModel;
use App\Models\LocacaoModel;

class Contratos extends BaseController
{
    public function index(): string
    {
        // Aba "Meus contratos" - buscar locações reais
        $meusContratos = $this->buscarContratosDasLocacoes();

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
        } catch (\Throwable $e) {
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
        }

        $data = [
            'title' => 'Contratos',
            'meus_contratos' => $meusContratos,
            'modelo_padrao' => $modeloPadrao,
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
     * Busca contratos baseados nas locações reais
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
                $contrato = $this->formatarDadosContrato($locacao);
                if ($contrato) {
                    $contratos[] = $contrato;
                }
            }

            return $contratos;
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao buscar contratos das locações: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            // Retornar array vazio em caso de erro ao invés de dados mockados
            return [];
        }
    }

    /**
     * Formata dados de uma locação para formato de contrato
     */
    private function formatarDadosContrato(array $locacao): ?array
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
            'numero' => $this->gerarNumeroContrato($locacao['id']),
            'locatario' => $locacao['cli_nome'] ?? '-',
            'veiculo' => $locacao['vei_placa'] ?? '-',
            'inicio' => formatarDataBR($locacao['loc_data_inicio'] ?? ''),
            'termino' => formatarDataBR($locacao['loc_data_fim_prevista'] ?? ''),
            'valor_total' => formatarMoedaBR($locacao['loc_valor_total'] ?? $locacao['loc_valor_locacao'] ?? 0),
            'status' => $statusMap[$locacao['loc_status']] ?? $locacao['loc_status'] ?? 'Desconhecido',
        ];
    }

    /**
     * Gera número único de contrato no formato CT-YYYY-NNN
     */
    private function gerarNumeroContrato(int $locacaoId): string
    {
        $ano = date('Y');
        // Usar ID da locação como sequencial (pode ser melhorado com contador real)
        $sequencial = str_pad((string) $locacaoId, 3, '0', STR_PAD_LEFT);
        return "CT-{$ano}-{$sequencial}";
    }
}
