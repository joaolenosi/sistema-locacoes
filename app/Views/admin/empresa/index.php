<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Cadastro da Empresa') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Empresa</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-4">Atualize os dados da sua empresa. O CNPJ não pode ser alterado.</p>
                <?= $this->include('admin/partials/form_empresa') ?>
            </div>
        </div>
    </div>
</div>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
window.__CONFIG_EMPRESA__ = <?= json_encode($empresa ?? []) ?>;
</script>
<script src="<?= asset_url('assets/admin/js/pages/empresa.js?v=logo1') ?>"></script>

<?= $this->endSection() ?>
