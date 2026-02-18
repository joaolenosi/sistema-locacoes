<?php

/**
 * Helper para manipulação de contratos
 */

if (!function_exists('substituirVariaveisContrato')) {
    /**
     * Substitui variáveis no modelo de contrato pelos valores reais
     * 
     * @param string $modeloConteudo Conteúdo do modelo com variáveis {{variavel}}
     * @param array $locacao Dados da locação
     * @param array $cliente Dados do cliente/locatário
     * @param array $veiculo Dados do veículo
     * @param array $empresa Dados da empresa/locadora
     * @return string Conteúdo do contrato com variáveis substituídas
     */
    function substituirVariaveisContrato(
        string $modeloConteudo,
        array $locacao,
        array $cliente,
        array $veiculo,
        array $empresa
    ): string {
        $conteudo = $modeloConteudo;
        
        // Mapear dados em estrutura hierárquica
        $dados = [
            'locatario' => [
                'nome_completo' => $cliente['cli_nome'] ?? '',
                'cpf_cnpj' => formatarCPFCNPJ($cliente['cli_cpf_cnpj'] ?? ''),
                'cnh_numero' => $cliente['cli_cnh_numero'] ?? '',
                'cnh_vencimento' => formatarDataBR($cliente['cli_cnh_validade'] ?? ''),
                'endereco' => $cliente['cli_rua'] ?? '',
                'numero' => $cliente['cli_numero'] ?? '',
                'complemento' => $cliente['cli_complemento'] ?? '',
                'bairro' => $cliente['cli_bairro'] ?? '',
                'cidade' => $cliente['cli_cidade'] ?? '',
                'estado' => $cliente['cli_estado'] ?? '',
                'cep' => $cliente['cli_cep'] ?? '',
                'telefone' => $cliente['cli_telefone'] ?? '',
                'whatsapp' => $cliente['cli_whatsapp'] ?? '',
            ],
            'veiculo' => [
                'marca' => $veiculo['vei_marca'] ?? '',
                'modelo' => $veiculo['vei_modelo'] ?? '',
                'ano' => $veiculo['vei_ano'] ?? '',
                'cor' => $veiculo['vei_cor'] ?? '',
                'placa' => $veiculo['vei_placa'] ?? '',
                'chassi' => $veiculo['vei_chassi'] ?? '',
                'renavam' => $veiculo['vei_renavam'] ?? '',
                'tipo' => $veiculo['vei_tipo'] ?? '',
                'km_atual' => $veiculo['vei_km_atual'] ?? '',
                'km_na_retirada' => $locacao['loc_km_retirada'] ?? $veiculo['vei_km_atual'] ?? '',
            ],
            'locacao' => [
                'data_inicio' => formatarDataBR($locacao['loc_data_inicio'] ?? ''),
                'data_fim_prevista' => formatarDataBR($locacao['loc_data_fim_prevista'] ?? ''),
                'data_fim_real' => formatarDataBR($locacao['loc_data_fim_real'] ?? ''),
                'valor' => formatarMoedaBR($locacao['loc_valor_total'] ?? $locacao['loc_valor_locacao'] ?? 0),
                'valor_caucao' => formatarMoedaBR($locacao['loc_valor_caucao'] ?? 0),
                'recorrencia_pagamento' => traduzirRecorrencia($locacao['loc_recorrencia_pagamento'] ?? ''),
                'inicio_pagamento' => formatarDataBR($locacao['loc_data_inicio_pagamento'] ?? ''),
                'taxa_juros' => formatarMoedaBR($locacao['loc_taxa_juros'] ?? 0),
                'taxa_multa' => formatarMoedaBR($locacao['loc_taxa_multa'] ?? 0),
                'tempo' => calcularTempoLocacao($locacao['loc_data_inicio'] ?? '', $locacao['loc_data_fim_prevista'] ?? ''),
            ],
            'locadora' => [
                'nome_completo' => $empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? '',
                'cpf_cnpj' => formatarCPFCNPJ($empresa['emp_cpf_cnpj'] ?? ''),
                'endereco' => $empresa['emp_rua'] ?? '',
                'numero' => $empresa['emp_numero'] ?? '',
                'complemento' => $empresa['emp_complemento'] ?? '',
                'bairro' => '', // Não há campo bairro na tabela empresas
                'cidade' => $empresa['emp_cidade'] ?? '',
                'estado' => $empresa['emp_estado'] ?? '',
                'cep' => $empresa['emp_cep'] ?? '',
            ],
            'data_de_hoje' => formatarDataBR(date('Y-m-d')),
        ];
        
        // Substituir todas as variáveis {{entidade.campo}} ou {{campo}}
        $conteudo = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($dados) {
            $variavel = trim($matches[1]);
            
            // Variável global simples
            if (isset($dados[$variavel])) {
                return $dados[$variavel];
            }
            
            // Variável hierárquica (entidade.campo)
            $partes = explode('.', $variavel);
            if (count($partes) === 2) {
                $entidade = $partes[0];
                $campo = $partes[1];
                
                if (isset($dados[$entidade][$campo])) {
                    return $dados[$entidade][$campo];
                }
            }
            
            // Se não encontrou, retorna a variável original
            return $matches[0];
        }, $conteudo);
        
        return $conteudo;
    }
}

if (!function_exists('traduzirRecorrencia')) {
    /**
     * Traduz código de recorrência para texto legível
     */
    function traduzirRecorrencia(?string $recorrencia): string
    {
        $traducao = [
            'diaria' => 'Diária',
            'semanal' => 'Semanal',
            'quinzenal' => 'Quinzenal',
            'mensal' => 'Mensal',
        ];
        
        return $traducao[$recorrencia] ?? $recorrencia ?? '';
    }
}

if (!function_exists('calcularTempoLocacao')) {
    /**
     * Calcula o tempo de locação em formato legível
     */
    function calcularTempoLocacao(?string $dataInicio, ?string $dataFim): string
    {
        if (empty($dataInicio) || empty($dataFim)) {
            return '';
        }
        
        try {
            $inicio = new \DateTime($dataInicio);
            $fim = new \DateTime($dataFim);
            $diff = $inicio->diff($fim);
            
            $dias = $diff->days;
            
            if ($dias === 1) {
                return '1 dia';
            } elseif ($dias < 30) {
                return $dias . ' dias';
            } elseif ($dias < 365) {
                $meses = floor($dias / 30);
                return $meses . ($meses === 1 ? ' mês' : ' meses');
            } else {
                $anos = floor($dias / 365);
                return $anos . ($anos === 1 ? ' ano' : ' anos');
            }
        } catch (\Exception $e) {
            return '';
        }
    }
}
