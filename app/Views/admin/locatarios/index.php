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
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Locatários') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Locatários</li>
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
                    <h5 class="card-title mb-0">Listagem de Locatários</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-add-locatario" data-bs-toggle="modal" data-bs-target="#modalLocatario">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Adicionar Locatário
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Gerencie todos os locatários cadastrados no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-locatarios"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <input type="text" class="form-control form-control-sm d-inline-block" id="filtro-cpf" placeholder="Buscar por CPF" style="width: 170px; margin-right: 0.5rem;">
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

<!-- Modal: Cadastro/Edição de Locatário -->
<div class="modal fade" id="modalLocatario" tabindex="-1" aria-labelledby="modalLocatarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLocatarioLabel">Cadastrar locatário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formLocatario" novalidate>
                    <input type="hidden" id="cli_id" name="cli_id" value="">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="cli_tipo_pessoa">Tipo pessoa <span class="text-danger">*</span></label>
                            <select class="form-select" id="cli_tipo_pessoa" name="cli_tipo_pessoa" required>
                                <option value="fisica">Física</option>
                                <option value="juridica">Jurídica</option>
                                <option value="estrangeiro">Estrangeiro</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="cli_nome">Nome / Razão social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cli_nome" name="cli_nome" placeholder="Ex.: João Silva" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="cli_cpf_cnpj">CPF/CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cli_cpf_cnpj" name="cli_cpf_cnpj" placeholder="000.000.000-00" required>
                            <div class="form-text">Para estrangeiro, pode ficar em branco.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cli_data_nascimento">Data de nascimento</label>
                            <input type="date" class="form-control" id="cli_data_nascimento" name="cli_data_nascimento">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cli_ativo" checked>
                                <label class="form-check-label" for="cli_ativo">Ativo</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="cli_email">E-mail</label>
                            <input type="email" class="form-control" id="cli_email" name="cli_email" placeholder="exemplo@email.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="cli_telefone">Telefone</label>
                            <input type="text" class="form-control" id="cli_telefone" name="cli_telefone" placeholder="(00) 0000-0000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="cli_whatsapp">WhatsApp</label>
                            <input type="text" class="form-control" id="cli_whatsapp" name="cli_whatsapp" placeholder="(00) 00000-0000">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="cli_cnh_numero">CNH (número)</label>
                            <input type="text" class="form-control" id="cli_cnh_numero" name="cli_cnh_numero" placeholder="Somente números">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cli_cnh_validade">CNH (validade)</label>
                            <input type="date" class="form-control" id="cli_cnh_validade" name="cli_cnh_validade">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cli_cep">CEP</label>
                            <input type="text" class="form-control" id="cli_cep" name="cli_cep" placeholder="00000-000">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label" for="cli_estado">UF</label>
                            <input type="text" class="form-control" id="cli_estado" name="cli_estado" placeholder="SP" maxlength="2">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="cli_cidade">Cidade</label>
                            <input type="text" class="form-control" id="cli_cidade" name="cli_cidade" placeholder="Ex.: São Paulo">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="cli_bairro">Bairro</label>
                            <input type="text" class="form-control" id="cli_bairro" name="cli_bairro" placeholder="Ex.: Centro">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="cli_rua">Rua</label>
                            <input type="text" class="form-control" id="cli_rua" name="cli_rua" placeholder="Ex.: Av. Paulista">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="cli_numero">Número</label>
                            <input type="text" class="form-control" id="cli_numero" name="cli_numero" placeholder="Ex.: 123">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cli_complemento">Complemento</label>
                            <input type="text" class="form-control" id="cli_complemento" name="cli_complemento" placeholder="Apto, bloco, etc.">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="cli_obs">Observações</label>
                            <textarea class="form-control" id="cli_obs" name="cli_obs" rows="3" placeholder="Observações internas..."></textarea>
                        </div>
                    </div>
                </form>

                <div id="loc-form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarLocatario">
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
window.__LOCATARIOS__ = <?= json_encode($locatarios ?? []) ?>;
window.__BASE_URL__ = '<?= base_url() ?>';
</script>

<!-- Gridjs Locatários js -->
<script src="<?= asset_url('assets/admin/js/pages/locatarios.js') ?>"></script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;
    
    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-locatarios .gridjs-search')?.parentElement;
        
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
