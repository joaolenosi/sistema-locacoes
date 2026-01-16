<?php

namespace App\Controllers;

class Manutencao extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Manutenções',
        ];
        
        try {
            return view('admin/manutencoes/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
