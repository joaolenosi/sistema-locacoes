<?php

namespace App\Controllers;

class Configuracoes extends BaseController
{
    public function index(): string
    {
        // Dados simulados dos planos (baseado no SQL fornecido)
        $planos = [
            [
                'id' => 1,
                'nome' => 'Pulse',
                'slug' => 'pulse',
                'descricao' => 'Plano ideal para pequenas operações',
                'preco_mensal' => 40.49,
                'preco_anual' => 340.13,
                'desconto_anual' => 30.00,
                'limite_veiculos' => 5,
                'limite_locatarios' => 50,
                'limite_locacoes' => 100,
                'suporte_tipo' => 'email',
                'backup_diario' => false,
                'relatorios_avancados' => false,
                'acesso_antecipado' => false,
                'ordem' => 1
            ],
            [
                'id' => 2,
                'nome' => 'Flow',
                'slug' => 'flow',
                'descricao' => 'Plano mais completo para negócios em crescimento',
                'preco_mensal' => 64.79,
                'preco_anual' => 544.25,
                'desconto_anual' => 30.00,
                'limite_veiculos' => 25,
                'limite_locatarios' => null,
                'limite_locacoes' => null,
                'suporte_tipo' => 'whatsapp',
                'backup_diario' => true,
                'relatorios_avancados' => false,
                'acesso_antecipado' => false,
                'ordem' => 2,
                'mais_escolhido' => true
            ],
            [
                'id' => 3,
                'nome' => 'Orbit',
                'slug' => 'orbit',
                'descricao' => 'Plano avançado para grandes operações',
                'preco_mensal' => 89.99,
                'preco_anual' => 755.93,
                'desconto_anual' => 30.00,
                'limite_veiculos' => null,
                'limite_locatarios' => null,
                'limite_locacoes' => null,
                'suporte_tipo' => 'prioritario',
                'backup_diario' => true,
                'relatorios_avancados' => true,
                'acesso_antecipado' => true,
                'ordem' => 3
            ]
        ];

        // Dados do plano atual (simulado)
        $plano_atual = [
            'nome' => 'Período de Teste',
            'dias_restantes' => 5
        ];

        $data = [
            'title' => 'Configurações',
            'planos' => $planos,
            'plano_atual' => $plano_atual,
        ];
        
        try {
            return view('admin/configuracoes/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
