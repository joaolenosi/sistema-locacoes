<?php

namespace App\Controllers;

use App\Models\ChecklistModel;
use App\Models\ChecklistConfigModel;
use App\Models\ChecklistItemModel;
use App\Models\ChecklistMarcacaoModel;
use App\Models\ChecklistAnexoModel;
use App\Models\LocacaoModel;
use App\Models\VeiculoModel;
use App\Models\EmpresaModel;

class Checklist extends BaseController
{
    public function index(): string
    {
        $data = ['title' => 'Checklists'];
        return view('admin/checklist/index', $data);
    }

    public function listar()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $db = \Config\Database::connect();
        $rows = $db->table('checklists')
            ->select('checklists.*, veiculos.vei_placa, veiculos.vei_modelo, veiculos.vei_marca')
            ->join('veiculos', 'veiculos.id = checklists.chk_veiculo_id', 'left')
            ->where('checklists.chk_empresa_id', $empresaId)
            ->orderBy('checklists.chk_data', 'DESC')
            ->orderBy('checklists.id', 'DESC')
            ->get()
            ->getResultArray();
        return $this->response->setJSON(['success' => true, 'data' => $rows]);
    }

    public function novo()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return redirect()->to(base_url('login'));
        }
        $veiculoModel = new VeiculoModel();
        $veiculos = $veiculoModel->where('vei_empresa_id', $empresaId)->orderBy('vei_placa')->findAll();
        $locacaoModel = new LocacaoModel();
        $locacoes = $locacaoModel->builderWithJoins()
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->orderBy('locacoes.created_at', 'DESC')
            ->get()
            ->getResultArray();
        $data = [
            'title' => 'Novo Checklist',
            'veiculos' => $veiculos,
            'locacoes' => $locacoes,
        ];
        return view('admin/checklist/novo', $data);
    }

    public function editar($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return redirect()->to(base_url('login'));
        }
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist) {
            return redirect()->to(base_url('admin/checklist'))->with('error', 'Checklist não encontrado.');
        }
        $db = \Config\Database::connect();
        $veiculo = $checklist['chk_veiculo_id'] ? $db->table('veiculos')->where('id', $checklist['chk_veiculo_id'])->get()->getRowArray() : null;
        $locacao = $checklist['chk_locacao_id'] ? $db->table('locacoes')->where('id', $checklist['chk_locacao_id'])->get()->getRowArray() : null;
        $itemModel = new ChecklistItemModel();
        $itens = $itemModel->getByEmpresa($empresaId);
        $marcacaoModel = new ChecklistMarcacaoModel();
        $marcacoes = $marcacaoModel->getByChecklist($id, $empresaId);
        $anexoModel = new ChecklistAnexoModel();
        $anexos = $anexoModel->getByChecklist($id, $empresaId);
        $configModel = new ChecklistConfigModel();
        $config = $configModel->getByEmpresa($empresaId);
        $imagemBase = ($config && !empty($config['cfc_imagem_caminho'])) ? base_url('admin/cadastro/checklist/imagem') : asset_url('assets/admin/images/checklist-img-base.jpg');
        $imagemDesenhoUrl = !empty($checklist['chk_imagem_desenho_caminho']) ? base_url('admin/checklist/desenho/' . $id) : null;
        $data = [
            'title' => 'Editar Checklist',
            'checklist' => $checklist,
            'veiculo' => $veiculo,
            'locacao' => $locacao,
            'itens' => $itens,
            'marcacoes' => $marcacoes,
            'anexos' => $anexos,
            'imagem_base_url' => $imagemBase,
            'imagem_desenho_url' => $imagemDesenhoUrl,
        ];
        return view('admin/checklist/editar', $data);
    }

    public function salvar()
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $post = $this->request->getPost();
        $id = isset($post['chk_id']) ? (int) $post['chk_id'] : 0;
        $checklistModel = new ChecklistModel();
        $chkTipo = isset($post['chk_tipo']) && in_array($post['chk_tipo'], ['checkin', 'checkout'], true) ? $post['chk_tipo'] : 'checkout';
        $data = [
            'chk_empresa_id' => $empresaId,
            'chk_locacao_id' => !empty($post['chk_locacao_id']) ? (int) $post['chk_locacao_id'] : null,
            'chk_veiculo_id' => !empty($post['chk_veiculo_id']) ? (int) $post['chk_veiculo_id'] : null,
            'chk_data' => $post['chk_data'] ?? date('Y-m-d'),
            'chk_tipo' => $chkTipo,
            'chk_hodometro_saida' => !empty($post['chk_hodometro_saida']) ? (int) preg_replace('/\D/', '', $post['chk_hodometro_saida']) : null,
            'chk_hodometro_chegada' => !empty($post['chk_hodometro_chegada']) ? (int) preg_replace('/\D/', '', $post['chk_hodometro_chegada']) : null,
            'chk_data_saida' => !empty($post['chk_data_saida']) ? $post['chk_data_saida'] : null,
            'chk_data_chegada' => !empty($post['chk_data_chegada']) ? $post['chk_data_chegada'] : null,
            'chk_responsavel_entrega' => trim((string) ($post['chk_responsavel_entrega'] ?? '')) ?: null,
            'chk_responsavel_devolucao' => trim((string) ($post['chk_responsavel_devolucao'] ?? '')) ?: null,
            'chk_anotacoes' => trim((string) ($post['chk_anotacoes'] ?? '')) ?: null,
        ];
        if ($id > 0) {
            $exist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
            if (!$exist) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Checklist não encontrado.']);
            }
            $checklistModel->update($id, $data);
            $checklistId = $id;
        } else {
            $checklistId = $checklistModel->insert($data, true);
            if (!$checklistId) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao criar checklist.']);
            }
        }
        $marcacaoModel = new ChecklistMarcacaoModel();
        $marcacaoModel->where('chm_checklist_id', $checklistId)->where('chm_empresa_id', $empresaId)->delete();
        foreach ($post as $key => $val) {
            if (strpos($key, 'marcacao_') === 0) {
                $itemId = (int) str_replace('marcacao_', '', $key);
                if ($itemId < 1) continue;
                $val = in_array(strtolower((string) $val), ['ok', 'nao'], true) ? strtolower((string) $val) : 'nao';
                $marcacaoModel->insert([
                    'chm_empresa_id' => $empresaId,
                    'chm_checklist_id' => $checklistId,
                    'chm_item_id' => $itemId,
                    'chm_valor' => $val,
                ]);
            }
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Salvo.', 'id' => $checklistId]);
    }

    public function salvarImagemDesenho($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Checklist não encontrado.']);
        }
        $base64 = $this->request->getPost('imagem');
        if (empty($base64) || strpos($base64, 'data:image') !== 0) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Imagem inválida.']);
        }
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64, $m)) {
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            $bin = base64_decode($m[2], true);
            if ($bin === false) {
                return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Dados da imagem inválidos.']);
            }
        } else {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Formato inválido.']);
        }
        $dir = WRITEPATH . 'uploads/' . $empresaId . '/checklist/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
            @file_put_contents($dir . 'index.html', '<!DOCTYPE html><html><body><p>403</p></body></html>');
        }
        $filename = $id . '_desenho.png';
        $path = $dir . $filename;
        if (file_put_contents($path, $bin) === false) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao salvar imagem.']);
        }
        $caminho = 'uploads/' . $empresaId . '/checklist/' . $filename;
        $checklistModel->update($id, ['chk_imagem_desenho_caminho' => $caminho]);
        return $this->response->setJSON(['success' => true, 'message' => 'Desenho salvo.', 'caminho' => $caminho]);
    }

    private const ANEXO_MAX_SIZE = 5 * 1024 * 1024;
    private const ANEXO_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'application/pdf'];

    public function uploadAnexo($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Checklist não encontrado.']);
        }
        $files = $this->request->getFileMultiple('anexos');
        if (empty($files) || (count($files) === 1 && $files[0]->getError() === UPLOAD_ERR_NO_FILE)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
        }
        $dir = WRITEPATH . 'uploads/' . $empresaId . '/checklist/' . $id . '/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
            @file_put_contents($dir . 'index.html', '<!DOCTYPE html><html><body><p>403</p></body></html>');
        }
        $anexoModel = new ChecklistAnexoModel();
        $ordem = (int) $anexoModel->where('cha_checklist_id', $id)->countAllResults();
        foreach ($files as $file) {
            if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK || $file->getSize() > self::ANEXO_MAX_SIZE) continue;
            $mime = $file->getMimeType();
            if (!in_array($mime, self::ANEXO_MIMES, true)) continue;
            $ext = $file->getClientExtension() ?: 'bin';
            $nome = bin2hex(random_bytes(8)) . '_' . time() . '.' . preg_replace('/[^a-z0-9]/i', '', $ext);
            $file->move($dir, $nome);
            $caminho = 'uploads/' . $empresaId . '/checklist/' . $id . '/' . $nome;
            $anexoModel->insert([
                'cha_empresa_id' => $empresaId,
                'cha_checklist_id' => $id,
                'cha_nome_arquivo' => $file->getClientName(),
                'cha_caminho' => $caminho,
                'cha_tamanho' => $file->getSize(),
                'cha_tipo' => $mime,
                'cha_ordem' => $ordem++,
            ]);
        }
        $anexos = $anexoModel->getByChecklist($id, $empresaId);
        return $this->response->setJSON(['success' => true, 'message' => 'Anexos enviados.', 'anexos' => $anexos]);
    }

    public function deletarAnexo($anexoId)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $anexoId = (int) $anexoId;
        $anexoModel = new ChecklistAnexoModel();
        $anexo = $anexoModel->where('id', $anexoId)->where('cha_empresa_id', $empresaId)->first();
        if (!$anexo) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Anexo não encontrado.']);
        }
        $path = WRITEPATH . $anexo['cha_caminho'];
        if (is_file($path)) @unlink($path);
        $anexoModel->delete($anexoId);
        return $this->response->setJSON(['success' => true, 'message' => 'Anexo removido.']);
    }

    public function excluir($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
        }
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Checklist não encontrado.']);
        }
        $marcacaoModel = new ChecklistMarcacaoModel();
        $marcacaoModel->where('chm_checklist_id', $id)->where('chm_empresa_id', $empresaId)->delete();
        $anexoModel = new ChecklistAnexoModel();
        $anexos = $anexoModel->where('cha_checklist_id', $id)->where('cha_empresa_id', $empresaId)->findAll();
        foreach ($anexos as $a) {
            $path = WRITEPATH . $a['cha_caminho'];
            if (is_file($path)) @unlink($path);
            $anexoModel->delete($a['id']);
        }
        if (!empty($checklist['chk_imagem_desenho_caminho'])) {
            $pathDesenho = WRITEPATH . $checklist['chk_imagem_desenho_caminho'];
            if (is_file($pathDesenho)) @unlink($pathDesenho);
        }
        $dirAnexos = WRITEPATH . 'uploads/' . $empresaId . '/checklist/' . $id . '/';
        if (is_dir($dirAnexos)) {
            array_map('unlink', glob($dirAnexos . '*'));
            @rmdir($dirAnexos);
        }
        $checklistModel->delete($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Checklist excluído.']);
    }

    /** Serve a imagem de desenho do checklist (para exibição na tela de edição). */
    public function desenho($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) return $this->response->setStatusCode(403);
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist || empty($checklist['chk_imagem_desenho_caminho'])) return $this->response->setStatusCode(404);
        $path = WRITEPATH . $checklist['chk_imagem_desenho_caminho'];
        if (!is_file($path)) return $this->response->setStatusCode(404);
        return $this->response->setHeader('Content-Type', 'image/png')->setBody(file_get_contents($path));
    }

    public function servirAnexo($anexoId)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) return $this->response->setStatusCode(403);
        $anexoId = (int) $anexoId;
        $anexoModel = new ChecklistAnexoModel();
        $anexo = $anexoModel->where('id', $anexoId)->where('cha_empresa_id', $empresaId)->first();
        if (!$anexo) return $this->response->setStatusCode(404);
        $path = WRITEPATH . $anexo['cha_caminho'];
        if (!is_file($path)) return $this->response->setStatusCode(404);
        $mime = $anexo['cha_tipo'] ?: 'application/octet-stream';
        return $this->response->setHeader('Content-Type', $mime)->setBody(file_get_contents($path));
    }

    public function pdf($id)
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) return $this->response->setStatusCode(403);
        $id = (int) $id;
        $checklistModel = new ChecklistModel();
        $checklist = $checklistModel->where('id', $id)->where('chk_empresa_id', $empresaId)->first();
        if (!$checklist) return $this->response->setStatusCode(404);
        $db = \Config\Database::connect();
        $veiculo = $checklist['chk_veiculo_id'] ? $db->table('veiculos')->where('id', $checklist['chk_veiculo_id'])->get()->getRowArray() : null;
        $empresa = (new EmpresaModel())->find($empresaId);
        $itemModel = new ChecklistItemModel();
        $itens = $itemModel->getByEmpresa($empresaId);
        $marcacaoModel = new ChecklistMarcacaoModel();
        $marcacoesRaw = $marcacaoModel->getByChecklist($id, $empresaId);
        $marcacoes = [];
        foreach ($marcacoesRaw as $m) $marcacoes[$m['chm_item_id']] = $m['chm_valor'];
        $imagemPath = null;
        if (!empty($checklist['chk_imagem_desenho_caminho'])) {
            $p = WRITEPATH . $checklist['chk_imagem_desenho_caminho'];
            if (is_file($p)) $imagemPath = $p;
        }
        if (!$imagemPath) {
            $configModel = new ChecklistConfigModel();
            $config = $configModel->getByEmpresa($empresaId);
            if ($config && !empty($config['cfc_imagem_caminho'])) {
                $p = WRITEPATH . $config['cfc_imagem_caminho'];
                if (is_file($p)) $imagemPath = $p;
            }
        }
        if (!$imagemPath && is_file(FCPATH . 'assets/admin/images/checklist-img-base.jpg')) {
            $imagemPath = FCPATH . 'assets/admin/images/checklist-img-base.jpg';
        }
        $html = $this->buildChecklistPdfHtml($empresa ?: [], $checklist, $veiculo ?: [], $itens, $marcacoes, $imagemPath);
        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $out = $dompdf->output();
        } catch (\Throwable $e) {
            log_message('error', 'Checklist PDF: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody('Erro ao gerar PDF.');
        }
        $nome = 'checklist-' . $id . '.pdf';
        return $this->response->setHeader('Content-Type', 'application/pdf')->setHeader('Content-Disposition', 'attachment; filename="' . $nome . '"')->setBody($out);
    }

    private function buildChecklistPdfHtml(array $empresa, array $checklist, array $veiculo, array $itens, array $marcacoes, $imagemPath): string
    {
        $empNome = htmlspecialchars($empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? 'Empresa', ENT_QUOTES, 'UTF-8');
        $empCnpj = htmlspecialchars($empresa['emp_cpf_cnpj'] ?? '', ENT_QUOTES, 'UTF-8');
        $placa = htmlspecialchars($veiculo['vei_placa'] ?? '-', ENT_QUOTES, 'UTF-8');
        $modelo = htmlspecialchars($veiculo['vei_modelo'] ?? '-', ENT_QUOTES, 'UTF-8');
        $data = $checklist['chk_data'] ?? '';
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $data, $m)) $data = $m[3] . '/' . $m[2] . '/' . $m[1];
        $respEntrega = htmlspecialchars($checklist['chk_responsavel_entrega'] ?? '-', ENT_QUOTES, 'UTF-8');
        $respDevolucao = htmlspecialchars($checklist['chk_responsavel_devolucao'] ?? '-', ENT_QUOTES, 'UTF-8');
        $hodometro = $checklist['chk_hodometro_saida'] ?? $checklist['chk_hodometro_chegada'] ?? '-';
        $tipoLabel = (isset($checklist['chk_tipo']) && $checklist['chk_tipo'] === 'checkin') ? 'Check-in (chegada)' : 'Check-out (saída)';
        $anotacoes = htmlspecialchars(trim($checklist['chk_anotacoes'] ?? ''), ENT_QUOTES, 'UTF-8');
        $imgHtml = '';
        if ($imagemPath && is_file($imagemPath)) {
            $b64 = base64_encode(file_get_contents($imagemPath));
            $mime = (pathinfo($imagemPath, PATHINFO_EXTENSION) === 'jpg' || strtolower(pathinfo($imagemPath, PATHINFO_EXTENSION)) === 'jpeg') ? 'image/jpeg' : 'image/png';
            $imgHtml = '<p><strong>Vistoria / Desenho</strong></p><img src="data:' . $mime . ';base64,' . $b64 . '" style="max-width:100%; height:auto;" />';
        }
        $rows = '';
        foreach ($itens as $item) {
            $val = $marcacoes[$item['id']] ?? '';
            $ok = ($val === 'ok') ? 'X' : '';
            $nao = ($val === 'nao') ? 'X' : '';
            $rows .= '<tr><td>' . htmlspecialchars($item['chi_nome'] ?? '', ENT_QUOTES, 'UTF-8') . '</td><td style="text-align:center;">' . $ok . '</td><td style="text-align:center;">' . $nao . '</td></tr>';
        }
        $css = 'body{font-family:DejaVu Sans,sans-serif;font-size:10pt;margin:20px;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #333;padding:6px;} th{background:#f0f0f0;}';
        $body = '<div style="border-bottom:2px solid #333;padding-bottom:8px;margin-bottom:12px;"><h1 style="margin:0;font-size:14pt;">' . $empNome . '</h1><p style="margin:0;font-size:9pt;">CNPJ: ' . $empCnpj . '</p></div>';
        $body .= '<h2 style="text-align:center;font-size:12pt;">CHECKLIST DE VEÍCULOS</h2>';
        $body .= '<table><tr><th colspan="2">Dados do veículo</th></tr><tr><td><strong>Placa</strong></td><td>' . $placa . '</td></tr><tr><td><strong>Modelo</strong></td><td>' . $modelo . '</td></tr><tr><td><strong>Tipo</strong></td><td>' . htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') . '</td></tr><tr><td><strong>Data</strong></td><td>' . $data . '</td></tr><tr><td><strong>Hodômetro</strong></td><td>' . $hodometro . '</td></tr><tr><td><strong>Responsável entrega</strong></td><td>' . $respEntrega . '</td></tr><tr><td><strong>Responsável devolução</strong></td><td>' . $respDevolucao . '</td></tr></table>';
        $body .= '<table><tr><th>Item</th><th width="60">OK</th><th width="60">NÃO</th></tr>' . $rows . '</table>';
        if ($imgHtml) $body .= $imgHtml;
        if ($anotacoes) $body .= '<p><strong>Anotações:</strong> ' . $anotacoes . '</p>';
        $body .= '<p style="font-size:8pt;color:#666;text-align:right;margin-top:20px;">Gerado em ' . date('d/m/Y H:i') . '</p>';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>' . $body . '</body></html>';
    }
}
