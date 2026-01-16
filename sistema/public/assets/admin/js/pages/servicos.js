// Listagem de Serviços com GridJS
if (document.getElementById("table-servicos")) {
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
            "Categoria",
            {
                name: 'Preço Padrão',
                width: '130px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="fw-semibold">' + cell + '</span>');
                })
            },
            {
                name: 'Status',
                width: '120px',
                formatter: (function (cell) {
                    const badgeClass = cell === 'Ativo' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
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
            ["1", "Lavagem Completa", "Lavagem", "R$ 80,00", "Ativo"],
            ["2", "Lavagem Simples", "Lavagem", "R$ 35,00", "Ativo"],
            ["3", "Enceramento", "Estética", "R$ 150,00", "Ativo"],
            ["4", "Revisão Preventiva", "Manutenção", "R$ 250,00", "Ativo"],
            ["5", "Troca de Óleo", "Manutenção", "R$ 120,00", "Ativo"],
            ["6", "Alinhamento e Balanceamento", "Manutenção", "R$ 90,00", "Ativo"],
            ["7", "Higienização Interna", "Estética", "R$ 200,00", "Ativo"],
            ["8", "Polimento Completo", "Estética", "R$ 300,00", "Ativo"],
            ["9", "Limpeza de Motor", "Manutenção", "R$ 100,00", "Ativo"],
            ["10", "Lavagem Premium", "Lavagem", "R$ 120,00", "Ativo"]
        ]
    }).render(document.getElementById("table-servicos"));
}
