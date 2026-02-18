<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Dashboard') ?></h4>
                <p class="text-muted mb-0">Bem vindo(a) de volta, joao 👋</p>
            </div>
            <div id="dashboard-toggle-valores" class="cursor-pointer" role="button" title="Ocultar valores" aria-label="Ocultar valores">
                <iconify-icon id="dashboard-toggle-icon" icon="iconamoon:eye-duotone" class="text-primary fs-20"></iconify-icon>
            </div>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<style>
#dashboard-toggle-valores { cursor: pointer; user-select: none; }
</style>

<!-- Faturamento do mês atual -->
                    <div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                        <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="text-white mb-3">Faturamento do mês atual</h5>
                        <h2 class="text-white mb-3"><span class="dashboard-valor" data-original="R$ <?= number_format($faturamento_mes_atual ?? 0, 2, ',', '.') ?>">R$ <?= number_format($faturamento_mes_atual ?? 0, 2, ',', '.') ?></span></h2>
                        <div class="d-flex gap-4">
                            <div>
                                <p class="text-white-50 mb-0 small">Mês anterior</p>
                                <p class="text-white mb-0"><span class="dashboard-valor" data-original="R$ <?= number_format($faturamento_mes_anterior ?? 0, 2, ',', '.') ?>">R$ <?= number_format($faturamento_mes_anterior ?? 0, 2, ',', '.') ?></span></p>
                            </div>
                            <div>
                                <p class="text-white-50 mb-0 small">% Crescimento</p>
                                <p class="text-white mb-0"><span class="dashboard-valor" data-original="<?= number_format($crescimento_percentual ?? 0, 0) ?>%"><?= number_format($crescimento_percentual ?? 0, 0) ?>%</span></p>
                                                    </div>
                                                </div>
                                                </div>
                    <div class="col-md-4 text-end">
                        <iconify-icon icon="iconamoon:arrow-repeat-2-duotone" class="text-white" style="font-size: 64px; opacity: 0.3;"></iconify-icon>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                    </div>
                                                </div>
<!-- end row -->

<!-- Fluxo de Caixa e Métricas -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">Fluxo de Caixa</h5>
                <p class="text-muted mb-3 small">Últimos 12 meses</p>
                <div id="fluxo-caixa-chart" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
        <!-- Gráficos: Tipos de Movimentação e Veículos por Status -->
        <div class="row mt-3">
            <!-- Gráfico: Tipos de Movimentação -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Tipos de movimentação</h5>
                        <p class="text-muted mb-2 small">+ Utilizadas</p>
                        <div id="tipos-movimentacao-chart" class="apex-charts" style="min-height: 220px;"></div>
                    </div>
                </div>
            </div>
            <!-- end col -->
            <!-- Gráfico: Veículos por Status -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Veículos por Status</h5>
                        <p class="text-muted mb-2 small">Distribuição atual</p>
                        <div id="veiculos-status-chart" class="apex-charts" style="min-height: 220px;"></div>
                    </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end col -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="text-white mb-0">Caixa Total</h5>
                    <iconify-icon icon="iconamoon:wallet-duotone" class="text-white" style="font-size: 32px;"></iconify-icon>
                                                </div>
                <h2 class="text-white mb-0"><span class="dashboard-valor" data-original="R$ <?= number_format($caixa_total ?? 0, 2, ',', '.') ?>">R$ <?= number_format($caixa_total ?? 0, 2, ',', '.') ?></span></h2>
                                        </div>
                                    </div>
                                    <!-- end card -->
        <div class="card mt-3">
                                        <div class="card-body">
                <h5 class="card-title mb-1">Lucro do mês atual</h5>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0"><span class="dashboard-valor" data-original="R$ <?= number_format($lucro_mes_atual ?? 0, 2, ',', '.') ?>">R$ <?= number_format($lucro_mes_atual ?? 0, 2, ',', '.') ?></span></h3>
                    <iconify-icon icon="iconamoon:trend-up-bold" class="text-success" style="font-size: 32px;"></iconify-icon>
                                                    </div>
                                                </div>
                                    </div>
                                    <!-- end card -->
        <!-- Cards Informativos -->
        <div class="row g-3 mt-0">
            <!-- Cobranças em atraso -->
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <div class="avatar-md bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:warning-duotone" class="text-warning" style="font-size: 24px;"></iconify-icon>
                            </div>
                        </div>
                        <h4 class="mb-1"><span class="dashboard-valor" data-original="<?= esc($cobrancas_atraso ?? 0) ?>"><?= esc($cobrancas_atraso ?? 0) ?></span></h4>
                        <p class="text-muted mb-0 small">Cobranças em atraso</p>
                                                </div>
                                            </div>
                                        </div>
            <!-- end col -->
            <!-- Precisa de manutenção -->
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <div class="avatar-md bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:warning-duotone" class="text-danger" style="font-size: 24px;"></iconify-icon>
                            </div>
                        </div>
                        <h4 class="mb-1"><span class="dashboard-valor" data-original="<?= esc($precisa_manutencao ?? 0) ?>"><?= esc($precisa_manutencao ?? 0) ?></span></h4>
                        <p class="text-muted mb-0 small">Precisa de manutenção</p>
                    </div>
                                        </div>
                                    </div>
            <!-- end col -->
            <!-- Veículos disponíveis -->
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <div class="avatar-md bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:box-duotone" class="text-primary" style="font-size: 24px;"></iconify-icon>
                                                        </div>
                                                    </div>
                        <h4 class="mb-1"><span class="dashboard-valor" data-original="<?= esc($veiculos_disponiveis ?? 0) ?> de <?= esc($total_veiculos ?? 0) ?>"><?= esc($veiculos_disponiveis ?? 0) ?> de <?= esc($total_veiculos ?? 0) ?></span></h4>
                        <p class="text-muted mb-0 small">Veículos disponíveis</p>
                                                    </div>
                                                </div>
                                            </div>
            <!-- end col -->
            <!-- CNH's vencidas -->
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <div class="avatar-md bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:minus-circle-duotone" class="text-danger" style="font-size: 24px;"></iconify-icon>
                            </div>
                        </div>
                        <h4 class="mb-1"><span class="dashboard-valor" data-original="<?= esc($cnhs_vencidas ?? 0) ?>"><?= esc($cnhs_vencidas ?? 0) ?></span></h4>
                        <p class="text-muted mb-0 small">CNH's vencidas</p>
                    </div>
                                        </div>
                                    </div>
            <!-- end col -->
                                </div>
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<!-- Toggle valores dos cards -->
<script>
(function() {
    var PLACEHOLDER = '••••••••';
    var btn = document.getElementById('dashboard-toggle-valores');
    var icon = document.getElementById('dashboard-toggle-icon');
    if (!btn || !icon) return;

    function isOculto() {
        var first = document.querySelector('.dashboard-valor');
        return first && first.textContent === PLACEHOLDER;
    }

    function ocultar() {
        document.querySelectorAll('.dashboard-valor').forEach(function(el) {
            var orig = el.getAttribute('data-original');
            if (orig) { el.textContent = PLACEHOLDER; }
        });
        icon.setAttribute('icon', 'iconamoon:eye-off-duotone');
        btn.setAttribute('title', 'Exibir valores');
        btn.setAttribute('aria-label', 'Exibir valores');
    }

    function exibir() {
        document.querySelectorAll('.dashboard-valor').forEach(function(el) {
            var orig = el.getAttribute('data-original');
            if (orig) { el.textContent = orig; }
        });
        icon.setAttribute('icon', 'iconamoon:eye-duotone');
        btn.setAttribute('title', 'Ocultar valores');
        btn.setAttribute('aria-label', 'Ocultar valores');
    }

    btn.addEventListener('click', function() {
        if (isOculto()) { exibir(); } else { ocultar(); }
    });
})();
</script>

<!-- Dashboard JS -->
<script>
// Aguardar carregamento completo da página e ApexCharts
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se ApexCharts está disponível
    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts não está disponível. Os gráficos não serão renderizados.');
        return;
    }

    // Dados do fluxo de caixa (últimos 12 meses)
    const fluxoCaixaData = <?= json_encode(array_column($fluxo_caixa ?? [], 'valor')) ?>;
    const fluxoCaixaLabels = <?= json_encode(array_column($fluxo_caixa ?? [], 'mes')) ?>;

    // Gráfico de Fluxo de Caixa (Linha)
    if (document.querySelector("#fluxo-caixa-chart")) {
        const fluxoCaixaOptions = {
        series: [{
            name: 'Fluxo de Caixa',
            data: fluxoCaixaData || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2304]
        }],
        chart: {
            height: 300,
            type: 'line',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#0d6efd']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.3,
                gradientToColors: ['#0d6efd'],
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0.1,
                stops: [0, 90]
            }
        },
        markers: {
            size: 4,
            strokeWidth: 2,
            hover: {
                size: 6
            }
        },
        xaxis: {
            categories: fluxoCaixaLabels || ['Jan/25', 'Fev/25', 'Mar/25', 'Abr/25', 'Mai/25', 'Jun/25', 'Jul/25', 'Ago/25', 'Set/25', 'Out/25', 'Nov/25', 'Dez/25', 'Jan/26'],
            axisTicks: {
                show: false
            },
            axisBorder: {
                show: false
            }
        },
        yaxis: {
            min: 0,
            axisBorder: {
                show: false
            },
            labels: {
                formatter: function(val) {
                    return 'R$ ' + val.toFixed(0);
                }
            }
        },
        grid: {
            show: true,
            strokeDashArray: 3,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 10
            }
        },
        colors: ['#0d6efd'],
        tooltip: {
            y: {
                formatter: function(val) {
                    return 'R$ ' + val.toFixed(2).replace('.', ',');
                }
            }
        }
    };

        const fluxoCaixaChart = new ApexCharts(document.querySelector("#fluxo-caixa-chart"), fluxoCaixaOptions);
        fluxoCaixaChart.render();
    }

    // Dados dos tipos de movimentação
    const tiposMovimentacaoData = <?= json_encode(array_column($tipos_movimentacao ?? [], 'valor')) ?>;
    const tiposMovimentacaoLabels = <?= json_encode(array_column($tipos_movimentacao ?? [], 'tipo')) ?>;

    // Gráfico de Tipos de Movimentação (Pizza)
    if (document.querySelector("#tipos-movimentacao-chart")) {
        const tiposMovimentacaoOptions = {
        series: tiposMovimentacaoData || [50, 50],
        chart: {
            height: 200,
            type: 'pie',
            toolbar: {
                show: false
            }
        },
        labels: tiposMovimentacaoLabels || ['Entrada', 'Saída'],
        colors: ['#20c997', '#dc3545'],
        legend: {
            show: true,
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 10
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return val.toFixed(0) + '%';
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toFixed(0) + '%';
                }
            }
        }
    };

        const tiposMovimentacaoChart = new ApexCharts(document.querySelector("#tipos-movimentacao-chart"), tiposMovimentacaoOptions);
        tiposMovimentacaoChart.render();
    }

    // Dados dos veículos por status
    const veiculosStatusQuantidades = <?= json_encode(array_column($veiculos_status ?? [], 'quantidade')) ?>;
    const veiculosStatusLabels = <?= json_encode(array_column($veiculos_status ?? [], 'status')) ?>;
    
    // Calcular percentuais baseado nas quantidades
    const totalVeiculosStatus = veiculosStatusQuantidades.reduce((a, b) => a + b, 0) || 1;
    const veiculosStatusData = veiculosStatusQuantidades.map(qtd => {
        return totalVeiculosStatus > 0 ? Math.round((qtd / totalVeiculosStatus) * 100) : 0;
    });

    // Gráfico de Veículos por Status (Pizza)
    if (document.querySelector("#veiculos-status-chart")) {
        const veiculosStatusOptions = {
            series: veiculosStatusData || [100, 0, 0],
            chart: {
                height: 200,
                type: 'pie',
                toolbar: {
                    show: false
                }
            },
            labels: veiculosStatusLabels || ['Ocupados', 'Livres', 'Manutenção'],
            colors: ['#ffc107', '#22c55e', '#dc3545'],
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                offsetY: 10
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(0) + '%';
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toFixed(0) + '%';
                    }
                }
            }
        };

        const veiculosStatusChart = new ApexCharts(document.querySelector("#veiculos-status-chart"), veiculosStatusOptions);
        veiculosStatusChart.render();
    }
});
</script>

<?= $this->endSection() ?>
