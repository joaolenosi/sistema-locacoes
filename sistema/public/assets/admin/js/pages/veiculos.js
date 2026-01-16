// Listagem de Veículos com GridJS
if (document.getElementById("table-veiculos")) {
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
                name: 'Placa',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
                })
            },
            "Modelo",
            "Marca",
            {
                name: 'Ano',
                width: '100px'
            },
            "Cor",
            {
                name: 'Status',
                width: '140px',
                formatter: (function (cell) {
                    let badgeClass = 'bg-success-subtle text-success';
                    if (cell === 'Em uso') {
                        badgeClass = 'bg-warning-subtle text-warning';
                    } else if (cell === 'Manutenção') {
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
            ["1", "ABC-1234", "Onix", "Chevrolet", "2022", "Branco", "Disponível"],
            ["2", "XYZ-5678", "HB20", "Hyundai", "2021", "Prata", "Em uso"],
            ["3", "DEF-9012", "Corolla", "Toyota", "2023", "Preto", "Disponível"],
            ["4", "GHI-3456", "Civic", "Honda", "2020", "Vermelho", "Manutenção"],
            ["5", "JKL-7890", "Fiesta", "Ford", "2019", "Azul", "Disponível"],
            ["6", "MNO-2468", "Gol", "Volkswagen", "2021", "Branco", "Em uso"],
            ["7", "PQR-1357", "Compass", "Jeep", "2022", "Prata", "Disponível"],
            ["8", "STU-8024", "T-Cross", "Volkswagen", "2023", "Preto", "Disponível"],
            ["9", "VWX-4680", "Renegade", "Jeep", "2020", "Laranja", "Manutenção"],
            ["10", "YZA-2468", "Creta", "Hyundai", "2021", "Branco", "Em uso"]
        ]
    }).render(document.getElementById("table-veiculos"));
}
