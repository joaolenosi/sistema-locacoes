<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Dashboard',
            // Dados financeiros
            'faturamento_mes_atual' => 2323.00,
            'faturamento_mes_anterior' => 0.00,
            'crescimento_percentual' => 0,
            'caixa_total' => 1923.00,
            'lucro_mes_atual' => 1923.00,
            // Dados de locação
            'veiculos_disponiveis' => 0,
            'total_veiculos' => 1,
            'cobrancas_atraso' => 0,
            'precisa_manutencao' => 0,
            'cnhs_vencidas' => 0,
            // Dados para gráficos
            'fluxo_caixa' => [
                ['mes' => 'Jan/25', 'valor' => 0],
                ['mes' => 'Fev/25', 'valor' => 0],
                ['mes' => 'Mar/25', 'valor' => 0],
                ['mes' => 'Abr/25', 'valor' => 0],
                ['mes' => 'Mai/25', 'valor' => 0],
                ['mes' => 'Jun/25', 'valor' => 0],
                ['mes' => 'Jul/25', 'valor' => 0],
                ['mes' => 'Ago/25', 'valor' => 0],
                ['mes' => 'Set/25', 'valor' => 0],
                ['mes' => 'Out/25', 'valor' => 0],
                ['mes' => 'Nov/25', 'valor' => 0],
                ['mes' => 'Dez/25', 'valor' => 0],
                ['mes' => 'Jan/26', 'valor' => 2304],
            ],
            'tipos_movimentacao' => [
                ['tipo' => 'Entrada', 'valor' => 50],
                ['tipo' => 'Saída', 'valor' => 50],
            ],
            'veiculos_status' => [
                ['status' => 'Ocupados', 'quantidade' => 1],
                ['status' => 'Livres', 'quantidade' => 0],
                ['status' => 'Manutenção', 'quantidade' => 0],
            ],
        ];
        
        try {
            return view('admin/dashboard', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
