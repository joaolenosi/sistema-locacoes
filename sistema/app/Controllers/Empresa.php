<?php

namespace App\Controllers;

use App\Models\EmpresaModel;

class Empresa extends BaseController
{
    public function index(): string
    {
        $empresaId = get_empresa_id();
        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->find($empresaId);

        $data = [
            'title' => 'Cadastro da Empresa',
            'empresa' => $empresa,
        ];

        return view('admin/empresa/index', $data);
    }
}
