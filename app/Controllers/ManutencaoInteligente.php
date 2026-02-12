<?php

namespace App\Controllers;

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
}
