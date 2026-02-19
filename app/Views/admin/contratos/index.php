<?php helper('asset'); ?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Gridjs Plugin css -->
<link
    href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css"
    rel="stylesheet"
    type="text/css"
/>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Contratos') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Contratos</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-meus-contratos" role="tab" aria-selected="true">
                            <span class="d-none d-sm-block">Meus contratos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-modelos-contratos" role="tab" aria-selected="false" id="tabModelosContratos">
                            <span class="d-none d-sm-block">Modelos de contratos</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab: Meus contratos -->
                    <div class="tab-pane active" id="tab-meus-contratos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Meus contratos</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                                    <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                                    Filtros
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-novo-contrato">
                                    <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                                    Novo Contrato
                                </button>
                            </div>
                        </div>
                        <p class="text-muted">
                            Gerencie os contratos gerados no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                        </p>

                        <div class="py-3">
                            <div id="table-contratos"></div>

                            <!-- Container para filtros customizados (será movido pelo JS) -->
                            <div id="filtros-container" style="display: none;">
                                <input type="text" class="form-control form-control-sm d-inline-block" id="filtro-numero" placeholder="Buscar por número" style="width: 170px; margin-right: 0.5rem;">
                                <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                                    <option value="">Selecione o status</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="encerrado">Encerrado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Modelos de contratos -->
                    <div class="tab-pane" id="tab-modelos-contratos" role="tabpanel">
                        <style>
                            .contrato-info {
                                border: 1px dashed #0d6efd;
                                background: rgba(13, 110, 253, 0.06);
                                border-radius: 12px;
                                padding: 14px 16px;
                            }
                            .contrato-variavel-pill {
                                background: #0d6efd;
                                color: #fff;
                                border-radius: 8px;
                                padding: 8px 10px;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 10px;
                                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                                font-size: 12px;
                                transition: background-color 0.2s ease;
                            }
                            .contrato-variavel-pill:hover {
                                background: #0b5ed7;
                            }
                            .contrato-variavel-pill .icon-copy {
                                transition: opacity 0.2s ease, transform 0.2s ease;
                            }
                            .contrato-variavel-pill.copied .icon-copy {
                                opacity: 0;
                                transform: scale(0);
                            }
                            .contrato-variavel-pill .icon-check {
                                position: absolute;
                                opacity: 0;
                                transform: scale(0);
                                transition: opacity 0.2s ease, transform 0.2s ease;
                            }
                            .contrato-variavel-pill.copied .icon-check {
                                opacity: 1;
                                transform: scale(1);
                            }
                            .contrato-editor {
                                border: 1px solid #e2e8f0;
                                border-radius: 12px;
                                overflow: hidden;
                                min-height: 320px;
                            }
                            .contrato-editor .ql-toolbar {
                                border: 0;
                                border-bottom: 1px solid #e2e8f0;
                            }
                            .contrato-editor .ql-container {
                                border: 0;
                                min-height: 320px;
                            }
                        </style>

                        <?php if (!empty($db_warning ?? null)): ?>
                            <div class="alert alert-warning mb-3">
                                <?= esc($db_warning) ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="card-title mb-0">Modelos de contratos</h5>
                                <div class="text-muted small">A princípio existe apenas um modelo padrão configurado no sistema.</div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasVariaveisContrato" aria-controls="offcanvasVariaveisContrato">
                                Conheça as variáveis disponíveis
                            </button>
                        </div>

                        <input type="hidden" id="con_modelo_id" value="<?= (int)($modelo_padrao['id'] ?? 1) ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" id="con_nome" value="<?= esc($modelo_padrao['con_nome'] ?? 'Contrato de Locação de Veículo') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="con_descricao" value="<?= esc($modelo_padrao['con_descricao'] ?? 'Modelo padrão de contrato de locação de veículo automotor, com campos dinâmicos.') ?>">
                            </div>
                        </div>

                        <div class="contrato-info my-3">
                            <div class="mb-2">
                                Você pode utilizar variáveis dinâmicas no modelo de contrato para que os dados sejam preenchidos automaticamente.
                            </div>
                            <div class="mb-2">
                                Use o formato <b>{{variavel}}</b>.
                            </div>
                            <div class="mb-0">
                                <b>Exemplo:</b><br>
                                <span class="text-muted">Editor</span><br>
                                <span>Locatário: <b>{{locatario.nome_completo}}</b></span><br><br>
                                <span class="text-muted">Resultado</span><br>
                                <span>Locatário: <i>Lucas de Souza</i></span>
                            </div>
                        </div>

                        <div class="contrato-editor mb-2">
                            <div id="contrato-modelo-editor"></div>
                        </div>

                        <textarea id="contrato-modelo-conteudo" class="d-none"><?= esc($modelo_padrao['con_conteudo_editor'] ?? $modelo_padrao['con_conteudo'] ?? '') ?></textarea>
                        <textarea id="contrato-modelo-conteudo-html" class="d-none"></textarea>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnSalvarModeloContrato">Salvar alterações</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- jQuery (Select2 dependency) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<!-- Gridjs Plugin js -->
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>
<script>window.__BASE_URL__ = '<?= base_url() ?>';</script>
<!-- Gridjs Contratos js -->
<script src="<?= asset_url('assets/admin/js/pages/contratos.js') ?>"></script>

<!-- Contratos Modelos js (Quill + variáveis) -->
<script src="<?= asset_url('assets/admin/js/pages/contratos-modelos.js') ?>"></script>

<script>
// Legado: dados do controller (a tabela agora carrega via API admin/contratos/listar)
window.__MEUS_CONTRATOS__ = <?= json_encode($meus_contratos ?? []) ?>;
</script>

<!-- Script para controle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFiltros = document.getElementById('btn-filtros');
    const filtrosContainer = document.getElementById('filtros-container');
    let filtrosAtivos = false;
    
    // Aguardar GridJS renderizar
    setTimeout(function() {
        const gridSearchWrapper = document.querySelector('#table-contratos .gridjs-search')?.parentElement;
        
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

<!-- Modal: Criar contrato -->
<div class="modal fade" id="modalCriarContrato" tabindex="-1" aria-labelledby="modalCriarContratoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCriarContratoLabel">Criar contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modal-locacao" class="form-label">Locação</label>
                    <select id="modal-locacao" class="form-select" style="width: 100%;">
                        <option value="">Pesquise pela locação...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="modal-modelo" class="form-label">Modelo do contrato</label>
                    <select id="modal-modelo" class="form-select">
                        <option value="">Selecione o modelo</option>
                        <?php foreach (($modelos_list ?? []) as $m): ?>
                            <option value="<?= (int)($m['id'] ?? 0) ?>"><?= esc($m['con_nome'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-modal-criar-contrato">Criar</button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas: Variáveis disponíveis -->
<div
    class="offcanvas offcanvas-end"
    tabindex="-1"
    id="offcanvasVariaveisContrato"
    aria-labelledby="offcanvasVariaveisContratoLabel"
    style="max-width: 420px; width: 100%;"
>
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasVariaveisContratoLabel">Variáveis disponíveis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body pt-0">
        <div class="mb-3">
            <div class="fw-semibold">Acesso rápido</div>
            <div class="text-muted small">Clique para inserir no editor</div>
        </div>

        <div class="mb-3">
            <a href="javascript:void(0);" class="text-primary text-decoration-underline">Acesse o guia completo</a>
        </div>

        <div class="d-grid gap-2">
            <?php foreach (($variaveis ?? []) as $v): ?>
                <?php $varText = '{{' . ($v['cov_chave'] ?? '') . '}}'; ?>
                <button
                    type="button"
                    class="btn p-0 text-start"
                    data-insert-variable="<?= esc($varText) ?>"
                    style="border: 0; background: transparent;"
                    title="<?= esc($v['cov_label'] ?? $varText) ?>"
                >
                    <div class="contrato-variavel-pill">
                        <span><?= esc($varText) ?></span>
                        <span class="d-inline-flex align-items-center gap-2 position-relative" style="width: 24px; height: 24px; justify-content: center;">
                            <iconify-icon icon="iconamoon:copy-duotone" class="fs-18 icon-copy"></iconify-icon>
                            <iconify-icon icon="iconamoon:check-circle-1-duotone" class="fs-18 icon-check"></iconify-icon>
                        </span>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
