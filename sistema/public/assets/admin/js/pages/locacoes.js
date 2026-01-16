// Listagem de Locações com GridJS
if (document.getElementById("table-locacoes")) {
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
                name: 'Veículo',
                width: '150px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
                })
            },
            {
                name: 'Modelo',
                width: '140px'
            },
            "Locatário",
            {
                name: 'Início',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
            {
                name: 'Término',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
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
                    if (cell === 'Finalizada') {
                        badgeClass = 'bg-secondary-subtle text-secondary';
                    } else if (cell === 'Cancelada') {
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
            ["1", "ABC-1234", "Onix", "João Silva", "15/01/2026", "25/01/2026", "R$ 1.200,00", "Ativa"],
            ["2", "XYZ-5678", "HB20", "Maria Santos", "10/01/2026", "20/01/2026", "R$ 1.000,00", "Finalizada"],
            ["3", "DEF-9012", "Corolla", "Pedro Oliveira", "20/01/2026", "30/01/2026", "R$ 1.800,00", "Ativa"],
            ["4", "GHI-3456", "Civic", "Ana Costa", "05/01/2026", "15/01/2026", "R$ 1.500,00", "Finalizada"],
            ["5", "JKL-7890", "Fiesta", "Carlos Pereira", "18/01/2026", "28/01/2026", "R$ 900,00", "Ativa"],
            ["6", "MNO-2468", "Gol", "Transportes ABC Ltda", "12/01/2026", "22/01/2026", "R$ 1.100,00", "Finalizada"],
            ["7", "PQR-1357", "Compass", "Fernanda Lima", "22/01/2026", "02/02/2026", "R$ 2.200,00", "Ativa"],
            ["8", "STU-8024", "T-Cross", "Logística XYZ EIRELI", "08/01/2026", "18/01/2026", "R$ 1.900,00", "Finalizada"],
            ["9", "VWX-4680", "Renegade", "Juliana Ferreira", "25/01/2026", "05/02/2026", "R$ 2.000,00", "Ativa"],
            ["10", "YZA-2468", "Creta", "João Silva", "03/01/2026", "13/01/2026", "R$ 1.300,00", "Finalizada"]
        ]
    }).render(document.getElementById("table-locacoes"));
}
