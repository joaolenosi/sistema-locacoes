// Listagem Financeira com GridJS
if (document.getElementById("table-financeiro")) {
    // Tradução para Português do Brasil
    const ptBR = {
        search: {
            placeholder: 'Digite uma palavra-chave...'
        },
        pagination: {
            previous: 'Anterior',
            next: 'Próximo',
            showing: 'Mostrando',
            to: 'a',
            of: 'de',
            results: 'resultados'
        }
    };

    new gridjs.Grid({
        columns: [
            {
                name: 'ID',
                width: '80px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="fw-semibold">' + cell + '</span>');
                })
            },
            {
                name: 'Data',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
            {
                name: 'Tipo',
                width: '120px',
                formatter: (function (cell) {
                    const badgeClass = cell === 'receita' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                    const label = cell === 'receita' ? 'Receita' : 'Despesa';
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
                })
            },
            "Categoria",
            {
                name: 'Descrição',
                width: '250px'
            },
            {
                name: 'Valor',
                width: '130px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="fw-semibold">' + cell + '</span>');
                })
            },
            {
                name: 'Status',
                width: '130px',
                formatter: (function (cell) {
                    let badgeClass = 'bg-success-subtle text-success';
                    if (cell === 'Pendente') {
                        badgeClass = 'bg-warning-subtle text-warning';
                    } else if (cell === 'Cancelado') {
                        badgeClass = 'bg-danger-subtle text-danger';
                    }
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + cell + '</span>');
                })
            },
            {
                name: 'Ações',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html("<a href='#' class='text-reset text-decoration-underline'>Detalhes</a>");
                })
            }
        ],
        pagination: {
            limit: 5
        },
        sort: true,
        search: true,
        language: ptBR,
        data: [
            ["1", "15/01/2026", "receita", "Locação de veículos", "Locação veículo ABC-1234 - João Silva", "R$ 1.200,00", "Pago"],
            ["2", "10/01/2026", "receita", "Locação de veículos", "Locação veículo XYZ-5678 - Maria Santos", "R$ 1.000,00", "Pago"],
            ["3", "20/01/2026", "receita", "Locação de veículos", "Locação veículo DEF-9012 - Pedro Oliveira", "R$ 1.800,00", "Pendente"],
            ["4", "08/01/2026", "despesa", "Combustível", "Abastecimento veículo ABC-1234", "R$ 250,00", "Pago"],
            ["5", "12/01/2026", "receita", "Caução", "Caução locação veículo JKL-7890", "R$ 500,00", "Pago"],
            ["6", "18/01/2026", "despesa", "Manutenção de veículos", "Revisão veículo GHI-3456", "R$ 450,00", "Pago"],
            ["7", "22/01/2026", "receita", "Locação de veículos", "Locação veículo PQR-1357 - Fernanda Lima", "R$ 2.200,00", "Pendente"],
            ["8", "05/01/2026", "despesa", "Peças e acessórios", "Troca de pneus veículo MNO-2468", "R$ 1.200,00", "Pago"],
            ["9", "25/01/2026", "receita", "Taxa administrativa", "Taxa administrativa contrato CT-2026-009", "R$ 150,00", "Pago"],
            ["10", "14/01/2026", "despesa", "Seguro", "Seguro veículo STU-8024", "R$ 380,00", "Pago"],
            ["11", "16/01/2026", "receita", "Serviços adicionais", "Lavagem completa veículo YZA-2468", "R$ 80,00", "Pago"],
            ["12", "19/01/2026", "despesa", "Energia elétrica", "Conta de energia - Janeiro/2026", "R$ 320,00", "Pendente"]
        ]
    }).render(document.getElementById("table-financeiro"));
}
