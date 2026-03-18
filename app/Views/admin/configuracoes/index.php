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
                        <?php
                            $temPlano = !empty($tem_plano_assinado);
                            $planoInfo = $plano_atual['plano'] ?? null;
                            $descontoAnual = 0;
                            if (!empty($planos) && isset($planos[0]['desconto_anual'])) {
                                $descontoAnual = (float) $planos[0]['desconto_anual'];
                            }
                        ?>
                        
                        <!-- Plano Atual -->
                        <div class="card plano-atual-card mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-1 text-white">
                                            <?php if ($temPlano): ?>
                                            <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-white me-1"></iconify-icon>
                                            <?php endif; ?>
                                            <?= esc($plano_atual['nome'] ?? 'Período de Teste') ?>
                                        </h5>
                                        <?php if ($temPlano && $planoInfo): ?>
                                        <p class="text-white small mb-0">
                                            Valor: R$ <?= number_format((float) ($planoInfo['pla_preco_mensal'] ?? 0), 2, ',', '.') ?> / mês
                                            <?php if (!empty($planoInfo['pla_limite_veiculos'])): ?>
                                            | Limite: <?= $planoInfo['pla_limite_veiculos'] ?> veículos
                                            <?php elseif ($temPlano): ?>
                                            | Veículos ilimitados
                                            <?php endif; ?>
                                        </p>
                                        <?php else: ?>
                                        <p class="text-muted small mb-0">Teste gratuito - sem compromisso</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <?php if (!$temPlano): ?>
                                        <span class="badge bg-warning text-dark fs-6">Período de Teste</span>
                                        <?php else: ?>
                                        <span class="badge bg-success fs-6">Assinante Ativo</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!$temPlano): ?>
                        <!-- Banner Trial (só aparece se NÃO tiver plano) --> 
                        <div class="trial-banner">
                            <p class="trial-text mb-0">
                                <iconify-icon icon="iconamoon:clock-duotone" style="font-size: 1.25rem;"></iconify-icon>
                                Experimente grátis por tempo limitado! Assine um plano para continuar usando.
                            </p>
                            <button type="button" class="btn btn-assinar-trial" onclick="document.querySelector('[href=\'#minha-assinatura\']').click(); document.querySelector('.toggle-periodo-wrap')?.scrollIntoView({ behavior: 'smooth' });">
                                Assinar Agora
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
                            <?php if (empty($faturas)): ?>
                            <div class="empty-state-cobrancas">
                                <div class="empty-icon">
                                    <iconify-icon icon="iconamoon:warning-duotone"></iconify-icon>
                                </div>
                                <p class="empty-text">Opa, ainda não tem cobranças, assine um plano.</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Descrição</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Pagamento</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($faturas as $fatura): ?>
                                        <?php
                                            $status = $fatura['fin_status'] ?? 'pendente';
                                            if ($status === 'pago') {
                                                $statusClass = 'bg-success';
                                                $statusLabel = 'Pago';
                                            } elseif ($status === 'vencido') {
                                                $statusClass = 'bg-danger';
                                                $statusLabel = 'Vencido';
                                            } elseif ($status === 'cancelado') {
                                                $statusClass = 'bg-secondary';
                                                $statusLabel = 'Cancelado';
                                            } else {
                                                $statusClass = 'bg-warning text-dark';
                                                $statusLabel = 'Pendente';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= esc($fatura['fin_descricao'] ?? '') ?></td>
                                            <td>R$ <?= number_format((float) ($fatura['fin_valor'] ?? 0), 2, ',', '.') ?></td>
                                            <td><?= date('d/m/Y', strtotime($fatura['fin_data_vencimento'] ?? '')) ?></td>
                                            <td><?= !empty($fatura['fin_data_pagamento']) ? date('d/m/Y', strtotime($fatura['fin_data_pagamento'])) : '-' ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                            <td>
                                                <?php if (in_array($fatura['fin_status'] ?? '', ['pendente', 'vencido'])): ?>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalPagamento(<?= (int) $fatura['id'] ?>)">
                                                    <iconify-icon icon="iconamoon:credit-card-duotone"></iconify-icon>
                                                    Efetuar Pagamento
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
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

<!-- Modal Pagamento PIX -->
<div class="modal fade" id="modalPagamentoPix" tabindex="-1" aria-labelledby="modalPagamentoPixLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPagamentoPixLabel">
                    <iconify-icon icon="iconamoon:qr-code-duotone"></iconify-icon>
                    Efetuar Pagamento - PIX
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 text-center">
                        <div class="qrcode-container mb-3">
                            <img src="<?= asset_url('assets/admin/images/qrcode-pix.png') ?>" alt="QR Code PIX" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <div class="valor-pix">
                            <span class="label">Valor a pagar:</span>
                            <span class="valor" id="valor-pix">R$ 59,90</span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6 class="mb-3">Instruções para pagamento:</h6>
                        <ol class="list-group list-group-numbered mb-3">
                            <li class="list-group-item">Abra o aplicativo do seu banco</li>
                            <li class="list-group-item">Escolha a opção <strong>Pix</strong> ou <strong>Pagar</strong></li>
                            <li class="list-group-item">Escaneie o QR Code ao lado ou copie o código PIX abaixo</li>
                            <li class="list-group-item">Confirme o valor de <strong>R$ 59,90</strong></li>
                            <li class="list-group-item">Efetue o pagamento</li>
                            <li class="list-group-item">Após o pagamento, clique no botão WhatsApp abaixo para confirmar</li>
                        </ol>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Código PIX (Copia e Cola):</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigo-pix" readonly value="">
                                <button class="btn btn-outline-secondary" type="button" onclick="copiarCodigoPix()">
                                    <iconify-icon icon="iconamoon:copy-duotone"></iconify-icon>
                                    Copiar
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <iconify-icon icon="iconamoon:bell-duotone" style="font-size: 1.5rem; margin-right: 0.5rem;"></iconify-icon>
                            <div>
                                Após efetivar o pagamento, é necessário enviar uma confirmação pelo WhatsApp para ativação do sistema.
                            </div>
                        </div>

                        <a href="https://wa.me/5584981359585?text=Olá!%20Efetuei%20o%20pagamento%20da%20mensalidade%20do%20sistema%20de%20locação.%20Por%20favor,%20efetuar%20liberação." 
                           target="_blank" 
                           class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                            <iconify-icon icon="iconamoon:brand-whatsapp-filled" style="font-size: 1.5rem;"></iconify-icon>
                            Confirmar Pagamento via WhatsApp
                        </a>
                        <p class="text-muted small text-center mt-2 mb-0">
                            Efetue o pagamento da mensalidade do sistema, por favor solicitar liberação.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarPagamento" onclick="confirmarPagamento()">
                    <iconify-icon icon="iconamoon:check-circle-duotone"></iconify-icon>
                    Já fiz o pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.__BASE_URL__ = '<?= base_url() ?>';
window.__CONFIG_PLANOS__ = <?= json_encode($planos ?? []) ?>;
window.__CONFIG_EMPRESA__ = <?= json_encode($empresa ?? []) ?>;
window.__FATURA_ATUAL__ = null;
</script>
<script src="<?= asset_url('assets/admin/js/pages/empresa.js?v=logo1') ?>"></script>
<script src="<?= asset_url('assets/admin/js/pages/configuracoes.js') ?>"></script>

<script>
let modalPagamento = null;
let faturaAtualId = null;

document.addEventListener('DOMContentLoaded', function() {
    modalPagamento = new bootstrap.Modal(document.getElementById('modalPagamentoPix'));
});

function abrirModalPagamento(faturaId) {
    faturaAtualId = faturaId;
    
    fetch(`${window.__BASE_URL__}/admin/configuracoes/obter-pix-fatura/${faturaId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('codigo-pix').value = data.data.codigo_pix || '';
                document.getElementById('valor-pix').textContent = `R$ ${parseFloat(data.data.valor || 59.90).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                modalPagamento.show();
            } else {
                alert(data.message || 'Erro ao carregar dados da fatura');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao carregar dados da fatura');
        });
}

function copiarCodigoPix() {
    const input = document.getElementById('codigo-pix');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = input.nextElementSibling;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon> Copiado!';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}

function confirmarPagamento() {
    if (!faturaAtualId) return;
    
    const btn = document.getElementById('btnConfirmarPagamento');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Confirmando...';
    
    const formData = new FormData();
    formData.append('id', faturaAtualId);
    
    fetch(`${window.__BASE_URL__}/admin/configuracoes/confirmar-pagamento`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            modalPagamento.hide();
            alert('Pagamento confirmado com sucesso!');
            location.reload();
        } else {
            alert(data.message || 'Erro ao confirmar pagamento');
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="iconamoon:check-circle-duotone"></iconify-icon> Já fiz o pagamento';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao confirmar pagamento');
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="iconamoon:check-circle-duotone"></iconify-icon> Já fiz o pagamento';
    });
}
</script>

<?= $this->endSection() ?>
