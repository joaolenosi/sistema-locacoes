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
    #filtros-container {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-left: 0.5rem;
        vertical-align: middle;
    }
    
    /* Container para pesquisa e filtros na mesma linha */
    .gridjs-wrapper > div:first-child {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    /* Ajustar barra de pesquisa do GridJS */
    .gridjs-search {
        flex: 0 0 auto;
        margin-bottom: 0 !important;
    }
    
    /* Garantir que o input de pesquisa do GridJS tenha altura consistente */
    .gridjs-search input[type="search"] {
        min-height: 38px !important;
    }
    
    /* Ajustar altura dos inputs de filtro para corresponder ao input de pesquisa do GridJS */
    #filtros-container .form-control,
    #filtros-container .form-select {
        min-height: 42.5px !important;
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
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Manutenções') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Manutenções</li>
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
                    <h5 class="card-title mb-0">Listagem de Manutenções</h5>
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
                <p class="text-muted">
                    Gerencie todas as manutenções cadastradas no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-manutencoes"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <input type="text" class="form-control form-control-sm d-inline-block" id="filtro-placa" placeholder="Buscar por placa" style="width: 170px; margin-right: 0.5rem;">
                        <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o status</option>
                            <option value="ativa">Ativa</option>
                            <option value="finalizada">Finalizada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<style>
/* Modal cadastro manutenção: footer fixo, conteúdo rolável */
#modalManutencao .modal-dialog { max-height: calc(100vh - 2rem); display: flex; }
#modalManutencao .modal-content { display: flex; flex-direction: column; max-height: calc(100vh - 2rem); }
#modalManutencao .modal-body { overflow-y: auto; flex: 1 1 auto; min-height: 0; }
#modalManutencao .modal-footer { flex-shrink: 0; }
</style>
<!-- Modal: Cadastro de Manutenção -->
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
                        <div class="col-md-4">
                            <label class="form-label" for="man_veiculo_id">Veículo <span class="text-danger">*</span></label>
                            <select class="form-select" id="man_veiculo_id" name="man_veiculo_id" required>
                                <option value="">Selecione um veículo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="man_tipo">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="man_tipo" name="man_tipo" required>
                                <option value="">Selecione o tipo</option>
                                <option value="preventiva">Preventiva</option>
                                <option value="corretiva">Corretiva</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="man_data">Data Prevista <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="man_data" name="man_data" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="man_km">KM Previsto</label>
                            <input type="number" class="form-control" id="man_km" name="man_km" placeholder="Ex.: 50000" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="man_km_atual">KM Atual</label>
                            <input type="number" class="form-control" id="man_km_atual" name="man_km_atual" placeholder="Ex.: 45000" min="0">
                            <small class="text-muted">Será buscada automaticamente do histórico se não informada.</small>
                        </div>
                        <div class="col-12" style="margin-top: -10px;">
                            <label class="form-label" for="man_obs">Observações</label>
                            <textarea class="form-control" id="man_obs" name="man_obs" rows="3" placeholder="Observações sobre a manutenção..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="man_trigger_qualquer" name="man_trigger_qualquer" checked>
                                <label class="form-check-label" for="man_trigger_qualquer">
                                    Qualquer um que atingir primeiro (Data OU KM)
                                </label>
                            </div>
                            <small class="text-muted">Se desmarcado, alertará apenas quando a data prevista passar</small>
                            <input type="hidden" id="man_trigger_tipo" name="man_trigger_tipo" value="qualquer">
                        </div>
                        <div class="col-12 border-top pt-3">
                            <label class="form-label fw-semibold mb-2">Produtos e Serviços</label>
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-auto">
                                    <label class="form-label small mb-0">Tipo</label>
                                    <select class="form-select form-select-sm" id="cadastro-item-tipo" name="tipo_item" style="min-width: 100px;">
                                        <option value="produto">Produto</option>
                                        <option value="servico">Serviço</option>
                                    </select>
                                </div>
                                <div class="col flex-grow-1" id="cadastro-grupo-produto">
                                    <label class="form-label small mb-0">Produto</label>
                                    <select class="form-select form-select-sm" id="cadastro-item-produto-id" name="produto_id">
                                        <option value="">Selecione um produto</option>
                                    </select>
                                </div>
                                <div class="col flex-grow-1 d-none" id="cadastro-grupo-servico">
                                    <label class="form-label small mb-0">Serviço</label>
                                    <select class="form-select form-select-sm" id="cadastro-item-servico-id" name="servico_id">
                                        <option value="">Selecione um serviço</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-0">Quantidade</label>
                                    <input type="number" class="form-control form-control-sm" id="cadastro-item-quantidade" name="quantidade" value="1" min="1" style="width: 80px;">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-adicionar-item-cadastro">
                                        <iconify-icon icon="iconamoon:plus-duotone"></iconify-icon> Adicionar
                                    </button>
                                </div>
                            </div>
                            <div id="cadastro-form-item-alert" class="alert alert-danger py-2 mb-2 d-none small" role="alert"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Descrição</th>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Qtd</th>
                                            <th class="text-end">Valor unit.</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-center" style="width:50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-itens-cadastro"></tbody>
                                </table>
                            </div>
                            <p class="mb-0 mt-2 fw-bold">Total: <span id="man-total-cadastro">R$ 0,00</span></p>
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
<!-- Gridjs Manutenções js -->
<script src="<?= asset_url('assets/admin/js/pages/manutencao.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;
    
    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-manutencoes .gridjs-search')?.parentElement;
        
        if (btnFiltros && filtrosContainer && gridSearchWrapper) {
            // Mover filtros para dentro do wrapper do GridJS, na mesma linha da pesquisa
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
