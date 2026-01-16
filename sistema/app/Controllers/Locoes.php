<?php

namespace App\Controllers;

class Locoes extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Locações',
            // Cards (dados simulados para layout, sem consulta em view)
            'entradas' => 12,
            'saidas' => 9,
            'em_atraso' => 2,
        ];
        
        try {
            return view('admin/locacoes/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
