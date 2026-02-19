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
            String(c.ver_id != null ? c.ver_id : c.id),
            c.tipo || 'contrato'
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
                const verId = row.cells[8].data;
                const tipo = row.cells[9].data;
                const url = tipo === 'contrato' ? getBaseUrl() + 'admin/contratos/ver/' + verId : getBaseUrl() + 'admin/locacoes?editar=' + verId;
                return gridjs.html("<a href='" + url + "' class='text-reset text-decoration-underline btn-detalhes-contrato'>Detalhes</a>");
            }},
            { name: '', width: '0px', hidden: true, formatter: () => '' },
            { name: '', width: '0px', hidden: true, formatter: () => '' }
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
            const href = btn.getAttribute('href');
            if (href && href !== '#') window.location.href = href;
        }
    });

    // ----- Modal Novo Contrato + Select2 -----
    const modalEl = document.getElementById('modalCriarContrato');
    const btnNovo = document.getElementById('btn-novo-contrato');
    const btnCriar = document.getElementById('btn-modal-criar-contrato');
    const selectLocacao = document.getElementById('modal-locacao');
    const selectModelo = document.getElementById('modal-modelo');

    if (modalEl && btnNovo && typeof $ !== 'undefined') {
        btnNovo.addEventListener('click', function () {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            if (selectLocacao && !selectLocacao._select2) {
                $(selectLocacao).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Pesquise pela locação...',
                    allowClear: true,
                    ajax: {
                        url: getBaseUrl() + 'admin/contratos/locacoes-disponiveis',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { q: params.term }; },
                        processResults: function (data) {
                            return { results: data.results || [] };
                        }
                    },
                    minimumInputLength: 0
                });
                selectLocacao._select2 = true;
            }
        });
    }

    if (btnCriar && selectLocacao && selectModelo && typeof $ !== 'undefined') {
        btnCriar.addEventListener('click', function () {
            const locacaoId = $(selectLocacao).val();
            const modeloId = selectModelo.value;
            if (!locacaoId || !modeloId) {
                alert('Selecione a locação e o modelo do contrato.');
                return;
            }
            btnCriar.disabled = true;
            btnCriar.textContent = 'Criando...';
            fetch(getBaseUrl() + 'admin/contratos/criar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'locacao_id=' + encodeURIComponent(locacaoId) + '&modelo_id=' + encodeURIComponent(modeloId)
            })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.success && json.redirect) {
                        window.location.href = json.redirect;
                        return;
                    }
                    alert(json.message || 'Erro ao criar contrato.');
                })
                .catch(function () { alert('Erro ao criar contrato.'); })
                .finally(function () {
                    btnCriar.disabled = false;
                    btnCriar.textContent = 'Criar';
                });
        });
    }
})();
