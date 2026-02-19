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

    /* Cards resumo (Financeiro) */
    .fin-kpi-card {
        border: 0;
        border-radius: 16px;
        color: #fff;
        overflow: hidden;
        min-height: 110px;
    }

    .fin-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Garantir que o texto digitado seja totalmente preto */
    #formReceita input[type="text"],
    #formReceita input[type="date"],
    #formReceita input[type="number"],
    #formReceita select,
    #formReceita textarea,
    #formDespesa input[type="text"],
    #formDespesa input[type="date"],
    #formDespesa input[type="number"],
    #formDespesa select,
    #formDespesa textarea {
        color: #000000 !important;
    }

    /* Placeholder mais claro */
    #formReceita input::placeholder,
    #formDespesa input::placeholder,
    #formReceita input::-webkit-input-placeholder,
    #formDespesa input::-webkit-input-placeholder,
    #formReceita input::-moz-placeholder,
    #formDespesa input::-moz-placeholder,
    #formReceita input:-ms-input-placeholder,
    #formDespesa input:-ms-input-placeholder {
        color: #adb5bd !important;
        opacity: 1 !important;
    }

    /* Estilos para modal de pagamento */
    #formPagamento input[type="text"],
    #formPagamento input[type="date"],
    #formPagamento select {
        color: #000000 !important;
    }

    #formPagamento .form-control-plaintext {
        color: #000000 !important;
        font-weight: 500;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem Financeira') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Financeiro</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<!-- Cards resumo -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: #22c55e;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Receitas do mês atual</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        R$ <span id="kpi-receitas-mes-atual"><?= number_format($receitas_mes_atual ?? 0, 2, ',', '.') ?></span>
                    </div>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:wallet-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: #ff5c6c;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Despesas do mês atual</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        R$ <span id="kpi-despesas-mes-atual"><?= number_format($despesas_mes_atual ?? 0, 2, ',', '.') ?></span>
                    </div>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:card-remove-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: #2d7ef7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Lucro do mês atual</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        R$ <span id="kpi-lucro-mes-atual"><?= number_format($lucro_mes_atual ?? 0, 2, ',', '.') ?></span>
                    </div>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:trend-up-bold" class="fs-22 text-white"></iconify-icon>
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
                    <h5 class="card-title mb-0">Listagem Financeira</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <a
                            class="btn btn-outline-secondary"
                            href="<?= base_url('admin/financeiro/movimentacoes') ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            <iconify-icon icon="iconamoon:arrow-repeat-2-duotone" class="fs-18"></iconify-icon>
                            Movimentações
                        </a>
                        <button type="button" class="btn btn-success" id="btn-abrir-receita">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Receita
                        </button>
                        <button type="button" class="btn btn-danger" id="btn-abrir-despesa">
                            <iconify-icon icon="iconamoon:minus-duotone" class="fs-18"></iconify-icon>
                            Despesa
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Gerencie todas as movimentações financeiras do sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-financeiro"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <select class="form-select form-select-sm d-inline-block" id="filtro-tipo" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o tipo</option>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                        </select>
                        <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o status</option>
                            <option value="pago">Pago</option>
                            <option value="pendente">Pendente</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Gridjs Plugin js -->
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>

<!-- jQuery Mask Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
window.__FINANCEIRO_BOOTSTRAP__ = {
  lancamentos: <?= json_encode($lancamentos ?? []) ?>,
  categoriasReceita: <?= json_encode($categorias_receita ?? []) ?>,
  categoriasDespesa: <?= json_encode($categorias_despesa ?? []) ?>,
  locacoes: <?= json_encode($locacoes ?? []) ?>,
  cards: {
    receitasMesAtual: <?= json_encode($receitas_mes_atual ?? 0) ?>,
    despesasMesAtual: <?= json_encode($despesas_mes_atual ?? 0) ?>,
    lucroMesAtual: <?= json_encode($lucro_mes_atual ?? 0) ?>
  }
};
</script>

<!-- Gridjs Financeiro js -->
<script src="<?= asset_url('assets/admin/js/pages/financeiro.js') ?>" onerror="console.error('Erro ao carregar financeiro.js')"></script>

<!-- Script para garantir que os botões funcionem após o carregamento -->
<script>
(function() {
    function setupButtons() {
        // Botões para abrir modais
        const btnReceita = document.getElementById('btn-abrir-receita');
        const btnDespesa = document.getElementById('btn-abrir-despesa');
        
        if (btnReceita && typeof window.abrirModalReceita === 'function') {
            btnReceita.addEventListener('click', function() {
                window.abrirModalReceita();
            });
        }
        
        if (btnDespesa && typeof window.abrirModalDespesa === 'function') {
            btnDespesa.addEventListener('click', function() {
                window.abrirModalDespesa();
            });
        }
        
        // Botões para salvar
        const btnSalvarReceita = document.getElementById('btnSalvarReceita');
        const btnSalvarDespesa = document.getElementById('btnSalvarDespesa');
        
        if (btnSalvarReceita && typeof window.salvarReceita === 'function') {
            btnSalvarReceita.addEventListener('click', function() {
                window.salvarReceita();
            });
        }
        
        if (btnSalvarDespesa && typeof window.salvarDespesa === 'function') {
            btnSalvarDespesa.addEventListener('click', function() {
                window.salvarDespesa();
            });
        }
        
        // Botão de efetuar pagamento
        const btnEfetuarPagamento = document.getElementById('btnEfetuarPagamento');
        if (btnEfetuarPagamento && typeof window.efetuarPagamento === 'function') {
            btnEfetuarPagamento.addEventListener('click', function() {
                window.efetuarPagamento();
            });
        }
    }
    
    // Tentar configurar imediatamente se já estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Aguardar um pouco para garantir que o financeiro.js foi carregado
            setTimeout(setupButtons, 200);
        });
    } else {
        // DOM já está pronto, aguardar um pouco para o script carregar
        setTimeout(setupButtons, 200);
    }
    
    // Fallback: tentar novamente após um tempo maior caso ainda não tenha carregado
    setTimeout(function() {
        if (typeof window.abrirModalReceita !== 'function' || typeof window.abrirModalDespesa !== 'function') {
            console.warn('Funções do financeiro ainda não estão disponíveis, tentando novamente...');
            setupButtons();
        }
    }, 1000);
})();
</script>

<!-- Modal Receita -->
<div class="modal fade" id="modalReceita" tabindex="-1" aria-labelledby="modalReceitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content d-flex flex-column">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReceitaLabel">Receita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="formReceita">
                    <input type="hidden" id="receita_id" name="lancamento_id" value="">
                    <input type="hidden" id="receita_tipo" name="lan_tipo" value="receita">
                    <!-- Campos Principais -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="receita_categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select class="form-select" id="receita_categoria" name="lan_categoria_id" required>
                                <option value="">Selecione a categoria da receita</option>
                                <?php foreach (($categorias_receita ?? []) as $c): ?>
                                    <option value="<?= esc($c['id']) ?>"><?= esc($c['cat_nome'] ?? $c['nome'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="<?= base_url('admin/cadastro/categorias-financeiras') ?>" target="_blank" rel="noopener" class="text-primary text-decoration-underline">Cadastrar nova categoria</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="receita_descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="receita_descricao" name="lan_descricao" placeholder="Ex.: Venda de um pneu" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="receita_data_vencimento" class="form-label">Data vencimento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="receita_data_vencimento" name="lan_data_vencimento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="receita_valor" class="form-label">Valor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money" id="receita_valor" name="lan_valor" placeholder="Ex.: 150,00" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="receita_locacao" class="form-label">Locação</label>
                            <select class="form-select" id="receita_locacao" name="lan_locacao_id">
                                <option value="">Selecione a locação</option>
                                <?php foreach (($locacoes ?? []) as $l): ?>
                                    <option value="<?= esc($l['id']) ?>"><?= esc($l['label'] ?? ('Locação #' . ($l['id'] ?? ''))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="receita_veiculo" class="form-label">Veículo</label>
                            <input type="text" class="form-control" id="receita_veiculo" name="lan_veiculo_placa" placeholder="Ex.: ABC-1234 ou ABC-1A23" style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="receita_marcar_recebida" name="marcar_recebida">
                                <label class="form-check-label" for="receita_marcar_recebida">
                                    Marcar como recebida
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Link para expandir campos adicionais -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <a href="#" class="text-primary text-decoration-underline" id="toggleReceitaCampos">
                                <iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon>
                                Ver mais
                            </a>
                        </div>
                    </div>

                    <!-- Campos Adicionais (Ocultos inicialmente) -->
                    <div id="receitaCamposAdicionais" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="receita_data_lancamento" class="form-label">Data lançamento</label>
                                <input type="date" class="form-control" id="receita_data_lancamento" name="lan_data_lancamento">
                            </div>
                            <div class="col-md-6">
                                <label for="receita_data_pagamento" class="form-label">Data pagamento</label>
                                <input type="date" class="form-control" id="receita_data_pagamento" name="lan_data_pagamento">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="receita_valor_pago" class="form-label">Valor pago</label>
                                <input type="text" class="form-control money" id="receita_valor_pago" name="lan_valor_pago" placeholder="Ex.: 150,00">
                            </div>
                            <div class="col-md-6">
                                <label for="receita_forma_pagamento" class="form-label">Forma de pagamento</label>
                                <select class="form-select" id="receita_forma_pagamento" name="lan_forma_pagamento">
                                    <option value="">Selecione...</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao_credito">Cartão de Crédito</option>
                                    <option value="cartao_debito">Cartão de Débito</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="transferencia">Transferência</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="receita_referencia" class="form-label">Referência</label>
                                <input type="text" class="form-control" id="receita_referencia" name="lan_referencia" placeholder="NSU, código PIX, comprovante, etc.">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="receita_obs" class="form-label">Observações</label>
                                <textarea class="form-control" id="receita_obs" name="lan_obs" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSalvarReceita">
                    <iconify-icon icon="iconamoon:plus-duotone"></iconify-icon>
                    <span class="btn-label">+ Receita</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Despesa -->
<div class="modal fade" id="modalDespesa" tabindex="-1" aria-labelledby="modalDespesaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content d-flex flex-column">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDespesaLabel">Despesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <form id="formDespesa">
                    <input type="hidden" id="despesa_id" name="lancamento_id" value="">
                    <input type="hidden" id="despesa_tipo" name="lan_tipo" value="despesa">
                    <!-- Campos Principais -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="despesa_categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                            <select class="form-select" id="despesa_categoria" name="lan_categoria_id" required>
                                <option value="">Selecione a categoria da despesa</option>
                                <?php foreach (($categorias_despesa ?? []) as $c): ?>
                                    <option value="<?= esc($c['id']) ?>"><?= esc($c['cat_nome'] ?? $c['nome'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="<?= base_url('admin/cadastro/categorias-financeiras') ?>" target="_blank" rel="noopener" class="text-primary text-decoration-underline">Cadastrar nova categoria</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="despesa_descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="despesa_descricao" name="lan_descricao" placeholder="Ex.: Pagamento da Internet" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="despesa_data_vencimento" class="form-label">Data vencimento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="despesa_data_vencimento" name="lan_data_vencimento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="despesa_valor" class="form-label">Valor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money" id="despesa_valor" name="lan_valor" placeholder="Ex.: 150,00" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="despesa_locacao" class="form-label">Locação</label>
                            <select class="form-select" id="despesa_locacao" name="lan_locacao_id">
                                <option value="">Selecione a locação</option>
                                <?php foreach (($locacoes ?? []) as $l): ?>
                                    <option value="<?= esc($l['id']) ?>"><?= esc($l['label'] ?? ('Locação #' . ($l['id'] ?? ''))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="despesa_veiculo" class="form-label">Veículo</label>
                            <input type="text" class="form-control" id="despesa_veiculo" name="lan_veiculo_placa" placeholder="Ex.: ABC-1234 ou ABC-1A23" style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="despesa_marcar_paga" name="marcar_paga">
                                <label class="form-check-label" for="despesa_marcar_paga">
                                    Marcar como paga
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Link para expandir campos adicionais -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <a href="#" class="text-primary text-decoration-underline" id="toggleDespesaCampos">
                                <iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon>
                                Ver mais
                            </a>
                        </div>
                    </div>

                    <!-- Campos Adicionais (Ocultos inicialmente) -->
                    <div id="despesaCamposAdicionais" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="despesa_data_lancamento" class="form-label">Data lançamento</label>
                                <input type="date" class="form-control" id="despesa_data_lancamento" name="lan_data_lancamento">
                            </div>
                            <div class="col-md-6">
                                <label for="despesa_data_pagamento" class="form-label">Data pagamento</label>
                                <input type="date" class="form-control" id="despesa_data_pagamento" name="lan_data_pagamento">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="despesa_valor_pago" class="form-label">Valor pago</label>
                                <input type="text" class="form-control money" id="despesa_valor_pago" name="lan_valor_pago" placeholder="Ex.: 150,00">
                            </div>
                            <div class="col-md-6">
                                <label for="despesa_forma_pagamento" class="form-label">Forma de pagamento</label>
                                <select class="form-select" id="despesa_forma_pagamento" name="lan_forma_pagamento">
                                    <option value="">Selecione...</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao_credito">Cartão de Crédito</option>
                                    <option value="cartao_debito">Cartão de Débito</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="transferencia">Transferência</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="despesa_referencia" class="form-label">Referência</label>
                                <input type="text" class="form-control" id="despesa_referencia" name="lan_referencia" placeholder="NSU, código PIX, comprovante, etc.">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="despesa_obs" class="form-label">Observações</label>
                                <textarea class="form-control" id="despesa_obs" name="lan_obs" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnSalvarDespesa">
                    <iconify-icon icon="iconamoon:minus-duotone"></iconify-icon>
                    <span class="btn-label">- Despesa</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagamentoLabel">Efetuar Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formPagamento">
                    <input type="hidden" id="pagamento_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descrição</label>
                        <div class="form-control-plaintext" id="pagamento_descricao">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Valor</label>
                        <div class="form-control-plaintext" id="pagamento_valor">-</div>
                    </div>

                    <div class="mb-3">
                        <label for="pagamento_data_pagamento" class="form-label">Data do Pagamento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="pagamento_data_pagamento" name="lan_data_pagamento" required>
                    </div>

                    <div class="mb-3">
                        <label for="pagamento_valor_pago" class="form-label">Valor Pago</label>
                        <input type="text" class="form-control money" id="pagamento_valor_pago" name="lan_valor_pago" placeholder="Ex.: 150,00">
                        <div class="form-text">Deixe em branco para usar o valor total do lançamento.</div>
                    </div>

                    <div class="mb-3">
                        <label for="pagamento_forma_pagamento" class="form-label">Forma de Pagamento</label>
                        <select class="form-select" id="pagamento_forma_pagamento" name="lan_forma_pagamento">
                            <option value="">Selecione...</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="boleto">Boleto</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pagamento_referencia" class="form-label">Referência</label>
                        <input type="text" class="form-control" id="pagamento_referencia" name="lan_referencia" placeholder="NSU, código PIX, comprovante, etc.">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnEfetuarPagamento">
                    <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                    <span class="btn-label">Efetuar Pagamento</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script para expandir/colapsar campos e máscaras -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle campos adicionais - Receita
    const toggleReceita = document.getElementById('toggleReceitaCampos');
    const receitaCamposAdicionais = document.getElementById('receitaCamposAdicionais');
    let receitaExpandido = false;

    if (toggleReceita) {
        toggleReceita.addEventListener('click', function(e) {
            e.preventDefault();
            receitaExpandido = !receitaExpandido;
            
            if (receitaExpandido) {
                receitaCamposAdicionais.style.display = 'block';
                toggleReceita.innerHTML = '<iconify-icon icon="iconamoon:arrow-up-2-duotone"></iconify-icon> Ver menos';
                // Aplicar máscaras nos campos adicionais quando expandidos
                setTimeout(aplicarMascaraMonetaria, 100);
            } else {
                receitaCamposAdicionais.style.display = 'none';
                toggleReceita.innerHTML = '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
            }
        });
    }

    // Toggle campos adicionais - Despesa
    const toggleDespesa = document.getElementById('toggleDespesaCampos');
    const despesaCamposAdicionais = document.getElementById('despesaCamposAdicionais');
    let despesaExpandido = false;

    if (toggleDespesa) {
        toggleDespesa.addEventListener('click', function(e) {
            e.preventDefault();
            despesaExpandido = !despesaExpandido;
            
            if (despesaExpandido) {
                despesaCamposAdicionais.style.display = 'block';
                toggleDespesa.innerHTML = '<iconify-icon icon="iconamoon:arrow-up-2-duotone"></iconify-icon> Ver menos';
                // Aplicar máscaras nos campos adicionais quando expandidos
                setTimeout(aplicarMascaraMonetaria, 100);
            } else {
                despesaCamposAdicionais.style.display = 'none';
                toggleDespesa.innerHTML = '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
            }
        });
    }

    // Aplicar máscara monetária usando jQuery Mask Plugin
    function aplicarMascaraMonetaria() {
        if (typeof $ !== 'undefined' && $.fn.mask) {
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
            
            // Adicionar ",00" quando sair do campo se tiver 2 ou menos caracteres
            $('.money').focusout(function(){
                if($(this).val().length <= 2 && $(this).val().length > 0){
                    var temp = $(this).val();
                    var newNum = temp + ',00';
                    $(this).val(newNum);
                }
            });
        }
    }
    
    // Aplicar máscaras quando a página carregar
    if (typeof $ !== 'undefined') {
        $(document).ready(function(){
            aplicarMascaraMonetaria();
        });
    } else {
        // Aguardar jQuery carregar
        setTimeout(function() {
            aplicarMascaraMonetaria();
        }, 500);
    }
    
    // Reaplicar máscaras quando modais forem abertos
    const modalReceitaEl = document.getElementById('modalReceita');
    const modalDespesaEl = document.getElementById('modalDespesa');
    const modalPagamentoEl = document.getElementById('modalPagamento');
    
    if (modalReceitaEl) {
        modalReceitaEl.addEventListener('shown.bs.modal', function() {
            setTimeout(aplicarMascaraMonetaria, 100);
        });
    }
    
    if (modalDespesaEl) {
        modalDespesaEl.addEventListener('shown.bs.modal', function() {
            setTimeout(aplicarMascaraMonetaria, 100);
        });
    }
    
    if (modalPagamentoEl) {
        modalPagamentoEl.addEventListener('shown.bs.modal', function() {
            setTimeout(aplicarMascaraMonetaria, 100);
        });
    }

    // Máscara customizada para placas de veículos (aceita formato antigo e Mercosul)
    function aplicarMascaraPlaca(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
            
            // Remove hífens extras
            value = value.replace(/-+/g, '-');
            
            // Limita o tamanho
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            
            // Adiciona hífen após 3 caracteres se não existir
            if (value.length > 3 && value.indexOf('-') === -1) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            
            e.target.value = value;
        });
    }

    // Aplicar máscara de placa
    const camposPlaca = ['receita_veiculo', 'despesa_veiculo'];
    camposPlaca.forEach(function(id) {
        const campo = document.getElementById(id);
        if (campo) {
            aplicarMascaraPlaca(campo);
        }
    });

    // Limpar formulários ao fechar modal
    if (modalReceitaEl) {
        modalReceitaEl.addEventListener('hidden.bs.modal', function() {
            document.getElementById('formReceita').reset();
            receitaCamposAdicionais.style.display = 'none';
            receitaExpandido = false;
            toggleReceita.innerHTML = '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
        });
    }

    if (modalDespesaEl) {
        modalDespesaEl.addEventListener('hidden.bs.modal', function() {
            document.getElementById('formDespesa').reset();
            despesaCamposAdicionais.style.display = 'none';
            despesaExpandido = false;
            toggleDespesa.innerHTML = '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
        });
    }

    // Navegação com Enter entre campos dos formulários
    function setupEnterNavigation(formId, saveButtonId) {
        const form = document.getElementById(formId);
        if (!form) return;

        // Obter todos os campos focáveis em ordem
        const getFocusableFields = () => {
            const fields = [];
            const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');
            inputs.forEach(field => {
                // Verificar se o campo está visível
                const style = window.getComputedStyle(field);
                if (style.display !== 'none' && style.visibility !== 'hidden') {
                    fields.push(field);
                }
            });
            return fields;
        };

        // Adicionar listener de Enter em todos os campos
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                
                const fields = getFocusableFields();
                const currentIndex = fields.indexOf(e.target);
                
                if (currentIndex !== -1) {
                    // Tentar focar no próximo campo
                    let nextIndex = currentIndex + 1;
                    
                    // Se não há próximo campo, focar no botão de salvar
                    if (nextIndex >= fields.length) {
                        const saveBtn = document.getElementById(saveButtonId);
                        if (saveBtn) {
                            saveBtn.focus();
                            return;
                        }
                    }
                    
                    // Focar no próximo campo visível
                    while (nextIndex < fields.length) {
                        const nextField = fields[nextIndex];
                        const style = window.getComputedStyle(nextField);
                        if (style.display !== 'none' && style.visibility !== 'hidden') {
                            nextField.focus();
                            // Se for select, abrir o dropdown
                            if (nextField.tagName === 'SELECT') {
                                setTimeout(() => {
                                    nextField.click();
                                }, 50);
                            }
                            return;
                        }
                        nextIndex++;
                    }
                    
                    // Se não encontrou próximo campo, focar no botão de salvar
                    const saveBtn = document.getElementById(saveButtonId);
                    if (saveBtn) {
                        saveBtn.focus();
                    }
                }
            }
        });
    }

    // Configurar navegação para ambos os formulários
    setupEnterNavigation('formReceita', 'btnSalvarReceita');
    setupEnterNavigation('formDespesa', 'btnSalvarDespesa');
});
</script>
<?= $this->endSection() ?>
