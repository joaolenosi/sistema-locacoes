(() => {
  const tableEl = document.getElementById("table-categorias-financeiras");
  if (!tableEl) return;

  // Helper para garantir base URL com barra final
  const getBaseUrl = () => {
    const base = window.__BASE_URL__ || window.location.origin;
    return base.endsWith('/') ? base : base + '/';
  };

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

  const tipoLabel = (tipo) => tipo === 'receita' ? 'Receita' : 'Despesa';
  const tipoBadge = (tipo) => tipo === 'receita' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
  const padraoLabel = (padrao) => String(padrao) === '1' ? 'Sim' : 'Não';
  const padraoBadge = (padrao) => String(padrao) === '1' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary';

  const toRows = (items) =>
    (items || []).map((c) => [
      String(c.id),
      c.cat_nome || '-',
      c.cat_tipo || '-',
      String(c.cat_padrao ?? '0'),
      String(c.id),
    ]);

  let grid = null;
  let currentData = Array.isArray(window.__CATEGORIAS__) ? window.__CATEGORIAS__ : [];

  const renderGrid = (items) => {
    currentData = items || [];
    const rows = toRows(currentData);

    const columns = [
      {
        name: 'ID',
        width: '80px',
        formatter: (cell) => {
          return gridjs.html('<span class="fw-semibold">' + cell + '</span>');
        }
      },
      'Nome',
      {
        name: 'Tipo',
        width: '130px',
        formatter: (cell) => {
          const badgeClass = tipoBadge(cell);
          const label = tipoLabel(cell);
          return gridjs.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
        }
      },
      {
        name: 'Padrão',
        width: '130px',
        formatter: (cell) => {
          const badgeClass = padraoBadge(cell);
          const label = padraoLabel(cell);
          return gridjs.html('<span class="badge ' + badgeClass + '">' + label + '</span>');
        }
      },
      {
        name: 'Ações',
        width: '120px',
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          return gridjs.html(`
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-categoria" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
            </div>
          `);
        }
      }
    ];

    if (!grid) {
      grid = new gridjs.Grid({
        columns: columns,
        pagination: {
          limit: 5
        },
        sort: true,
        search: true,
        language: ptBR,
        data: rows
      }).render(tableEl);
      return;
    }

    grid.updateConfig({ columns: columns, data: rows }).forceRender();
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

  const reload = async () => {
    const json = await fetchJson(`${getBaseUrl()}admin/cadastro/categorias-financeiras/listar`);
    renderGrid(json.data || []);
  };

  const modalEl = document.getElementById('modalCategoria');
  const formEl = document.getElementById('formCategoria');
  const alertEl = document.getElementById('cat-form-alert');
  const btnSave = document.getElementById('btnSalvarCategoria');
  const btnAdd = document.getElementById('btn-add-categoria');
  const titleEl = document.getElementById('modalCategoriaLabel');

  const getBsModal = () => {
    if (!modalEl) return null;
    if (!window.bootstrap?.Modal) return null;
    return window.bootstrap.Modal.getOrCreateInstance(modalEl);
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

  const lockButton = (locked, text) => {
    if (!btnSave) return;
    btnSave.disabled = locked;
    const span = btnSave.querySelector('.btn-text');
    if (span && text) span.textContent = text;
    if (locked) {
      if (!btnSave.querySelector('.spinner-border')) {
        btnSave.insertAdjacentHTML(
          'afterbegin',
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
        );
      }
    } else {
      btnSave.querySelector('.spinner-border')?.remove();
    }
  };

  const resetForm = () => {
    setAlert('');
    formEl?.reset();
    const idEl = document.getElementById('cat_id');
    if (idEl) idEl.value = '';
    if (titleEl) titleEl.textContent = 'Cadastrar categoria';
    btnSave?.querySelector('.btn-text') && (btnSave.querySelector('.btn-text').textContent = 'Adicionar');
  };

  const fillForm = (c) => {
    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val ?? '';
    };
    setVal('cat_id', c.id);
    setVal('cat_nome', c.cat_nome);
    setVal('cat_tipo', c.cat_tipo);
    setVal('cat_padrao', String(c.cat_padrao ?? '0'));

    if (titleEl) titleEl.textContent = 'Editar categoria';
    btnSave?.querySelector('.btn-text') && (btnSave.querySelector('.btn-text').textContent = 'Salvar alterações');
  };

  // Enter: avançar campo a campo
  const enableEnterNavigation = () => {
    if (!formEl) return;
    formEl.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      const tag = (e.target?.tagName || '').toLowerCase();
      if (tag === 'textarea') return;
      e.preventDefault();
      const focusables = Array.from(
        formEl.querySelectorAll(
          'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'
        )
      ).filter((el) => el.offsetParent !== null);
      const idx = focusables.indexOf(e.target);
      if (idx === -1) return;
      const next = focusables[e.shiftKey ? idx - 1 : idx + 1] || focusables[0];
      next?.focus?.();
      if (next && next.tagName?.toLowerCase() === 'input') {
        try { next.select?.(); } catch (_) {}
      }
    });
  };

  const openCreate = () => {
    resetForm();
    getBsModal()?.show();
    setTimeout(() => document.getElementById('cat_nome')?.focus?.(), 150);
  };

  const openEdit = async (id) => {
    resetForm();
    lockButton(true, 'Carregando...');
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/cadastro/categorias-financeiras/editar/${id}`);
      fillForm(json.data);
      getBsModal()?.show();
      setTimeout(() => document.getElementById('cat_nome')?.focus?.(), 150);
    } catch (e) {
      setAlert(e.message || 'Erro ao carregar categoria.');
      getBsModal()?.show();
    } finally {
      lockButton(false);
    }
  };

  const submit = async () => {
    if (!formEl) return;
    setAlert('');

    const required = ['cat_nome', 'cat_tipo'];
    for (const r of required) {
      const el = document.getElementById(r);
      if (el && !String(el.value || '').trim()) {
        setAlert('Preencha os campos obrigatórios.');
        return;
      }
    }

    const id = document.getElementById('cat_id')?.value || '';
    const fd = new FormData(formEl);

    lockButton(true, id ? 'Salvando...' : 'Adicionando...');
    try {
      const url = id
        ? `${getBaseUrl()}admin/cadastro/categorias-financeiras/atualizar/${id}`
        : `${getBaseUrl()}admin/cadastro/categorias-financeiras/criar`;

      await fetchJson(url, { method: 'POST', body: fd });
      getBsModal()?.hide();
      await reload();
      resetForm();
    } catch (e) {
      setAlert(e.message || 'Erro ao salvar categoria.');
    } finally {
      lockButton(false);
    }
  };

  btnAdd?.addEventListener('click', openCreate);
  btnSave?.addEventListener('click', submit);

  document.addEventListener('click', (e) => {
    const btn = e.target.closest?.('.btn-edit-categoria');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (id) openEdit(id);
  });

  modalEl?.addEventListener('hidden.bs.modal', () => {
    resetForm();
    lockButton(false);
  });

  renderGrid(currentData);
  if (!currentData || currentData.length === 0) reload().catch(() => {});
  enableEnterNavigation();
})();
