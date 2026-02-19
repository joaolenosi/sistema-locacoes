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

<style>
    /* Cards resumo (padrão financeiro) */
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
</style>

<!-- Cards de resumo -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: #2d7ef7;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Total em aberto</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        R$ <?= number_format($totalAberto, 2, ',', '.') ?>
                    </div>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:wallet-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: #22c55e;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Total de veículos alugados</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <?= (int) $totalLocacoes ?>
                    </div>
                    <?php if ($temVeiculoLocado && !empty($locacaoAtiva)): ?>
                        <div class="fw-medium" style="opacity: .85; font-size: 0.875rem; margin-top: 0.25rem;">
                            <?= esc($locacaoAtiva['vei_placa'] ?? '') ?> – <?= esc($locacaoAtiva['vei_marca'] ?? '') ?> <?= esc($locacaoAtiva['vei_modelo'] ?? '') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:car-duotone" class="fs-22 text-white"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card fin-kpi-card" style="background: <?= $temVeiculoLocado ? '#22c55e' : '#6c757d' ?>;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-medium" style="opacity: .95;">Veículo locado</div>
                    <div class="fw-semibold" style="font-size: 2rem; line-height: 1.1;">
                        <?= $temVeiculoLocado ? 'Sim' : 'Não' ?>
                    </div>
                </div>
                <div class="fin-kpi-icon" aria-hidden="true">
                    <iconify-icon icon="iconamoon:key-duotone" class="fs-22 text-white"></iconify-icon>
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
