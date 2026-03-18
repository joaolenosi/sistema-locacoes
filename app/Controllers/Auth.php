<?php

namespace App\Controllers;

use App\Models\EmpresaModel;
use App\Models\FinanceiroModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    private const VALOR_MENSALIDADE = 59.90;
    private const DESCRICAO_MENSALIDADE = 'Mensalidade Sistema de Locações';
    private const CODIGO_PIX = '00020126330014BR.GOV.BCB.PIX0111094367194935204000053039865406599.005802BR5901N6001C62220518MENSALIDADESISTEMA6304324C';

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

        $this->gerarCobrancaMensalSeNecessario((int) $empresa['id']);

        return redirect()->to(base_url());
    }

    private function gerarCobrancaMensalSeNecessario(int $empresaId): void
    {
        try {
            $financeiroModel = new FinanceiroModel();
            $mesAtual = date('Y-m');

            if (! $financeiroModel->existeCobrancaMes($empresaId, $mesAtual)) {
                $financeiroModel->criarCobrancaMensal(
                    $empresaId,
                    self::DESCRICAO_MENSALIDADE,
                    self::VALOR_MENSALIDADE,
                    self::CODIGO_PIX
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao gerar cobrança mensal: ' . $e->getMessage());
        }
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
