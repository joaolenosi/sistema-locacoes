<?php

namespace App\Controllers;

use App\Models\ChecklistConfigModel;
use App\Models\ChecklistItemModel;

class ChecklistConfig extends BaseController
{
    public function index(): string
    {
        $data = ['title' => 'Configuração do Checklist'];
        return view('admin/cadastro/checklist/index', $data);
    }

    public function getConfig()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $model = new ChecklistConfigModel();
        $config = $model->getByEmpresa($empresaId);
        return $this->response->setJSON(['success' => true, 'data' => $config]);
    }

    /** Serve a imagem de configuração do checklist (para preview). Se não houver, retorna 404. */
    public function imagem()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(403);
        }
        $model = new ChecklistConfigModel();
        $config = $model->getByEmpresa($empresaId);
        if (!$config || empty($config['cfc_imagem_caminho'])) {
            return $this->response->setStatusCode(404);
        }
        $path = WRITEPATH . $config['cfc_imagem_caminho'];
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }
        $mime = mime_content_type($path) ?: 'image/jpeg';
        return $this->response->setHeader('Content-Type', $mime)->setBody(file_get_contents($path));
    }

    public function uploadImagem()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $file = $this->request->getFile('imagem');
        if (!$file || !$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Nenhum arquivo válido enviado.']);
        }
        $mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($file->getMimeType(), $mimes, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Formato não permitido. Use JPG, PNG ou WebP.']);
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5 MB.']);
        }
        $dir = WRITEPATH . 'uploads/' . $empresaId . '/checklist/';
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível criar o diretório.']);
            }
            @file_put_contents($dir . 'index.html', '<!DOCTYPE html><html><head><title>403</title></head><body><p>Forbidden</p></body></html>');
        }
        $ext = $file->getClientExtension() ?: 'jpg';
        $nome = 'imagem_veiculo.' . preg_replace('/[^a-z0-9]/i', '', $ext);
        $file->move($dir, $nome);
        $caminho = 'uploads/' . $empresaId . '/checklist/' . $nome;
        $configModel = new ChecklistConfigModel();
        $config = $configModel->getByEmpresa($empresaId);
        if ($config) {
            $oldPath = WRITEPATH . ($config['cfc_imagem_caminho'] ?? '');
            if ($oldPath && is_file($oldPath) && $oldPath !== $dir . $nome) {
                @unlink($oldPath);
            }
            $configModel->update($config['id'], ['cfc_imagem_caminho' => $caminho]);
        } else {
            $configModel->insert(['cfc_empresa_id' => $empresaId, 'cfc_imagem_caminho' => $caminho]);
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Imagem salva.', 'caminho' => $caminho]);
    }

    public function listarItens()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $model = new ChecklistItemModel();
        $itens = $model->getByEmpresa($empresaId);
        return $this->response->setJSON(['success' => true, 'data' => $itens]);
    }

    public function criarItem()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $nome = trim((string) $this->request->getPost('chi_nome'));
        if ($nome === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Informe o nome do item.']);
        }
        $model = new ChecklistItemModel();
        $ordem = (int) $model->where('chi_empresa_id', $empresaId)->countAllResults();
        $id = $model->insert([
            'chi_empresa_id' => $empresaId,
            'chi_nome' => $nome,
            'chi_ordem' => $ordem,
        ], true);
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao criar item.']);
        }
        $item = $model->find($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Item adicionado.', 'data' => $item]);
    }

    public function atualizarItem($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $id = (int) $id;
        $model = new ChecklistItemModel();
        $item = $model->where('id', $id)->where('chi_empresa_id', $empresaId)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Item não encontrado.']);
        }
        $nome = trim((string) $this->request->getPost('chi_nome'));
        if ($nome === '') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Informe o nome do item.']);
        }
        $model->update($id, ['chi_nome' => $nome]);
        return $this->response->setJSON(['success' => true, 'message' => 'Item atualizado.', 'data' => $model->find($id)]);
    }

    public function deletarItem($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $id = (int) $id;
        $model = new ChecklistItemModel();
        $item = $model->where('id', $id)->where('chi_empresa_id', $empresaId)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Item não encontrado.']);
        }
        $model->delete($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Item removido.']);
    }
}
