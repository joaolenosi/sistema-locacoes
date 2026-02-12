// Listagem de Manutenção Inteligente com GridJS
if (document.getElementById("table-manutencao-inteligente")) {
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
            "Modelo",
            {
                name: 'Tipo',
                width: '130px',
                formatter: (function (cell) {
                    let badgeClass = 'bg-info-subtle text-info';
                    if (cell === 'Corretiva') {
                        badgeClass = 'bg-danger-subtle text-danger';
                    } else if (cell === 'Preditiva') {
                        badgeClass = 'bg-warning-subtle text-warning';
                    }
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + cell + '</span>');
                })
            },
            {
                name: 'Serviço',
                width: '200px'
            },
            {
                name: 'Data Prevista',
                width: '130px'
            },
            {
                name: 'Status',
                width: '140px',
                formatter: (function (cell) {
                    let badgeClass = 'bg-success-subtle text-success';
                    if (cell === 'Agendada') {
                        badgeClass = 'bg-info-subtle text-info';
                    } else if (cell === 'Em Andamento') {
                        badgeClass = 'bg-warning-subtle text-warning';
                    } else if (cell === 'Atrasada') {
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
            ["1", "ABC-1234", "Onix", "Preventiva", "Troca de óleo e filtros", "15/02/2026", "Agendada"],
            ["2", "XYZ-5678", "HB20", "Corretiva", "Reparo no sistema de freios", "20/02/2026", "Em Andamento"],
            ["3", "DEF-9012", "Corolla", "Preventiva", "Revisão completa", "25/02/2026", "Agendada"],
            ["4", "GHI-3456", "Civic", "Preditiva", "Análise de componentes", "10/02/2026", "Atrasada"],
            ["5", "JKL-7890", "Fiesta", "Preventiva", "Alinhamento e balanceamento", "18/02/2026", "Agendada"],
            ["6", "MNO-2468", "Gol", "Corretiva", "Substituição de bateria", "12/02/2026", "Concluída"],
            ["7", "PQR-1357", "Compass", "Preventiva", "Troca de pastilhas de freio", "22/02/2026", "Agendada"],
            ["8", "STU-8024", "T-Cross", "Preditiva", "Diagnóstico eletrônico", "28/02/2026", "Agendada"],
            ["9", "VWX-4680", "Renegade", "Preventiva", "Revisão de suspensão", "16/02/2026", "Em Andamento"],
            ["10", "YZA-2468", "Creta", "Corretiva", "Reparo no ar condicionado", "14/02/2026", "Concluída"]
        ]
    }).render(document.getElementById("table-manutencao-inteligente"));
}
