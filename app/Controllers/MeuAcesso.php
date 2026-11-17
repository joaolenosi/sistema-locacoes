<?php

namespace App\Controllers;

use App\Models\EmpresaModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * "Meu acesso": o usuário logado altera o telefone de login e a senha
 * da PRÓPRIA conta. O id vem sempre da sessão, nunca do formulário.
 */
class MeuAcesso extends BaseController
{
    private const SENHA_MIN = 8;
    private const MAX_TENTATIVAS = 5;

    public function index(): string
    {
        $empresa = (new EmpresaModel())->find(get_empresa_id());

        return view('admin/meu-acesso/index', [
            'title'   => 'Meu acesso',
            'empresa' => $empresa,
        ]);
    }

    public function atualizar(): ResponseInterface
    {
        $empresaId = get_empresa_id();
        $model = new EmpresaModel();
        $empresa = $empresaId > 0 ? $model->find($empresaId) : null;

        if (! $empresa) {
            return $this->erro('Sua sessão expirou. Entre novamente.', 401);
        }

        $senhaAtual = (string) $this->request->getPost('senha_atual');
        $telefone   = preg_replace('/\D/', '', (string) $this->request->getPost('telefone'));
        $novaSenha  = (string) $this->request->getPost('nova_senha');
        $confirma   = (string) $this->request->getPost('confirmar_senha');

        if ($senhaAtual === '') {
            return $this->erro('Digite sua senha atual para confirmar as alterações.');
        }

        // Proteção contra tentativas repetidas de adivinhar a senha atual
        $tentativas = (int) session()->get('meu_acesso_tentativas');
        if (! password_verify($senhaAtual, (string) ($empresa['senha'] ?? ''))) {
            $tentativas++;
            session()->set('meu_acesso_tentativas', $tentativas);
            if ($tentativas >= self::MAX_TENTATIVAS) {
                session()->destroy();
                return $this->erro('Senha atual incorreta várias vezes. Por segurança, você foi desconectado.', 401, true);
            }
            return $this->erro('A senha atual está incorreta.');
        }
        session()->remove('meu_acesso_tentativas');

        $dados = [];

        // Telefone de acesso (login)
        if ($telefone === '' || strlen($telefone) < 10 || strlen($telefone) > 11) {
            return $this->erro('Informe o telefone de acesso com DDD, por exemplo (84) 99999-9999.');
        }
        if ($telefone !== (string) $empresa['emp_telefone']) {
            $emUso = $model->where('emp_telefone', $telefone)->where('id !=', $empresaId)->countAllResults();
            if ($emUso > 0) {
                return $this->erro('Este telefone já é usado para acessar outra conta. Escolha outro número.');
            }
            $dados['emp_telefone'] = $telefone;
        }

        // Nova senha (opcional)
        if ($novaSenha !== '' || $confirma !== '') {
            if (mb_strlen($novaSenha) < self::SENHA_MIN) {
                return $this->erro('A nova senha precisa ter pelo menos ' . self::SENHA_MIN . ' caracteres.');
            }
            if ($novaSenha !== $confirma) {
                return $this->erro('A confirmação não é igual à nova senha.');
            }
            if (password_verify($novaSenha, (string) $empresa['senha'])) {
                return $this->erro('A nova senha precisa ser diferente da atual.');
            }
            $dados['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        if ($dados === []) {
            return $this->response->setJSON([
                'success' => true,
                'changed' => false,
                'message' => 'Nada foi alterado: o telefone é o mesmo e nenhuma senha nova foi digitada.',
                'csrf'    => csrf_hash(),
            ]);
        }

        if (! $model->update($empresaId, $dados)) {
            return $this->erro('Não foi possível salvar. Tente novamente.', 500);
        }

        session()->regenerate(true);

        $partes = [];
        if (isset($dados['emp_telefone'])) {
            $partes[] = 'o telefone de acesso agora é <strong>' . esc($this->formatarTelefone($telefone)) . '</strong>';
        }
        if (isset($dados['senha'])) {
            $partes[] = 'sua senha foi alterada';
        }

        return $this->response->setJSON([
            'success'  => true,
            'changed'  => true,
            'telefone' => $this->formatarTelefone($telefone),
            'message'  => 'Pronto: ' . implode(' e ', $partes) . '. Use os novos dados no próximo acesso.',
            'csrf'     => csrf_hash(),
        ]);
    }

    private function erro(string $mensagem, int $status = 422, bool $logout = false): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => false,
            'message' => $mensagem,
            'logout'  => $logout,
            'csrf'    => csrf_hash(),
        ]);
    }

    private function formatarTelefone(string $tel): string
    {
        if (strlen($tel) === 11) {
            return sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 5), substr($tel, 7));
        }
        if (strlen($tel) === 10) {
            return sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 4), substr($tel, 6));
        }
        return $tel;
    }
}
