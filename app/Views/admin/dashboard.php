<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Dashboard') ?></h4>
                <p class="text-muted mb-0">Bem vindo(a) de volta, <?= esc(session()->get('empresa_nome') ?: 'usuário') ?> 👋</p>
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
.dashboard-help-btn { cursor: help; opacity: 0.9; font-size: 1rem; }
.dashboard-help-btn:hover { opacity: 1; }
.card-title.d-flex .dashboard-help-btn.text-white { color: rgba(255,255,255,0.9) !important; }
</style>

<!-- Faturamento do mês atual -->
                    <div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                        <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="text-white mb-1 d-flex align-items-center gap-1">
                            Receitas do mês (faturamento)
                            <span class="dashboard-help-btn text-white" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="O que é faturamento?" data-bs-content="<strong>Faturamento</strong> = o que você <em>recebeu</em> dos clientes no mês (receitas com pagamento recebido). Não é o que você pagou — são as entradas. Ex.: se um cliente te pagou R$ 200, esse valor entra aqui. Mês anterior e % Crescimento usam o mesmo critério." aria-label="Ajuda sobre faturamento">
                                <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                            </span>
                        </h5>
                        <p class="text-white-50 small mb-2">Receitas recebidas no mês</p>
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
                <h5 class="card-title mb-1 d-flex align-items-center gap-1">
                    Fluxo de Caixa
                    <span class="dashboard-help-btn text-muted" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Cada ponto = <strong>Receitas</strong> (o que você recebeu) <em>menos</em> <strong>Despesas</strong> (o que você pagou) naquele mês. A linha mostra a evolução do saldo mês a mês." aria-label="Ajuda sobre fluxo de caixa">
                        <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                    </span>
                </h5>
                <p class="text-muted mb-3 small">Receitas − Despesas por mês (últimos 12 meses)</p>
                <div id="fluxo-caixa-chart" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
        <!-- Gráficos: Tipos de Movimentação e Veículos por Status -->
        <div class="row mt-3">
            <!-- Gráfico: Tipos de Movimentação -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 d-flex align-items-center gap-1">
                        Tipos de movimentação
                        <span class="dashboard-help-btn text-body-secondary" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Proporção entre <strong>Receitas</strong> (entradas — o que você recebeu) e <strong>Despesas</strong> (saídas — o que você pagou) em todo o histórico." aria-label="Ajuda">
                            <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                        </span>
                    </h5>
                        <p class="text-muted mb-2 small">Receitas vs Despesas (todo o histórico)</p>
                        <div id="tipos-movimentacao-chart" class="apex-charts" style="min-height: 220px;"></div>
                    </div>
                </div>
            </div>
            <!-- end col -->
            <!-- Gráfico: Veículos por Status -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1 d-flex align-items-center gap-1">
                        Veículos por Status
                        <span class="dashboard-help-btn text-body-secondary" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Distribuição dos veículos por situação: <strong>Ocupados</strong> (locados), <strong>Livres</strong> (disponíveis), <strong>Manutenção</strong> ou <strong>Inativo</strong>. Os percentuais são calculados sobre o total de veículos." aria-label="Ajuda">
                            <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                        </span>
                    </h5>
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
                    <h5 class="text-white mb-0 d-flex align-items-center gap-1">
                        Caixa Total
                        <span class="dashboard-help-btn text-white" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="O que é?" data-bs-content="Saldo acumulado: <strong>Receitas</strong> (tudo que você recebeu) − <strong>Despesas</strong> (tudo que você pagou), em todo o tempo. Por isso pode ter valor (ex.: R$ 850) mesmo com Receitas e Lucro do mês em zero — o mês atual só entra quando houver receitas/despesas pagas neste mês." aria-label="Ajuda sobre caixa total">
                            <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                        </span>
                    </h5>
                    <iconify-icon icon="iconamoon:wallet-duotone" class="text-white" style="font-size: 32px;"></iconify-icon>
                </div>
                <p class="text-white-50 small mb-1">Receitas − Despesas (acumulado)</p>
                <h2 class="text-white mb-0"><span class="dashboard-valor" data-original="R$ <?= number_format($caixa_total ?? 0, 2, ',', '.') ?>">R$ <?= number_format($caixa_total ?? 0, 2, ',', '.') ?></span></h2>
                                        </div>
                                    </div>
                                    <!-- end card -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title mb-3 d-flex align-items-center gap-1">
                    Receitas e despesas do mês
                    <span class="dashboard-help-btn text-body-secondary" data-bs-toggle="popover" data-bs-placement="left" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="<strong>Receitas</strong> = o que você recebeu no mês. <strong>Despesas</strong> = o que você pagou no mês. <strong>Lucro</strong> = Receitas − Despesas. Ex.: recebeu R$ 200 e não pagou nada → Lucro R$ 200." aria-label="Ajuda sobre receitas e despesas">
                        <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                    </span>
                </h5>
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <span class="text-muted">Receitas (entradas)</span>
                    <strong class="text-success"><span class="dashboard-valor" data-original="R$ <?= number_format($receitas_mes_atual ?? 0, 2, ',', '.') ?>">R$ <?= number_format($receitas_mes_atual ?? 0, 2, ',', '.') ?></span></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <span class="text-muted">Despesas (saídas)</span>
                    <strong class="text-danger"><span class="dashboard-valor" data-original="R$ <?= number_format($despesas_mes_atual ?? 0, 2, ',', '.') ?>">R$ <?= number_format($despesas_mes_atual ?? 0, 2, ',', '.') ?></span></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1">
                    <span class="fw-semibold">Lucro (receitas − despesas)</span>
                    <h4 class="mb-0 text-primary"><span class="dashboard-valor" data-original="R$ <?= number_format($lucro_mes_atual ?? 0, 2, ',', '.') ?>">R$ <?= number_format($lucro_mes_atual ?? 0, 2, ',', '.') ?></span></h4>
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
                        <p class="text-muted mb-0 small d-flex align-items-center justify-content-center gap-1">
                            Cobranças em atraso
                            <span class="dashboard-help-btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Quantidade de <strong>receitas pendentes</strong> com data de vencimento já passada. São valores que a empresa ainda deve receber e estão em atraso." aria-label="Ajuda">
                                <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                            </span>
                        </p>
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
                        <p class="text-muted mb-0 small d-flex align-items-center justify-content-center gap-1">
                            Precisa de manutenção
                            <span class="dashboard-help-btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Quantidade de <strong>ordens de manutenção</strong> com status <em>aberta</em>. São veículos ou itens com manutenção em andamento ou aguardando execução." aria-label="Ajuda">
                                <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                            </span>
                        </p>
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
                        <p class="text-muted mb-0 small d-flex align-items-center justify-content-center gap-1">
                            Veículos disponíveis
                            <span class="dashboard-help-btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="<strong>Disponíveis</strong> = veículos com status disponível ou livre. O número à direita é o <strong>total</strong> de veículos cadastrados na empresa." aria-label="Ajuda">
                                <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                            </span>
                        </p>
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
                        <p class="text-muted mb-0 small d-flex align-items-center justify-content-center gap-1">
                            CNH's vencidas
                            <span class="dashboard-help-btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="focus" data-bs-html="true" tabindex="0" role="button" title="Como funciona" data-bs-content="Quantidade de <strong>clientes/locatários</strong> cuja data de validade da CNH já passou. Útil para lembrar de solicitar documentação atualizada." aria-label="Ajuda">
                                <iconify-icon icon="mdi:help-circle-outline"></iconify-icon>
                            </span>
                        </p>
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
            name: 'Receitas − Despesas',
            data: fluxoCaixaData || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
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
        labels: tiposMovimentacaoLabels || ['Receitas', 'Despesas'],
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
            labels: veiculosStatusLabels || ['Ocupados', 'Livres', 'Manutenção', 'Inativo'],
            colors: ['#ffc107', '#22c55e', '#dc3545', '#6c757d'],
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

// Inicializar popovers de ajuda do dashboard
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
    new bootstrap.Popover(el, { sanitize: false });
});
</script>

<?= $this->endSection() ?>
