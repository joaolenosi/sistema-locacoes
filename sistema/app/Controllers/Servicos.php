<?php

namespace App\Controllers;

class Servicos extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Serviços',
        ];
        
        try {
            return view('admin/servicos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
