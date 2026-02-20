<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Detalhes da Manutenção') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/manutencao') ?>">Manutenções</a></li>
                <li class="breadcrumb-item active">Detalhes #<?= (int) ($manutencao['id'] ?? 0) ?></li>
            </ol>
        </div>
    </div>
</div>

<?php
$man = $manutencao ?? [];
$id = (int) ($man['id'] ?? 0);
$dataFormatada = !empty($man['man_data']) ? date('d/m/Y', strtotime($man['man_data'])) : '—';
$tipoLabel = ($man['man_tipo'] ?? '') === 'preventiva' ? 'Preventiva' : 'Corretiva';
$statusLabel = ['aberta' => 'Aberta', 'finalizada' => 'Finalizada', 'rascunho' => 'Rascunho', 'cancelada' => 'Cancelada'][$man['man_status'] ?? ''] ?? $man['man_status'] ?? '—';
$fotos = $man['fotos'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="card-title mb-0">Manutenção #<?= $id ?></h5>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/manutencao') ?>" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="iconamoon:arrow-left-duotone"></iconify-icon> Voltar
                        </a>
                        <a href="<?= base_url('admin/manutencao/' . $id . '/pdf') ?>" class="btn btn-primary btn-sm" target="_blank" id="btn-pdf-manutencao">
                            <iconify-icon icon="iconamoon:printer-duotone"></iconify-icon> Imprimir / PDF
                        </a>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Veículo</label>
                        <p class="mb-0 fw-semibold"><?= esc($man['vei_placa'] ?? '—') ?> – <?= esc($man['vei_modelo'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Data</label>
                        <p class="mb-0"><?= $dataFormatada ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Tipo / Status</label>
                        <p class="mb-0"><?= $tipoLabel ?> / <?= $statusLabel ?></p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Observações</label>
                        <p class="mb-0"><?= !empty($man['man_obs']) ? esc($man['man_obs']) : '—' ?></p>
                    </div>
                </div>

                <!-- Upload de fotos -->
                <div class="border rounded p-3 mb-4 bg-light">
                    <label class="form-label fw-semibold mb-2">Anexar fotos</label>
                    <input type="file" class="form-control" id="input-fotos-manutencao" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                    <small class="text-muted">Formatos: JPG, PNG, WebP. Máximo 5 MB por foto.</small>
                    <div id="upload-fotos-alert" class="alert mt-2 mb-0 d-none" role="alert"></div>
                </div>

                <!-- Galeria de fotos (lightbox) -->
                <div class="mb-0">
                    <label class="form-label fw-semibold mb-2">Fotos anexadas <span id="fotos-count" class="badge bg-primary"><?= count($fotos) ?></span></label>
                    <div id="galeria-fotos-manutencao" class="row g-2">
                        <?php foreach ($fotos as $f): ?>
                        <div class="col-6 col-md-4 col-lg-3" data-foto-id="<?= (int) $f['id'] ?>">
                            <a href="<?= base_url('admin/manutencao-inteligente/foto/' . (int) $f['id']) ?>" class="glightbox d-block rounded overflow-hidden border" data-gallery="manutencao-fotos">
                                <img src="<?= base_url('admin/manutencao-inteligente/foto/' . (int) $f['id']) ?>" alt="Foto" class="img-fluid w-100" style="object-fit:cover; height:120px;">
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-1 w-100 btn-remover-foto" data-foto-id="<?= (int) $f['id'] ?>">Remover</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p id="galeria-vazia" class="text-muted small <?= empty($fotos) ? '' : 'd-none' ?>">Nenhuma foto anexada. Use o campo acima para enviar fotos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.__MANUTENCAO_ID__ = <?= $id ?>;
window.__BASE_URL__ = '<?= base_url() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script src="<?= asset_url('assets/admin/js/pages/manutencao-detalhes.js') ?>"></script>
<?= $this->endSection() ?>
