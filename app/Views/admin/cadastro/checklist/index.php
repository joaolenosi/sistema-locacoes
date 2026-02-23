<?php helper('asset'); ?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Configuração do Checklist') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Cadastro</a></li>
                <li class="breadcrumb-item active">Checklist</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Imagem do veículo (checklist)</h5>
                <p class="text-muted small">Esta imagem será exibida na tela de preenchimento do checklist. Você pode substituí-la por uma imagem personalizada.</p>
                <div class="mb-3">
                    <img id="preview-imagem-checklist" src="" alt="Imagem do veículo" class="img-fluid rounded border" style="max-height:220px; display:none;">
                    <div id="preview-placeholder" class="border rounded d-flex align-items-center justify-content-center bg-light" style="height:180px;">
                        <span class="text-muted">Nenhuma imagem configurada (será usado o modelo padrão)</span>
                    </div>
                </div>
                <div class="input-group">
                    <input type="file" class="form-control" id="input-imagem-checklist" accept="image/jpeg,image/png,image/webp,image/jpg">
                    <button type="button" class="btn btn-primary" id="btn-upload-imagem-checklist">Salvar imagem</button>
                </div>
                <div id="alert-imagem" class="alert mt-2 mb-0 d-none"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="card-title mb-0">Itens do checklist</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-item-checklist" data-bs-toggle="modal" data-bs-target="#modalItemChecklist">
                        <iconify-icon icon="iconamoon:plus-duotone"></iconify-icon> Adicionar item
                    </button>
                </div>
                <p class="text-muted small">Estes itens aparecerão no checklist para marcar OK ou NÃO. Você pode adicionar ou remover itens.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Ordem</th>
                                <th>Nome</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-itens-checklist">
                            <tr><td colspan="3" class="text-center text-muted">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar Item -->
<div class="modal fade" id="modalItemChecklist" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalItemChecklistLabel">Adicionar item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="item-checklist-id" value="">
                <div class="mb-2">
                    <label class="form-label">Nome do item</label>
                    <input type="text" class="form-control" id="item-checklist-nome" placeholder="Ex.: Extintor">
                </div>
                <div id="alert-item" class="alert d-none mb-0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-item-checklist">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>window.__BASE_URL__ = '<?= base_url() ?>';</script>
<script src="<?= asset_url('assets/admin/js/pages/checklist-config.js') ?>"></script>
<?= $this->endSection() ?>
