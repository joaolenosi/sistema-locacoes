// Listagem de Manutenções com GridJS (dados da API Manutenção Inteligente)
(function () {
    const tableEl = document.getElementById("table-manutencoes");
    if (!tableEl) return;

    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + '/');
        return base.endsWith('/') ? base : base + '/';
    };

    const fetchJson = (url, options) => {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, ...options })
            .then(function (res) { return res.json().catch(function () { return null; }); })
            .then(function (json) {
                if (json && !json.success && json.message) throw new Error(json.message);
                return json;
            });
    };

    const ptBR = {
        search: { placeholder: 'Digite uma palavra-chave...' },
        pagination: { previous: 'Anterior', next: 'Próximo', showing: 'Mostrando', to: 'a', of: 'de', results: 'resultados' }
    };

    const formatDate = function (dateStr) {
        if (!dateStr) return '—';
        try {
            return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-BR');
        } catch (e) { return dateStr; }
    };

    const statusLabel = { 'agendada': 'Agendada', 'em-andamento': 'Em Andamento', 'concluida': 'Concluída', 'atrasada': 'Atrasada' };
    const statusBadge = { 'agendada': 'bg-info-subtle text-info', 'em-andamento': 'bg-warning-subtle text-warning', 'concluida': 'bg-success-subtle text-success', 'atrasada': 'bg-danger-subtle text-danger' };

    let gridInstance = null;

    const renderGrid = function (items) {
        var data = Array.isArray(items) ? items : [];
        var rows = data
            .filter(function (item) { return item && item.origem === 'manutencao'; })
            .map(function (item) {
                var status = item.status || 'agendada';
                return [
                    String(item.id || ''),
                    item.veiculo_placa || '—',
                    item.veiculo_modelo || '—',
                    item.tipo || '—',
                    formatDate(item.data_prevista),
                    status,
                    String(item.id || '')
                ];
            });

        var columns = [
            { name: 'ID', width: '70px', formatter: function (c) { return gridjs.html('<span class="fw-semibold">' + c + '</span>'); } },
            { name: 'Placa', width: '110px', formatter: function (c) { return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + c + '</span>'); } },
            { name: 'Modelo', width: '140px' },
            { name: 'Tipo', width: '100px' },
            { name: 'Data Prevista', width: '120px' },
            { name: 'Status', width: '120px', formatter: function (c) {
                var cls = statusBadge[c] || 'bg-secondary-subtle text-secondary';
                var label = statusLabel[c] || c;
                return gridjs.html('<span class="badge ' + cls + '">' + label + '</span>');
            }},
            { name: 'Ações', width: '220px', formatter: function (cell, row) {
                var id = row.cells[0].data;
                var placa = (row.cells[1]?.data || '').toString().replace(/"/g, '&quot;');
                var base = (window.__BASE_URL__ || (window.location.origin + '/')).replace(/\/$/, '') + '/';
                var iconTrash = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
                return gridjs.html(
                    '<div class="d-flex gap-1 align-items-center flex-nowrap">' +
                    '<a href="' + base + 'admin/manutencao/detalhes/' + id + '" class="btn btn-sm btn-outline-secondary" title="Ver Detalhes"><iconify-icon icon="iconamoon:info-duotone" class="fs-18"></iconify-icon> Detalhes</a>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-manutencao" data-id="' + id + '" title="Editar"><iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon> Editar</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center btn-delete-manutencao" data-id="' + id + '" data-placa="' + placa + '" title="Excluir">' + iconTrash + '</button>' +
                    '</div>'
                );
            }}
        ];

        if (!gridInstance) {
            gridInstance = new gridjs.Grid({
                columns: columns,
                pagination: { limit: 10 },
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

    const loadGrid = function () {
        fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/listar')
            .then(function (json) {
                renderGrid(Array.isArray(json && json.data) ? json.data : []);
            })
            .catch(function (err) {
                console.error('Erro ao carregar manutenções:', err);
                renderGrid([]);
            });
    };

    renderGrid([]);
    loadGrid();
    window.addEventListener('manutencao-reload', loadGrid);
})();

// Modal Nova Manutenção (usa API da Manutenção Inteligente)
(function () {
    const modalEl = document.getElementById('modalManutencao');
    const formEl = document.getElementById('formManutencao');
    const alertEl = document.getElementById('man-form-alert');
    const btnSave = document.getElementById('btnSalvarManutencao');
    let itensCadastro = [];

    if (!modalEl || !formEl) return;

    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || window.location.origin + '/';
        return base.endsWith('/') ? base : base + '/';
    };

    const formatMoney = (v) => 'R$ ' + (typeof v === 'number' ? v : parseFloat(v || 0)).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const fetchJson = async (url, options = {}) => {
        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            ...options
        });
        const json = await res.json().catch(() => null);
        if (!res.ok) throw new Error(json?.message || 'Erro na requisição.');
        return json;
    };

    const setAlert = (msg) => {
        if (!alertEl) return;
        if (!msg) {
            alertEl.classList.add('d-none');
            alertEl.textContent = '';
            return;
        }
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
    };

    const buscarKmAtualVeiculo = async function (veiculoId) {
        if (!veiculoId || veiculoId < 1) return null;
        try {
            const json = await fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/km-atual/' + veiculoId);
            return json && json.km_atual ? json.km_atual : null;
        } catch (e) {
            console.error('Erro ao buscar KM atual do veículo:', e);
            return null;
        }
    };

    const carregarVeiculos = async () => {
        try {
            const json = await fetchJson(getBaseUrl() + 'admin/veiculos/listar');
            const data = Array.isArray(json?.data) ? json.data : [];
            const select = document.getElementById('man_veiculo_id');
            if (select) {
                select.innerHTML = '<option value="">Selecione um veículo</option>';
                data.forEach(function (v) {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = (v.vei_placa || '') + ' - ' + (v.vei_modelo || '');
                    select.appendChild(opt);
                });
                
                // Adicionar listener para buscar KM atual ao selecionar veículo
                select.addEventListener('change', async function() {
                    const veiculoId = this.value;
                    const kmAtualEl = document.getElementById('man_km_atual');
                    if (veiculoId && kmAtualEl && !kmAtualEl.value) {
                        const kmAtual = await buscarKmAtualVeiculo(veiculoId);
                        if (kmAtual !== null) {
                            kmAtualEl.value = kmAtual;
                        }
                    }
                });
            }
        } catch (e) {
            console.error('Erro ao carregar veículos:', e);
        }
    };

    const getBsModal = () => {
        if (!window.bootstrap || !window.bootstrap.Modal) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalEl);
    };

    const titleEl = document.getElementById('modalManutencaoLabel');

    const renderItensCadastro = () => {
        const tbody = document.getElementById('tbody-itens-cadastro');
        const totalEl = document.getElementById('man-total-cadastro');
        if (!tbody) return;
        let total = 0;
        tbody.innerHTML = '';
        itensCadastro.forEach(function (item, idx) {
            total += parseFloat(item.mai_valor_total || item.valor_total || 0);
            const tipoLabel = (item.mai_tipo_item || item.tipo_item || '') === 'produto' ? 'Produto' : 'Serviço';
            const tipoCls = (item.mai_tipo_item || item.tipo_item || '') === 'produto' ? 'info' : 'success';
            const tr = document.createElement('tr');
            tr.setAttribute('data-idx', idx);
            tr.innerHTML = '<td>' + (item.mai_descricao || item.descricao || '—') + '</td>' +
                '<td class="text-center"><span class="badge bg-' + tipoCls + '-subtle text-' + tipoCls + '">' + tipoLabel + '</span></td>' +
                '<td class="text-center">' + (item.mai_quantidade || item.quantidade || 1) + '</td>' +
                '<td class="text-end">' + formatMoney(item.mai_valor_unitario || item.valor_unitario || 0) + '</td>' +
                '<td class="text-end fw-semibold">' + formatMoney(item.mai_valor_total || item.valor_total || 0) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remover-item-cadastro" data-idx="' + idx + '">×</button></td>';
            tbody.appendChild(tr);
        });
        if (itensCadastro.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="6" class="text-muted text-center py-2">Nenhum item. Clique em Adicionar item.</td>';
            tbody.appendChild(tr);
        }
        if (totalEl) totalEl.textContent = formatMoney(total);
    };

    const carregarProdutosServicos = async () => {
        try {
            const [pRes, sRes] = await Promise.all([
                fetchJson(getBaseUrl() + 'admin/cadastro/produtos/listar'),
                fetchJson(getBaseUrl() + 'admin/cadastro/servicos/listar')
            ]);
            const produtos = Array.isArray(pRes?.data) ? pRes.data : [];
            const servicos = Array.isArray(sRes?.data) ? sRes.data : [];
            const sp = document.getElementById('cadastro-item-produto-id');
            const ss = document.getElementById('cadastro-item-servico-id');
            if (sp) {
                sp.innerHTML = '<option value="">Selecione um produto</option>' + produtos.map(function (p) {
                    const preco = parseFloat(p.pro_preco_venda || p.pro_preco_custo || 0);
                    return '<option value="' + p.id + '" data-preco="' + preco + '">' + (p.pro_nome || '') + ' - ' + formatMoney(preco) + '</option>';
                }).join('');
            }
            if (ss) {
                ss.innerHTML = '<option value="">Selecione um serviço</option>' + servicos.map(function (s) {
                    const preco = parseFloat(s.ser_preco_padrao || 0);
                    return '<option value="' + s.id + '" data-preco="' + preco + '">' + (s.ser_nome || '') + ' - ' + formatMoney(preco) + '</option>';
                }).join('');
            }
        } catch (e) {
            console.error('Erro ao carregar produtos/serviços:', e);
        }
    };

    const resetForm = () => {
        setAlert('');
        formEl.reset();
        itensCadastro = [];
        renderItensCadastro();
        var idEl = document.getElementById('man_id');
        if (idEl) idEl.value = '';
        var triggerTipoEl = document.getElementById('man_trigger_tipo');
        if (triggerTipoEl) triggerTipoEl.value = 'qualquer';
        var triggerCheckbox = document.getElementById('man_trigger_qualquer');
        if (triggerCheckbox) triggerCheckbox.checked = true;
        if (titleEl) titleEl.textContent = 'Cadastrar manutenção';
        var span = btnSave && btnSave.querySelector('.btn-text');
        if (span) span.textContent = 'Adicionar';
    };

    const fillForm = (m) => {
        var setVal = function (id, val) {
            var el = document.getElementById(id);
            if (el) el.value = val !== undefined && val !== null ? val : '';
        };
        setVal('man_id', m.id);
        setVal('man_veiculo_id', m.man_veiculo_id || m.veiculo_id);
        setVal('man_tipo', m.man_tipo || m.tipo);
        setVal('man_data', m.man_data || m.data_prevista);
        setVal('man_km', m.man_km || m.km_previsto);
        setVal('man_km_atual', m.man_km_atual || m.km_atual || '');
        setVal('man_obs', m.man_obs || m.observacoes);
        
        // Preencher switch baseado em man_trigger_tipo
        var triggerTipo = m.man_trigger_tipo || 'qualquer';
        var triggerCheckbox = document.getElementById('man_trigger_qualquer');
        var triggerTipoEl = document.getElementById('man_trigger_tipo');
        if (triggerCheckbox) triggerCheckbox.checked = (triggerTipo === 'qualquer');
        if (triggerTipoEl) triggerTipoEl.value = triggerTipo;
        
        itensCadastro = Array.isArray(m.itens) ? m.itens.map(function (i) {
            return { id: i.id, mai_descricao: i.mai_descricao, mai_tipo_item: i.mai_tipo_item, mai_quantidade: i.mai_quantidade, mai_valor_unitario: i.mai_valor_unitario, mai_valor_total: i.mai_valor_total };
        }) : [];
        renderItensCadastro();
        
        if (titleEl) titleEl.textContent = 'Editar manutenção';
        var span = btnSave && btnSave.querySelector('.btn-text');
        if (span) span.textContent = 'Salvar alterações';
    };

    const openEdit = function (id) {
        if (!id) return;
        resetForm();
        btnSave.disabled = true;
        var span = btnSave.querySelector('.btn-text');
        if (span) span.textContent = 'Carregando...';
        fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/detalhes/' + id)
            .then(function (json) {
                if (json && json.data) fillForm(json.data);
                getBsModal().show();
            })
            .catch(function (e) {
                setAlert(e.message || 'Erro ao carregar manutenção.');
                getBsModal().show();
            })
            .finally(function () {
                btnSave.disabled = false;
                span = btnSave.querySelector('.btn-text');
                if (span) span.textContent = document.getElementById('man_id').value ? 'Salvar alterações' : 'Adicionar';
            });
    };

    const submit = async () => {
        setAlert('');
        const required = ['man_veiculo_id', 'man_tipo', 'man_data'];
        for (let i = 0; i < required.length; i++) {
            const el = document.getElementById(required[i]);
            if (el && !String(el.value || '').trim()) {
                setAlert('Preencha os campos obrigatórios.');
                return;
            }
        }

        const id = document.getElementById('man_id').value || '';
        
        // Atualizar campo hidden man_trigger_tipo baseado no checkbox
        const triggerCheckbox = document.getElementById('man_trigger_qualquer');
        const triggerTipoEl = document.getElementById('man_trigger_tipo');
        if (triggerCheckbox && triggerTipoEl) {
            triggerTipoEl.value = triggerCheckbox.checked ? 'qualquer' : 'data';
        }
        
        const fd = new FormData(formEl);
        btnSave.disabled = true;
        const span = btnSave.querySelector('.btn-text');
        if (span) span.textContent = 'Salvando...';

        try {
            var novoId = id;
            if (!id) {
                const criarRes = await fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/criar', { method: 'POST', body: fd });
                novoId = criarRes && criarRes.id ? criarRes.id : null;
                if (novoId && itensCadastro.length > 0) {
                    for (var i = 0; i < itensCadastro.length; i++) {
                        var it = itensCadastro[i];
                        var payload = {
                            tipo_item: it.mai_tipo_item || 'produto',
                            quantidade: it.mai_quantidade || 1
                        };
                        if ((it.mai_tipo_item || '') === 'servico') {
                            payload.servico_id = it.mai_servico_id;
                        } else {
                            payload.produto_id = it.mai_produto_id;
                        }
                        await fetchJson(getBaseUrl() + 'admin/manutencao/' + novoId + '/itens', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: JSON.stringify(payload)
                        });
                    }
                }
            } else {
                await fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/atualizar/' + id, { method: 'POST', body: fd });
            }
            getBsModal().hide();
            resetForm();
            if (typeof window.toastr !== 'undefined') {
                window.toastr.success(id ? 'Manutenção atualizada com sucesso.' : 'Manutenção cadastrada com sucesso.');
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Sucesso', text: id ? 'Manutenção atualizada com sucesso.' : 'Manutenção cadastrada com sucesso.', confirmButtonText: 'OK' });
            }
            window.dispatchEvent(new CustomEvent('manutencao-reload'));
        } catch (e) {
            setAlert(e.message || 'Erro ao salvar manutenção.');
        } finally {
            btnSave.disabled = false;
            if (span) span.textContent = id ? 'Salvar alterações' : 'Adicionar';
        }
    };

    var itemTipoCad = document.getElementById('cadastro-item-tipo');
    var itemProdCad = document.getElementById('cadastro-item-produto-id');
    var itemServCad = document.getElementById('cadastro-item-servico-id');
    var itemQtdCad = document.getElementById('cadastro-item-quantidade');
    var getVuCad = function () {
        var tipo = (itemTipoCad && itemTipoCad.value) || 'produto';
        var sel = tipo === 'produto' ? itemProdCad : itemServCad;
        var opt = sel && sel.options && sel.options[sel.selectedIndex];
        return opt ? parseFloat(opt.getAttribute('data-preco') || 0) : 0;
    };
    var toggleGruposCad = function () {
        var tipo = (itemTipoCad && itemTipoCad.value) || 'produto';
        var gp = document.getElementById('cadastro-grupo-produto');
        var gs = document.getElementById('cadastro-grupo-servico');
        if (gp) gp.classList.toggle('d-none', tipo !== 'produto');
        if (gs) gs.classList.toggle('d-none', tipo !== 'servico');
        if (itemProdCad) itemProdCad.value = '';
        if (itemServCad) itemServCad.value = '';
    };
    if (itemTipoCad) {
        itemTipoCad.addEventListener('change', function () {
            carregarProdutosServicos().then(toggleGruposCad);
        });
    }

    var btnAddItem = document.getElementById('btn-adicionar-item-cadastro');
    if (btnAddItem) {
        btnAddItem.addEventListener('click', function () {
            var alertItem = document.getElementById('cadastro-form-item-alert');
            if (alertItem) { alertItem.classList.add('d-none'); alertItem.textContent = ''; }
            var manId = document.getElementById('man_id').value;
            var tipo = (itemTipoCad && itemTipoCad.value) || 'produto';
            var prodId = tipo === 'produto' ? parseInt((itemProdCad && itemProdCad.value) || 0, 10) : 0;
            var servId = tipo === 'servico' ? parseInt((itemServCad && itemServCad.value) || 0, 10) : 0;
            var qtd = parseInt((itemQtdCad && itemQtdCad.value) || 1, 10) || 1;
            if (tipo === 'produto' && prodId < 1) {
                if (alertItem) { alertItem.textContent = 'Selecione um produto.'; alertItem.classList.remove('d-none'); }
                return;
            }
            if (tipo === 'servico' && servId < 1) {
                if (alertItem) { alertItem.textContent = 'Selecione um serviço.'; alertItem.classList.remove('d-none'); }
                return;
            }
            var vu = getVuCad();
            var descricao = (tipo === 'produto' && itemProdCad && itemProdCad.options[itemProdCad.selectedIndex]) ? itemProdCad.options[itemProdCad.selectedIndex].text.split(' - ')[0] : ((tipo === 'servico' && itemServCad && itemServCad.options[itemServCad.selectedIndex]) ? itemServCad.options[itemServCad.selectedIndex].text.split(' - ')[0] : '');
            var novoItem = {
                mai_descricao: descricao,
                mai_tipo_item: tipo,
                mai_quantidade: qtd,
                mai_valor_unitario: vu,
                mai_valor_total: qtd * vu,
                mai_produto_id: tipo === 'produto' ? prodId : null,
                mai_servico_id: tipo === 'servico' ? servId : null
            };
            if (manId) {
                btnAddItem.disabled = true;
                fetch(getBaseUrl() + 'admin/manutencao/' + manId + '/itens', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ tipo_item: tipo, quantidade: qtd, produto_id: prodId || undefined, servico_id: servId || undefined })
                }).then(function (r) { return r.json(); }).then(function (json) {
                    if (json && json.success && json.item) {
                        itensCadastro.push(json.item);
                        renderItensCadastro();
                        if (window.toastr) window.toastr.success('Item adicionado.');
                        if (itemQtdCad) itemQtdCad.value = '1';
                    } else if (alertItem) {
                        alertItem.textContent = (json && json.message) || 'Erro ao adicionar.';
                        alertItem.classList.remove('d-none');
                    }
                }).catch(function () {
                    if (alertItem) { alertItem.textContent = 'Erro ao adicionar item.'; alertItem.classList.remove('d-none'); }
                }).finally(function () { btnAddItem.disabled = false; });
            } else {
                itensCadastro.push(novoItem);
                renderItensCadastro();
                if (itemQtdCad) itemQtdCad.value = '1';
            }
        });
    }

    modalEl.addEventListener('show.bs.modal', function () {
        carregarProdutosServicos().then(toggleGruposCad);
    });

    document.addEventListener('click', function (e) {
        var btnEdit = e.target && e.target.closest && e.target.closest('.btn-edit-manutencao');
        if (btnEdit) {
            e.preventDefault();
            var id = btnEdit.getAttribute('data-id');
            if (id) openEdit(id);
            return;
        }

        var btnDelete = e.target && e.target.closest && e.target.closest('.btn-delete-manutencao');
        if (btnDelete) {
            e.preventDefault();
            var id = btnDelete.getAttribute('data-id');
            var placa = btnDelete.getAttribute('data-placa') || '';
            if (!id) return;

            var msg = placa ? 'Você está prestes a excluir a manutenção do veículo ' + placa + '.' : 'Você está prestes a excluir esta manutenção.';

            var executeDelete = function () {
                btnDelete.disabled = true;
                btnDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                fetch(getBaseUrl() + 'admin/manutencao-inteligente/excluir/' + id, {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Manutenção excluída',
                                text: result.json.message || 'A manutenção foi excluída com sucesso.',
                                confirmButtonText: 'OK'
                            }).then(function () { window.dispatchEvent(new CustomEvent('manutencao-reload')); });
                        } else {
                            var errMsg = (result.json && result.json.message) ? result.json.message : 'Não foi possível excluir a manutenção.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Não foi possível excluir',
                                text: errMsg,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Erro ao excluir manutenção. Tente novamente.',
                            confirmButtonText: 'OK'
                        });
                    })
                    .finally(function () {
                        btnDelete.disabled = false;
                        btnDelete.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
                    });
            };

            Swal.fire({
                icon: 'warning',
                title: 'Excluir manutenção?',
                text: msg,
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result && result.isConfirmed) executeDelete();
            });
            return;
        }

        var btnRm = e.target && e.target.closest && e.target.closest('.btn-remover-item-cadastro');
        if (btnRm) {
            e.preventDefault();
            var idx = parseInt(btnRm.getAttribute('data-idx'), 10);
            var manId = document.getElementById('man_id').value;
            var item = itensCadastro[idx];
            if (item && item.id && manId) {
                btnRm.disabled = true;
                fetch(getBaseUrl() + 'admin/manutencao/itens/deletar/' + item.id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
                }).then(function (r) { return r.json(); }).then(function (json) {
                    if (json && json.success) {
                        itensCadastro.splice(idx, 1);
                        renderItensCadastro();
                        if (window.toastr) window.toastr.success('Item removido.');
                    }
                }).finally(function () { btnRm.disabled = false; });
            } else {
                itensCadastro.splice(idx, 1);
                renderItensCadastro();
            }
        }
    });

    var btnAdd = document.getElementById('btn-add-manutencao');
    if (btnAdd) btnAdd.addEventListener('click', resetForm);
    modalEl.addEventListener('hidden.bs.modal', resetForm);
    btnSave.addEventListener('click', submit);

    carregarVeiculos();
})();
