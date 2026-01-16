<?php

namespace App\Controllers;

class Produtos extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Produtos',
        ];
        
        try {
            return view('admin/produtos/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
