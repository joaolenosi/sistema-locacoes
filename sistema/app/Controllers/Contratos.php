<?php

namespace App\Controllers;

class Contratos extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Contratos',
        ];
        
        try {
            return view('admin/contratos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
