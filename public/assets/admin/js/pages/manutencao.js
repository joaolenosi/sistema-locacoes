// Listagem de Manutenções com GridJS
if (document.getElementById("table-manutencoes")) {
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
                width: '140px'
            },
            {
                name: 'Placa',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
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
                    return gridjs.html('<span class="text-muted">' + (cell === '—' ? '<span class="text-muted">—</span>' : cell) + '</span>');
                })
            },
            {
                name: 'Descrição',
                width: '250px'
            },
            {
                name: 'Status',
                width: '130px',
                formatter: (function (cell) {
                    const badgeClass = cell === 'Ativa' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success';
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
            ["1", "Onix", "ABC-1234", "10/01/2026", "12/01/2026", "Revisão preventiva e troca de óleo", "Finalizada"],
            ["2", "HB20", "XYZ-5678", "18/01/2026", "—", "Troca de pastilhas de freio", "Ativa"],
            ["3", "Corolla", "DEF-9012", "22/01/2026", "25/01/2026", "Troca de pneus e alinhamento", "Finalizada"],
            ["4", "Civic", "GHI-3456", "28/01/2026", "—", "Manutenção elétrica e bateria", "Ativa"],
            ["5", "Fiesta", "JKL-7890", "05/01/2026", "07/01/2026", "Revisão completa", "Finalizada"],
            ["6", "Gol", "MNO-2468", "15/01/2026", "17/01/2026", "Troca de correia dentada", "Finalizada"],
            ["7", "Compass", "PQR-1357", "30/01/2026", "—", "Revisão de suspensão", "Ativa"],
            ["8", "T-Cross", "STU-8024", "08/01/2026", "10/01/2026", "Troca de filtros", "Finalizada"],
            ["9", "Renegade", "VWX-4680", "25/01/2026", "—", "Manutenção de ar condicionado", "Ativa"],
            ["10", "Creta", "YZA-2468", "12/01/2026", "14/01/2026", "Revisão e balanceamento", "Finalizada"]
        ]
    }).render(document.getElementById("table-manutencoes"));
}
