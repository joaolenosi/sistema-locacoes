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

    /* Cards resumo (Manutenção Inteligente) */
    .man-kpi-card {
        border: 0;
        border-radius: 16px;
        color: #fff;
        overflow: hidden;
        min-height: 110px;
    }

    .man-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

<!-- Cards resumo -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card man-kpi-card" style="background: #ef4444;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Atrasadas</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-manutencao-atrasadas">0</span>
                    </div>
                    <small style="opacity: .9;">Manutenções em atraso</small>
                </div>
                <div class="man-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:warning-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card man-kpi-card" style="background: #2d7ef7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Agendadas</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-manutencao-agendadas">0</span>
                    </div>
                    <small style="opacity: .9;">Em dia ou programadas</small>
                </div>
                <div class="man-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:calendar-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card man-kpi-card" style="background: #64748b;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Total em acompanhamento</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-manutencao-total">0</span>
                    </div>
                    <small style="opacity: .9;">Alertas e agendamentos</small>
                </div>
                <div class="man-kpi-icon" aria-hidden="true">
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

<style>
/* Modal cadastro manutenção: footer fixo, conteúdo rolável */
#modalManutencao .modal-dialog { max-height: calc(100vh - 2rem); display: flex; }
#modalManutencao .modal-content { display: flex; flex-direction: column; max-height: calc(100vh - 2rem); }
#modalManutencao .modal-body { overflow-y: auto; flex: 1 1 auto; min-height: 0; }
#modalManutencao .modal-footer { flex-shrink: 0; }
</style>
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
                            <small class="text-muted">KM atual do veículo (será buscada automaticamente do histórico se não informada)</small>
                        </div>

                        <div class="col-12">
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
                                    <select class="form-select form-select-sm" id="cadastro-inteligente-item-tipo" style="min-width: 100px;">
                                        <option value="produto">Produto</option>
                                        <option value="servico">Serviço</option>
                                    </select>
                                </div>
                                <div class="col flex-grow-1" id="cadastro-inteligente-grupo-produto">
                                    <label class="form-label small mb-0">Produto</label>
                                    <select class="form-select form-select-sm" id="cadastro-inteligente-item-produto-id">
                                        <option value="">Selecione um produto</option>
                                    </select>
                                </div>
                                <div class="col flex-grow-1 d-none" id="cadastro-inteligente-grupo-servico">
                                    <label class="form-label small mb-0">Serviço</label>
                                    <select class="form-select form-select-sm" id="cadastro-inteligente-item-servico-id">
                                        <option value="">Selecione um serviço</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-0">Quantidade</label>
                                    <input type="number" class="form-control form-control-sm" id="cadastro-inteligente-item-quantidade" value="1" min="1" style="width: 80px;">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-adicionar-item-cadastro-inteligente">
                                        <iconify-icon icon="iconamoon:plus-duotone"></iconify-icon> Adicionar
                                    </button>
                                </div>
                            </div>
                            <div id="cadastro-inteligente-form-item-alert" class="alert alert-danger py-2 mb-2 d-none small" role="alert"></div>
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
                                    <tbody id="tbody-itens-cadastro-inteligente"></tbody>
                                </table>
                            </div>
                            <p class="mb-0 mt-2 fw-bold">Total: <span id="man-total-cadastro-inteligente">R$ 0,00</span></p>
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

<!-- Modal: Detalhes da Manutenção -->
<div class="modal fade" id="modalDetalhesManutencao" tabindex="-1" aria-labelledby="modalDetalhesManutencaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesManutencaoLabel">
                    <iconify-icon icon="iconamoon:info-duotone" class="me-2"></iconify-icon>
                    Detalhes da manutenção
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="detalhes-manutencao-body">
                <div class="text-center py-4 text-muted">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Carregando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Adicionar Item (Produto/Serviço) na Manutenção - Detalhes -->
<div class="modal fade" id="modalAdicionarItem" tabindex="-1" aria-labelledby="modalAdicionarItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarItemLabel">Adicionar produto ou serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarItem">
                    <input type="hidden" id="item-manutencao-id" value="">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="item-tipo" name="tipo_item" required>
                            <option value="produto">Produto</option>
                            <option value="servico">Serviço</option>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo-produto-item">
                        <label class="form-label">Produto</label>
                        <select class="form-select" id="item-produto-id" name="produto_id">
                            <option value="">Selecione um produto</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="grupo-servico-item">
                        <label class="form-label">Serviço</label>
                        <select class="form-select" id="item-servico-id" name="servico_id">
                            <option value="">Selecione um serviço</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="item-quantidade" name="quantidade" value="1" min="1" required>
                    </div>
                    <div class="mb-0">
                        <p class="mb-0 text-muted small">Valor unit.: <span id="item-valor-unit-display">R$ 0,00</span> | Subtotal: <span id="item-subtotal-display" class="fw-semibold">R$ 0,00</span></p>
                    </div>
                </form>
                <div id="form-item-alert" class="alert alert-danger mt-2 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-item-inteligente">
                    <span class="btn-text">Adicionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Marcar como realizada -->
<div class="modal fade" id="modalCompletarManutencao" tabindex="-1" aria-labelledby="modalCompletarManutencaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCompletarManutencaoLabel">
                    <iconify-icon icon="iconamoon:check-circle-duotone" class="me-2"></iconify-icon>
                    Marcar como realizada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formCompletarManutencao" novalidate>
                    <input type="hidden" id="completar_id" name="completar_id" value="">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="completar_data_realizacao">Data da realização <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="completar_data_realizacao" name="data_realizacao" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="completar_km_atual">KM atual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="completar_km_atual" name="km_atual" min="0" placeholder="Ex.: 45000" required>
                        </div>
                        <div class="col-12" id="completar-wrap-switch">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="completar_atualizar_proxima" name="atualizar_proxima" value="1">
                                <label class="form-check-label" for="completar_atualizar_proxima">Deseja agendar a próxima manutenção com base nos dados desta? (recalcula o próximo KM)</label>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="completar-form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarCompletar">
                    <span class="btn-text">Marcar como realizada</span>
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
