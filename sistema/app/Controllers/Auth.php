<?php

namespace App\Controllers;

use App\Models\EmpresaModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('empresa_logado')) {
            return redirect()->to(base_url());
        }
        return view('auth/login', ['title' => 'Login']);
    }

    public function processar(): RedirectResponse
    {
        $telefone = preg_replace('/\D/', '', (string) $this->request->getPost('telefone'));
        $senha = (string) $this->request->getPost('senha');

        if ($telefone === '' || $senha === '') {
            return redirect()->back()->withInput()->with('error', 'Informe telefone e senha.');
        }

        $model = new EmpresaModel();
        $empresa = $model->where('emp_telefone', $telefone)->first();

        if (! $empresa) {
            return redirect()->back()->withInput()->with('error', 'Telefone ou senha incorretos.');
        }

        $hash = $empresa['senha'] ?? '';
        if ($hash === '' || ! password_verify($senha, $hash)) {
            return redirect()->back()->withInput()->with('error', 'Telefone ou senha incorretos.');
        }

        session()->set([
            'empresa_logado' => true,
            'empresa_id' => (int) $empresa['id'],
            'empresa_nome' => $empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? '',
        ]);

        return redirect()->to(base_url());
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
