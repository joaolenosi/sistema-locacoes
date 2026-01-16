// Dashboard - Gráficos de Locação de Veículos e Financeiro

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
            height: 250,
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
