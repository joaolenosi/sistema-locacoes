// Listagem de Manutenções com GridJS
if (document.getElementById("table-manutencoes")) {
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
                name: 'Veículo',
                width: '140px'
            },
            {
                name: 'Placa',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
                })
            },
            {
                name: 'Início',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + cell + '</span>');
                })
            },
            {
                name: 'Término',
                width: '120px',
                formatter: (function (cell) {
                    return gridjs.html('<span class="text-muted">' + (cell === '—' ? '<span class="text-muted">—</span>' : cell) + '</span>');
                })
            },
            {
                name: 'Descrição',
                width: '250px'
            },
            {
                name: 'Status',
                width: '130px',
                formatter: (function (cell) {
                    const badgeClass = cell === 'Ativa' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success';
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
            ["1", "Onix", "ABC-1234", "10/01/2026", "12/01/2026", "Revisão preventiva e troca de óleo", "Finalizada"],
            ["2", "HB20", "XYZ-5678", "18/01/2026", "—", "Troca de pastilhas de freio", "Ativa"],
            ["3", "Corolla", "DEF-9012", "22/01/2026", "25/01/2026", "Troca de pneus e alinhamento", "Finalizada"],
            ["4", "Civic", "GHI-3456", "28/01/2026", "—", "Manutenção elétrica e bateria", "Ativa"],
            ["5", "Fiesta", "JKL-7890", "05/01/2026", "07/01/2026", "Revisão completa", "Finalizada"],
            ["6", "Gol", "MNO-2468", "15/01/2026", "17/01/2026", "Troca de correia dentada", "Finalizada"],
            ["7", "Compass", "PQR-1357", "30/01/2026", "—", "Revisão de suspensão", "Ativa"],
            ["8", "T-Cross", "STU-8024", "08/01/2026", "10/01/2026", "Troca de filtros", "Finalizada"],
            ["9", "Renegade", "VWX-4680", "25/01/2026", "—", "Manutenção de ar condicionado", "Ativa"],
            ["10", "Creta", "YZA-2468", "12/01/2026", "14/01/2026", "Revisão e balanceamento", "Finalizada"]
        ]
    }).render(document.getElementById("table-manutencoes"));
}

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
            }
        } catch (e) {
            console.error('Erro ao carregar veículos:', e);
        }
    };

    const getBsModal = () => {
        if (!window.bootstrap || !window.bootstrap.Modal) return null;
        return window.bootstrap.Modal.getOrCreateInstance(modalEl);
    };

    const resetForm = () => {
        setAlert('');
        formEl.reset();
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

        const fd = new FormData(formEl);
        btnSave.disabled = true;
        const span = btnSave.querySelector('.btn-text');
        if (span) span.textContent = 'Salvando...';

        try {
            await fetchJson(getBaseUrl() + 'admin/manutencao-inteligente/criar', { method: 'POST', body: fd });
            getBsModal().hide();
            resetForm();
            if (typeof window.toastr !== 'undefined') {
                window.toastr.success('Manutenção cadastrada com sucesso.');
            } else {
                alert('Manutenção cadastrada com sucesso.');
            }
        } catch (e) {
            setAlert(e.message || 'Erro ao salvar manutenção.');
        } finally {
            btnSave.disabled = false;
            if (span) span.textContent = 'Adicionar';
        }
    };

    modalEl.addEventListener('shown.bs.modal', resetForm);
    modalEl.addEventListener('hidden.bs.modal', resetForm);
    btnSave.addEventListener('click', submit);

    carregarVeiculos();
})();
