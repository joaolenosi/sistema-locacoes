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
          item.origem || 'manutencao',
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
          name: '',
          width: '1px',
          formatter: () => ''
        },
        {
          name: 'Ações',
          width: '220px',
          formatter: (_cell, row) => {
            const id = row.cells[0].data;
            const origem = row.cells[7].data;
            const placa = (row.cells[1]?.data || '').toString().replace(/"/g, '&quot;');
            const isManutencao = origem === 'manutencao';
            const iconTrash = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
            const btnExcluir = isManutencao
              ? `<button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center btn-delete-manutencao-inteligente" data-id="${id}" data-placa="${placa}" title="Excluir">${iconTrash}</button>`
              : '';
            return gridjs.html(`
              <div class="d-flex gap-1 align-items-center flex-nowrap">
                <button type="button" class="btn btn-sm btn-outline-success btn-completar-manutencao" data-id="${id}" data-origem="${origem}" title="Marcar como realizada">
                  <iconify-icon icon="mdi:check-circle" class="fs-18"></iconify-icon>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary btn-detalhes-manutencao" data-id="${id}" title="Detalhes">
                  <iconify-icon icon="iconamoon:eye-duotone" class="fs-18"></iconify-icon>
                </button>
                ${row.cells[2].data !== '-' ? `
                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-manutencao" data-id="${id}" title="Editar">
                  <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
                </button>
                ` : ''}
                ${btnExcluir}
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
    const buscarKmAtualVeiculo = async (veiculoId) => {
      if (!veiculoId || veiculoId < 1) return null;
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/km-atual/${veiculoId}`);
        return json?.km_atual || null;
      } catch (e) {
        console.error('Erro ao buscar KM atual do veículo:', e);
        return null;
      }
    };

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
          
          // Adicionar listener para buscar KM atual ao selecionar veículo
          selectVeiculo.addEventListener('change', async function() {
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

    const modalEl = document.getElementById('modalManutencao');
    const formEl = document.getElementById('formManutencao');
    const alertEl = document.getElementById('man-form-alert');
    const btnSave = document.getElementById('btnSalvarManutencao');
    const btnAdd = document.getElementById('btn-add-manutencao');
    const titleEl = document.getElementById('modalManutencaoLabel');
    let itensCadastroInteligente = [];

    const formatMoney = (v) => 'R$ ' + (typeof v === 'number' ? v : parseFloat(v || 0)).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const renderItensCadastroInteligente = () => {
      const tbody = document.getElementById('tbody-itens-cadastro-inteligente');
      const totalEl = document.getElementById('man-total-cadastro-inteligente');
      if (!tbody) return;
      let total = 0;
      tbody.innerHTML = '';
      itensCadastroInteligente.forEach((item, idx) => {
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
          '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remover-item-cadastro-inteligente" data-idx="' + idx + '">×</button></td>';
        tbody.appendChild(tr);
      });
      if (itensCadastroInteligente.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="6" class="text-muted text-center py-2">Nenhum item. Clique em Adicionar item.</td>';
        tbody.appendChild(tr);
      }
      if (totalEl) totalEl.textContent = formatMoney(total);
    };

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
      itensCadastroInteligente = [];
      renderItensCadastroInteligente();
      const idEl = document.getElementById('man_id');
      if (idEl) idEl.value = '';
      const triggerTipoEl = document.getElementById('man_trigger_tipo');
      if (triggerTipoEl) triggerTipoEl.value = 'qualquer';
      const triggerCheckbox = document.getElementById('man_trigger_qualquer');
      if (triggerCheckbox) triggerCheckbox.checked = true;
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
      setVal('man_km_atual', m.man_km_atual || m.km_atual || '');
      setVal('man_obs', m.observacoes || m.man_obs);
      
      // Preencher switch baseado em man_trigger_tipo
      const triggerTipo = m.man_trigger_tipo || 'qualquer';
      const triggerCheckbox = document.getElementById('man_trigger_qualquer');
      const triggerTipoEl = document.getElementById('man_trigger_tipo');
      if (triggerCheckbox) triggerCheckbox.checked = (triggerTipo === 'qualquer');
      if (triggerTipoEl) triggerTipoEl.value = triggerTipo;

      itensCadastroInteligente = Array.isArray(m.itens) ? m.itens.map(i => ({
        id: i.id,
        mai_descricao: i.mai_descricao,
        mai_tipo_item: i.mai_tipo_item,
        mai_quantidade: i.mai_quantidade,
        mai_valor_unitario: i.mai_valor_unitario,
        mai_valor_total: i.mai_valor_total,
        mai_produto_id: i.mai_produto_id,
        mai_servico_id: i.mai_servico_id
      })) : [];
      renderItensCadastroInteligente();

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
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/detalhes/${id}`);
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

    const buildDetalhesHtml = (m) => {
      const veiculo = (m.vei_placa || m.veiculo_placa || '-') + ' — ' + (m.vei_modelo || m.veiculo_modelo || '-');
      const tipo = tipoLabel(m.man_tipo || m.tipo);
      const data = formatDate(m.man_data || m.data_prevista);
      const km = formatKm(m.man_km || m.km_previsto);
      const statusCalc = calcularStatus(m);
      const status = statusLabel(statusCalc);
      const obs = m.man_obs || m.observacoes || '';
      const servico = m.servico_nome || 'Manutenção agendada';
      const obsEscaped = obs ? obs.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : '';
      const itens = Array.isArray(m.itens) ? m.itens : [];
      const manTotal = parseFloat(m.man_total || 0);
      const podeEditarItens = ['aberta', 'rascunho'].includes(m.man_status || '');
      const isManutencao = !!m.id && Array.isArray(m.itens); // detalhes API retorna itens só para manutenções

      let itensHtml = '';
      if (isManutencao) {
        itensHtml = `
          <div class="pt-2 border-top" data-manutencao-id="${m.id}">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Produtos e Serviços</span>
              ${podeEditarItens ? `<button type="button" class="btn btn-sm btn-primary btn-adicionar-item-inteligente" data-man-id="${m.id}"><iconify-icon icon="iconamoon:plus-duotone"></iconify-icon> Adicionar item</button>` : ''}
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Descrição</th><th class="text-center">Tipo</th><th class="text-center">Qtd</th><th class="text-end">Valor</th><th class="text-end">Total</th>${podeEditarItens ? '<th style="width:50px;"></th>' : ''}</tr></thead>
                <tbody id="detalhes-itens-tbody">
                  ${itens.map((i) => {
                    const tipoItem = (i.mai_tipo_item || '') === 'produto' ? 'Produto' : 'Serviço';
                    const tipoCls = (i.mai_tipo_item || '') === 'produto' ? 'info' : 'success';
                    return `<tr data-item-id="${i.id}"><td>${String(i.mai_descricao || '-').replace(/</g, '&lt;')}</td><td class="text-center"><span class="badge bg-${tipoCls}-subtle text-${tipoCls}">${tipoItem}</span></td><td class="text-center">${i.mai_quantidade || 1}</td><td class="text-end">${formatMoney(i.mai_valor_unitario)}</td><td class="text-end fw-semibold">${formatMoney(i.mai_valor_total)}</td>${podeEditarItens ? `<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remover-item-inteligente" data-item-id="${i.id}" title="Remover">×</button></td>` : ''}</tr>`;
                  }).join('')}
                  ${itens.length === 0 ? `<tr><td colspan="${podeEditarItens ? 6 : 5}" class="text-muted text-center py-2">Nenhum item. Clique em Adicionar item.</td></tr>` : ''}
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-end mt-2">
              <span class="fw-bold">Total: <span id="detalhes-man-total">${formatMoney(manTotal)}</span></span>
            </div>
            <div class="mt-2">
              <a href="${getBaseUrl()}admin/manutencao/${m.id}/pdf" target="_blank" class="btn btn-sm btn-outline-primary"><iconify-icon icon="iconamoon:printer-duotone"></iconify-icon> Imprimir / PDF</a>
            </div>
          </div>
        `;
      }

      let html = `
        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">Veículo</span>
            <span class="fw-medium">${veiculo}</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">Serviço / Item</span>
            <span>${servico}</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">Tipo</span>
            <span class="badge ${tipoBadge(m.man_tipo || m.tipo)}">${tipo}</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">Data prevista</span>
            <span>${data}</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">KM previsto</span>
            <span>${km}</span>
          </div>
          <div class="d-flex align-items-center">
            <span class="text-muted me-2" style="min-width: 120px;">Status</span>
            <span class="badge ${statusBadge(statusCalc)}">${status}</span>
          </div>
          ${obsEscaped ? `
          <div class="pt-2 border-top">
            <span class="text-muted d-block small mb-1">Observações</span>
            <p class="mb-0 text-break">${obsEscaped}</p>
          </div>
          ` : ''}
          ${itensHtml}
        </div>
      `;
      return html;
    };

    const openDetalhes = async (id) => {
      const modalEl = document.getElementById('modalDetalhesManutencao');
      const bodyEl = document.getElementById('detalhes-manutencao-body');
      if (!modalEl || !bodyEl) return;

      const showLoading = () => {
        bodyEl.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Carregando...</div>';
      };

      const escapeHtml = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      const showError = (msg) => {
        bodyEl.innerHTML = '<div class="alert alert-danger mb-0">' + escapeHtml(msg || 'Erro ao carregar detalhes.') + '</div>';
      };

      const modalInstance = window.bootstrap?.Modal ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
      if (modalInstance) modalInstance.show();
      showLoading();

      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/detalhes/${id}`);
        const m = json.data;
        bodyEl.innerHTML = buildDetalhesHtml(m);
      } catch (e) {
        const item = currentData.find((row) => String(row.id) === String(id));
        if (item) {
          bodyEl.innerHTML = buildDetalhesHtml(item);
        } else {
          showError(e.message || 'Erro ao carregar detalhes.');
        }
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
      
      // Atualizar campo hidden man_trigger_tipo baseado no checkbox
      const triggerCheckbox = document.getElementById('man_trigger_qualquer');
      const triggerTipoEl = document.getElementById('man_trigger_tipo');
      if (triggerCheckbox && triggerTipoEl) {
        triggerTipoEl.value = triggerCheckbox.checked ? 'qualquer' : 'data';
      }
      
      const fd = new FormData(formEl);

      lockButton(true, id ? 'Salvando...' : 'Adicionando...');
      try {
        let novoId = id;
        if (!id) {
          const criarRes = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/criar`, { method: 'POST', body: fd });
          novoId = criarRes?.id || null;
          if (novoId && itensCadastroInteligente.length > 0) {
            for (const it of itensCadastroInteligente) {
              const payload = {
                tipo_item: it.mai_tipo_item || 'produto',
                quantidade: it.mai_quantidade || 1
              };
              if ((it.mai_tipo_item || '') === 'servico') payload.servico_id = it.mai_servico_id;
              else payload.produto_id = it.mai_produto_id;
              await fetchJson(`${getBaseUrl()}admin/manutencao/${novoId}/itens`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload)
              });
            }
          }
        } else {
          await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/atualizar/${id}`, { method: 'POST', body: fd });
        }
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

    const modalCompletar = document.getElementById('modalCompletarManutencao');
    const formCompletar = document.getElementById('formCompletarManutencao');
    const alertCompletar = document.getElementById('completar-form-alert');
    const wrapSwitch = document.getElementById('completar-wrap-switch');
    const btnConfirmarCompletar = document.getElementById('btnConfirmarCompletar');

    const setCompletarAlert = (msg) => {
      if (!alertCompletar) return;
      if (!msg) {
        alertCompletar.classList.add('d-none');
        alertCompletar.textContent = '';
        return;
      }
      alertCompletar.textContent = msg;
      alertCompletar.classList.remove('d-none');
    };

    const openCompletar = (id, origem) => {
      const idEl = document.getElementById('completar_id');
      const dataEl = document.getElementById('completar_data_realizacao');
      const kmEl = document.getElementById('completar_km_atual');
      const switchEl = document.getElementById('completar_atualizar_proxima');
      if (idEl) idEl.value = id;
      const hoje = new Date().toISOString().slice(0, 10);
      if (dataEl) dataEl.value = hoje;
      const item = currentData.find((r) => String(r.id) === String(id));
      if (kmEl) kmEl.value = item?.km_atual ?? '';
      if (switchEl) switchEl.checked = origem === 'controle';
      if (wrapSwitch) wrapSwitch.style.display = 'block';
      setCompletarAlert('');
      if (window.bootstrap?.Modal && modalCompletar) {
        window.bootstrap.Modal.getOrCreateInstance(modalCompletar).show();
      }
    };

    const submitCompletar = async () => {
      setCompletarAlert('');
      const id = document.getElementById('completar_id')?.value;
      const dataRealizacao = document.getElementById('completar_data_realizacao')?.value;
      const kmAtual = document.getElementById('completar_km_atual')?.value;
      if (!id || !dataRealizacao || !kmAtual || Number(kmAtual) < 0) {
        setCompletarAlert('Preencha a data e o KM atual.');
        return;
      }
      const atualizarProxima = document.getElementById('completar_atualizar_proxima')?.checked ? 1 : 0;
      if (!btnConfirmarCompletar) return;
      btnConfirmarCompletar.disabled = true;
      try {
        await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/completar/${id}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({
            data_realizacao: dataRealizacao,
            km_atual: Number(kmAtual),
            atualizar_proxima: atualizarProxima
          })
        });
        if (window.bootstrap?.Modal && modalCompletar) {
          window.bootstrap.Modal.getOrCreateInstance(modalCompletar).hide();
        }
        await reload();
      } catch (e) {
        setCompletarAlert(e.message || 'Erro ao marcar como realizada.');
      } finally {
        btnConfirmarCompletar.disabled = false;
      }
    };

    btnConfirmarCompletar?.addEventListener('click', submitCompletar);

    // Carregar produtos e serviços para o modal Adicionar item
    const carregarProdutosServicos = async (forCadastro = false) => {
      const [produtosRes, servicosRes] = await Promise.all([
        fetchJson(`${getBaseUrl()}admin/cadastro/produtos/listar`).catch(() => ({ data: [] })),
        fetchJson(`${getBaseUrl()}admin/cadastro/servicos/listar`).catch(() => ({ data: [] }))
      ]);
      const produtos = Array.isArray(produtosRes?.data) ? produtosRes.data : [];
      const servicos = Array.isArray(servicosRes?.data) ? servicosRes.data : [];
      const prodOpts = '<option value="">Selecione um produto</option>' + produtos.map(p => {
        const preco = parseFloat(p.pro_preco_venda || p.pro_preco_custo || 0);
        return `<option value="${p.id}" data-preco="${preco}">${String(p.pro_nome || '').replace(/</g, '&lt;')} - ${formatMoney(preco)}</option>`;
      }).join('');
      const servOpts = '<option value="">Selecione um serviço</option>' + servicos.map(s => {
        const preco = parseFloat(s.ser_preco_padrao || 0);
        return `<option value="${s.id}" data-preco="${preco}">${String(s.ser_nome || '').replace(/</g, '&lt;')} - ${formatMoney(preco)}</option>`;
      }).join('');
      if (forCadastro) {
        const sp = document.getElementById('cadastro-inteligente-item-produto-id');
        const ss = document.getElementById('cadastro-inteligente-item-servico-id');
        if (sp) sp.innerHTML = prodOpts;
        if (ss) ss.innerHTML = servOpts;
      } else {
        const selectProd = document.getElementById('item-produto-id');
        const selectServ = document.getElementById('item-servico-id');
        if (selectProd) selectProd.innerHTML = prodOpts;
        if (selectServ) selectServ.innerHTML = servOpts;
      }
    };

    const modalAdicionarItem = document.getElementById('modalAdicionarItem');
    const itemTipo = document.getElementById('item-tipo');
    const itemProdutoId = document.getElementById('item-produto-id');
    const itemServicoId = document.getElementById('item-servico-id');
    const itemQuantidade = document.getElementById('item-quantidade');
    const itemValorUnitDisplay = document.getElementById('item-valor-unit-display');
    const itemSubtotalDisplay = document.getElementById('item-subtotal-display');
    const grupoProdutoItem = document.getElementById('grupo-produto-item');
    const grupoServicoItem = document.getElementById('grupo-servico-item');
    const formItemAlert = document.getElementById('form-item-alert');
    const btnSalvarItemInteligente = document.getElementById('btn-salvar-item-inteligente');

    const getValorUnitarioItem = () => {
      const tipo = (itemTipo?.value || 'produto');
      const sel = tipo === 'produto' ? itemProdutoId : itemServicoId;
      const opt = sel?.options?.[sel.selectedIndex];
      return opt ? parseFloat(opt.getAttribute('data-preco') || 0) : 0;
    };

    const atualizarPreviewItem = () => {
      const qtd = parseInt(itemQuantidade?.value || 1, 10) || 1;
      const vu = getValorUnitarioItem();
      const st = qtd * vu;
      if (itemValorUnitDisplay) itemValorUnitDisplay.textContent = formatMoney(vu);
      if (itemSubtotalDisplay) itemSubtotalDisplay.textContent = formatMoney(st);
    };

    const toggleGruposItem = () => {
      const tipo = (itemTipo?.value || 'produto');
      if (grupoProdutoItem) grupoProdutoItem.classList.toggle('d-none', tipo !== 'produto');
      if (grupoServicoItem) grupoServicoItem.classList.toggle('d-none', tipo !== 'servico');
      atualizarPreviewItem();
    };

    const openAdicionarItem = async (manId) => {
      const idEl = document.getElementById('item-manutencao-id');
      if (idEl) idEl.value = manId;
      if (formItemAlert) { formItemAlert.classList.add('d-none'); formItemAlert.textContent = ''; }
      document.getElementById('formAdicionarItem')?.reset?.();
      if (itemQuantidade) itemQuantidade.value = '1';
      await carregarProdutosServicos();
      toggleGruposItem();
      if (window.bootstrap?.Modal && modalAdicionarItem) {
        window.bootstrap.Modal.getOrCreateInstance(modalAdicionarItem).show();
      }
    };

    const refreshDetalhesBody = async (manId) => {
      const bodyEl = document.getElementById('detalhes-manutencao-body');
      if (!bodyEl || !manId) return;
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/manutencao-inteligente/detalhes/${manId}`);
        bodyEl.innerHTML = buildDetalhesHtml(json.data);
      } catch (err) {
        console.error('Erro ao atualizar detalhes:', err);
      }
    };

    const submitAdicionarItem = async () => {
      const manId = document.getElementById('item-manutencao-id')?.value;
      const tipo = (itemTipo?.value || 'produto');
      const produtoId = tipo === 'produto' ? parseInt(itemProdutoId?.value || 0, 10) : 0;
      const servicoId = tipo === 'servico' ? parseInt(itemServicoId?.value || 0, 10) : 0;
      const quantidade = parseInt(itemQuantidade?.value || 1, 10) || 1;
      if (!manId) return;
      if (tipo === 'produto' && produtoId < 1) {
        if (formItemAlert) { formItemAlert.textContent = 'Selecione um produto.'; formItemAlert.classList.remove('d-none'); }
        return;
      }
      if (tipo === 'servico' && servicoId < 1) {
        if (formItemAlert) { formItemAlert.textContent = 'Selecione um serviço.'; formItemAlert.classList.remove('d-none'); }
        return;
      }
      const payload = { tipo_item: tipo, quantidade };
      if (tipo === 'produto') payload.produto_id = produtoId; else payload.servico_id = servicoId;

      if (btnSalvarItemInteligente) btnSalvarItemInteligente.disabled = true;
      const span = btnSalvarItemInteligente?.querySelector('.btn-text');
      const origText = span?.textContent;
      if (span) span.textContent = 'Aguarde...';
      try {
        const res = await fetch(`${getBaseUrl()}admin/manutencao/${manId}/itens`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify(payload)
        });
        const json = await res.json().catch(() => ({}));
        if (json.success) {
          if (window.bootstrap?.Modal && modalAdicionarItem) window.bootstrap.Modal.getOrCreateInstance(modalAdicionarItem).hide();
          await refreshDetalhesBody(manId);
          if (window.toastr) window.toastr.success('Item adicionado.');
        } else {
          if (formItemAlert) { formItemAlert.textContent = json.message || 'Erro ao adicionar.'; formItemAlert.classList.remove('d-none'); }
        }
      } catch (err) {
        if (formItemAlert) { formItemAlert.textContent = 'Erro ao adicionar item.'; formItemAlert.classList.remove('d-none'); }
      } finally {
        if (btnSalvarItemInteligente) btnSalvarItemInteligente.disabled = false;
        if (span) span.textContent = origText || 'Adicionar';
      }
    };

    if (itemTipo) itemTipo.addEventListener('change', toggleGruposItem);
    if (itemProdutoId) itemProdutoId.addEventListener('change', atualizarPreviewItem);
    if (itemServicoId) itemServicoId.addEventListener('change', atualizarPreviewItem);
    if (itemQuantidade) itemQuantidade.addEventListener('input', atualizarPreviewItem);
    btnSalvarItemInteligente?.addEventListener('click', submitAdicionarItem);

    if (modalAdicionarItem) {
      modalAdicionarItem.addEventListener('show.bs.modal', toggleGruposItem);
    }

    // Cadastro inline: Adicionar item (produtos/serviços)
    const itemTipoCadInt = document.getElementById('cadastro-inteligente-item-tipo');
    const itemProdCadInt = document.getElementById('cadastro-inteligente-item-produto-id');
    const itemServCadInt = document.getElementById('cadastro-inteligente-item-servico-id');
    const itemQtdCadInt = document.getElementById('cadastro-inteligente-item-quantidade');
    const getVuCadInt = () => {
      const tipo = (itemTipoCadInt?.value || 'produto');
      const sel = tipo === 'produto' ? itemProdCadInt : itemServCadInt;
      const opt = sel?.options?.[sel.selectedIndex];
      return opt ? parseFloat(opt.getAttribute('data-preco') || 0) : 0;
    };
    const toggleGruposCadInt = () => {
      const tipo = (itemTipoCadInt?.value || 'produto');
      const gp = document.getElementById('cadastro-inteligente-grupo-produto');
      const gs = document.getElementById('cadastro-inteligente-grupo-servico');
      if (gp) gp.classList.toggle('d-none', tipo !== 'produto');
      if (gs) gs.classList.toggle('d-none', tipo !== 'servico');
      if (itemProdCadInt) itemProdCadInt.value = '';
      if (itemServCadInt) itemServCadInt.value = '';
      attPreviewCadInt();
    };
    itemTipoCadInt?.addEventListener('change', () => {
      carregarProdutosServicos(true).then(toggleGruposCadInt);
    });

    document.getElementById('btn-adicionar-item-cadastro-inteligente')?.addEventListener('click', async () => {
      const alertItem = document.getElementById('cadastro-inteligente-form-item-alert');
      if (alertItem) alertItem.classList.add('d-none');
      const manId = document.getElementById('man_id')?.value || '';
      const tipo = (itemTipoCadInt?.value || 'produto');
      const prodId = tipo === 'produto' ? parseInt((itemProdCadInt?.value || 0), 10) : 0;
      const servId = tipo === 'servico' ? parseInt((itemServCadInt?.value || 0), 10) : 0;
      const qtd = parseInt((itemQtdCadInt?.value || 1), 10) || 1;
      if (tipo === 'produto' && prodId < 1) {
        if (alertItem) { alertItem.textContent = 'Selecione um produto.'; alertItem.classList.remove('d-none'); }
        return;
      }
      if (tipo === 'servico' && servId < 1) {
        if (alertItem) { alertItem.textContent = 'Selecione um serviço.'; alertItem.classList.remove('d-none'); }
        return;
      }
      const vu = getVuCadInt();
      const descricao = ((tipo === 'produto' ? itemProdCadInt?.options?.[itemProdCadInt.selectedIndex] : itemServCadInt?.options?.[itemServCadInt.selectedIndex])?.text?.split(' - ')[0]) || '';
      const novoItem = {
        mai_descricao: descricao,
        mai_tipo_item: tipo,
        mai_quantidade: qtd,
        mai_valor_unitario: vu,
        mai_valor_total: qtd * vu,
        mai_produto_id: tipo === 'produto' ? prodId : null,
        mai_servico_id: tipo === 'servico' ? servId : null
      };
      const btnAdd = document.getElementById('btn-adicionar-item-cadastro-inteligente');
      if (manId) {
        if (btnAdd) btnAdd.disabled = true;
        try {
          const res = await fetchJson(`${getBaseUrl()}admin/manutencao/${manId}/itens`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipo_item: tipo, quantidade: qtd, produto_id: prodId || undefined, servico_id: servId || undefined })
          });
          if (res?.success && res?.item) {
            itensCadastroInteligente.push(res.item);
            renderItensCadastroInteligente();
            if (itemQtdCadInt) itemQtdCadInt.value = '1';
            if (window.toastr) window.toastr.success('Item adicionado.');
          } else if (alertItem) { alertItem.textContent = res?.message || 'Erro ao adicionar.'; alertItem.classList.remove('d-none'); }
        } catch (err) {
          if (alertItem) { alertItem.textContent = 'Erro ao adicionar item.'; alertItem.classList.remove('d-none'); }
        } finally {
          if (btnAdd) btnAdd.disabled = false;
        }
      } else {
        itensCadastroInteligente.push(novoItem);
        renderItensCadastroInteligente();
        if (itemQtdCadInt) itemQtdCadInt.value = '1';
      }
    });

    modalEl?.addEventListener('show.bs.modal', () => {
      carregarProdutosServicos(true).then(toggleGruposCadInt);
    });

    document.addEventListener('click', (e) => {
      const btnRmCad = e.target.closest?.('.btn-remover-item-cadastro-inteligente');
      if (btnRmCad) {
        e.preventDefault();
        const idx = parseInt(btnRmCad.getAttribute('data-idx'), 10);
        const manId = document.getElementById('man_id')?.value || '';
        const item = itensCadastroInteligente[idx];
        if (item?.id && manId) {
          btnRmCad.disabled = true;
          fetch(`${getBaseUrl()}admin/manutencao/itens/deletar/${item.id}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' } })
            .then(r => r.json()).then((json) => {
              if (json?.success) {
                itensCadastroInteligente.splice(idx, 1);
                renderItensCadastroInteligente();
                if (window.toastr) window.toastr.success('Item removido.');
              }
            }).finally(() => { btnRmCad.disabled = false; });
        } else {
          itensCadastroInteligente.splice(idx, 1);
          renderItensCadastroInteligente();
        }
        return;
      }
      const btnCompletar = e.target.closest?.('.btn-completar-manutencao');
      if (btnCompletar) {
        const id = btnCompletar.getAttribute('data-id');
        const origem = btnCompletar.getAttribute('data-origem') || 'manutencao';
        if (id) {
          e.preventDefault();
          openCompletar(id, origem);
        }
      }

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

      const btnDeleteMan = e.target.closest?.('.btn-delete-manutencao-inteligente');
      if (btnDeleteMan) {
        e.preventDefault();
        const id = btnDeleteMan.getAttribute('data-id');
        const placa = btnDeleteMan.getAttribute('data-placa') || '';
        if (!id) return;

        const msg = placa ? 'Você está prestes a excluir a manutenção do veículo ' + placa + '.' : 'Você está prestes a excluir esta manutenção.';

        const executeDelete = async () => {
          btnDeleteMan.disabled = true;
          btnDeleteMan.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
          try {
            const res = await fetch(`${getBaseUrl()}admin/manutencao-inteligente/excluir/${id}`, {
              method: 'POST',
              headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
            });
            const json = await res.json().catch(() => null);
            if (res.ok && json?.success) {
              if (typeof Swal !== 'undefined') {
                await Swal.fire({
                  icon: 'success',
                  title: 'Manutenção excluída',
                  text: json.message || 'A manutenção foi excluída com sucesso.',
                  confirmButtonText: 'OK'
                });
              } else {
                alert(json.message || 'Manutenção excluída com sucesso.');
              }
              await reload();
            } else {
              const errMsg = json?.message || 'Não foi possível excluir a manutenção.';
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
                text: 'Erro ao excluir manutenção. Tente novamente.',
                confirmButtonText: 'OK'
              });
            } else {
              alert('Erro ao excluir manutenção. Tente novamente.');
            }
          } finally {
            btnDeleteMan.disabled = false;
            btnDeleteMan.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
          }
        };

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Excluir manutenção?',
            text: msg,
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
          }).then((result) => {
            if (result?.isConfirmed) executeDelete();
          });
        } else if (confirm('Tem certeza que deseja excluir esta manutenção?')) {
          executeDelete();
        }
      }

      const btnAdicionar = e.target.closest?.('.btn-adicionar-item-inteligente');
      if (btnAdicionar) {
        e.preventDefault();
        const manId = btnAdicionar.getAttribute('data-man-id');
        if (manId) openAdicionarItem(manId);
      }

      const btnRemover = e.target.closest?.('.btn-remover-item-inteligente');
      if (btnRemover) {
        e.preventDefault();
        const itemId = btnRemover.getAttribute('data-item-id');
        const wrapper = document.querySelector('[data-manutencao-id]');
        const manId = wrapper?.getAttribute('data-manutencao-id');
        if (!itemId || !manId || !confirm('Remover este item?')) return;
        btnRemover.disabled = true;
        fetch(`${getBaseUrl()}admin/manutencao/itens/deletar/${itemId}`, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
        })
          .then(r => r.json().catch(() => ({})))
          .then(async (json) => {
            if (json.success) {
              await refreshDetalhesBody(manId);
              if (window.toastr) window.toastr.success('Item removido.');
            } else {
              if (window.toastr) window.toastr.error(json.message || 'Erro ao remover.');
              btnRemover.disabled = false;
            }
          })
          .catch(() => {
            if (window.toastr) window.toastr.error('Erro ao remover item.');
            btnRemover.disabled = false;
          });
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
