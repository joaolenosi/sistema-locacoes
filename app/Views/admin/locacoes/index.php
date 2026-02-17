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
              <label for="loc_valor_caucao" class="form-label">Valor da caução</label>
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
              <label for="loc_status" class="form-label">Status</label>
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
<?= $this->endSection() ?>
