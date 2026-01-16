<?php

namespace App\Controllers;

class Veiculos extends BaseController
{
    public function index(): string
    {
        $totalVeiculos = 10;
        $veiculosOcupados = 3;

        $data = [
            'title' => 'Listagem de Veículos',
            // Cards (dados simulados para layout, sem consulta em view)
            'total_veiculos' => $totalVeiculos,
            'veiculos_livres' => $totalVeiculos - $veiculosOcupados,
            'veiculos_ocupados' => $veiculosOcupados,
        ];
        
        try {
            return view('admin/veiculos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
