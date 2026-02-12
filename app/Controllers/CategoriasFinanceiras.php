<?php

namespace App\Controllers;

class CategoriasFinanceiras extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Categorias Financeiras',
        ];
        
        try {
            return view('admin/categorias-financeiras/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
