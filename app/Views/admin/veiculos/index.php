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

    /* Cards resumo (Veículos) */
    .vei-kpi-card {
        border: 0;
        border-radius: 16px;
        color: #fff;
        overflow: hidden;
        min-height: 110px;
    }

    .vei-kpi-icon {
        width: 44px;   
        height: 44px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Melhor leitura no modal */
    #modalVeiculo .form-control,
    #modalVeiculo .form-select {
        color: #111827; /* mais escuro */
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Veículos') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Veículos</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<!-- Cards resumo -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card vei-kpi-card" style="background: #2d7ef7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Total de veículos</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-total-veiculos"><?= esc($total_veiculos ?? 0) ?></span>
                    </div>
                </div>
                <div class="vei-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:box-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card vei-kpi-card" style="background: #22c55e;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Veículos livres</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-veiculos-livres"><?= esc($veiculos_livres ?? 0) ?></span>
                    </div>
                </div>
                <div class="vei-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:check-circle-1-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card vei-kpi-card" style="background: #ff5c6c;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Veículos ocupados</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-veiculos-ocupados"><?= esc($veiculos_ocupados ?? 0) ?></span>
                    </div>
                </div>
                <div class="vei-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:car-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Listagem de Veículos</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-add-veiculo" data-bs-toggle="modal" data-bs-target="#modalVeiculo">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Adicionar Veículo
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Gerencie todos os veículos cadastrados no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-veiculos"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <input type="text" class="form-control form-control-sm d-inline-block" id="filtro-placa" placeholder="Buscar por placa" style="width: 170px;  margin-left: 7px; margin-right: 0.5rem;">
                        <select class="form-select form-select-sm d-inline-block" id="filtro-tipo" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o tipo</option>
                            <option value="carro">Carro</option>
                            <option value="moto">Moto</option>
                            <option value="caminhao">Caminhão</option>
                        </select>
                        <select class="form-select form-select-sm d-inline-block" id="filtro-marca" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione a marca</option>
                            <option value="chevrolet">Chevrolet</option>
                            <option value="hyundai">Hyundai</option>
                            <option value="toyota">Toyota</option>
                            <option value="honda">Honda</option>
                            <option value="ford">Ford</option>
                            <option value="volkswagen">Volkswagen</option>
                        </select>
                        <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o status</option>
                            <option value="disponivel">Disponível</option>
                            <option value="em-uso">ALUGADO</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="alugado">Alugado</option>
                        </select>
                        <div class="form-check d-inline-block ms-2">
                            <input class="form-check-input" type="checkbox" id="filtro-retirados">
                            <label class="form-check-label small" for="filtro-retirados">
                                Mostrar retirados da frota
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Modal: Cadastro/Edição de Veículo -->
<div class="modal fade" id="modalVeiculo" tabindex="-1" aria-labelledby="modalVeiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVeiculoLabel">Cadastrar veículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formVeiculo" novalidate>
                    <input type="hidden" id="vei_id" name="vei_id" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="vei_tipo">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="vei_tipo" name="vei_tipo" required>
                                <option value="">Selecione tipo</option>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                                <option value="caminhao">Caminhão</option>
                                <option value="utilitario">Utilitário</option>
                                <option value="suv">SUV</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vei_placa">Placa <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="vei_placa" name="vei_placa" placeholder="ABC-1A23" style="text-transform: uppercase; padding-right: 44px;" required>
                                <span id="vei-placa-loading" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none" aria-hidden="true">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </span>
                            </div>
                            <div class="form-text">Digite o código da placa para pesquisar.</div>
                        </div>


                        <div class="col-md-4">
                            <label class="form-label" for="vei_marca">Marca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vei_marca" name="vei_marca" placeholder="Ex.: Hyundai" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="vei_modelo">Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vei_modelo" name="vei_modelo" placeholder="Ex.: HB20" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="vei_ano">Ano <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vei_ano" name="vei_ano" placeholder="Ex.: 2022" required>
                        </div>

                     


                        <div class="col-md-6">
                            <label class="form-label" for="vei_cor">Cor</label>
                            <input type="text" class="form-control" id="vei_cor" name="vei_cor" placeholder="Ex.: Prata">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vei_renavam">Nº Renavam</label>
                            <input type="text" class="form-control" id="vei_renavam" name="vei_renavam" placeholder="Ex.: 12345678910">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vei_chassi">Nº Chassi</label>
                            <input type="text" class="form-control" id="vei_chassi" name="vei_chassi" placeholder="Ex.: 9BWZZZ377VT004251">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vei_data_licenciamento">Data licenciamento</label>
                            <input type="date" class="form-control" id="vei_data_licenciamento" name="vei_data_licenciamento">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vei_km_atual">KM atual</label>
                            <input type="number" class="form-control" id="vei_km_atual" name="vei_km_atual" placeholder="Ex.: 68000" min="0" step="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vei_data_compra">Data da compra (opcional)</label>
                            <input type="date" class="form-control" id="vei_data_compra" name="vei_data_compra">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vei_valor_compra">Valor compra (opcional)</label>
                            <input type="text" class="form-control" id="vei_valor_compra" name="vei_valor_compra" placeholder="Valor da compra">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vei_status">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="vei_status" name="vei_status" required>
                                <option value="disponivel">Disponível</option>
                                <option value="locado">Locado</option>
                                <option value="manutencao">Manutenção</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div id="vei-form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarVeiculo">
                    <span class="btn-text">Adicionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gridjs Plugin js -->
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>

<!-- jQuery Mask Plugin (padrão usado no Financeiro) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
window.__VEICULOS__ = <?= json_encode($veiculos ?? []) ?>;
window.__BASE_URL__ = '<?= rtrim(base_url(), '/') . '/' ?>';
// Debug: verificar se os dados estão sendo passados
console.log('Veículos carregados:', window.__VEICULOS__);
console.log('Total de veículos:', window.__VEICULOS__?.length || 0);
console.log('Base URL:', window.__BASE_URL__);
</script>

<!-- Gridjs Veículos js -->
<script src="<?= asset_url('assets/admin/js/pages/veiculos.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;
    
    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-veiculos .gridjs-search')?.parentElement;
        
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
