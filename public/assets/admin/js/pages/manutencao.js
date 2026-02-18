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
            { name: 'Ações', width: '100px', formatter: function (cell, row) {
                var id = row.cells[0].data;
                return gridjs.html('<button type="button" class="btn btn-sm btn-outline-primary btn-edit-manutencao" data-id="' + id + '" title="Editar"><iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon> Editar</button>');
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

    if (!modalEl || !formEl) return;

    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || window.location.origin + '/';
        return base.endsWith('/') ? base : base + '/';
    };

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

    const resetForm = () => {
        setAlert('');
        formEl.reset();
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
        fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/editar/' + id)
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
            const url = id
                ? getBaseUrl() + 'admin/manutencao-inteligente/atualizar/' + id
                : getBaseUrl() + 'admin/manutencao-inteligente/criar';
            await fetchJson(url, { method: 'POST', body: fd });
            getBsModal().hide();
            resetForm();
            if (typeof window.toastr !== 'undefined') {
                window.toastr.success(id ? 'Manutenção atualizada com sucesso.' : 'Manutenção cadastrada com sucesso.');
            } else {
                alert(id ? 'Manutenção atualizada com sucesso.' : 'Manutenção cadastrada com sucesso.');
            }
            window.dispatchEvent(new CustomEvent('manutencao-reload'));
        } catch (e) {
            setAlert(e.message || 'Erro ao salvar manutenção.');
        } finally {
            btnSave.disabled = false;
            if (span) span.textContent = id ? 'Salvar alterações' : 'Adicionar';
        }
    };

    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest && e.target.closest('.btn-edit-manutencao');
        if (btn) {
            e.preventDefault();
            var id = btn.getAttribute('data-id');
            if (id) openEdit(id);
        }
    });

    var btnAdd = document.getElementById('btn-add-manutencao');
    if (btnAdd) btnAdd.addEventListener('click', resetForm);
    modalEl.addEventListener('hidden.bs.modal', resetForm);
    btnSave.addEventListener('click', submit);

    carregarVeiculos();
})();
