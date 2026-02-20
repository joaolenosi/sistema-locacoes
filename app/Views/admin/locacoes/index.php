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
    
    /* Estilo para botão de ajuda do status */
    .form-label .btn-link {
        display: inline-flex;
        align-items: center;
        padding: 0;
        margin-left: 0.25rem;
        vertical-align: middle;
        border: none;
        background: none;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }
    
    .form-label .btn-link:hover {
        opacity: 0.7;
    }
    
    .form-label .btn-link:focus {
        box-shadow: none;
        outline: 2px solid rgba(13, 110, 253, 0.25);
        outline-offset: 2px;
        border-radius: 4px;
    }

    /* Cards resumo (Locações) */
    .loc-kpi-card {
        border: 0;
        border-radius: 16px;
        color: #fff;
        overflow: hidden;
        min-height: 110px;
    }

    .loc-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Campos readonly com aparência de desabilitados */
    #loc_cli_display[readonly],
    #loc_vei_display[readonly] {
        background-color: #e9ecef !important;
        cursor: not-allowed;
    }

    /* Garantir que o texto digitado seja totalmente preto */
    .form-control,
    .form-select,
    .form-control:focus,
    .form-select:focus {
        color: #000000 !important;
    }

    /* Placeholders em cinza mais claro */
    .form-control::placeholder,
    .form-control::-webkit-input-placeholder,
    .form-control::-moz-placeholder,
    .form-control:-ms-input-placeholder {
        color: #adb5bd !important;
        opacity: 1 !important;
    }

    /* Aplicar também nos modais */
    #modalLocacao .form-control,
    #modalLocacao .form-select,
    #modalEscolherLocatario .form-control,
    #modalEscolherLocatario .form-select,
    #modalEscolherVeiculo .form-control,
    #modalEscolherVeiculo .form-select {
        color: #000000 !important;
    }

    #modalLocacao .form-control::placeholder,
    #modalLocacao .form-control::-webkit-input-placeholder,
    #modalLocacao .form-control::-moz-placeholder,
    #modalLocacao .form-control:-ms-input-placeholder,
    #modalEscolherLocatario .form-control::placeholder,
    #modalEscolherLocatario .form-control::-webkit-input-placeholder,
    #modalEscolherLocatario .form-control::-moz-placeholder,
    #modalEscolherLocatario .form-control:-ms-input-placeholder,
    #modalEscolherVeiculo .form-control::placeholder,
    #modalEscolherVeiculo .form-control::-webkit-input-placeholder,
    #modalEscolherVeiculo .form-control::-moz-placeholder,
    #modalEscolherVeiculo .form-control:-ms-input-placeholder {
        color: #adb5bd !important;
        opacity: 1 !important;
    }

    /* Estilos para modal de detalhes - Cards financeiros */
    #modalDetalhesLocacao .card[style*="background: #"] {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #modalDetalhesLocacao .card[style*="background: #"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Card de próximo pagamento */
    #modalDetalhesLocacao .card[style*="background: linear-gradient"] {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Cards de informações detalhadas */
    #modalDetalhesLocacao .card.border {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    /* Tema escuro - ajustes para cards coloridos */
    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: #"],
    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: linear-gradient"] {
        opacity: 0.95;
    }

    /* Garantir legibilidade no tema escuro */
    [data-bs-theme="dark"] #modalDetalhesLocacao .card.border {
        background-color: #252836 !important;
        border-color: #2f3542 !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card.border .text-dark {
        color: #e9ecef !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card.border .text-muted {
        color: #adb5bd !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: #f8f9fa"] {
        background-color: #252836 !important;
        border-color: #2f3542 !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: #f8f9fa"] .text-dark {
        color: #e9ecef !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: linear-gradient(135deg, #f8f9fa"] {
        background: linear-gradient(135deg, #252836 0%, #1f232e 100%) !important;
    }

    [data-bs-theme="dark"] #modalDetalhesLocacao .card[style*="background: linear-gradient(135deg, #f8f9fa"] .text-dark {
        color: #e9ecef !important;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Listagem de Locações') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Locações</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<!-- Cards resumo -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card loc-kpi-card" style="background: #22c55e;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Entradas</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-loc-entradas"><?= esc($entradas ?? 0) ?></span>
                    </div>
                </div>
                <div class="loc-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card loc-kpi-card" style="background: #2d7ef7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Saídas</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-loc-saidas"><?= esc($saidas ?? 0) ?></span>
                    </div>
                </div>
                <div class="loc-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:arrow-up-2-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card loc-kpi-card" style="background: #ff5c6c;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Em atraso</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <span id="kpi-loc-em-atraso"><?= esc($em_atraso ?? 0) ?></span>
                    </div>
                </div>
                <div class="loc-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:warning-duotone" class="fs-22 text-white"></iconify-icon>
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
                    <h5 class="card-title mb-0">Listagem de Locações</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btn-filtros">
                            <iconify-icon icon="iconamoon:filter-duotone" class="fs-18"></iconify-icon>
                            Filtros
                        </button>
                        <button type="button" class="btn btn-primary" onclick="abrirModalLocacao()">
                            <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                            Nova Locação
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Gerencie todas as locações cadastradas no sistema. Use a busca para filtrar ou clique nas colunas para ordenar.
                </p>
                
                <div class="py-3">
                    <div id="table-locacoes"></div>
                    
                    <!-- Container para filtros customizados (será movido pelo JS) -->
                    <div id="filtros-container" style="display: none;">
                        <input type="text" class="form-control form-control-sm d-inline-block" id="filtro-placa" placeholder="Buscar por placa" style="width: 170px; margin-right: 0.5rem;">
                        <select class="form-select form-select-sm d-inline-block" id="filtro-status" style="width: 170px; margin-right: 0.5rem;">
                            <option value="">Selecione o status</option>
                            <option value="ativa">Ativa</option>
                            <option value="finalizada">Finalizada</option>
                            <option value="cancelada">Cancelada</option>
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

<!-- jQuery Mask Plugin (já usado em outras telas) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
window.__LOCACOES_BOOTSTRAP__ = {
  locacoes: <?= json_encode($locacoes ?? []) ?>,
  kpis: {
    entradas: <?= json_encode($entradas ?? 0) ?>,
    saidas: <?= json_encode($saidas ?? 0) ?>,
    emAtraso: <?= json_encode($em_atraso ?? 0) ?>
  }
};
</script>

<!-- Gridjs Locações js -->
<script src="<?= asset_url('assets/admin/js/pages/locacoes.js') ?>"></script>

<!-- Modal Locação (Cadastro/Edição) -->
<div class="modal fade" id="modalLocacao" tabindex="-1" aria-labelledby="modalLocacaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content d-flex flex-column">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLocacaoLabel">Cadastrar locação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
        <form id="formLocacao">
          <input type="hidden" id="locacao_id" name="locacao_id" value="">
          <input type="hidden" id="loc_cli_id" name="loc_cli_id" value="">
          <input type="hidden" id="loc_vei_id" name="loc_vei_id" value="">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Locatário <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control" id="loc_cli_display" placeholder="Selecione o locatário" readonly required>
                <button class="btn btn-outline-secondary" type="button" onclick="abrirModalEscolherLocatario()" title="Pesquisar locatário">
                  <iconify-icon icon="iconamoon:search-duotone" class="fs-18"></iconify-icon>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Placa do veículo <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control" id="loc_vei_display" placeholder="Selecione a placa do veículo" readonly required>
                <button class="btn btn-outline-secondary" type="button" onclick="abrirModalEscolherVeiculo()" title="Pesquisar veículo">
                  <iconify-icon icon="iconamoon:search-duotone" class="fs-18"></iconify-icon>
                </button>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_data_inicio" class="form-label">Início da locação <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="loc_data_inicio" name="loc_data_inicio" required>
            </div>
            <div class="col-md-6">
              <label for="loc_tempo_minimo" class="form-label">Tempo mínimo</label>
              <select class="form-select" id="loc_tempo_minimo">
                <option value="">Selecione a duração</option>
                <option value="1">1 dia</option>
                <option value="3">3 dias</option>
                <option value="7">7 dias</option>
                <option value="15">15 dias</option>
                <option value="30">30 dias</option>
                <option value="365">1 ano</option>
                <option value="730">2 anos</option>  
              </select>
              <div class="form-text">Opcional: sugere a data fim prevista (você pode alterar).</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_valor_locacao" class="form-label">Valor da locação <span class="text-danger">*</span></label>
              <input type="text" class="form-control money" id="loc_valor_locacao" name="loc_valor_locacao" placeholder="0,00" required>
            </div>
            <div class="col-md-6">
              <label for="loc_valor_caucao" class="form-label">Valor do caução</label>
              <input type="text" class="form-control money" id="loc_valor_caucao" name="loc_valor_caucao" placeholder="0,00">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_data_inicio_pagamento" class="form-label">Início do pagamento</label>
              <input type="date" class="form-control" id="loc_data_inicio_pagamento" name="loc_data_inicio_pagamento">
            </div>
            <div class="col-md-6">
              <label for="loc_recorrencia_pagamento" class="form-label">Recorrência de pagamento</label>
              <select class="form-select" id="loc_recorrencia_pagamento" name="loc_recorrencia_pagamento">
                <option value="">Selecione a recorrência</option>
                <option value="diaria">Diária</option>
                <option value="semanal">Semanal</option>
                <option value="quinzenal">Quinzenal</option>
                <option value="mensal">Mensal</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_taxa_juros" class="form-label">Taxa de juros R$</label>
              <input type="text" class="form-control money" id="loc_taxa_juros" name="loc_taxa_juros" placeholder="0,00">
            </div>
            <div class="col-md-6">
              <label for="loc_taxa_multa" class="form-label">Taxa de multa R$</label>
              <input type="text" class="form-control money" id="loc_taxa_multa" name="loc_taxa_multa" placeholder="0,00">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_km_retirada" class="form-label">KM na retirada</label>
              <input type="number" class="form-control" id="loc_km_retirada" name="loc_km_retirada" placeholder="Ex.: 68000">
            </div>
            <div class="col-md-6">
              <label for="loc_data_fim_prevista" class="form-label">Fim previsto <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="loc_data_fim_prevista" name="loc_data_fim_prevista" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="loc_status" class="form-label">
                Status
                <button type="button" class="btn btn-link p-0 ms-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; vertical-align: middle; text-decoration: none;" data-bs-toggle="modal" data-bs-target="#modalAjudaStatus" title="Ajuda sobre status" aria-label="Ajuda sobre status">
                  <iconify-icon icon="iconamoon:question-duotone" class="text-primary" style="width: 18px; height: 18px; font-size: 18px;"></iconify-icon>
                </button>
              </label>
              <select class="form-select" id="loc_status" name="loc_status">
                <option value="reservada">Reservada</option>
                <option value="ativa">Ativa</option>
                <option value="atrasada">Atrasada</option>
                <option value="inadimplente">Inadimplente</option>
                <option value="finalizada">Finalizada</option>
                <option value="cancelada">Cancelada</option>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="loc_valores_recebidos" name="loc_valores_recebidos">
                <label class="form-check-label" for="loc_valores_recebidos">Marcar valores como recebido</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnSalvarLocacao" onclick="salvarLocacao()">
          <span class="btn-label">Adicionar</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: escolher veículo -->
<div class="modal fade" id="modalEscolherVeiculo" tabindex="-1" aria-labelledby="modalEscolherVeiculoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEscolherVeiculoLabel">Selecionar veículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <input type="text" class="form-control" id="filtro-veiculo-geral" placeholder="Buscar por placa, modelo ou marca...">
          </div>
          <div class="col-md-3">
            <select class="form-select" id="filtro-veiculo-status">
              <option value="">Status (todos)</option>
              <option value="disponivel">Disponível</option>
              <option value="locado">Locado</option>
              <option value="manutencao">Manutenção</option>
              <option value="inativo">Inativo</option>
            </select>
          </div>
          <div class="col-md-3 d-flex justify-content-end align-items-center">
            <small class="text-muted">Clique em selecionar para preencher</small>
          </div>
        </div>
        <div id="table-escolher-veiculo"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: escolher locatário -->
<div class="modal fade" id="modalEscolherLocatario" tabindex="-1" aria-labelledby="modalEscolherLocatarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEscolherLocatarioLabel">Selecionar locatário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <input type="text" class="form-control" id="filtro-locatario-nome" placeholder="Buscar por nome...">
          </div>
          <div class="col-md-3">
            <input type="text" class="form-control" id="filtro-locatario-cpfcnpj" placeholder="Filtrar por CPF/CNPJ">
          </div>
          <div class="col-md-3 d-flex justify-content-end align-items-center">
            <small class="text-muted">Clique em selecionar para preencher</small>
          </div>
        </div>
        <div id="table-escolher-locatario"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Detalhes da Locação -->
<div class="modal fade" id="modalDetalhesLocacao" tabindex="-1" aria-labelledby="modalDetalhesLocacaoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="modalDetalhesLocacaoLabel" style="font-size: 1.1rem;">
          <iconify-icon icon="iconamoon:info-duotone" class="text-primary"></iconify-icon>
          Detalhes da Locação
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-3 px-3">
        <!-- Cabeçalho com ID e Status -->
        <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px;">
          <div class="card-body py-2 px-3">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <div style="width: 36px; height: 36px; border-radius: 8px; background: #0d6efd; display: inline-flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="iconamoon:file-duotone" class="fs-18 text-white"></iconify-icon>
                  </div>
                  <div>
                    <h5 class="mb-0 text-dark fw-bold" id="detalhes-loc-id" style="font-size: 1.1rem;">#000000</h5>
                    <p class="text-muted mb-0 small" style="font-size: 0.8rem;">
                      <strong id="detalhes-loc-cliente">-</strong>
                    </p>
                  </div>
                </div>
                <p class="text-primary mb-0 mt-1 fw-medium small">
                  <iconify-icon icon="iconamoon:car-duotone" class="fs-16"></iconify-icon>
                  <span id="detalhes-loc-veiculo-placa">-</span> | 
                  <span id="detalhes-loc-veiculo-modelo">-</span>
                </p>
              </div>
              <div class="col-md-6 text-end">
                <div class="mb-1">
                  <span class="badge px-2 py-1" id="detalhes-loc-status" style="border-radius: 6px; font-size: 0.75rem;">-</span>
                </div>
                <div class="text-dark small" style="font-size: 0.8rem;">
                  <div><strong>Início:</strong> <span id="detalhes-loc-data-inicio">-</span></div>
                  <div><strong>Fim previsto:</strong> <span id="detalhes-loc-data-fim-prevista">-</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações Financeiras -->
        <div class="row g-2 mb-3">
          <div class="col-md-12">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:wallet-duotone" class="text-primary"></iconify-icon>
              Informações Financeiras
            </h6>
          </div>
          <div class="col-md-4">
            <div class="card border-0" style="background: #22c55e; border-radius: 10px; overflow: hidden;">
              <div class="card-body text-center text-white py-2 px-2">
                <div class="mb-1">
                  <div style="width: 32px; height: 32px; border-radius: 999px; background: rgba(255, 255, 255, 0.22); display: inline-flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="fs-18 text-white"></iconify-icon>
                  </div>
                </div>
                <div class="fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.1;" id="detalhes-loc-valor-locacao">R$ 0,00</div>
                <div class="text-white" style="opacity: 0.95; font-size: 0.75rem;">Valor da Locação</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0" style="background: #2d7ef7; border-radius: 10px; overflow: hidden;">
              <div class="card-body text-center text-white py-2 px-2">
                <div class="mb-1">
                  <div style="width: 32px; height: 32px; border-radius: 999px; background: rgba(255, 255, 255, 0.22); display: inline-flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="iconamoon:shield-check-duotone" class="fs-18 text-white"></iconify-icon>
                  </div>
                </div>
                <div class="fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.1;" id="detalhes-loc-valor-caucao">R$ 0,00</div>
                <div class="text-white" style="opacity: 0.95; font-size: 0.75rem;">Valor do Caução</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0" style="background: #6366f1; border-radius: 10px; overflow: hidden;">
              <div class="card-body text-center text-white py-2 px-2">
                <div class="mb-1">
                  <div style="width: 32px; height: 32px; border-radius: 999px; background: rgba(255, 255, 255, 0.22); display: inline-flex; align-items: center; justify-content: center;">
                    <iconify-icon icon="iconamoon:calculator-duotone" class="fs-18 text-white"></iconify-icon>
                  </div>
                </div>
                <div class="fw-semibold text-white" style="font-size: 1.25rem; line-height: 1.1;" id="detalhes-loc-valor-total">R$ 0,00</div>
                <div class="text-white" style="opacity: 0.95; font-size: 0.75rem;">Valor Total</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Próximo Pagamento -->
        <div class="row g-2 mb-3">
          <div class="col-md-12">
            <div class="card border-0 mb-0" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 10px;">
              <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center justify-content-between text-white">
                  <div class="d-flex align-items-center gap-2">
                    <div style="width: 28px; height: 28px; border-radius: 999px; background: rgba(255, 255, 255, 0.22); display: inline-flex; align-items: center; justify-content: center;">
                      <iconify-icon icon="iconamoon:calendar-duotone" class="fs-16 text-white"></iconify-icon>
                    </div>
                    <div>
                      <span class="opacity-90" style="font-size: 0.75rem;">Próximo pagamento</span>
                      <span class="fw-semibold d-block" style="font-size: 0.95rem;" id="detalhes-loc-proximo-pagamento">-</span>
                    </div>
                  </div>
                  <div class="text-end">
                    <span class="opacity-90" style="font-size: 0.75rem;">Valor</span>
                    <span class="fw-bold d-block" style="font-size: 1.1rem;" id="detalhes-loc-valor-proximo-pagamento">R$ 0,00</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações Detalhadas -->
        <div class="row g-2">
          <div class="col-md-6">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:calendar-duotone" class="text-primary"></iconify-icon>
              Datas
            </h6>
            <div class="card border" style="background: #ffffff; border-color: #e9ecef !important; border-radius: 8px;">
              <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                  <tbody>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="width: 40%; padding: 0.4rem 0.75rem;">Início:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-data-inicio-duplicado">-</strong></td></tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Fim previsto:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-data-fim-prevista-duplicado">-</strong></td></tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Fim real:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-data-fim-real">-</strong></td></tr>
                    <tr><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Início pagamento:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-data-inicio-pagamento">-</strong></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:settings-duotone" class="text-primary"></iconify-icon>
              Configurações
            </h6>
            <div class="card border" style="background: #ffffff; border-color: #e9ecef !important; border-radius: 8px;">
              <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                  <tbody>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="width: 40%; padding: 0.4rem 0.75rem;">Recorrência:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-recorrencia">-</strong></td></tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Taxa de juros:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-taxa-juros">-</strong></td></tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Taxa de multa:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-taxa-multa">-</strong></td></tr>
                    <tr><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">Valores recebidos:</td><td style="padding: 0.4rem 0.75rem;"><span class="badge" id="detalhes-loc-valores-recebidos-badge">-</span></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Quilometragem -->
        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:car-duotone" class="text-primary"></iconify-icon>
              Quilometragem
            </h6>
            <div class="card border" style="background: #ffffff; border-color: #e9ecef !important; border-radius: 8px;">
              <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                  <tbody>
                    <tr style="border-bottom: 1px solid #f1f3f5;"><td class="text-muted fw-medium" style="width: 40%; padding: 0.4rem 0.75rem;">KM na retirada:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-km-retirada">-</strong></td></tr>
                    <tr><td class="text-muted fw-medium" style="padding: 0.4rem 0.75rem;">KM na devolução:</td><td style="padding: 0.4rem 0.75rem;"><strong class="text-dark" id="detalhes-loc-km-devolucao">-</strong></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:note-duotone" class="text-primary"></iconify-icon>
              Observações Operacionais
            </h6>
            <div class="card border" style="background: #f8f9fa; border-color: #e9ecef !important; border-radius: 8px;">
              <div class="card-body py-2 px-3" style="font-size: 0.85rem;">
                <p class="mb-0 text-dark" id="detalhes-loc-obs-operacionais" style="white-space: pre-wrap;">-</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <h6 class="fw-semibold mb-2" style="font-size: 0.9rem;">
              <iconify-icon icon="iconamoon:wallet-duotone" class="text-primary"></iconify-icon>
              Observações Financeiras
            </h6>
            <div class="card border" style="background: #f8f9fa; border-color: #e9ecef !important; border-radius: 8px;">
              <div class="card-body py-2 px-3" style="font-size: 0.85rem;">
                <p class="mb-0 text-dark" id="detalhes-loc-obs-financeiras" style="white-space: pre-wrap;">-</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        <button type="button" class="btn btn-primary" id="btnEditarLocacaoDetalhes">
          <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
          Editar Locação
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ajuda sobre Status -->
<div class="modal fade" id="modalAjudaStatus" tabindex="-1" aria-labelledby="modalAjudaStatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAjudaStatusLabel">
          <iconify-icon icon="iconamoon:question-duotone" class="text-primary"></iconify-icon>
          Ajuda sobre Status de Locação
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <p class="text-muted">Entenda quando usar cada status e como eles ajudam a gerenciar suas locações:</p>
        </div>

        <div class="list-group">
          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-info text-dark me-2">Reservada</span>
                Reservada
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando um cliente fez uma reserva, mas ainda não retirou o veículo.</p>
            <small class="text-muted">Exemplo: Cliente fez o pagamento antecipado ou deixou um sinal, mas a locação ainda não começou oficialmente.</small>
          </div>

          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-success me-2">Ativa</span>
                Ativa
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando o veículo foi retirado pelo cliente e a locação está em andamento normalmente.</p>
            <small class="text-muted">Exemplo: Cliente retirou o veículo, está dentro do prazo de devolução e não há problemas pendentes.</small>
          </div>

          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-warning text-dark me-2">Atrasada</span>
                Atrasada
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando a data de devolução prevista já passou, mas o cliente ainda não devolveu o veículo.</p>
            <small class="text-muted">Exemplo: A locação deveria ter terminado ontem, mas o veículo ainda não foi devolvido. Pode haver cobrança de multa por atraso.</small>
          </div>

          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-danger me-2">Inadimplente</span>
                Inadimplente
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando o cliente está com pagamentos em atraso ou não está cumprindo as obrigações contratuais.</p>
            <small class="text-muted">Exemplo: Cliente não pagou as mensalidades, multas ou está com pendências financeiras relacionadas à locação.</small>
          </div>

          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-secondary me-2">Finalizada</span>
                Finalizada
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando a locação foi concluída com sucesso, o veículo foi devolvido e todos os pagamentos foram quitados.</p>
            <small class="text-muted">Exemplo: Cliente devolveu o veículo, todos os valores foram pagos e não há pendências. A locação foi encerrada normalmente.</small>
          </div>

          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between align-items-start mb-2">
              <h6 class="mb-1">
                <span class="badge bg-dark me-2">Cancelada</span>
                Cancelada
              </h6>
            </div>
            <p class="mb-1"><strong>Quando usar:</strong> Use este status quando a locação foi cancelada antes de ser concluída, seja por desistência do cliente ou por outros motivos.</p>
            <small class="text-muted">Exemplo: Cliente desistiu antes de retirar o veículo, ou houve algum problema que impediu a locação de prosseguir.</small>
          </div>
        </div>

        <div class="alert alert-info mt-4 mb-0">
          <iconify-icon icon="iconamoon:info-duotone"></iconify-icon>
          <strong>Dica:</strong> O status pode ser alterado a qualquer momento durante o ciclo de vida da locação para refletir a situação atual.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
