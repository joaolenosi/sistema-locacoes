// Listagem de Contratos com GridJS (dados dinâmicos via API)
(function () {
    const tableEl = document.getElementById("table-contratos");
    if (!tableEl) return;

    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + (window.location.pathname.indexOf('/sistema') !== -1 ? '/sistema/' : '/'));
        return base.endsWith('/') ? base : base + '/';
    };

    const ptBR = {
        search: { placeholder: 'Digite uma palavra-chave...' },
        pagination: { previous: 'Anterior', next: 'Próximo', showing: 'Mostrando', to: 'a', of: 'de', results: 'resultados' }
    };

    const toRows = (contratos) => {
        if (!Array.isArray(contratos)) return [];
        return contratos.map((c) => [
            String(c.id),
            c.numero || '-',
            c.locatario || '-',
            c.veiculo || '-',
            c.inicio || '-',
            c.termino || '-',
            c.valor_total || '-',
            c.status || '-',
            String(c.id)
        ]);
    };

    let gridInstance = null;

    const renderGrid = (data) => {
        const rows = toRows(data || []);

        const columns = [
            { name: 'ID', width: '80px', formatter: (cell) => gridjs.html('<span class="fw-semibold">' + cell + '</span>') },
            { name: 'Número', width: '130px', formatter: (cell) => gridjs.html('<span class="badge bg-primary-subtle text-primary">' + (cell || '-') + '</span>') },
            'Locatário',
            { name: 'Veículo', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { name: 'Início', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { name: 'Término', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { name: 'Valor Total', width: '130px', formatter: (cell) => gridjs.html('<span class="fw-semibold">' + (cell || '-') + '</span>') },
            {
                name: 'Status',
                width: '130px',
                formatter: (cell) => {
                    let badgeClass = 'bg-success-subtle text-success';
                    if (cell === 'Encerrado' || cell === 'Cancelado') badgeClass = 'bg-secondary-subtle text-secondary';
                    if (cell === 'Cancelado' || cell === 'Inadimplente') badgeClass = 'bg-danger-subtle text-danger';
                    if (cell === 'Atrasado') badgeClass = 'bg-warning-subtle text-warning';
                    return gridjs.html('<span class="badge ' + badgeClass + '">' + (cell || '-') + '</span>');
                }
            },
            { name: 'Ações', width: '120px', formatter: (cell, row) => {
                const id = row.cells[0].data;
                return gridjs.html("<a href='#' class='text-reset text-decoration-underline btn-detalhes-contrato' data-id='" + id + "'>Detalhes</a>");
            }}
        ];

        if (!gridInstance) {
            gridInstance = new gridjs.Grid({
                columns,
                pagination: { limit: 5 },
                sort: true,
                search: true,
                language: ptBR,
                data: rows
            });
            gridInstance.render(tableEl);
        } else {
            gridInstance.updateConfig({ data: rows }).forceRender();
        }
    };

    const loadContratos = () => {
        const url = getBaseUrl() + 'admin/contratos/listar';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((res) => res.json())
            .then((json) => {
                const data = json && json.success && Array.isArray(json.data) ? json.data : [];
                renderGrid(data);
            })
            .catch((err) => {
                console.error('Erro ao carregar contratos:', err);
                renderGrid([]);
            });
    };

    renderGrid([]);
    loadContratos();

    document.addEventListener('click', (e) => {
        const btn = e.target && e.target.closest && e.target.closest('.btn-detalhes-contrato');
        if (btn) {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            if (id) {
                window.location.href = getBaseUrl() + 'admin/locacoes?editar=' + id;
            }
        }
    });
})();
