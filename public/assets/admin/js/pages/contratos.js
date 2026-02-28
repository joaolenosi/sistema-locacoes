// Listagem de Contratos com GridJS (dados dinâmicos via API)
(function () {
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
            (c.locatario || '-').toString().toUpperCase(),
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

    const renderGrid = (data, tableEl) => {
        if (!tableEl) return;
        const rows = toRows(data || []);

        const columns = [
            { id: 'id', name: 'ID', width: '80px', formatter: (cell) => gridjs.html('<span class="fw-semibold">' + cell + '</span>') },
            { id: 'numero', name: 'Número', width: '130px', formatter: (cell) => gridjs.html('<span class="badge bg-primary-subtle text-primary">' + (cell || '-') + '</span>') },
            { id: 'locatario', name: 'Locatário', formatter: (cell) => gridjs.html(String(cell || '-')) },
            { id: 'veiculo', name: 'Veículo', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { id: 'inicio', name: 'Início', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { id: 'termino', name: 'Término', width: '120px', formatter: (cell) => gridjs.html('<span class="text-muted">' + (cell || '-') + '</span>') },
            { id: 'valor_total', name: 'Valor Total', width: '130px', formatter: (cell) => gridjs.html('<span class="fw-semibold">' + (cell || '-') + '</span>') },
            {
                id: 'status',
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
            { id: 'acoes', name: 'Ações', width: '160px', formatter: (cell, row) => {
                const verId = row.cells[8].data;
                const tipo = row.cells[9].data;
                const numero = (row.cells[1]?.data || '').toString();
                const locatario = (row.cells[2]?.data || '').toString();
                const base = getBaseUrl();
                const url = tipo === 'contrato' ? base + 'admin/contratos/ver/' + verId : base + 'admin/locacoes?editar=' + verId;
                const iconInfo = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
                const iconTrash = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
                const btnExcluir = tipo === 'contrato'
                    ? '<button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center btn-delete-contrato" data-id="' + verId + '" data-numero="' + (numero || '').replace(/"/g, '&quot;') + '" data-locatario="' + (locatario || '').replace(/"/g, '&quot;') + '" title="Excluir">' + iconTrash + '</button>'
                    : '';
                return gridjs.html(
                    '<div class="d-flex gap-2 align-items-center">' +
                    '<a href="' + url + '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center btn-detalhes-contrato" title="Detalhes">' + iconInfo + '</a>' +
                    btnExcluir +
                    '</div>'
                );
            }},
            { id: 'ver_id', name: 'VerId', width: '0px', hidden: true, formatter: () => '' },
            { id: 'tipo', name: 'Tipo', width: '0px', hidden: true, formatter: () => '' }
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

    const loadContratos = (tableEl) => {
        const url = getBaseUrl() + 'admin/contratos/listar';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((res) => res.json())
            .then((json) => {
                const data = json && json.success && Array.isArray(json.data) ? json.data : [];
                renderGrid(data, tableEl);
            })
            .catch((err) => {
                console.error('Erro ao carregar contratos:', err);
                renderGrid([], tableEl);
            });
    };

    function init(tableEl) {
        if (!tableEl) return;
        renderGrid([], tableEl);
        loadContratos(tableEl);

        document.addEventListener('click', function (e) {
            const btnDetalhes = e.target && e.target.closest && e.target.closest('.btn-detalhes-contrato');
            if (btnDetalhes) {
                e.preventDefault();
                const href = btnDetalhes.getAttribute('href');
                if (href && href !== '#') window.location.href = href;
                return;
            }

            const btnDelete = e.target && e.target.closest && e.target.closest('.btn-delete-contrato');
            if (btnDelete) {
                const id = btnDelete.getAttribute('data-id');
                const numero = btnDelete.getAttribute('data-numero') || '';
                const locatario = btnDelete.getAttribute('data-locatario') || '';
                if (!id) return;

                const msg = numero || locatario
                    ? 'Você está prestes a excluir o contrato' + (numero ? ' "' + numero + '"' : '') + (locatario ? ' do locatário ' + locatario : '') + '.'
                    : 'Você está prestes a excluir este contrato.';

                const executeDelete = function () {
                    btnDelete.disabled = true;
                    btnDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    fetch(getBaseUrl() + 'admin/contratos/excluir/' + id, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
                    })
                        .then(function (res) {
                            return res.json().catch(function () { return null; }).then(function (json) {
                                return { ok: res.ok, json: json };
                            });
                        })
                        .then(function (result) {
                            if (result.ok && result.json && result.json.success) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Contrato excluído',
                                        text: result.json.message || 'O contrato foi excluído com sucesso.',
                                        confirmButtonText: 'OK'
                                    }).then(function () { loadContratos(tableEl); });
                                } else {
                                    alert(result.json.message || 'Contrato excluído com sucesso.');
                                    loadContratos(tableEl);
                                }
                            } else {
                                var errMsg = (result.json && result.json.message) ? result.json.message : 'Não foi possível excluir o contrato.';
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Não foi possível excluir',
                                        text: errMsg,
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert(errMsg);
                                }
                            }
                        })
                        .catch(function () {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: 'Erro ao excluir contrato. Tente novamente.',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Erro ao excluir contrato. Tente novamente.');
                            }
                        })
                        .finally(function () {
                            btnDelete.disabled = false;
                            btnDelete.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
                        });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Excluir contrato?',
                        text: msg,
                        showCancelButton: true,
                        confirmButtonText: 'Sim, excluir',
                        cancelButtonText: 'Cancelar'
                    }).then(function (result) {
                        if (result && result.isConfirmed) executeDelete();
                    });
                } else if (confirm('Tem certeza que deseja excluir este contrato?')) {
                    executeDelete();
                }
            }
        });

        var modalEl = document.getElementById('modalCriarContrato');
        var btnNovo = document.getElementById('btn-novo-contrato');
        var btnCriar = document.getElementById('btn-modal-criar-contrato');
        var selectLocacao = document.getElementById('modal-locacao');
        var selectModelo = document.getElementById('modal-modelo');

        if (btnNovo && modalEl) {
            btnNovo.addEventListener('click', function () {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    modalEl.setAttribute('aria-modal', 'true');
                    document.body.classList.add('modal-open');
                    var backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'modalCriarContrato-backdrop';
                    document.body.appendChild(backdrop);
                }
                if (selectLocacao && !selectLocacao._select2 && typeof window.$ !== 'undefined') {
                    window.$(selectLocacao).select2({
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

        if (btnCriar && selectLocacao && selectModelo) {
            btnCriar.addEventListener('click', function () {
                var locacaoId = (typeof window.$ !== 'undefined' && window.$(selectLocacao).val) ? window.$(selectLocacao).val() : selectLocacao.value;
                var modeloId = selectModelo.value;
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
    }

    function run() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                init(document.getElementById('table-contratos'));
            });
        } else {
            init(document.getElementById('table-contratos'));
        }
    }
    run();
})();
