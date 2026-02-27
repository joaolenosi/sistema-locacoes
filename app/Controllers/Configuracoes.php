<?php

namespace App\Controllers;

use App\Models\EmpresaModel;
use App\Models\PlanoModel;

class Configuracoes extends BaseController
{
    public function index(): string
    {
        $empresaId = get_empresa_id();
        $empresaModel = new EmpresaModel();
        $planoModel = new PlanoModel();

        $empresa = $empresaModel->find($empresaId);

        $planosDb = $planoModel
            ->where('pla_status', 'ativo')
            ->orderBy('pla_ordem', 'ASC')
            ->findAll();

        $planos = $this->mapPlanosForView($planosDb);

        // Plano atual: por enquanto, UI (sem integração de assinatura)
        $plano_atual = [
            'nome' => 'Período de Teste',
            'dias_restantes' => 5,
        ];

        $data = [
            'title' => 'Configurações',
            'planos' => $planos,
            'plano_atual' => $plano_atual,
            'empresa' => $empresa,
        ];
        
        try {
            return view('admin/configuracoes/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listarPlanos()
    {
        try {
            $planoModel = new PlanoModel();
            $planosDb = $planoModel
                ->where('pla_status', 'ativo')
                ->orderBy('pla_ordem', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => $this->mapPlanosForView($planosDb),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar planos.',
            ]);
        }
    }

    public function atualizarEmpresa()
    {
        try {
            $empresaId = get_empresa_id();
            $empresaModel = new EmpresaModel(); 
            $existing = $empresaModel->find($empresaId);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Empresa não encontrada.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeEmpresaPayload($payload);
            $validationError = $this->validateEmpresaPayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            // CNPJ não pode ser alterado
            unset($data['emp_cpf_cnpj']);

            // Upload opcional de foto/logo da empresa
            $file = $this->request->getFile('foto_empresa');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Arquivo de imagem inválido.',
                    ]);
                }

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                $mimeType = $file->getMimeType();
                if (!in_array($mimeType, $allowedMimes, true)) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Formato de imagem não permitido. Use JPG, PNG ou WebP.',
                    ]);
                }

                if ($file->getSize() > 5 * 1024 * 1024) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Arquivo muito grande. Tamanho máximo: 5 MB.',
                    ]);
                }

                $dir = WRITEPATH . 'uploads/' . $empresaId . '/empresa/';
                if (!is_dir($dir)) {
                    if (!@mkdir($dir, 0755, true)) {
                        return $this->response->setStatusCode(500)->setJSON([
                            'success' => false,
                            'message' => 'Não foi possível criar o diretório para armazenar a imagem.',
                        ]);
                    }
                    @file_put_contents(
                        $dir . 'index.html',
                        '<!DOCTYPE html><html><head><title>403</title></head><body><p>Forbidden</p></body></html>'
                    );
                }

                $ext = $file->getClientExtension() ?: 'jpg';
                $ext = preg_replace('/[^a-z0-9]/i', '', $ext) ?: 'jpg';
                $nome = 'logo_empresa.' . $ext;

                // Remove arquivo antigo se existir e for diferente
                if (!empty($existing['emp_logo'] ?? null)) {
                    $oldPath = WRITEPATH . $existing['emp_logo'];
                    if (is_file($oldPath) && realpath($oldPath) !== realpath($dir . $nome)) {
                        @unlink($oldPath);
                    }
                }

                $file->move($dir, $nome, true);

                $caminhoRelativo = 'uploads/' . $empresaId . '/empresa/' . $nome;
                $data['emp_logo'] = $caminhoRelativo;
            }

            // manter empresa fixa (não confiar no payload)
            $ok = $empresaModel->update($empresaId, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar os dados da empresa.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dados da empresa atualizados com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar dados da empresa.',
            ]);
        }
    }

    /**
     * Converte linhas do banco (pla_*) no formato esperado pela view/JS atual.
     */
    private function mapPlanosForView(array $planosDb): array
    {
        $out = [];
        foreach ($planosDb as $p) {
            $out[] = [
                'id' => (int) ($p['id'] ?? 0),
                'nome' => (string) ($p['pla_nome'] ?? ''),
                'slug' => (string) ($p['pla_slug'] ?? ''),
                'descricao' => (string) ($p['pla_descricao'] ?? ''),
                'preco_mensal' => (float) ($p['pla_preco_mensal'] ?? 0),
                'preco_anual' => (float) ($p['pla_preco_anual'] ?? 0),
                'desconto_anual' => (float) ($p['pla_desconto_anual_percentual'] ?? 0),
                'limite_veiculos' => $p['pla_limite_veiculos'] ?? null,
                'limite_locatarios' => $p['pla_limite_locatarios'] ?? null,
                'limite_locacoes' => $p['pla_limite_locacoes'] ?? null,
                'suporte_tipo' => (string) ($p['pla_suporte_tipo'] ?? 'email'),
                'backup_diario' => (bool) ($p['pla_backup_diario'] ?? 0),
                'relatorios_avancados' => (bool) ($p['pla_relatorios_avancados'] ?? 0),
                'acesso_antecipado' => (bool) ($p['pla_acesso_antecipado'] ?? 0),
                'ordem' => (int) ($p['pla_ordem'] ?? 1),
            ];
        }

        // Mantém a UX de destaque (se existir slug "flow", marca como mais escolhido)
        foreach ($out as $idx => $plano) {
            if (($plano['slug'] ?? '') === 'flow') {
                $out[$idx]['mais_escolhido'] = true;
                break;
            }
        }

        return array_values($out);
    }

    private function normalizeEmpresaPayload(array $payload): array
    {
        // Normaliza CPF/CNPJ e CEP sem formatação
        $cpfCnpj = preg_replace('/\\D/', '', (string) ($payload['cpf_cnpj'] ?? '')) ?: null;
        $cep = preg_replace('/\\D/', '', (string) ($payload['cep'] ?? '')) ?: null;
        $telefone = preg_replace('/\\D/', '', (string) ($payload['telefone'] ?? '')) ?: null;

        $tipo = trim((string) ($payload['emp_tipo'] ?? ($payload['tipo_negocio'] ?? '')));

        $data = [
            'emp_nome' => trim((string) ($payload['nome_empresa'] ?? '')),
            'emp_cpf_cnpj' => $cpfCnpj,
            'emp_telefone' => $telefone,
            'emp_email' => trim((string) ($payload['email'] ?? '')) ?: null,
            'emp_cep' => $cep,
            'emp_rua' => trim((string) ($payload['endereco'] ?? '')) ?: null,
            'emp_numero' => trim((string) ($payload['numero'] ?? '')) ?: null,
            'emp_complemento' => trim((string) ($payload['complemento'] ?? '')) ?: null,
            'emp_cidade' => trim((string) ($payload['cidade'] ?? '')) ?: null,
            'emp_estado' => trim((string) ($payload['estado'] ?? '')) ?: null,
            'emp_obs' => trim((string) ($payload['observacoes'] ?? '')) ?: null,
            'emp_inscricao_estadual' => trim((string) ($payload['inscricao_estadual'] ?? '')) ?: null,
            'emp_inscricao_municipal' => trim((string) ($payload['inscricao_municipal'] ?? '')) ?: null,
            'emp_site' => trim((string) ($payload['site'] ?? '')) ?: null,
        ];

        // Só atualiza emp_tipo quando vier preenchido no payload (evita gravar NULL e quebrar NOT NULL)
        if ($tipo !== '') {
            $data['emp_tipo'] = $tipo;
        }

        // Senha: só atualiza se foi preenchida (hash)
        $senha = trim((string) ($payload['senha'] ?? ''));
        if ($senha !== '') {
            $data['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        return $data;
    }

    private function validateEmpresaPayload(array $data): ?string
    {
        if (($data['emp_nome'] ?? '') === '') return 'Informe o nome da empresa.';

        if (array_key_exists('emp_tipo', $data)) {
            $allowedTipos = ['salao', 'locadora', 'clinica', 'outro'];
            if (!in_array($data['emp_tipo'], $allowedTipos, true)) {
                return 'Tipo de negócio inválido.';
            }
        }

        return null;
    }
}
