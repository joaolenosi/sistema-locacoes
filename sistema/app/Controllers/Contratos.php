<?php

namespace App\Controllers;

use App\Models\ContratoModeloModel;
use App\Models\ContratoVariavelModel;

class Contratos extends BaseController
{
    public function index(): string
    {
        // Aba "Meus contratos" (simulado para UI)
        $meusContratos = [
            ['id' => 1, 'numero' => 'CT-2026-001', 'locatario' => 'João Silva', 'veiculo' => 'ABC-1234', 'inicio' => '15/01/2026', 'termino' => '15/02/2026', 'valor_total' => 'R$ 1.200,00', 'status' => 'Ativo'],
            ['id' => 2, 'numero' => 'CT-2026-002', 'locatario' => 'Maria Santos', 'veiculo' => 'XYZ-5678', 'inicio' => '10/01/2026', 'termino' => '10/02/2026', 'valor_total' => 'R$ 1.000,00', 'status' => 'Encerrado'],
            ['id' => 3, 'numero' => 'CT-2026-003', 'locatario' => 'Pedro Oliveira', 'veiculo' => 'DEF-9012', 'inicio' => '20/01/2026', 'termino' => '20/02/2026', 'valor_total' => 'R$ 1.800,00', 'status' => 'Ativo'],
        ];

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
}
