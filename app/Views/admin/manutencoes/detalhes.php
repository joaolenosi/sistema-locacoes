<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Detalhes da Manutenção') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/manutencao') ?>">Manutenções</a></li>
                <li class="breadcrumb-item active">Detalhes #<?= (int) ($manutencao['id'] ?? 0) ?></li>
            </ol>
        </div>
    </div>
</div>

<?php
$man = $manutencao ?? [];
$id = (int) ($man['id'] ?? 0);
$dataFormatada = !empty($man['man_data']) ? date('d/m/Y', strtotime($man['man_data'])) : '—';
$tipoLabel = ($man['man_tipo'] ?? '') === 'preventiva' ? 'Preventiva' : 'Corretiva';
$statusLabel = ['aberta' => 'Aberta', 'finalizada' => 'Finalizada', 'rascunho' => 'Rascunho', 'cancelada' => 'Cancelada'][$man['man_status'] ?? ''] ?? $man['man_status'] ?? '—';
$fotos = $man['fotos'] ?? [];
$itens = $man['itens'] ?? [];
$manTotal = (float) ($man['man_total'] ?? 0);
$podeEditarItens = in_array($man['man_status'] ?? '', ['aberta', 'rascunho'], true);
$produtos = $produtos ?? [];
$servicos = $servicos ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="card-title mb-0">Manutenção #<?= $id ?></h5>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/manutencao') ?>" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="iconamoon:arrow-left-duotone"></iconify-icon> Voltar
                        </a>
                        <a href="<?= base_url('admin/manutencao/' . $id . '/pdf') ?>" class="btn btn-primary btn-sm" target="_blank" id="btn-pdf-manutencao">
                            <iconify-icon icon="iconamoon:printer-duotone"></iconify-icon> Imprimir / PDF
                        </a>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Veículo</label>
                        <p class="mb-0 fw-semibold"><?= esc($man['vei_placa'] ?? '—') ?> – <?= esc($man['vei_modelo'] ?? '—') ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Data</label>
                        <p class="mb-0"><?= $dataFormatada ?></p>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small">Tipo / Status</label>
                        <p class="mb-0"><?= $tipoLabel ?> / <?= $statusLabel ?></p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Observações</label>
                        <p class="mb-0"><?= !empty($man['man_obs']) ? esc($man['man_obs']) : '—' ?></p>
                    </div>
                </div>

                <!-- Produtos e Serviços -->
                <div class="border rounded p-3 mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">Produtos e Serviços</label>
                        <?php if ($podeEditarItens): ?>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-adicionar-item-manutencao" data-bs-toggle="modal" data-bs-target="#modalAdicionarItem">
                            <iconify-icon icon="iconamoon:plus-duotone"></iconify-icon> Adicionar item
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Valor unit.</th>
                                    <th class="text-end">Total</th>
                                    <?php if ($podeEditarItens): ?>
                                    <th class="text-center" style="width:80px;">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="tbody-itens-manutencao">
                                <?php foreach ($itens as $item): ?>
                                <tr data-item-id="<?= (int) $item['id'] ?>">
                                    <td><?= esc($item['mai_descricao'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= ($item['mai_tipo_item'] ?? '') === 'produto' ? 'info' : 'success' ?>-subtle text-<?= ($item['mai_tipo_item'] ?? '') === 'produto' ? 'info' : 'success' ?>">
                                            <?= ($item['mai_tipo_item'] ?? '') === 'produto' ? 'Produto' : 'Serviço' ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= (int) ($item['mai_quantidade'] ?? 1) ?></td>
                                    <td class="text-end">R$ <?= number_format((float) ($item['mai_valor_unitario'] ?? 0), 2, ',', '.') ?></td>
                                    <td class="text-end fw-semibold">R$ <?= number_format((float) ($item['mai_valor_total'] ?? 0), 2, ',', '.') ?></td>
                                    <?php if ($podeEditarItens): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remover-item-manutencao" data-item-id="<?= (int) $item['id'] ?>" title="Remover">×</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($itens)): ?>
                                <tr id="tr-itens-vazios">
                                    <td colspan="<?= $podeEditarItens ? '6' : '5' ?>" class="text-muted text-center py-3">Nenhum item adicionado. Clique em "Adicionar item" para incluir produtos ou serviços.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <p class="mb-0 fw-bold fs-5">Total: <span id="man-total-display">R$ <?= number_format($manTotal, 2, ',', '.') ?></span></p>
                    </div>
                </div>

                <!-- Upload de fotos -->
                <div class="border rounded p-3 mb-4 bg-light">
                    <label class="form-label fw-semibold mb-2">Anexar fotos</label>
                    <input type="file" class="form-control" id="input-fotos-manutencao" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                    <small class="text-muted">Formatos: JPG, PNG, WebP. Máximo 5 MB por foto.</small>
                    <div id="upload-fotos-alert" class="alert mt-2 mb-0 d-none" role="alert"></div>
                </div>

                <!-- Galeria de fotos (lightbox) -->
                <div class="mb-0">
                    <label class="form-label fw-semibold mb-2">Fotos anexadas <span id="fotos-count" class="badge bg-primary"><?= count($fotos) ?></span></label>
                    <div id="galeria-fotos-manutencao" class="row g-2">
                        <?php foreach ($fotos as $f): ?>
                        <div class="col-6 col-md-4 col-lg-3" data-foto-id="<?= (int) $f['id'] ?>">
                            <a href="<?= base_url('admin/manutencao-inteligente/foto/' . (int) $f['id']) ?>" class="glightbox d-block rounded overflow-hidden border" data-gallery="manutencao-fotos">
                                <img src="<?= base_url('admin/manutencao-inteligente/foto/' . (int) $f['id']) ?>" alt="Foto" class="img-fluid w-100" style="object-fit:cover; height:120px;">
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger mt-1 w-100 btn-remover-foto" data-foto-id="<?= (int) $f['id'] ?>">Remover</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p id="galeria-vazia" class="text-muted small <?= empty($fotos) ? '' : 'd-none' ?>">Nenhuma foto anexada. Use o campo acima para enviar fotos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Adicionar Item (Produto/Serviço) -->
<div class="modal fade" id="modalAdicionarItem" tabindex="-1" aria-labelledby="modalAdicionarItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarItemLabel">Adicionar produto ou serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarItem">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="item-tipo" name="tipo_item" required>
                            <option value="produto">Produto</option>
                            <option value="servico">Serviço</option>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo-produto">
                        <label class="form-label" for="item-produto-id">Produto</label>
                        <select class="form-select" id="item-produto-id" name="produto_id">
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" data-preco="<?= (float) ($p['pro_preco_venda'] ?? $p['pro_preco_custo'] ?? 0) ?>"><?= esc($p['pro_nome'] ?? '') ?> - R$ <?= number_format((float) ($p['pro_preco_venda'] ?? $p['pro_preco_custo'] ?? 0), 2, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="grupo-servico">
                        <label class="form-label" for="item-servico-id">Serviço</label>
                        <select class="form-select" id="item-servico-id" name="servico_id">
                            <option value="">Selecione um serviço</option>
                            <?php foreach ($servicos as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" data-preco="<?= (float) ($s['ser_preco_padrao'] ?? 0) ?>"><?= esc($s['ser_nome'] ?? '') ?> - R$ <?= number_format((float) ($s['ser_preco_padrao'] ?? 0), 2, ',', '.') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="item-quantidade">Quantidade</label>
                        <input type="number" class="form-control" id="item-quantidade" name="quantidade" value="1" min="1" required>
                    </div>
                    <div class="mb-0">
                        <p class="mb-0 text-muted small">Valor unitário: <span id="item-valor-unit-display">R$ 0,00</span> | Subtotal: <span id="item-subtotal-display" class="fw-semibold">R$ 0,00</span></p>
                    </div>
                </form>
                <div id="form-item-alert" class="alert alert-danger mt-2 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-salvar-item-manutencao">
                    <span class="btn-text">Adicionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.__MANUTENCAO_ID__ = <?= $id ?>;
window.__BASE_URL__ = '<?= base_url() ?>';
window.__PRODUTOS__ = <?= json_encode(array_map(function ($p) {
    return ['id' => (int) $p['id'], 'nome' => $p['pro_nome'] ?? '', 'preco' => (float) ($p['pro_preco_venda'] ?? $p['pro_preco_custo'] ?? 0)];
}, $produtos)) ?>;
window.__SERVICOS__ = <?= json_encode(array_map(function ($s) {
    return ['id' => (int) $s['id'], 'nome' => $s['ser_nome'] ?? '', 'preco' => (float) ($s['ser_preco_padrao'] ?? 0)];
}, $servicos)) ?>;
window.__ITENS__ = <?= json_encode($itens) ?>;
window.__MAN_TOTAL__ = <?= $manTotal ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script src="<?= asset_url('assets/admin/js/pages/manutencao-detalhes.js') ?>"></script>
<?= $this->endSection() ?>
