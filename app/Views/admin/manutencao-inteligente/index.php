<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Gridjs Plugin css -->
<link
    href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css"
    rel="stylesheet"
    type="text/css"
/>

<!-- CSS Customizado para correções -->
<style>
    /* Corrigir placeholder do campo de busca */
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
    
    /* Corrigir cor da paginação ativa */
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
    
    /* Remover box-shadow do footer para layout mais clean */
    .gridjs-footer {
        box-shadow: none !important;
    }
    
    /* Remover padding do container para layout mais limpo */
    .gridjs-container {
        padding: 0 !important;
    }
    
    /* Alinhar filtros com a barra de pesquisa do GridJS */
    .gridjs-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .gridjs-search {
        flex: 0 0 auto;
        margin-bottom: 0 !important;
    }

    /* Estilo para campos de filtro */
    #filtros-container {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex-grow: 1;
    }
    
    #filtros-container .form-group {
        flex: 0 0 auto;
        width: 150px;
    }

    #filtros-container .form-label {
        display: none;
    }
    
    #filtros-container .form-control,
    #filtros-container .form-select {
        min-height: 38px !important;
        font-size: 0.875rem !important;
        padding: 0.375rem 0.75rem !important;
        line-height: 1.5 !important;
        border-radius: 0.375rem;
    }
    
    #filtros-container .position-relative .form-control {
        padding-left: 2.5rem !important;
    }
    
    /* Ajustar checkbox para alinhar verticalmente */
    #filtros-container .form-check {
        display: inline-flex;
        align-items: center;
        margin: 0;
        min-height: 38px;
    }
    
    #filtros-container .form-check-label {
        margin-left: 0.5rem;
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    /* Melhor leitura no modal */
    #modalManutencao .form-control,
    #modalManutencao .form-select {
        color: #111827;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Manutenção Inteligente') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Manutenção Inteligente</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-1">Manutenção inteligente para sua frota</h5>
                        <p class="text-muted mb-0"><?= esc($subtitle ?? 'Gerencie peças, serviços e prazos com eficiência. Alertas automáticos ajudam você a manter seus veículos sempre em dia, sem surpresas.') ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-add-manutencao" data-bs-toggle="modal" data-bs-target="#modalManutencao">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Nova Manutenção
                        </button>
                    </div>
                </div>
                
                <div class="py-3">
                    <!-- O GridJS será renderizado aqui -->
                    <div id="table-manutencao-inteligente"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Modal: Cadastro/Edição de Manutenção -->
<div class="modal fade" id="modalManutencao" tabindex="-1" aria-labelledby="modalManutencaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalManutencaoLabel">Cadastrar manutenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formManutencao" novalidate>
                    <input type="hidden" id="man_id" name="man_id" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="man_veiculo_id">Veículo <span class="text-danger">*</span></label>
                            <select class="form-select" id="man_veiculo_id" name="man_veiculo_id" required>
                                <option value="">Selecione um veículo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="man_tipo">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="man_tipo" name="man_tipo" required>
                                <option value="">Selecione o tipo</option>
                                <option value="preventiva">Preventiva</option>
                                <option value="corretiva">Corretiva</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="man_data">Data Prevista <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="man_data" name="man_data" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="man_km">KM Previsto</label>
                            <input type="number" class="form-control" id="man_km" name="man_km" placeholder="Ex.: 50000" min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="man_obs">Observações</label>
                            <textarea class="form-control" id="man_obs" name="man_obs" rows="3" placeholder="Observações sobre a manutenção..."></textarea>
                        </div>
                    </div>
                </form>

                <div id="man-form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarManutencao">
                    <span class="btn-text">Adicionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gridjs Plugin js -->
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
</script>

<!-- Gridjs Manutenção Inteligente js -->
<script src="<?= asset_url('assets/admin/js/pages/manutencao-inteligente.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;

    // Cria o container para os filtros
    const newFiltrosContainer = document.createElement('div');
    newFiltrosContainer.id = 'filtros-container';
    newFiltrosContainer.style.display = 'none';
    newFiltrosContainer.innerHTML = `
        <div class="form-group">
            <div class="position-relative">
                <input type="text" class="form-control form-control-sm" id="filtro-veiculo" placeholder="Buscar por veículo">
                <iconify-icon icon="iconamoon:search-duotone" class="position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="pointer-events: none; font-size: 14px;"></iconify-icon>
            </div>
        </div>
        <div class="form-group">
            <select class="form-select form-select-sm" id="filtro-tipo">
                <option value="">Tipo</option>
                <option value="preventiva">Preventiva</option>
                <option value="corretiva">Corretiva</option>
                <option value="preditiva">Preditiva</option>
            </select>
        </div>
        <div class="form-group">
            <select class="form-select form-select-sm" id="filtro-status">
                <option value="">Status</option>
                <option value="agendada">Agendada</option>
                <option value="em-andamento">Em Andamento</option>
                <option value="concluida">Concluída</option>
                <option value="atrasada">Atrasada</option>
            </select>
        </div>
    `;

    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-manutencao-inteligente .gridjs-search')?.parentElement;
        
        if (btnFiltros && newFiltrosContainer && gridSearchWrapper) {
            // Mover filtros para dentro do wrapper do GridJS, na mesma linha da pesquisa
            gridSearchWrapper.appendChild(newFiltrosContainer);
            
            btnFiltros.addEventListener('click', function() {
                filtrosAtivos = !filtrosAtivos;
                
                if (filtrosAtivos) {
                    newFiltrosContainer.style.display = 'flex';
                    btnFiltros.classList.remove('btn-outline-primary');
                    btnFiltros.classList.add('btn-primary');
                } else {
                    newFiltrosContainer.style.display = 'none';
                    btnFiltros.classList.remove('btn-primary');
                    btnFiltros.classList.add('btn-outline-primary');
                }
            });
        }
    }, 500);
});
</script>
<?= $this->endSection() ?>
