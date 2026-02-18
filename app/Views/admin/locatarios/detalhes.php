<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php
$cliente = $cliente ?? null;
$totalAberto = $total_aberto ?? 0;
$totalLocacoes = $total_locacoes ?? 0;
$temVeiculoLocado = $tem_veiculo_locado ?? false;
$locacaoAtiva = $locacao_ativa ?? null;
$contasReceber = $contas_receber ?? [];
$historicoLocacoes = $historico_locacoes ?? [];

$formatarCpfCnpj = function ($v) {
    if (empty($v)) return '-';
    $n = preg_replace('/\D/', '', $v);
    if (strlen($n) === 11) return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $n);
    if (strlen($n) === 14) return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $n);
    return $v;
};
$statusLocacao = [
    'reservada' => 'Reservada',
    'ativa' => 'Ativa',
    'atrasada' => 'Em atraso',
    'finalizada' => 'Finalizada',
    'cancelada' => 'Cancelada',
    'inadimplente' => 'Inadimplente',
];
?>
<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Ficha do cliente') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/locatarios') ?>">Locatários</a></li>
                <li class="breadcrumb-item active"><?= esc($cliente['cli_nome'] ?? 'Detalhes') ?></li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1"><?= esc($cliente['cli_nome'] ?? '') ?></h5>
                        <p class="text-muted mb-0">CPF/CNPJ: <?= $formatarCpfCnpj($cliente['cli_cpf_cnpj'] ?? '') ?></p>
                        <?php if (!empty($cliente['cli_email'])): ?>
                            <p class="text-muted small mb-0">E-mail: <a href="mailto:<?= esc($cliente['cli_email']) ?>"><?= esc($cliente['cli_email']) ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['cli_telefone']) || !empty($cliente['cli_whatsapp'])): ?>
                            <p class="text-muted small mb-0">Contato: <?= esc($cliente['cli_telefone'] ?? $cliente['cli_whatsapp'] ?? '-') ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= base_url('admin/locatarios') ?>" class="btn btn-outline-secondary">
                        <iconify-icon icon="iconamoon:arrow-left-duotone" class="fs-18"></iconify-icon>
                        Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cards de resumo -->
<div class="row">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                        </span>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Total em aberto</h6>
                        <h4 class="mb-0">R$ <?= number_format($totalAberto, 2, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-20">
                            <iconify-icon icon="solar:car-bold-duotone"></iconify-icon>
                        </span>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Total de veículos alugados</h6>
                        <h4 class="mb-0"><?= (int) $totalLocacoes ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <span class="avatar-title <?= $temVeiculoLocado ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> rounded-circle fs-20">
                            <iconify-icon icon="solar:key-bold-duotone"></iconify-icon>
                        </span>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Veículo locado</h6>
                        <h4 class="mb-0"><?= $temVeiculoLocado ? 'Sim' : 'Não' ?></h4>
                        <?php if ($temVeiculoLocado && !empty($locacaoAtiva)): ?>
                            <p class="text-muted small mb-0"><?= esc($locacaoAtiva['vei_placa'] ?? '') ?> – <?= esc($locacaoAtiva['vei_marca'] ?? '') ?> <?= esc($locacaoAtiva['vei_modelo'] ?? '') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contas a receber -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Contas a receber</h5>
            </div>
            <div class="card-body">
                <?php if (empty($contasReceber)): ?>
                    <p class="text-muted mb-0">Nenhuma conta a receber em aberto.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th>Veículo</th>
                                    <th>Competência</th>
                                    <th>Vencimento</th>
                                    <th class="text-end">Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contasReceber as $c): ?>
                                    <tr>
                                        <td><?= esc($c['descricao'] ?? '-') ?></td>
                                        <td><?= esc($c['veiculo'] ?? '-') ?></td>
                                        <td><?= esc($c['competencia'] ?? '-') ?></td>
                                        <td><?= esc($c['vencimento'] ?? '-') ?></td>
                                        <td class="text-end fw-semibold">R$ <?= number_format($c['valor'] ?? 0, 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge <?= ($c['status'] ?? '') === 'Em atraso' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' ?>">
                                                <?= esc($c['status'] ?? 'Pendente') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Histórico de veículos locados -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Histórico de veículos locados</h5>
            </div>
            <div class="card-body">
                <?php if (empty($historicoLocacoes)): ?>
                    <p class="text-muted mb-0">Nenhuma locação registrada.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Período</th>
                                    <th>Veículo</th>
                                    <th>Placa</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historicoLocacoes as $loc): ?>
                                    <?php
                                    $inicio = !empty($loc['loc_data_inicio']) ? date('d/m/Y', strtotime($loc['loc_data_inicio'])) : '-';
                                    $fim = !empty($loc['loc_data_fim_real']) ? date('d/m/Y', strtotime($loc['loc_data_fim_real'])) : (!empty($loc['loc_data_fim_prevista']) ? date('d/m/Y', strtotime($loc['loc_data_fim_prevista'])) . ' (prev.)' : '-');
                                    ?>
                                    <tr>
                                        <td><?= $inicio ?> a <?= $fim ?></td>
                                        <td><?= esc($loc['vei_marca'] ?? '') ?> <?= esc($loc['vei_modelo'] ?? '-') ?></td>
                                        <td><?= esc($loc['vei_placa'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <?= esc($statusLocacao[$loc['loc_status'] ?? ''] ?? $loc['loc_status'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="text-end">R$ <?= number_format((float) ($loc['loc_valor_total'] ?? $loc['loc_valor_locacao'] ?? 0), 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Infrações (placeholder) -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Infrações</h5>
            </div>
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">Nenhuma infração registrada.</p>
                <p class="text-muted small mt-1">O módulo de infrações estará disponível em breve.</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
