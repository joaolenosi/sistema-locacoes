<?php

namespace App\Controllers;

class Locatarios extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Listagem de Locatários',
        ];
        
        try {
            return view('admin/locatarios/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
