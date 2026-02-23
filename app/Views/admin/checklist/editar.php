<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php helper('asset');
$chk = $checklist ?? [];
$id = (int)($chk['id'] ?? 0);
$marcacoesMap = [];
foreach ($marcacoes ?? [] as $m) { $marcacoesMap[$m['chm_item_id']] = $m['chm_valor']; }
$imagemInicial = isset($imagem_desenho_url) && $imagem_desenho_url ? $imagem_desenho_url : ($imagem_base_url ?? '');
?>

<style>
    #wrap-desenho { position: relative; display: inline-block; max-width: 100%; }
    #wrap-desenho canvas { display: block; cursor: crosshair; touch-action: none; }
    #wrap-desenho img.bg-img { display: none; }
    .checklist-item-row { padding: 0.4rem 0; border-bottom: 1px solid #eee; }
    [data-bs-theme="dark"] .checklist-item-row { border-color: #333; }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Editar Checklist') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/checklist') ?>">Checklist</a></li>
                <li class="breadcrumb-item active">#<?= $id ?></li>
            </ol>
        </div>
    </div>
</div>

<form id="formChecklist" novalidate>
    <input type="hidden" name="chk_id" value="<?= $id ?>">
    <input type="hidden" name="chk_locacao_id" value="<?= (int)($chk['chk_locacao_id'] ?? 0) ?>">
    <input type="hidden" name="chk_veiculo_id" value="<?= (int)($chk['chk_veiculo_id'] ?? 0) ?>">

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="card-title mb-0">Dados do checklist</h5>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('admin/checklist') ?>" class="btn btn-outline-secondary btn-sm">Voltar</a>
                            <a href="<?= base_url('admin/checklist/' . $id . '/pdf') ?>" class="btn btn-primary btn-sm" target="_blank">Imprimir / PDF</a>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" name="chk_data" value="<?= esc($chk['chk_data'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Veículo</label>
                            <input type="text" class="form-control" readonly value="<?= esc(($veiculo['vei_placa'] ?? '') . ' - ' . ($veiculo['vei_modelo'] ?? '-')) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hodômetro saída</label>
                            <input type="text" class="form-control" name="chk_hodometro_saida" value="<?= esc($chk['chk_hodometro_saida'] ?? '') ?>" placeholder="Ex: 50000">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hodômetro chegada</label>
                            <input type="text" class="form-control" name="chk_hodometro_chegada" value="<?= esc($chk['chk_hodometro_chegada'] ?? '') ?>" placeholder="Ex: 50100">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Locação</label>
                            <input type="text" class="form-control" readonly value="<?= !empty($chk['chk_locacao_id']) ? 'Sim #' . (int)$chk['chk_locacao_id'] : 'Não' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Responsável entrega</label>
                            <input type="text" class="form-control" name="chk_responsavel_entrega" value="<?= esc($chk['chk_responsavel_entrega'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Responsável devolução</label>
                            <input type="text" class="form-control" name="chk_responsavel_devolucao" value="<?= esc($chk['chk_responsavel_devolucao'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data chegada</label>
                            <input type="date" class="form-control" name="chk_data_chegada" value="<?= esc($chk['chk_data_chegada'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data saída</label>
                            <input type="date" class="form-control" name="chk_data_saida" value="<?= esc($chk['chk_data_saida'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label d-block">Tipo</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="chk_tipo" id="chk_tipo_checkin" value="checkin" <?= (isset($chk['chk_tipo']) && $chk['chk_tipo'] === 'checkin') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="chk_tipo_checkin">Check-in (chegada)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="chk_tipo" id="chk_tipo_checkout" value="checkout" <?= (!isset($chk['chk_tipo']) || $chk['chk_tipo'] === 'checkout') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="chk_tipo_checkout">Check-out (saída)</label>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-2">Imagem do veículo (desenho / vistoria)</h5>
                    <p class="text-muted small mb-3">Desenhe sobre a imagem para marcar riscos ou observações. Clique em "Salvar desenho" para gravar.</p>
                    <div id="wrap-desenho">
                        <img id="imgBaseChecklist" class="bg-img" src="" alt="Base" crossorigin="anonymous">
                        <canvas id="canvasDesenho"></canvas>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-2" id="btnSalvarDesenho">Salvar desenho na imagem</button>
                    <span id="desenhoStatus" class="ms-2 text-muted small"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Itens do checklist</h5>
                    <?php if (empty($itens)): ?>
                    <p class="text-muted">Nenhum item configurado. <a href="<?= base_url('admin/cadastro/checklist') ?>">Configure os itens em Cadastro → Checklist</a>.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Item</th><th width="100">OK</th><th width="100">NÃO</th></tr></thead>
                            <tbody>
                            <?php foreach ($itens as $item): 
                                $val = $marcacoesMap[$item['id']] ?? '';
                            ?>
                            <tr class="checklist-item-row">
                                <td><?= esc($item['chi_nome'] ?? '') ?></td>
                                <td><input type="radio" name="marcacao_<?= (int)$item['id'] ?>" value="ok" <?= $val === 'ok' ? 'checked' : '' ?>></td>
                                <td><input type="radio" name="marcacao_<?= (int)$item['id'] ?>" value="nao" <?= $val === 'nao' ? 'checked' : '' ?>></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold">Anotações</label>
                    <textarea class="form-control" name="chk_anotacoes" rows="3"><?= esc($chk['chk_anotacoes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold">Anexos</label>
                    <input type="file" class="form-control mb-2" id="inputAnexos" accept="image/jpeg,image/png,image/webp,image/jpg,application/pdf" multiple>
                    <small class="text-muted">Imagens ou PDF. Máx. 5 MB por arquivo.</small>
                    <div id="anexosLista" class="mt-2">
                        <?php foreach ($anexos ?? [] as $a): ?>
                        <div class="d-flex align-items-center justify-content-between py-1 border-bottom anexo-item" data-id="<?= (int)$a['id'] ?>">
                            <a href="<?= base_url('admin/checklist/anexo/' . (int)$a['id']) ?>" target="_blank" class="text-truncate me-2"><?= esc($a['cha_nome_arquivo'] ?? '') ?></a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-anexo">Remover</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="<?= base_url('admin/checklist') ?>" class="btn btn-light">Cancelar</a>
        </div>
    </div>
</form>

<div id="formChecklistAlert" class="alert alert-danger mt-3 d-none" role="alert"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.__CHECKLIST_ID__ = <?= $id ?>;
window.__CHECKLIST_BASE_URL__ = '<?= base_url() ?>';
window.__IMAGEM_INICIAL__ = '<?= $imagemInicial ?>';
</script>
<script src="<?= asset_url('assets/admin/js/pages/checklist-editar.js') ?>"></script>
<?= $this->endSection() ?>
