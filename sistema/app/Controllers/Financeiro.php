<?php

namespace App\Controllers;

class Financeiro extends BaseController
{
    public function index(): string
    {
        $receitasMesAtual = 2323.00;
        $despesasMesAtual = 400.00;

        $data = [
            'title' => 'Listagem Financeira',
            // Cards (dados simulados para layout, sem consulta em view)
            'receitas_mes_atual' => $receitasMesAtual,
            'despesas_mes_atual' => $despesasMesAtual,
            'lucro_mes_atual' => $receitasMesAtual - $despesasMesAtual,
        ];
        
        try {
            return view('admin/financeiro/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function movimentacoes(): string
    {
        $data = [
            'title' => 'Movimentações',
            // Dados simulados para testes (sem consulta em view)
            'categorias_receita' => [
                ['id' => 1, 'nome' => 'Locação de veículos'],
                ['id' => 2, 'nome' => 'Caução'],
                ['id' => 3, 'nome' => 'Multa por atraso'],
                ['id' => 4, 'nome' => 'Taxa administrativa'],
                ['id' => 5, 'nome' => 'Serviços adicionais'],
                ['id' => 6, 'nome' => 'Venda de serviços'],
            ],
            'categorias_despesa' => [
                ['id' => 7, 'nome' => 'Combustível'],
                ['id' => 8, 'nome' => 'Manutenção de veículos'],
                ['id' => 9, 'nome' => 'Peças e acessórios'],
                ['id' => 10, 'nome' => 'Seguro'],
                ['id' => 11, 'nome' => 'IPVA'],
                ['id' => 12, 'nome' => 'Licenciamento'],
                ['id' => 13, 'nome' => 'Multas de trânsito'],
                ['id' => 14, 'nome' => 'Internet'],
                ['id' => 15, 'nome' => 'Aluguel'],
                ['id' => 16, 'nome' => 'Energia elétrica'],
                ['id' => 17, 'nome' => 'Água'],
                ['id' => 18, 'nome' => 'Folha de pagamento'],
            ],
            'locacoes' => [
                ['id' => 1, 'nome' => 'Locação #001 - João Silva'],
                ['id' => 2, 'nome' => 'Locação #002 - Maria Santos'],
                ['id' => 3, 'nome' => 'Locação #003 - Pedro Oliveira'],
            ],
            'formas_pagamento' => [
                ['id' => 'dinheiro', 'nome' => 'Dinheiro'],
                ['id' => 'pix', 'nome' => 'PIX'],
                ['id' => 'cartao_credito', 'nome' => 'Cartão de Crédito'],
                ['id' => 'cartao_debito', 'nome' => 'Cartão de Débito'],
                ['id' => 'boleto', 'nome' => 'Boleto'],
                ['id' => 'transferencia', 'nome' => 'Transferência'],
            ],
        ];

        try {
            return view('admin/financeiro/movimentacoes', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
