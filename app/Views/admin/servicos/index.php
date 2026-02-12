<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Gridjs Plugin css -->
<link
    href="<?= base_url('assets/admin/vendor/gridjs/theme/mermaid.min.css') ?>"
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

    /* Melhor leitura no modal */
    #modalServico .form-control,
    #modalServico .form-select {
        color: #111827;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Serviços') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('admin/cadastro/servicos') ?>">Cadastro</a>
                </li>
                <li class="breadcrumb-item active">Serviços</li>
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
                    <h5 class="card-title mb-0">Listagem de Serviços</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-add-servico" data-bs-toggle="modal" data-bs-target="#modalServico">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Adicionar Serviço
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Gerencie todos os serviços cadastrados no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-servicos"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <select class="form-select form-select-sm d-inline-block" id="filtro-categoria" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione a categoria</option>
                            <option value="lavagem">Lavagem</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="estetica">Estética</option>
                        </select>
                        <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Modal: Cadastro/Edição de Serviço -->
<div class="modal fade" id="modalServico" tabindex="-1" aria-labelledby="modalServicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalServicoLabel">Cadastrar serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formServico" novalidate>
                    <input type="hidden" id="ser_id" name="ser_id" value="">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="ser_nome">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ser_nome" name="ser_nome" placeholder="Ex.: Troca de Óleo" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ser_preco_padrao">Preço padrão</label>
                            <input type="text" class="form-control money" id="ser_preco_padrao" name="ser_preco_padrao" placeholder="Ex.: 120,00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="ser_categoria">Categoria</label>
                            <input type="text" class="form-control" id="ser_categoria" name="ser_categoria" placeholder="Ex.: Manutenção">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ser_ativo">Status</label>
                            <select class="form-select" id="ser_ativo" name="ser_ativo">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="ser_descricao">Descrição (opcional)</label>
                            <input type="text" class="form-control" id="ser_descricao" name="ser_descricao" placeholder="Ex.: Inclui filtro e mão de obra">
                        </div>
                    </div>
                </form>

                <div id="ser-form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarServico">
                    <span class="btn-text">Adicionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gridjs Plugin js -->
<script src="<?= base_url('assets/admin/vendor/gridjs/gridjs.umd.js') ?>"></script>

<!-- jQuery Mask Plugin (padrão usado no Financeiro) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
window.__SERVICOS__ = <?= json_encode($servicos ?? []) ?>;
</script>

<!-- Gridjs Serviços js -->
<script src="<?= base_url('assets/admin/js/pages/servicos.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;
    
    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-servicos .gridjs-search')?.parentElement;
        
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
