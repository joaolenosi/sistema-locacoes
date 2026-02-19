<?php helper('asset'); helper('contrato'); ?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php
$contrato = $contrato ?? [];
$locacao = $locacao ?? [];
$cliente = $cliente ?? [];
$veiculo = $veiculo ?? [];
$conteudoSubstituido = $conteudo_substituido ?? '';
$numero = $contrato['con_numero'] ?? '';
$status = ($contrato['con_status'] ?? '') === 'gerado' ? 'Gerado' : 'Rascunho';
$contratoId = (int)($contrato['id'] ?? 0);
$baseUrl = base_url();
?>
<!-- ========== Page Title / Header ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <a href="<?= $baseUrl ?>admin/contratos" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1 mb-1">
                    <iconify-icon icon="iconamoon:arrow-left-duotone" class="fs-18"></iconify-icon>
                    Voltar para contratos
                </a>
                <h4 class="mb-0 fw-semibold"><?= esc($numero) ?></h4>
                <span class="badge bg-secondary-subtle text-secondary mt-1"><?= esc($status) ?></span>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $baseUrl ?>admin/contratos/pdf/<?= $contratoId ?>" class="btn btn-outline-danger btn-download-pdf" target="_blank" download>
                    <iconify-icon icon="iconamoon:file-pdf-duotone" class="fs-18"></iconify-icon>
                    Download PDF
                </a>
                <button type="button" class="btn btn-primary" id="btn-salvar-enviar">
                    <iconify-icon icon="iconamoon:send-duotone" class="fs-18"></iconify-icon>
                    Salvar e enviar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-dados" role="tab">Dados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-visualizacao" role="tab">Visualização</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Dados -->
                    <div class="tab-pane active" id="tab-dados" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="border rounded p-3">
                                    <h6 class="d-flex align-items-center gap-2 mb-3">
                                        <iconify-icon icon="solar:document-text-bold-duotone" class="fs-20 text-primary"></iconify-icon>
                                        Locação
                                    </h6>
                                    <div class="row g-2 small">
                                        <div class="col-md-4"><span class="text-muted">Número:</span> #<?= str_pad((string)($locacao['id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Data da retirada:</span> <?= esc(formatarDataBR($locacao['loc_data_inicio'] ?? '')) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Início do pagamento:</span> <?= esc(formatarDataBR($locacao['loc_data_inicio_pagamento'] ?? $locacao['loc_data_inicio'] ?? '')) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Data prevista de devolução:</span> <?= esc(formatarDataBR($locacao['loc_data_fim_prevista'] ?? '')) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Pagamento:</span> <?= esc(traduzirRecorrencia($locacao['loc_recorrencia_pagamento'] ?? '') ?: '-') ?></div>
                                        <div class="col-md-4"><span class="text-muted">Valor:</span> <?= esc(formatarMoedaBR($locacao['loc_valor_total'] ?? $locacao['loc_valor_locacao'] ?? 0)) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Taxa de juros R$:</span> <?= esc(formatarMoedaBR($locacao['loc_taxa_juros'] ?? 0)) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Taxa de multa R$:</span> <?= esc(formatarMoedaBR($locacao['loc_taxa_multa'] ?? 0)) ?></div>
                                        <div class="col-md-4"><span class="text-muted">Caução:</span> <?= esc(formatarMoedaBR($locacao['loc_valor_caucao'] ?? 0)) ?></div>
                                        <div class="col-md-4"><span class="text-muted">KM na retirada:</span> <?= esc($locacao['loc_km_retirada'] ?? $veiculo['vei_km_atual'] ?? '-') ?> KM</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="d-flex align-items-center gap-2 mb-3">
                                        <iconify-icon icon="solar:user-bold-duotone" class="fs-20 text-primary"></iconify-icon>
                                        Locatário
                                    </h6>
                                    <p class="mb-1 fw-semibold"><?= esc($cliente['cli_nome'] ?? '-') ?></p>
                                    <?php if (!empty($cliente['cli_whatsapp'])): ?>
                                        <p class="mb-1 small">
                                            <iconify-icon icon="logos:whatsapp-icon" class="align-middle"></iconify-icon>
                                            <?= esc($cliente['cli_whatsapp']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="mb-0 small text-muted">CPF: <?= esc(formatarCPFCNPJ($cliente['cli_cpf_cnpj'] ?? '')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="d-flex align-items-center gap-2 mb-3">
                                        <iconify-icon icon="solar:car-bold-duotone" class="fs-20 text-primary"></iconify-icon>
                                        Veículo
                                    </h6>
                                    <p class="mb-1 fw-semibold"><?= esc($veiculo['vei_placa'] ?? '-') ?></p>
                                    <p class="mb-1 small"><?= esc($veiculo['vei_marca'] ?? '') ?> <?= esc($veiculo['vei_modelo'] ?? '') ?></p>
                                    <p class="mb-1 small text-muted">Ano: <?= esc($veiculo['vei_ano'] ?? '-') ?></p>
                                    <p class="mb-0 small text-muted">KM atual: <?= esc($veiculo['vei_km_atual'] ?? $locacao['loc_km_retirada'] ?? '-') ?> KM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Visualização -->
                    <div class="tab-pane" id="tab-visualizacao" role="tabpanel">
                        <div class="mb-3">
                            <h6 class="mb-2">Prévia do contrato</h6>
                            <p class="text-muted small">
                                Veja como o seu cliente vai visualizar o contrato e prepare-se para enviá-lo.
                                Para baixar em PDF, use o botão <strong>Download PDF</strong> no topo da página.
                            </p>
                            <a href="<?= $baseUrl ?>admin/contratos/pdf/<?= $contratoId ?>" class="btn btn-primary btn-sm me-2 btn-pdf-preview" target="_blank">
                                <iconify-icon icon="iconamoon:file-pdf-duotone"></iconify-icon>
                                Gerar / Ver PDF
                            </a>
                        </div>
                        <div class="border rounded p-4 bg-light bg-opacity-50" style="min-height: 320px;">
                            <div class="contrato-conteudo-previa" style="white-space: pre-wrap; font-family: inherit;"><?= nl2br(esc($conteudoSubstituido)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Enviar contrato -->
<div class="modal fade" id="modalEnviarContrato" tabindex="-1" aria-labelledby="modalEnviarContratoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEnviarContratoLabel">Enviar contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Envie o link do contrato para o cliente pelo meio que preferir.</p>
                <div class="mb-3">
                    <label class="form-label small text-muted">Link do contrato</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="input-link-contrato" readonly value="">
                        <button type="button" class="btn btn-outline-primary" id="btn-copiar-link">Copiar</button>
                    </div>
                </div>
                <p class="small text-danger mb-0">
                    Ao copiar o link, o contrato será considerado como <strong>Gerado</strong>. A partir deste momento, não será mais possível editar o contrato!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const baseUrl = '<?= $baseUrl ?>'.replace(/\/$/, '') + '/';
    const contratoId = <?= (int)$contratoId ?>;

    document.getElementById('btn-salvar-enviar').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Gerando contrato, aguarde...';
        fetch(baseUrl + 'admin/contratos/marcar-gerado/' + contratoId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    document.getElementById('input-link-contrato').value = data.link || '';
                    var modal = new bootstrap.Modal(document.getElementById('modalEnviarContrato'));
                    modal.show();
                    document.getElementById('btn-copiar-link').onclick = function () {
                        var input = document.getElementById('input-link-contrato');
                        input.select();
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(input.value);
                        } else {
                            document.execCommand('copy');
                        }
                    };
                    // Opcional: recarregar para atualizar badge para "Gerado"
                    document.getElementById('modalEnviarContrato').addEventListener('hidden.bs.modal', function () {
                        window.location.reload();
                    }, { once: true });
                }
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="iconamoon:send-duotone" class="fs-18"></iconify-icon> Salvar e enviar';
            });
    });

    document.querySelectorAll('.btn-download-pdf, .btn-pdf-preview').forEach(function (el) {
        el.addEventListener('click', function () {
            var label = el.textContent.trim();
            if (el.classList.contains('btn-download-pdf')) {
                el.innerHTML = 'Gerando PDF...';
                setTimeout(function () { el.innerHTML = '<iconify-icon icon="iconamoon:file-pdf-duotone" class="fs-18"></iconify-icon> Download PDF'; }, 3000);
            }
        });
    });
})();
</script>
<?= $this->endSection() ?>
