<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#minha-assinatura" role="tab" aria-selected="false">
                            <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                            <span class="d-none d-sm-block">Minha Assinatura</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#locadora" role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                            <span class="d-none d-sm-block">Locadora</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Minha Assinatura -->
                    <div class="tab-pane" id="minha-assinatura" role="tabpanel">
                        <!-- Plano Atual -->
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Plano Atual</h5>
                                <p class="mb-2"><strong>Nome do plano:</strong> <?= esc($plano_atual['nome'] ?? 'Período de Teste') ?></p>
                                <?php if (isset($plano_atual['dias_restantes']) && $plano_atual['dias_restantes'] > 0): ?>
                                <p class="mb-0">
                                    <iconify-icon icon="iconamoon:clock-duotone" class="text-warning"></iconify-icon>
                                    Seu teste grátis termina em <?= esc($plano_atual['dias_restantes']) ?> dias!
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Escolha seu plano -->
                        <h5 class="mb-3">Escolha seu plano</h5>
                        
                        <!-- Toggle Mensal/Anual -->
                        <?php
                            $descontoAnual = 0;
                            if (!empty($planos) && isset($planos[0]['desconto_anual'])) {
                                $descontoAnual = (float) $planos[0]['desconto_anual'];
                            }
                        ?>
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group" role="group" id="toggle-periodo">
                                <input type="radio" class="btn-check" name="periodo" id="periodo-mensal" value="mensal" checked>
                                <label class="btn btn-outline-primary" for="periodo-mensal">Mensal</label>
                                
                                <input type="radio" class="btn-check" name="periodo" id="periodo-anual" value="anual">
                                <label class="btn btn-outline-primary" for="periodo-anual">
                                    Anual
                                    <?php if ($descontoAnual > 0): ?>
                                        <span class="badge bg-success ms-1">(<?= esc(rtrim(rtrim(number_format($descontoAnual, 2, ',', '.'), '0'), ',')) ?>% de desconto)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>

                        <!-- Cards de Planos -->
                        <div class="row g-3">
                            <?php foreach (($planos ?? []) as $i => $plano): ?>
                            <div class="col-lg-4">
                                <div class="card h-100 border <?= isset($plano['mais_escolhido']) && $plano['mais_escolhido'] ? 'border-primary' : '' ?>">
                                    <?php if (isset($plano['mais_escolhido']) && $plano['mais_escolhido']): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-success">
                                            <iconify-icon icon="iconamoon:like-1-duotone"></iconify-icon>
                                            Mais escolhido
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h3 class="card-title text-primary mb-3"><?= esc($plano['nome']) ?></h3>
                                        <div class="mb-3">
                                            <span class="text-muted text-decoration-line-through" id="preco-original-<?= $plano['id'] ?>">
                                                de R$ <?= number_format($plano['preco_mensal'] * 1.2, 2, ',', '.') ?>
                                            </span>
                                            <h2 class="mb-0">
                                                <span id="preco-atual-<?= $plano['id'] ?>">R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?></span>
                                                <small class="text-muted fs-14" id="periodo-texto-<?= $plano['id'] ?>">/ Mês</small>
                                            </h2>
                                        </div>
                                        
                                        <?php if ($i > 0): ?>
                                        <p class="text-muted small mb-3">
                                            Inclui tudo do Plano
                                            <?= esc($planos[$i - 1]['nome'] ?? '') ?>
                                            <?= $i >= 2 ? ' e ' . esc($planos[$i - 2]['nome'] ?? '') : '' ?>,
                                            mais:
                                        </p>
                                        <?php else: ?>
                                        <p class="text-muted small mb-3"><?= esc($plano['descricao']) ?></p>
                                        <?php endif; ?>
                                        
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Cadastro de <?= $plano['limite_veiculos'] ? 'até ' . $plano['limite_veiculos'] . ' veículos' : 'veículos ilimitados' ?>
                                            </li>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                <?= $plano['limite_locatarios'] ? 'Cadastro de até ' . $plano['limite_locatarios'] . ' locatários' : 'Cadastro ilimitado de locatários' ?>
                                            </li>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                <?= $plano['limite_locacoes'] ? 'Controle de até ' . $plano['limite_locacoes'] . ' locações' : 'Controle ilimitado de locações' ?>
                                            </li>
                                            <?php if ($plano['suporte_tipo'] == 'whatsapp'): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte via WhatsApp e e-mail
                                            </li>
                                            <?php elseif ($plano['suporte_tipo'] == 'prioritario'): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte prioritário 24/7
                                            </li>
                                            <?php else: ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte via e-mail
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['backup_diario']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Backup diário automático
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['relatorios_avancados']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Relatórios avançados e personalizados
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['acesso_antecipado']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Acesso antecipado a novas funcionalidades
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                        
                                        <div class="mt-auto">
                                            <button type="button" class="btn btn-primary w-100" onclick="assinarPlano(<?= $plano['id'] ?>)">
                                                Assinar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab Locadora -->
                    <div class="tab-pane active" id="locadora" role="tabpanel">
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
<script src="<?= base_url('assets/admin/js/pages/empresa.js') ?>"></script>
<script src="<?= base_url('assets/admin/js/pages/configuracoes.js') ?>"></script>

<?= $this->endSection() ?>
