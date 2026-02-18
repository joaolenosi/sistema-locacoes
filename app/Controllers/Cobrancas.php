<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class Cobrancas extends BaseController
{
    public function index(): string
    {
        $clienteModel = new ClienteModel();
        
        $empresaId = get_empresa_id();
        $locatarios = $clienteModel
            ->where('cli_empresa_id', $empresaId)
            ->where('cli_ativo', 1)
            ->orderBy('cli_nome', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Cobranças',
            'locatarios' => $locatarios,
        ];

        try {
            return view('admin/cobrancas/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}

