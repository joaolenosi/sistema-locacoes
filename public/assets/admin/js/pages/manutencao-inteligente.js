(() => {
  const init = () => {
    const tableEl = document.getElementById("table-manutencao-inteligente");
    if (!tableEl) {
      console.warn('Elemento table-manutencao-inteligente não encontrado');
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

    // Funções auxiliares
    const tipoLabel = (tipo) => {
      const tipos = {
        'preventiva': 'Preventiva',
        'corretiva': 'Corretiva',
        'preditiva': 'Preditiva'
      };
      return tipos[tipo] || tipo;
    };

    const tipoBadge = (tipo) => {
      const badges = {
        'preventiva': 'bg-info-subtle text-info',
        'corretiva': 'bg-danger-subtle text-danger',
        'preditiva': 'bg-warning-subtle text-warning'
      };
      return badges[tipo] || 'bg-secondary-subtle text-secondary';
    };

    const statusLabel = (status) => {
      const statuses = {
        'agendada': 'Agendada',
        'em-andamento': 'Em Andamento',
        'concluida': 'Concluída',
        'atrasada': 'Atrasada'
      };
      return statuses[status] || status;
    };

    const statusBadge = (status) => {
      const badges = {
        'agendada': 'bg-info-subtle text-info',
        'em-andamento': 'bg-warning-subtle text-warning',
        'concluida': 'bg-success-subtle text-success',
        'atrasada': 'bg-danger-subtle text-danger'
      };
      return badges[status] || 'bg-secondary-subtle text-secondary';
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '-';
      try {
        const date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
      } catch {
        return dateStr;
      }
    };

    const formatKm = (km) => {
      if (!km || km === 0) return '-';
      return km.toLocaleString('pt-BR');
    };

    // Função para calcular status baseado em data/KM
    const calcularStatus = (item) => {
      if (item.status) return item.status; // Se já vem calculado do backend
      
      const hoje = new Date();
      hoje.setHours(0, 0, 0, 0);
      const dataPrevista = item.data_prevista ? new Date(item.data_prevista + 'T00:00:00') : null;
      const kmAtual = item.km_atual || 0;
      const kmPrevisto = item.km_previsto || 0;

      // Atrasada se data passou ou KM passou
      if (dataPrevista && dataPrevista < hoje) return 'atrasada';
      if (kmPrevisto > 0 && kmPrevisto < kmAtual) return 'atrasada';

      return 'agendada';
    };

    const toRows = (items) => {
      if (!Array.isArray(items)) return [];
      return items.map((item) => {
        if (!item || typeof item !== 'object') return null;
        const status = calcularStatus(item);
        return [
          String(item.id || ''),
          item.veiculo_placa || '-',
          item.veiculo_modelo || '-',
          item.tipo || '-',
          item.servico_nome || '-',
          formatDate(item.data_prevista),
          status,
          String(item.id || ''),
        ];
      }).filter(row => row !== null);
    };

    let grid = null;
    let currentData = [];
    let veiculosData = [];

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
        {
          name: 'Veículo',
          width: '150px',
          formatter: (cell) => {
            return gridjs.html('<span class="badge bg-primary-subtle text-primary">' + cell + '</span>');
          }
        },
        'Modelo',
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
          name: 'Serviço',
          width: '200px'
        },
        {
          name: 'Data Prevista',
          width: '130px'
        },
        {
          name: 'Status',
          width: '140px',
          formatter: (cell) => {
            const badgeClass = statusBadge(cell);
            const label = statusLabel(cell);
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
                <button type="button" class="btn btn-sm btn-outline-primary btn-detalhes-manutencao" data-id="${id}" title="Detalhes">
                  <iconify-icon icon="iconamoon:eye-duotone" class="fs-18"></iconify-icon>
                </button>
                ${row.cells[2].data !== '-' ? `
                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-manutencao" data-id="${id}" title="Editar">
                  <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
                </button>
                ` : ''}
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

    const updateCards = (resumo) => {
      const r = resumo || { atrasadas: 0, agendadas: 0, total: 0 };
      const elAtrasadas = document.getElementById('kpi-manutencao-atrasadas');
      const elAgendadas = document.getElementById('kpi-manutencao-agendadas');
      const elTotal = document.getElementById('kpi-manutencao-total');
      if (elAtrasadas) elAtrasadas.textContent = r.atrasadas ?? 0;
      if (elAgendadas) elAgendadas.textContent = r.agendadas ?? 0;
      if (elTotal) elTotal.textContent = r.total ?? 0;
    };

    const reload = async () => {
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/listar`);
        const data = Array.isArray(json?.data) ? json.data : [];
        renderGrid(data);
        updateCards(json?.resumo);
      } catch (e) {
        console.error('Erro ao recarregar dados:', e);
        renderGrid([]);
        updateCards({ atrasadas: 0, agendadas: 0, total: 0 });
      }
    };

    // Carregar veículos para o select
    const carregarVeiculos = async () => {
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/veiculos/listar`);
        veiculosData = Array.isArray(json?.data) ? json.data : [];
        const selectVeiculo = document.getElementById('man_veiculo_id');
        if (selectVeiculo) {
          selectVeiculo.innerHTML = '<option value="">Selecione um veículo</option>';
          veiculosData.forEach(v => {
            const option = document.createElement('option');
            option.value = v.id;
            option.textContent = `${v.vei_placa} - ${v.vei_modelo}`;
            selectVeiculo.appendChild(option);
          });
        }
      } catch (e) {
        console.error('Erro ao carregar veículos:', e);
      }
    };

    const modalEl = document.getElementById('modalManutencao');
    const formEl = document.getElementById('formManutencao');
    const alertEl = document.getElementById('man-form-alert');
    const btnSave = document.getElementById('btnSalvarManutencao');
    const btnAdd = document.getElementById('btn-add-manutencao');
    const titleEl = document.getElementById('modalManutencaoLabel');

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
      const idEl = document.getElementById('man_id');
      if (idEl) idEl.value = '';
      if (titleEl) titleEl.textContent = 'Cadastrar manutenção';
      btnSave?.querySelector('.btn-text') && (btnSave.querySelector('.btn-text').textContent = 'Adicionar');
    };

    const fillForm = (m) => {
      const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val ?? '';
      };
      setVal('man_id', m.id);
      setVal('man_veiculo_id', m.veiculo_id || m.man_veiculo_id);
      setVal('man_tipo', m.tipo || m.man_tipo);
      setVal('man_data', m.data_prevista || m.man_data);
      setVal('man_km', m.km_previsto || m.man_km);
      setVal('man_obs', m.observacoes || m.man_obs);

      if (titleEl) titleEl.textContent = 'Editar manutenção';
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
      setTimeout(() => document.getElementById('man_veiculo_id')?.focus?.(), 150);
    };

    const openEdit = async (id) => {
      resetForm();
      lockButton(true, 'Carregando...');
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/editar/${id}`);
        fillForm(json.data);
        getBsModal()?.show();
        setTimeout(() => document.getElementById('man_veiculo_id')?.focus?.(), 150);
      } catch (e) {
        setAlert(e.message || 'Erro ao carregar manutenção.');
        getBsModal()?.show();
      } finally {
        lockButton(false);
      }
    };

    const openDetalhes = async (id) => {
      lockButton(true, 'Carregando...');
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/detalhes/${id}`);
        const m = json.data;
        let detalhes = `Veículo: ${m.vei_placa || '-'} - ${m.vei_modelo || '-'}\n`;
        detalhes += `Tipo: ${tipoLabel(m.man_tipo || m.tipo)}\n`;
        detalhes += `Data: ${formatDate(m.man_data || m.data_prevista)}\n`;
        detalhes += `KM: ${formatKm(m.man_km || m.km_previsto)}\n`;
        detalhes += `Status: ${statusLabel(m.man_status || calcularStatus(m))}\n`;
        if (m.man_obs || m.observacoes) {
          detalhes += `\nObservações:\n${m.man_obs || m.observacoes}`;
        }
        alert(detalhes);
      } catch (e) {
        alert('Erro ao carregar detalhes: ' + (e.message || 'Erro desconhecido'));
      } finally {
        lockButton(false);
      }
    };

    const submit = async () => {
      if (!formEl) return;
      setAlert('');

      const required = ['man_veiculo_id', 'man_tipo', 'man_data'];
      for (const r of required) {
        const el = document.getElementById(r);
        if (el && !String(el.value || '').trim()) {
          setAlert('Preencha os campos obrigatórios.');
          return;
        }
      }

      const id = document.getElementById('man_id')?.value || '';
      const fd = new FormData(formEl);

      lockButton(true, id ? 'Salvando...' : 'Adicionando...');
      try {
        const url = id
          ? `${getBaseUrl()}admin/manutencao-inteligente/atualizar/${id}`
          : `${getBaseUrl()}admin/manutencao-inteligente/criar`;

        await fetchJson(url, { method: 'POST', body: fd });
        getBsModal()?.hide();
        await reload();
        resetForm();
      } catch (e) {
        setAlert(e.message || 'Erro ao salvar manutenção.');
      } finally {
        lockButton(false);
      }
    };

    // Filtros
    const aplicarFiltros = () => {
      const filtroVeiculo = document.getElementById('filtro-veiculo')?.value?.toLowerCase() || '';
      const filtroTipo = document.getElementById('filtro-tipo')?.value || '';
      const filtroStatus = document.getElementById('filtro-status')?.value || '';

      let dadosFiltrados = currentData;

      if (filtroVeiculo) {
        dadosFiltrados = dadosFiltrados.filter(item =>
          (item.veiculo_placa || '').toLowerCase().includes(filtroVeiculo) ||
          (item.veiculo_modelo || '').toLowerCase().includes(filtroVeiculo)
        );
      }

      if (filtroTipo) {
        dadosFiltrados = dadosFiltrados.filter(item => (item.tipo || '') === filtroTipo);
      }

      if (filtroStatus) {
        dadosFiltrados = dadosFiltrados.filter(item => calcularStatus(item) === filtroStatus);
      }

      renderGrid(dadosFiltrados);
    };

    btnAdd?.addEventListener('click', openCreate);
    btnSave?.addEventListener('click', submit);

    document.addEventListener('click', (e) => {
      const btnEdit = e.target.closest?.('.btn-edit-manutencao');
      if (btnEdit) {
        const id = btnEdit.getAttribute('data-id');
        if (id) {
          e.preventDefault();
          openEdit(id);
        }
      }

      const btnDetalhes = e.target.closest?.('.btn-detalhes-manutencao');
      if (btnDetalhes) {
        const id = btnDetalhes.getAttribute('data-id');
        if (id) {
          e.preventDefault();
          openDetalhes(id);
        }
      }
    });

    // Aplicar filtros quando mudarem
    document.getElementById('filtro-veiculo')?.addEventListener('input', aplicarFiltros);
    document.getElementById('filtro-tipo')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-status')?.addEventListener('change', aplicarFiltros);

    modalEl?.addEventListener('hidden.bs.modal', () => {
      resetForm();
      lockButton(false);
    });

    // Inicialização
    renderGrid(currentData);
    reload().catch((e) => {
      console.error('Erro ao carregar dados iniciais:', e);
    });
    carregarVeiculos();
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
