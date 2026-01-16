// Listagem de Produtos com GridJS
if (document.getElementById("table-produtos")) {
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
                name: 'SKU',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
                })
            },
            "Nome",
            "Categoria",
            {
                name: 'Preço Venda',
                width: '130px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="fw-semibold">' + cell + '</span>');
                })
            },
            {
                name: 'Estoque',
                width: '120px',
                formatter: (function (cell) {
                    const [estoque, minimo] = cell.split('|');
                    const estoqueNum = parseInt(estoque);
                    const minimoNum = parseInt(minimo);
                    let badgeClass = 'bg-success-subtle text-success';
                    if (estoqueNum < minimoNum) {
                        badgeClass = 'bg-danger-subtle text-danger';
                    } else if (estoqueNum <= minimoNum + 5) {
                        badgeClass = 'bg-warning-subtle text-warning';
                    }
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + estoque + '</span>');
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
            ["1", "FIL-001", "Filtro de Óleo Motor", "Peças", "R$ 45,90", "25|10", "Ativo"],
            ["2", "PAS-002", "Pastilhas de Freio Dianteiras", "Peças", "R$ 89,50", "15|8", "Ativo"],
            ["3", "PNE-003", "Pneu Aro 15 185/65", "Peças", "R$ 320,00", "8|12", "Ativo"],
            ["4", "BAT-004", "Bateria 60Ah", "Peças", "R$ 280,00", "12|10", "Ativo"],
            ["5", "CAP-005", "Capa para Bancos Universal", "Acessórios", "R$ 85,00", "30|15", "Ativo"],
            ["6", "TAP-006", "Tapete Automotivo Completo", "Acessórios", "R$ 120,00", "18|10", "Ativo"],
            ["7", "SUP-007", "Suporte para Celular", "Acessórios", "R$ 35,90", "45|20", "Ativo"],
            ["8", "SHA-008", "Shampoo Automotivo 5L", "Limpeza", "R$ 42,50", "22|15", "Ativo"],
            ["9", "CER-009", "Cera Líquida Automotiva", "Limpeza", "R$ 38,00", "5|10", "Ativo"],
            ["10", "DES-010", "Desengraxante 1L", "Limpeza", "R$ 28,90", "35|20", "Ativo"]
        ]
    }).render(document.getElementById("table-produtos"));
}
