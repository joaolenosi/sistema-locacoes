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

    /**
     * Serve a imagem de logo/foto da empresa para uso em <img src="...">.
     */
    public function logo()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403);
        }

        $empresaModel = new EmpresaModel();
        $empresa = $empresaModel->find($empresaId);
        if (!$empresa || empty($empresa['emp_logo'])) {
            return $this->response->setStatusCode(404);
        }

        $path = WRITEPATH . $empresa['emp_logo'];
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setBody(file_get_contents($path));
    }
}
