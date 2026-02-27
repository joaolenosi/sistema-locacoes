<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<link href="<?= asset_url('assets/admin/css/configuracoes-planos.css') ?>" rel="stylesheet" type="text/css" />

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Configurações') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Configurações</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row configuracoes-card-wrapper">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#minha-assinatura" role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                            <span class="d-none d-sm-block">Minha Assinatura</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#locadora" role="tab" aria-selected="false">
                            <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                            <span class="d-none d-sm-block">Locadora</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Minha Assinatura -->
                    <div class="tab-pane assinatura-planos active" id="minha-assinatura" role="tabpanel">
                        <!-- Plano Atual -->
                        <div class="card plano-atual-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Plano Atual</h5>
                                <label class="form-label text-muted small mb-1">Nome do plano:</label>
                                <input type="text" class="form-control form-control-plano" value="<?= esc($plano_atual['nome'] ?? 'Período de Teste') ?>" readonly />
                            </div>
                        </div>

                        <?php
                            $diasRestantes = (int) ($plano_atual['dias_restantes'] ?? 0);
                            $descontoAnual = 0;
                            if (!empty($planos) && isset($planos[0]['desconto_anual'])) {
                                $descontoAnual = (float) $planos[0]['desconto_anual'];
                            }
                        ?>
                        <?php if ($diasRestantes > 0): ?>
                        <!-- Banner Trial --> 
                        <div class="trial-banner">
                            <p class="trial-text mb-0">
                                <iconify-icon icon="iconamoon:clock-duotone" style="font-size: 1.25rem;"></iconify-icon>
                                Seu teste grátis termina em <?= $diasRestantes ?> dias! Não perca o acesso
                            </p>
                            <button type="button" class="btn btn-assinar-trial" onclick="document.querySelector('[href=\'#minha-assinatura\']').click(); document.querySelector('.toggle-periodo-wrap')?.scrollIntoView({ behavior: 'smooth' });">
                                Assinar
                            </button>
                        </div>   
                        <?php endif; ?>

                        <!-- Escolha seu plano -->
                        <h5 class="titulo-planos">Escolha seu plano</h5>

                        <!-- Toggle Mensal/Anual -->
                        <div class="d-flex justify-content-center mb-4">
                            <div class="toggle-periodo-wrap" id="toggle-periodo" role="group">
                            <input type="radio" class="btn-check" name="periodo" id="periodo-mensal" value="mensal" checked>
                            <label class="btn btn-toggle-periodo" for="periodo-mensal">Mensal</label>
                            <input type="radio" class="btn-check" name="periodo" id="periodo-anual" value="anual">
                            <label class="btn btn-toggle-periodo" for="periodo-anual">
                                Anual
                                <?php if ($descontoAnual > 0): ?>
                                    <span class="desconto-badge">(<?= esc(rtrim(rtrim(number_format($descontoAnual, 2, ',', '.'), '0'), ',')) ?>% de desconto)</span>
                                <?php endif; ?>
                            </label>
                            </div>
                        </div>

                        <!-- Cards de Planos -->
                        <div class="row g-4">
                            <?php foreach (($planos ?? []) as $i => $plano): ?>
                            <div class="col-lg-4">
                                <div class="card card-plano h-100 <?= isset($plano['mais_escolhido']) && $plano['mais_escolhido'] ? 'card-plano-destaque' : '' ?>">
                                    <?php if (isset($plano['mais_escolhido']) && $plano['mais_escolhido']): ?>
                                    <span class="badge-mais-escolhido">
                                        <iconify-icon icon="iconamoon:like-1-duotone"></iconify-icon>
                                        Mais escolhido
                                    </span>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h3 class="card-title"><?= esc($plano['nome']) ?></h3>
                                        <div class="preco-atual-wrap">
                                            <span class="preco-original" id="preco-original-<?= $plano['id'] ?>">
                                                de R$ <?= number_format($plano['preco_mensal'] * 1.2, 2, ',', '.') ?> por
                                            </span>
                                            <span class="preco-valor" id="preco-atual-<?= $plano['id'] ?>">R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?></span>
                                            <span class="preco-periodo" id="periodo-texto-<?= $plano['id'] ?>">/ Mês</span>
                                        </div>

                                        <?php if ($i > 0): ?>
                                        <p class="text-muted small mb-3">
                                            Inclui tudo do Plano <?= esc($planos[$i - 1]['nome'] ?? '') ?><?= $i >= 2 ? ' e ' . esc($planos[$i - 2]['nome'] ?? '') : '' ?>, mais:
                                        </p>
                                        <?php else: ?>
                                        <p class="text-muted small mb-3"><?= esc($plano['descricao']) ?></p>
                                        <?php endif; ?>

                                        <ul class="lista-beneficios">
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Cadastro de <?= $plano['limite_veiculos'] ? 'até ' . $plano['limite_veiculos'] . ' veículos' : 'veículos ilimitados' ?></span>
                                            </li>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span><?= $plano['limite_locatarios'] ? 'Cadastro de até ' . $plano['limite_locatarios'] . ' locatários' : 'Cadastro ilimitado de locatários' ?></span>
                                            </li>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span><?= $plano['limite_locacoes'] ? 'Controle de até ' . $plano['limite_locacoes'] . ' locações' : 'Controle ilimitado de locações' ?></span>
                                            </li>
                                            <?php if ($plano['suporte_tipo'] == 'whatsapp'): ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Suporte via WhatsApp e e-mail</span>
                                            </li>
                                            <?php elseif ($plano['suporte_tipo'] == 'prioritario'): ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Suporte prioritário via WhatsApp e e-mail</span>
                                            </li>
                                            <?php else: ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Suporte via e-mail</span>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['backup_diario']): ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Backup diário automático de segurança em nuvem</span>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['relatorios_avancados']): ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Relatórios personalizados sob demanda</span>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['acesso_antecipado']): ?>
                                            <li>
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                <span>Acesso antecipado a novas funcionalidades e melhorias</span>
                                            </li>
                                            <?php endif; ?>
                                        </ul>

                                        <button type="button" class="btn btn-assinar-plano w-100" onclick="assinarPlano(<?= $plano['id'] ?>)">
                                            Assinar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Histórico de cobranças -->
                        <div class="historico-cobrancas">
                            <h5 class="titulo-historico">Histórico de cobranças</h5>
                            <div class="empty-state-cobrancas">
                                <div class="empty-icon">
                                    <iconify-icon icon="iconamoon:warning-duotone"></iconify-icon>
                                </div>
                                <p class="empty-text">Opa, ainda não tem cobranças, assine um plano.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Locadora -->
                    <div class="tab-pane" id="locadora" role="tabpanel">
                        <p class="text-muted mb-4">Atualize os dados da sua locadora aqui.</p>
                        <?= $this->include('admin/partials/form_empresa') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<script>
window.__CONFIG_PLANOS__ = <?= json_encode($planos ?? []) ?>;
window.__CONFIG_EMPRESA__ = <?= json_encode($empresa ?? []) ?>;
</script>
<script src="<?= asset_url('assets/admin/js/pages/empresa.js') ?>"></script>
<script src="<?= asset_url('assets/admin/js/pages/configuracoes.js') ?>"></script>

<?= $this->endSection() ?>
