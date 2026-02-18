<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Gridjs Plugin css -->
<link
    href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css"
    rel="stylesheet"
    type="text/css"
/>

<style>
    /* Padrão das listagens (clean) */
    .gridjs-search input[type="search"] {
        padding-left: 40px !important;
        padding-right: 12px !important;
        min-height: 38px !important;
    }

    .gridjs-search {
        position: relative;
    }

    .gridjs-search::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cpath d='m21 21-4.35-4.35'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        pointer-events: none;
        z-index: 10;
    }

    .gridjs-pagination .gridjs-pages button[aria-current="page"],
    .gridjs-pagination .gridjs-pages button.gridjs-currentPage {
        background-color: #0d6efd !important;
        color: #fff !important;
        border-color: #0d6efd !important;
    }

    .gridjs-pagination .gridjs-pages button[aria-current="page"]:hover,
    .gridjs-pagination .gridjs-pages button.gridjs-currentPage:hover {
        background-color: #0b5ed7 !important;
        border-color: #0a58ca !important;
        color: #fff !important;
    }

    .gridjs-footer {
        box-shadow: none !important;
    }

    .gridjs-container {
        padding: 0 !important;
    }

    /* Filtros na mesma linha da busca */
    .gridjs-wrapper > div:first-child {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    #filtros-container {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-left: 0.5rem;
        vertical-align: middle;
    }

    #filtros-container .form-control,
    #filtros-container .form-select {
        min-height: 38px !important;
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
        line-height: 1.5 !important;
        border-radius: 0.375rem;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Cobranças') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Cobranças</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Cobranças</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Os lançamentos de cobranças são gerados automaticamente com base na recorrência definida na locação, como diário, semanal ou mensal.
                    Marque as cobranças quitadas para removê-las da lista.
                </p>

                <div class="py-3">
                    <div id="table-cobrancas"></div>

                    <!-- Filtros customizados (movido para o header do GridJS via JS) -->
                    <div id="filtros-container" style="display: none;">
                        <select class="form-select form-select-sm" id="filtro-status" style="width: 180px;">
                            <option value="">Status</option>
                            <option value="Pendente">Pendente</option>
                            <option value="Em atraso">Em atraso</option>
                        </select>
                        <select class="form-select form-select-sm" id="filtro-recorrencia" style="width: 180px;">
                            <option value="">Recorrência</option>
                            <option value="Diária">Diária</option>
                            <option value="Semanal">Semanal</option>
                            <option value="Mensal">Mensal</option>
                        </select>
                        <select class="form-select form-select-sm" id="filtro-locatario" style="width: 220px;">
                            <option value="">Locatário</option>
                            <?php foreach (($locatarios ?? []) as $locatario): ?>
                                <option value="<?= esc($locatario['cli_nome'] ?? '') ?>"><?= esc($locatario['cli_nome'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gridjs Plugin js -->
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
</script>

<!-- Gridjs Cobranças js -->
<script src="<?= asset_url('assets/admin/js/pages/cobrancas.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;

    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-cobrancas .gridjs-search')?.parentElement;

        if (btnFiltros && filtrosContainer && gridSearchWrapper) {
            gridSearchWrapper.appendChild(filtrosContainer);

            btnFiltros.addEventListener('click', function() {
                filtrosAtivos = !filtrosAtivos;

                if (filtrosAtivos) {
                    filtrosContainer.style.display = 'inline-flex';
                    btnFiltros.classList.remove('btn-outline-primary');
                    btnFiltros.classList.add('btn-primary');
                } else {
                    filtrosContainer.style.display = 'none';
                    btnFiltros.classList.remove('btn-primary');
                    btnFiltros.classList.add('btn-outline-primary');
                }
            });
        }
    }, 500);
});
</script>

<?= $this->endSection() ?>

