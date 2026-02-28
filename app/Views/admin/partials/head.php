<!-- Title Meta -->
<meta charset="utf-8" />
<title><?= esc($title ?? 'Dashboard') ?> | Sistema de Locações.</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta
    name="description"
    content="Sistema de Locações."
/>
<meta name="author" content="Techzaa" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<!-- App favicon -->
<link rel="shortcut icon" href="<?= asset_url('assets/admin/images/fav.png') ?>" type="image/png" />

<!-- Vendor css (Require in all Page) -->
<link href="<?= asset_url('assets/admin/css/vendor.min.css') ?>" rel="stylesheet" type="text/css" />

<!-- Icons css (Require in all Page) -->
<link href="<?= asset_url('assets/admin/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />

<!-- App css (Require in all Page) -->
<link href="<?= asset_url('assets/admin/css/app.min.css') ?>" rel="stylesheet" type="text/css" />

<!-- CSS Customizado para Footer e Tema Dark -->
<style>
    /* Área de conteúdo - min-height sempre aplicado */
    .page-content {
        min-height: calc(100vh - 70px);
    }
    
    /* Estilos para tema DARK */
    [data-bs-theme="dark"] .page-content {
        background-color: #1a1d29 !important;
    }
    
    [data-bs-theme="dark"] .container-xxl {
        background-color: transparent !important;
    }
    
    /* Cards com fundo escuro apenas no tema dark */
    [data-bs-theme="dark"] .card {
        background-color: #252836 !important;
        border-color: #2f3542 !important;
        color: #e9ecef !important;
    }
    
    /* Card body com padding adequado - garante espaçamento dos textos */
    .card-body {
        padding: 1.5rem !important;
    }
    
    [data-bs-theme="dark"] .card-body {
        color: #e9ecef !important;
    }
    
    /* Garantir padding em elementos diretos do card quando não há card-body */
    .card > .row,
    .card > .d-flex,
    .card > div:not(.card-header):not(.card-footer):not(.card-img-top):not(.card-body) {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }
    
    .card > .row:first-child,
    .card > .d-flex:first-child,
    .card > div:first-child:not(.card-header):not(.card-img-top):not(.card-footer):not(.card-body) {
        padding-top: 1.5rem !important;
    }
    
    .card > .row:last-child,
    .card > .d-flex:last-child,
    .card > div:last-child:not(.card-header):not(.card-footer):not(.card-body) {
        padding-bottom: 1.5rem !important;
    }
    
    /* Cards de KPI (com padding específico) */
    .card.vei-kpi-card .card-body,
    .card.fin-kpi-card .card-body,
    .card.loc-kpi-card .card-body {
        padding: 1.25rem !important;
    }
    
    /* Textos em cores claras apenas no tema dark */
    [data-bs-theme="dark"] .text-muted {
        color: #adb5bd !important;
    }
    
    /* Títulos em cores claras apenas no tema dark */
    [data-bs-theme="dark"] h1, 
    [data-bs-theme="dark"] h2, 
    [data-bs-theme="dark"] h3, 
    [data-bs-theme="dark"] h4, 
    [data-bs-theme="dark"] h5, 
    [data-bs-theme="dark"] h6,
    [data-bs-theme="dark"] .h1, 
    [data-bs-theme="dark"] .h2, 
    [data-bs-theme="dark"] .h3, 
    [data-bs-theme="dark"] .h4, 
    [data-bs-theme="dark"] .h5, 
    [data-bs-theme="dark"] .h6 {
        color: #e9ecef !important;
    }
    
    /* Footer com cor uniforme e layout harmonioso - apenas no tema dark */
    [data-bs-theme="dark"] .footer {
        background-color: #1a1d29 !important;
        border-top: 1px solid #2f3542;
    }
    
    .footer {
        padding: 1rem 0;
        margin-top: auto;
    }
    
    .footer .container-fluid {
        background-color: transparent !important;
    }
    
    .footer .row {
        background-color: transparent !important;
    }
    
    [data-bs-theme="dark"] .footer .text-center {
        color: #adb5bd !important;
    }
    
    .footer .text-center {
        font-size: 0.875rem;
    }
    
    /* Remover qualquer borda ou separação visual que crie layout quadrado */
    .footer::before,
    .footer::after {
        display: none !important;
    }
    
    /* Page title box - apenas no tema dark */
    .page-title-box {
        background-color: transparent !important;
    }
    
    [data-bs-theme="dark"] .page-title-box h4,
    [data-bs-theme="dark"] .page-title-box .breadcrumb {
        color: #e9ecef !important;
    }
    
    [data-bs-theme="dark"] .breadcrumb-item a {
        color: #adb5bd !important;
    }
    
    [data-bs-theme="dark"] .breadcrumb-item.active {
        color: #e9ecef !important;
    }
    
    /* Backdrop dos modais: sombra mais forte e desfoque para destacar o modal em foco */
    .modal-backdrop {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    .modal-backdrop.show {
        opacity: 0.7 !important;
    }
</style>

<!-- Theme Config js (Require in all Page) -->
<script src="<?= asset_url('assets/admin/js/config.js') ?>"></script>
