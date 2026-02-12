// Listagem de Categorias Financeiras com GridJS
if (document.getElementById("table-categorias-financeiras")) {
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
            "Nome",
            {
                name: 'Tipo',
                width: '130px',
                formatter: (function (cell) {
                    const badgeClass = cell === 'receita' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                    const label = cell === 'receita' ? 'Receita' : 'Despesa';
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
                })
            },
            {
                name: 'Padrão',
                width: '130px',
                formatter: (function (cell) {
                    const badgeClass = cell === '1' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary';
                    const label = cell === '1' ? 'Sim' : 'Não';
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
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
            ["1", "Locação de veículos", "receita", "1"],
            ["2", "Caução", "receita", "1"],
            ["3", "Multa por atraso", "receita", "1"],
            ["4", "Taxa administrativa", "receita", "1"],
            ["5", "Serviços adicionais", "receita", "1"],
            ["6", "Venda de serviços", "receita", "1"],
            ["7", "Combustível", "despesa", "1"],
            ["8", "Manutenção de veículos", "despesa", "1"],
            ["9", "Peças e acessórios", "despesa", "1"],
            ["10", "Seguro", "despesa", "1"],
            ["11", "IPVA", "despesa", "1"],
            ["12", "Licenciamento", "despesa", "1"],
            ["13", "Multas de trânsito", "despesa", "1"],
            ["14", "Internet", "despesa", "1"],
            ["15", "Aluguel", "despesa", "1"],
            ["16", "Energia elétrica", "despesa", "1"],
            ["17", "Água", "despesa", "1"],
            ["18", "Folha de pagamento", "despesa", "1"]
        ]
    }).render(document.getElementById("table-categorias-financeiras"));
}
