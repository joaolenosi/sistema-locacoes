<?php

namespace App\Controllers;

class Cobrancas extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Cobranças',
        ];

        try {
            return view('admin/cobrancas/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}

