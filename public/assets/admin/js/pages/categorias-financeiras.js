(() => {
  const init = () => {
    const tableEl = document.getElementById("table-categorias-financeiras");
    if (!tableEl) {
      console.warn('Elemento table-categorias-financeiras não encontrado');
      return;
    }

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

    const toRows = (items) => {
      if (!Array.isArray(items)) return [];
      return items.map((c) => {
        if (!c || typeof c !== 'object') return null;
        return [
          String(c.id || ''),
          c.cat_nome || '-',
          c.cat_tipo || '-',
          String(c.cat_padrao ?? '0'),
          String(c.id || ''),
        ];
      }).filter(row => row !== null);
    };

    let grid = null;
    let currentData = Array.isArray(window.__CATEGORIAS__) ? window.__CATEGORIAS__ : [];

    const renderGrid = (items) => {
      currentData = Array.isArray(items) ? items : [];
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
        width: '160px',
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          const nome = (row.cells[1]?.data || '').toString().replace(/"/g, '&quot;');
          const iconTrash = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
          return gridjs.html(`
            <div class="d-flex gap-2 align-items-center">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-categoria" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center btn-delete-categoria" data-id="${id}" data-nome="${nome}" title="Excluir">${iconTrash}</button>
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
        data: Array.isArray(rows) ? rows : []
      }).render(tableEl);
      return;
    }

    grid.updateConfig({ columns: columns, data: Array.isArray(rows) ? rows : [] }).forceRender();
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
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/cadastro/categorias-financeiras/listar`);
        renderGrid(Array.isArray(json?.data) ? json.data : []);
      } catch (e) {
        console.error('Erro ao recarregar dados:', e);
        renderGrid([]);
      }
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
      const btnEdit = e.target.closest?.('.btn-edit-categoria');
      if (btnEdit) {
        const id = btnEdit.getAttribute('data-id');
        if (id) {
          e.preventDefault();
          openEdit(id);
        }
        return;
      }

      const btnDelete = e.target.closest?.('.btn-delete-categoria');
      if (btnDelete) {
        const id = btnDelete.getAttribute('data-id');
        const nome = btnDelete.getAttribute('data-nome') || '';
        if (!id) return;

        const msg = nome ? 'Você está prestes a excluir a categoria "' + nome + '".' : 'Você está prestes a excluir esta categoria.';

        const executeDelete = async () => {
          btnDelete.disabled = true;
          btnDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
          try {
            const res = await fetch(getBaseUrl() + 'admin/cadastro/categorias-financeiras/excluir/' + id, {
              method: 'POST',
              headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
            });
            const json = await res.json().catch(() => null);
            if (res.ok && json && json.success) {
              if (typeof Swal !== 'undefined') {
                await Swal.fire({
                  icon: 'success',
                  title: 'Categoria excluída',
                  text: json.message || 'A categoria foi excluída com sucesso.',
                  confirmButtonText: 'OK'
                });
              } else {
                alert(json.message || 'Categoria excluída com sucesso.');
              }
              await reload();
            } else {
              const errMsg = (json && json.message) ? json.message : 'Não foi possível excluir a categoria.';
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
          } catch (err) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao excluir categoria. Tente novamente.',
                confirmButtonText: 'OK'
              });
            } else {
              alert('Erro ao excluir categoria. Tente novamente.');
            }
          } finally {
            btnDelete.disabled = false;
            btnDelete.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
          }
        };

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Excluir categoria?',
            text: msg,
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
          }).then((result) => {
            if (result && result.isConfirmed) executeDelete();
          });
        } else if (confirm('Tem certeza que deseja excluir esta categoria?')) {
          executeDelete();
        }
      }
    });

    modalEl?.addEventListener('hidden.bs.modal', () => {
      resetForm();
      lockButton(false);
    });

    // Verificar se os elementos necessários existem antes de anexar listeners
    if (!btnSave) {
      console.error('Elemento btnSalvarCategoria não encontrado');
    }
    if (!btnAdd) {
      console.error('Elemento btn-add-categoria não encontrado');
    }
    if (!formEl) {
      console.error('Elemento formCategoria não encontrado');
    }

    renderGrid(currentData);
    if (!currentData || currentData.length === 0) {
      reload().catch((e) => {
        console.error('Erro ao carregar dados iniciais:', e);
      });
    }
    enableEnterNavigation();
  };

  // Executar quando o DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    // DOM já está pronto
    init();
  }
})();
