(() => {
  const tableEl = document.getElementById("table-financeiro");
  if (!tableEl) return;

  // Helper para garantir base URL com barra final
  const getBaseUrl = () => {
    const base = window.__BASE_URL__ || window.location.origin;
    return base.endsWith('/') ? base : base + '/';
  };

  // Tradução para Português do Brasil
  const ptBR = {
    search: { placeholder: "Digite uma palavra-chave..." },
    pagination: {
      previous: "Anterior",
      next: "Próximo",
      showing: "Mostrando",
      to: "a",
      of: "de",
      results: "resultados",
    },
  };

  const bootstrapData = window.__FINANCEIRO_BOOTSTRAP__ || {};

  let allData = Array.isArray(bootstrapData.lancamentos) ? bootstrapData.lancamentos : [];
  let grid = null;
  let filtrosInitialized = false;
  let filtrosAtivos = false;

  const tipoLabel = (tipo) => (tipo === "despesa" ? "Despesa" : "Receita");
  const tipoBadge = (tipo) =>
    tipo === "despesa" ? "bg-danger-subtle text-danger" : "bg-success-subtle text-success";

  const statusLabel = (status) => {
    switch (status) {
      case "pago":
        return "Pago";
      case "cancelado":
        return "Cancelado";
      default:
        return "Pendente";
    }
  };

  const statusBadge = (status) => {
    if (status === "pago") return "bg-success-subtle text-success";
    if (status === "cancelado") return "bg-danger-subtle text-danger";
    return "bg-warning-subtle text-warning";
  };

  const toDateBR = (iso) => {
    if (!iso) return "-";
    const s = String(iso).slice(0, 10);
    const parts = s.split("-");
    if (parts.length !== 3) return s;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  };

  const toMoneyNumber = (value) => {
    if (value === null || value === undefined || value === "") return 0;
    const n = Number(value);
    return Number.isFinite(n) ? n : 0;
  };

  const toMoneyBR = (value) =>
    toMoneyNumber(value).toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

  const valorLancamento = (l) => {
    const status = String(l?.lan_status || "pendente");
    if (status === "pago" && l?.lan_valor_pago !== null && l?.lan_valor_pago !== "") {
      return toMoneyNumber(l.lan_valor_pago);
    }
    return toMoneyNumber(l?.lan_valor);
  };

  const toGridRows = (items) =>
    (items || []).map((l) => [
      String(l.id),
      l.lan_data_vencimento || l.lan_data_lancamento || "",
      l.lan_tipo || "receita",
      l.categoria_nome || "-",
      l.lan_descricao || "-",
      valorLancamento(l),
      l.lan_status || "pendente",
      String(l.id), // ações
    ]);

  const computeCardsMesAtual = (items) => {
    const mes = new Date().getMonth() + 1;
    const ano = new Date().getFullYear();

    let receitas = 0;
    let despesas = 0;
    // Mesmo critério do backend: data = pagamento || vencimento || lançamento
    const dataBase = (l) => (l.lan_data_pagamento || l.lan_data_vencimento || l.lan_data_lancamento || "").slice(0, 10);

    (items || []).forEach((l) => {
      if (String(l.lan_status || "") !== "pago") return;
      const base = dataBase(l);
      if (!base) return;
      const parts = base.split("-");
      if (parts.length < 2) return;
      const y = Number(parts[0]);
      const m = Number(parts[1]);
      if (y !== ano || m !== mes) return;

      const v = valorLancamento(l);
      if (String(l.lan_tipo) === "despesa") despesas += v;
      else receitas += v;
    });

    return { receitas, despesas, lucro: receitas - despesas };
  };

  const updateCards = (items) => {
    const totals = computeCardsMesAtual(items);
    const elReceitas = document.getElementById("kpi-receitas-mes-atual");
    const elDespesas = document.getElementById("kpi-despesas-mes-atual");
    const elLucro = document.getElementById("kpi-lucro-mes-atual");
    if (elReceitas) elReceitas.textContent = toMoneyBR(totals.receitas);
    if (elDespesas) elDespesas.textContent = toMoneyBR(totals.despesas);
    if (elLucro) elLucro.textContent = toMoneyBR(totals.lucro);
  };

  const fetchJson = async (url, options = {}) => {
    const res = await fetch(url, {
      headers: { "X-Requested-With": "XMLHttpRequest" },
      ...options,
    });
    const json = await res.json().catch(() => null);
    if (!res.ok) {
      const msg = json?.message || "Erro na requisição.";
      throw new Error(msg);
    }
    return json;
  };

  const setButtonLoading = (btn, loading) => {
    if (!btn) return;
    const label = btn.querySelector(".btn-label");

    if (loading) {
      btn.disabled = true;
      if (label && !btn.dataset.originalLabel) btn.dataset.originalLabel = label.textContent || "";
      if (label) label.textContent = "Salvando...";

      if (!btn.querySelector(".spinner-border")) {
        const sp = document.createElement("span");
        sp.className = "spinner-border spinner-border-sm me-2";
        sp.setAttribute("role", "status");
        sp.setAttribute("aria-hidden", "true");
        btn.insertBefore(sp, btn.firstChild);
      }
      return;
    }

    btn.disabled = false;
    const sp = btn.querySelector(".spinner-border");
    if (sp) sp.remove();
    if (label && btn.dataset.originalLabel !== undefined) {
      label.textContent = btn.dataset.originalLabel;
      delete btn.dataset.originalLabel;
    }
  };

  const setModalMode = (tipo, mode) => {
    const isReceita = tipo === "receita";
    const titleEl = document.getElementById(isReceita ? "modalReceitaLabel" : "modalDespesaLabel");
    const btn = document.getElementById(isReceita ? "btnSalvarReceita" : "btnSalvarDespesa");
    const label = btn?.querySelector(".btn-label");

    if (mode === "edit") {
      if (titleEl) titleEl.textContent = isReceita ? "Editar Receita" : "Editar Despesa";
      if (label) label.textContent = "Salvar";
    } else {
      if (titleEl) titleEl.textContent = isReceita ? "Receita" : "Despesa";
      if (label) label.textContent = isReceita ? "+ Receita" : "- Despesa";
    }
  };

  const setCamposAdicionaisVisiveis = (tipo, visible) => {
    const isReceita = tipo === "receita";
    const camposEl = document.getElementById(isReceita ? "receitaCamposAdicionais" : "despesaCamposAdicionais");
    const toggleEl = document.getElementById(isReceita ? "toggleReceitaCampos" : "toggleDespesaCampos");
    if (!camposEl || !toggleEl) return;

    camposEl.style.display = visible ? "block" : "none";
    toggleEl.innerHTML = visible
      ? '<iconify-icon icon="iconamoon:arrow-up-2-duotone"></iconify-icon> Ver menos'
      : '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
  };

  const preencherModal = (tipo, l) => {
    const isReceita = tipo === "receita";
    const prefix = isReceita ? "receita" : "despesa";

    const setVal = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value ?? "";
    };

    setVal(`${prefix}_id`, l.id);
    setVal(`${prefix}_categoria`, l.lan_categoria_id ?? "");
    setVal(`${prefix}_descricao`, l.lan_descricao ?? "");
    setVal(`${prefix}_data_vencimento`, (l.lan_data_vencimento || "").slice(0, 10));
    setVal(`${prefix}_valor`, toMoneyBR(l.lan_valor ?? 0));
    setVal(`${prefix}_locacao`, l.lan_locacao_id ?? "");

    // Veículo: aqui só populamos placa se você estiver armazenando/retornando isso. Caso não, mantém vazio.
    setVal(`${prefix}_veiculo`, "");

    const marcarEl = document.getElementById(isReceita ? "receita_marcar_recebida" : "despesa_marcar_paga");
    if (marcarEl) marcarEl.checked = String(l.lan_status || "") === "pago";

    // Campos adicionais
    setVal(`${prefix}_data_lancamento`, (l.lan_data_lancamento || "").slice(0, 10));
    setVal(`${prefix}_data_pagamento`, (l.lan_data_pagamento || "").slice(0, 10));
    setVal(`${prefix}_valor_pago`, l.lan_valor_pago !== null && l.lan_valor_pago !== "" ? toMoneyBR(l.lan_valor_pago) : "");
    setVal(`${prefix}_forma_pagamento`, l.lan_forma_pagamento ?? "");
    setVal(`${prefix}_referencia`, l.lan_referencia ?? "");
    setVal(`${prefix}_obs`, l.lan_obs ?? "");

    const hasAdicionais =
      !!(l.lan_data_lancamento || l.lan_data_pagamento || l.lan_valor_pago || l.lan_forma_pagamento || l.lan_referencia || l.lan_obs);
    setCamposAdicionaisVisiveis(tipo, hasAdicionais);

    // Reaplicar máscaras (jQuery Mask Plugin)
    if (typeof $ !== "undefined" && $.fn.mask) {
      $(`.money`).mask("000.000.000.000.000,00", { reverse: true });
    }
  };

  const openModal = async (tipo, id = null) => {
    const isReceita = tipo === "receita";
    const modalEl = document.getElementById(isReceita ? "modalReceita" : "modalDespesa");
    const formEl = document.getElementById(isReceita ? "formReceita" : "formDespesa");
    if (!modalEl || !formEl) return;

    // reset sempre (garante estado limpo)
    formEl.reset();
    setCamposAdicionaisVisiveis(tipo, false);

    if (!id) {
      setModalMode(tipo, "new");
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      return;
    }

    setModalMode(tipo, "edit");
    const json = await fetchJson(`${getBaseUrl()}admin/financeiro/editar/${id}`);
    preencherModal(tipo, json.data || {});

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  };

  const save = async (tipo) => {
    const isReceita = tipo === "receita";
    const formEl = document.getElementById(isReceita ? "formReceita" : "formDespesa");
    const modalEl = document.getElementById(isReceita ? "modalReceita" : "modalDespesa");
    const btn = document.getElementById(isReceita ? "btnSalvarReceita" : "btnSalvarDespesa");
    const idInput = document.getElementById(isReceita ? "receita_id" : "despesa_id");
    if (!formEl || !modalEl) return;

    if (!formEl.checkValidity()) {
      formEl.reportValidity();
      return;
    }

    const fd = new FormData(formEl);

    // Garantir que lan_tipo seja enviado explicitamente
    fd.set("lan_tipo", tipo);

    // Normalizar dinheiro (BR -> decimal)
    const stripMoney = (s) => String(s || "").replace(/[^\d,]/g, "").replace(",", ".");
    const valorInput = document.getElementById(isReceita ? "receita_valor" : "despesa_valor");
    const valorPagoInput = document.getElementById(isReceita ? "receita_valor_pago" : "despesa_valor_pago");
    if (valorInput) fd.set("lan_valor", stripMoney(valorInput.value) || "0");
    if (valorPagoInput && valorPagoInput.value) fd.set("lan_valor_pago", stripMoney(valorPagoInput.value));

    // Checkbox define status pago e data_pagamento (server também reforça)
    const chk = document.getElementById(isReceita ? "receita_marcar_recebida" : "despesa_marcar_paga");
    const dataPagamentoEl = document.getElementById(isReceita ? "receita_data_pagamento" : "despesa_data_pagamento");
    if (chk?.checked) {
      fd.set("lan_status", "pago");
      if (dataPagamentoEl && !dataPagamentoEl.value) {
        dataPagamentoEl.value = new Date().toISOString().slice(0, 10);
      }
      if (dataPagamentoEl?.value) fd.set("lan_data_pagamento", dataPagamentoEl.value);
    } else {
      fd.set("lan_status", "pendente");
    }

    const id = idInput?.value ? String(idInput.value) : "";
    const url = id
      ? `${getBaseUrl()}admin/financeiro/atualizar/${id}`
      : `${getBaseUrl()}admin/financeiro/criar`;

    try {
      setButtonLoading(btn, true);
      const json = await fetchJson(url, { method: "POST", body: fd });

      const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.hide();

      await reload();
      alert(json?.message || "Salvo com sucesso.");
    } catch (e) {
      const errorMsg = e?.message || "Erro ao salvar.";
      console.error("Erro ao salvar lançamento:", e);
      alert(errorMsg);
    } finally {
      setButtonLoading(btn, false);
    }
  };

  const renderGrid = (items) => {
    const rows = toGridRows(items);

    const columns = [
      {
        name: "ID",
        width: "80px",
        formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`),
      },
      {
        name: "Vencimento",
        width: "130px",
        formatter: (cell) => gridjs.html(`<span class="text-muted">${toDateBR(cell)}</span>`),
      },
      {
        name: "Tipo",
        width: "120px",
        formatter: (cell) =>
          gridjs.html(`<span class="badge ${tipoBadge(cell)}">${tipoLabel(cell)}</span>`),
      },
      "Categoria",
      { name: "Descrição", width: "250px" },
      {
        name: "Valor",
        width: "140px",
        formatter: (cell) => gridjs.html(`<span class="fw-semibold">R$ ${toMoneyBR(cell)}</span>`),
      },
      {
        name: "Status",
        width: "140px",
        formatter: (cell) =>
          gridjs.html(`<span class="badge ${statusBadge(cell)}">${statusLabel(cell)}</span>`),
      },
      {
        name: "Ações",
        width: "280px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          const tipo = row.cells[2].data;
          const status = row.cells[6].data;
          const lancamento = (allData || []).find((l) => String(l.id) === String(id));
          const isPendente = status === "pendente";
          const canPrintInvoice = status === "pago" && Number(lancamento?.lan_locacao_id || 0) > 0;
          
          return gridjs.html(`
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-lancamento" data-id="${id}" data-tipo="${tipo}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
              ${isPendente ? `
                <button type="button" class="btn btn-sm btn-success btn-efetuar-pagamento" data-id="${id}" title="Efetuar Pagamento">
                  <iconify-icon icon="iconamoon:check-circle-1-duotone" class="fs-18"></iconify-icon>
                </button>
              ` : ''}
              ${canPrintInvoice ? `
                <button type="button" class="btn btn-sm btn-outline-dark btn-imprimir-fatura" data-id="${id}" title="Imprimir fatura">
                  <iconify-icon icon="iconamoon:printer-duotone" class="fs-18"></iconify-icon>
                </button>
              ` : ''}
              <button type="button" class="btn btn-sm btn-outline-danger btn-excluir-lancamento" data-id="${id}" title="Excluir">
                <iconify-icon icon="iconamoon:trash-duotone" class="fs-18"></iconify-icon>
              </button>
            </div>
          `);
        },
      },
    ];

    if (!grid) {
      grid = new gridjs.Grid({
        columns,
        pagination: { limit: 10 },
        sort: true,
        search: true,
        language: ptBR,
        data: rows,
      }).render(tableEl);

      // Setup filtros/UI após primeira renderização
      setupFiltrosUI();
      return;
    }

    grid.updateConfig({ columns, data: rows }).forceRender();
  };

  const applyFilters = () => {
    const tipoSel = document.getElementById("filtro-tipo");
    const statusSel = document.getElementById("filtro-status");
    const tipo = tipoSel?.value || "";
    const status = statusSel?.value || "";

    const filtered = (allData || []).filter((l) => {
      if (tipo && String(l.lan_tipo) !== tipo) return false;
      if (status && String(l.lan_status) !== status) return false;
      return true;
    });

    renderGrid(filtered);
  };

  const setupFiltrosUI = () => {
    if (filtrosInitialized) return;
    filtrosInitialized = true;

    const btnFiltros = document.getElementById("btn-filtros");
    const filtrosContainer = document.getElementById("filtros-container");
    const tipoSel = document.getElementById("filtro-tipo");
    const statusSel = document.getElementById("filtro-status");

    // mover filtros para a mesma linha do search
    setTimeout(() => {
      const gridSearchWrapper = tableEl.querySelector(".gridjs-search")?.parentElement;
      if (gridSearchWrapper && filtrosContainer) {
        gridSearchWrapper.appendChild(filtrosContainer);
      }
    }, 0);

    if (btnFiltros && filtrosContainer) {
      btnFiltros.addEventListener("click", () => {
        filtrosAtivos = !filtrosAtivos;
        filtrosContainer.style.display = filtrosAtivos ? "inline-flex" : "none";
        btnFiltros.classList.toggle("btn-primary", filtrosAtivos);
        btnFiltros.classList.toggle("btn-outline-primary", !filtrosAtivos);
      });
    }

    tipoSel?.addEventListener("change", applyFilters);
    statusSel?.addEventListener("change", applyFilters);
  };

  const reload = async () => {
    const json = await fetchJson(`${getBaseUrl()}admin/financeiro/listar`);
    allData = json.data || [];
    updateCards(allData);
    applyFilters();
  };

  const excluirLancamento = async (id) => {
    if (!confirm("Deseja realmente excluir este lançamento? Esta ação não pode ser desfeita.")) {
      return;
    }
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/financeiro/excluir/${id}`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      await reload();
      alert(json?.message || "Lançamento excluído com sucesso.");
    } catch (e) {
      const errorMsg = e?.message || "Erro ao excluir lançamento.";
      console.error("Erro ao excluir:", e);
      alert(errorMsg);
    }
  };

  // Delegação: click no botão editar, pagamento ou excluir dentro do grid
  tableEl.addEventListener("click", (e) => {
    const btnEdit = e.target?.closest?.(".btn-edit-lancamento");
    if (btnEdit) {
      e.preventDefault();
      const id = btnEdit.getAttribute("data-id");
      const tipo = btnEdit.getAttribute("data-tipo") || "receita";
      openModal(tipo, id);
      return;
    }
    const btnPagamento = e.target?.closest?.(".btn-efetuar-pagamento");
    if (btnPagamento) {
      e.preventDefault();
      const id = btnPagamento.getAttribute("data-id");
      if (id) openModalPagamento(id);
      return;
    }
    const btnExcluir = e.target?.closest?.(".btn-excluir-lancamento");
    if (btnExcluir) {
      e.preventDefault();
      const id = btnExcluir.getAttribute("data-id");
      if (id) excluirLancamento(id);
      return;
    }
    const btnImprimirFatura = e.target?.closest?.(".btn-imprimir-fatura");
    if (btnImprimirFatura) {
      e.preventDefault();
      const id = btnImprimirFatura.getAttribute("data-id");
      if (id) {
        window.open(`${getBaseUrl()}admin/financeiro/fatura/${id}`, "_blank", "noopener");
      }
    }
  });

  // Reset UI ao fechar modais
  const modalReceitaEl = document.getElementById("modalReceita");
  const modalDespesaEl = document.getElementById("modalDespesa");
  modalReceitaEl?.addEventListener("hidden.bs.modal", () => setModalMode("receita", "new"));
  modalDespesaEl?.addEventListener("hidden.bs.modal", () => setModalMode("despesa", "new"));

  const openModalPagamento = (id) => {
    const modalEl = document.getElementById("modalPagamento");
    if (!modalEl) return;

    // Buscar dados do lançamento para preencher informações
    const lancamento = allData.find((l) => String(l.id) === String(id));
    
    // Preencher informações do lançamento no modal
    const descricaoEl = document.getElementById("pagamento_descricao");
    const valorEl = document.getElementById("pagamento_valor");
    const dataPagamentoEl = document.getElementById("pagamento_data_pagamento");
    const valorPagoEl = document.getElementById("pagamento_valor_pago");
    
    if (descricaoEl && lancamento) {
      descricaoEl.textContent = lancamento.lan_descricao || "-";
    }
    
    if (valorEl && lancamento) {
      valorEl.textContent = `R$ ${toMoneyBR(lancamento.lan_valor || 0)}`;
    }
    
    if (dataPagamentoEl) {
      dataPagamentoEl.value = new Date().toISOString().slice(0, 10);
    }
    
    if (valorPagoEl && lancamento) {
      valorPagoEl.value = toMoneyBR(lancamento.lan_valor || 0);
    }

    // Armazenar ID do lançamento
    const idInput = document.getElementById("pagamento_id");
    if (idInput) {
      idInput.value = id;
    }

    // Limpar campos opcionais
    const formaPagamentoEl = document.getElementById("pagamento_forma_pagamento");
    const referenciaEl = document.getElementById("pagamento_referencia");
    if (formaPagamentoEl) formaPagamentoEl.value = "";
    if (referenciaEl) referenciaEl.value = "";

    // Reaplicar máscara monetária
    if (typeof $ !== "undefined" && $.fn.mask && valorPagoEl) {
      $(valorPagoEl).mask("000.000.000.000.000,00", { reverse: true });
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  };

  const efetuarPagamento = async () => {
    const idInput = document.getElementById("pagamento_id");
    const dataPagamentoEl = document.getElementById("pagamento_data_pagamento");
    const valorPagoEl = document.getElementById("pagamento_valor_pago");
    const btn = document.getElementById("btnEfetuarPagamento");
    const modalEl = document.getElementById("modalPagamento");

    if (!idInput || !dataPagamentoEl || !modalEl) return;

    const id = idInput.value;
    const dataPagamento = dataPagamentoEl.value;

    if (!dataPagamento) {
      alert("Informe a data do pagamento.");
      dataPagamentoEl.focus();
      return;
    }

    const fd = new FormData();
    fd.set("lan_data_pagamento", dataPagamento);

    // Normalizar valor pago
    if (valorPagoEl && valorPagoEl.value) {
      const stripMoney = (s) => String(s || "").replace(/[^\d,]/g, "").replace(",", ".");
      fd.set("lan_valor_pago", stripMoney(valorPagoEl.value));
    }

    // Forma de pagamento (opcional)
    const formaPagamentoEl = document.getElementById("pagamento_forma_pagamento");
    if (formaPagamentoEl && formaPagamentoEl.value) {
      fd.set("lan_forma_pagamento", formaPagamentoEl.value);
    }

    // Referência (opcional)
    const referenciaEl = document.getElementById("pagamento_referencia");
    if (referenciaEl && referenciaEl.value) {
      fd.set("lan_referencia", referenciaEl.value.trim());
    }

    try {
      setButtonLoading(btn, true);
      const json = await fetchJson(`${getBaseUrl()}admin/financeiro/efetuar-pagamento/${id}`, {
        method: "POST",
        body: fd,
      });

      const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.hide();

      await reload();
      alert(json?.message || "Pagamento efetuado com sucesso.");
    } catch (e) {
      const errorMsg = e?.message || "Erro ao efetuar pagamento.";
      console.error("Erro ao efetuar pagamento:", e);
      alert(errorMsg);
    } finally {
      setButtonLoading(btn, false);
    }
  };

  // Expor funções globais usadas pela view
  window.abrirModalReceita = (id = null) => openModal("receita", id);
  window.abrirModalDespesa = (id = null) => openModal("despesa", id);
  window.salvarReceita = () => save("receita");
  window.salvarDespesa = () => save("despesa");
  window.efetuarPagamento = efetuarPagamento;

  // Primeira renderização: usar totais do servidor (já corretos por mês) em vez de recalcular no cliente
  const cards = bootstrapData.cards || {};
  if (cards.receitasMesAtual != null || cards.despesasMesAtual != null || cards.lucroMesAtual != null) {
    const elR = document.getElementById("kpi-receitas-mes-atual");
    const elD = document.getElementById("kpi-despesas-mes-atual");
    const elL = document.getElementById("kpi-lucro-mes-atual");
    if (elR && cards.receitasMesAtual != null) elR.textContent = toMoneyBR(cards.receitasMesAtual);
    if (elD && cards.despesasMesAtual != null) elD.textContent = toMoneyBR(cards.despesasMesAtual);
    if (elL && cards.lucroMesAtual != null) elL.textContent = toMoneyBR(cards.lucroMesAtual);
  } else {
    updateCards(allData);
  }
  renderGrid(allData);
})();
