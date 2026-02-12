// Listagem de Locatários com GridJS
if (document.getElementById("table-locatarios")) {
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
                name: 'CPF/CNPJ',
                width: '150px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
            {
                name: 'Telefone',
                width: '140px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
            {
                name: 'Email',
                width: '200px',
                formatter: (function (cell) {
                    return gridjs.html('<a href="mailto:' + cell + '" class="text-reset">' + cell + '</a>');
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
            ["1", "João Silva", "123.456.789-00", "(11) 98765-4321", "joao.silva@email.com", "Ativo"],
            ["2", "Maria Santos", "987.654.321-00", "(21) 99876-5432", "maria.santos@email.com", "Ativo"],
            ["3", "Pedro Oliveira", "456.789.123-00", "(31) 98765-1234", "pedro.oliveira@email.com", "Ativo"],
            ["4", "Ana Costa", "789.123.456-00", "(41) 99876-3210", "ana.costa@email.com", "Ativo"],
            ["5", "Carlos Pereira", "321.654.987-00", "(51) 98765-9876", "carlos.pereira@email.com", "Ativo"],
            ["6", "Transportes ABC Ltda", "12.345.678/0001-90", "(11) 3456-7890", "contato@transportesabc.com.br", "Ativo"],
            ["7", "Fernanda Lima", "654.321.987-00", "(21) 98765-4321", "fernanda.lima@email.com", "Ativo"],
            ["8", "Roberto Alves", "147.258.369-00", "(31) 99876-5432", "roberto.alves@email.com", "Inativo"],
            ["9", "Logística XYZ EIRELI", "98.765.432/0001-10", "(41) 3456-7890", "financeiro@logisticaxyz.com.br", "Ativo"],
            ["10", "Juliana Ferreira", "258.369.147-00", "(51) 98765-1234", "juliana.ferreira@email.com", "Ativo"]
        ]
    }).render(document.getElementById("table-locatarios"));
}
