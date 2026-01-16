// Listagem de Contratos com GridJS
if (document.getElementById("table-contratos")) {
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
                name: 'Número',
                width: '130px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
                })
            },
            "Locatário",
            {
                name: 'Veículo',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
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
                name: 'Valor Total',
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
                    if (cell === 'Encerrado') {
                        badgeClass = 'bg-secondary-subtle text-secondary';
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
            ["1", "CT-2026-001", "João Silva", "ABC-1234", "15/01/2026", "15/02/2026", "R$ 1.200,00", "Ativo"],
            ["2", "CT-2026-002", "Maria Santos", "XYZ-5678", "10/01/2026", "10/02/2026", "R$ 1.000,00", "Encerrado"],
            ["3", "CT-2026-003", "Pedro Oliveira", "DEF-9012", "20/01/2026", "20/02/2026", "R$ 1.800,00", "Ativo"],
            ["4", "CT-2026-004", "Ana Costa", "GHI-3456", "05/01/2026", "05/02/2026", "R$ 1.500,00", "Encerrado"],
            ["5", "CT-2026-005", "Carlos Pereira", "JKL-7890", "18/01/2026", "18/02/2026", "R$ 900,00", "Ativo"],
            ["6", "CT-2026-006", "Transportes ABC Ltda", "MNO-2468", "12/01/2026", "12/02/2026", "R$ 1.100,00", "Encerrado"],
            ["7", "CT-2026-007", "Fernanda Lima", "PQR-1357", "22/01/2026", "22/02/2026", "R$ 2.200,00", "Ativo"],
            ["8", "CT-2026-008", "Logística XYZ EIRELI", "STU-8024", "08/01/2026", "08/02/2026", "R$ 1.900,00", "Encerrado"],
            ["9", "CT-2026-009", "Juliana Ferreira", "VWX-4680", "25/01/2026", "25/02/2026", "R$ 2.000,00", "Ativo"],
            ["10", "CT-2026-010", "João Silva", "YZA-2468", "03/01/2026", "03/02/2026", "R$ 1.300,00", "Encerrado"]
        ]
    }).render(document.getElementById("table-contratos"));
}
