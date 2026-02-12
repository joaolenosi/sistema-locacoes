(() => {
  const tableEl = document.getElementById("table-locacoes");
  if (!tableEl) return;

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

  const bootstrapData = window.__LOCACOES_BOOTSTRAP__ || {};
  let allData = Array.isArray(bootstrapData.locacoes) ? bootstrapData.locacoes : [];

  let grid = null;
  let filtrosAtivos = false;
  let filtrosInitialized = false;

  let veiculosData = null;
  let locatariosData = null;
  let gridVeiculos = null;
  let gridLocatarios = null;

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
    toMoneyNumber(value).toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const parseDigits = (s) => String(s || "").replace(/\D/g, "");

  const formatCpfCnpj = (digits) => {
    const d = parseDigits(digits);
    if (d.length === 11) return d.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
    if (d.length === 14) return d.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
    return digits ? String(digits) : "-";
  };

  const statusLabel = (s) => {
    switch (s) {
      case "ativa":
        return "Ativa";
      case "atrasada":
        return "Atrasada";
      case "inadimplente":
        return "Inadimplente";
      case "finalizada":
        return "Finalizada";
      case "cancelada":
        return "Cancelada";
      default:
        return "Reservada";
    }
  };

  const statusBadge = (label) => {
    if (label === "Finalizada") return "bg-secondary-subtle text-secondary";
    if (label === "Cancelada") return "bg-danger-subtle text-danger";
    if (label === "Atrasada" || label === "Inadimplente") return "bg-warning-subtle text-warning";
    if (label === "Reservada") return "bg-info-subtle text-info";
    return "bg-success-subtle text-success";
  };

  const updateKpis = (items) => {
    let entradas = 0;
    let saidas = 0;
    let emAtraso = 0;

    (items || []).forEach((l) => {
      const st = String(l.loc_status || revealedDefaultStatus(l));
      if (st === "reservada" || st === "ativa") entradas++;
      if (st === "finalizada" || st === "cancelada") saidas++;
      if (st === "atrasada" || st === "inadimplente") emAtraso++;
    });

    const elE = document.getElementById("kpi-loc-entradas");
    const elS = document.getElementById("kpi-loc-saidas");
    const elA = document.getElementById("kpi-loc-em-atraso");
    if (elE) elE.textContent = String(entradas);
    if (elS) elS.textContent = String(saidas);
    if (elA) elA.textContent = String(emAtraso);
  };

  const revealedDefaultStatus = (l) => l?.loc_status || "reservada";

  const toGridRows = (items) =>
    (items || []).map((l) => [
      String(l.id),
      l.vei_placa || "-",
      l.vei_modelo || "-",
      l.cli_nome || "-",
      l.loc_data_inicio || "",
      l.loc_data_fim_prevista || "",
      toMoneyNumber(l.loc_valor_locacao || l.loc_valor_total || 0),
      l.loc_status || "reservada",
      String(l.id), // ações
    ]);

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

  const setupMasks = () => {
    if (typeof $ !== "undefined" && $.fn.mask) {
      $(".money").mask("000.000.000.000.000,00", { reverse: true });
      $(".money")
        .off("focusout.locacoes")
        .on("focusout.locacoes", function () {
          if ($(this).val().length <= 2 && $(this).val().length > 0) {
            $(this).val($(this).val() + ",00");
          }
        });
    }
  };

  const setupFiltrosUI = () => {
    if (filtrosInitialized) return;
    filtrosInitialized = true;

    const btnFiltros = document.getElementById("btn-filtros");
    const filtrosContainer = document.getElementById("filtros-container");
    const filtroPlaca = document.getElementById("filtro-placa");
    const filtroStatus = document.getElementById("filtro-status");

    setTimeout(() => {
      const gridSearchWrapper = tableEl.querySelector(".gridjs-search")?.parentElement;
      if (gridSearchWrapper && filtrosContainer) {
        gridSearchWrapper.appendChild(filtrosContainer);
      }
    }, 0);

    btnFiltros?.addEventListener("click", () => {
      filtrosAtivos = !filtrosAtivos;
      if (filtrosContainer) filtrosContainer.style.display = filtrosAtivos ? "inline-flex" : "none";
      btnFiltros.classList.toggle("btn-primary", filtrosAtivos);
      btnFiltros.classList.toggle("btn-outline-primary", !filtrosAtivos);
    });

    const onFilter = () => applyFilters();
    filtroPlaca?.addEventListener("input", onFilter);
    filtroStatus?.addEventListener("change", onFilter);
  };

  const applyFilters = () => {
    const filtroPlaca = document.getElementById("filtro-placa")?.value?.trim()?.toUpperCase() || "";
    const filtroStatus = document.getElementById("filtro-status")?.value || "";

    const filtered = (allData || []).filter((l) => {
      if (filtroStatus && String(l.loc_status) !== filtroStatus) return false;
      if (filtroPlaca) {
        const placa = String(l.vei_placa || "").toUpperCase();
        if (!placa.includes(filtroPlaca)) return false;
      }
      return true;
    });

    updateKpis(filtered);
    renderGrid(filtered);
  };

  const renderGrid = (items) => {
    const rows = toGridRows(items);

    const columns = [
      { name: "ID", width: "80px", formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`) },
      {
        name: "Veículo",
        width: "140px",
        formatter: (cell) => gridjs.html(`<span class="badge bg-primary-subtle text-primary">${cell}</span>`),
      },
      { name: "Modelo", width: "160px" },
      { name: "Locatário", width: "220px" },
      { name: "Início", width: "120px", formatter: (cell) => gridjs.html(`<span class="text-muted">${toDateBR(cell)}</span>`) },
      { name: "Fim", width: "120px", formatter: (cell) => gridjs.html(`<span class="text-muted">${toDateBR(cell)}</span>`) },
      {
        name: "Valor",
        width: "140px",
        formatter: (cell) => gridjs.html(`<span class="fw-semibold">R$ ${toMoneyBR(cell)}</span>`),
      },
      {
        name: "Status",
        width: "150px",
        formatter: (cell) => {
          const label = statusLabel(cell);
          return gridjs.html(`<span class="badge ${statusBadge(label)}">${label}</span>`);
        },
      },
      {
        name: "Ações",
        width: "120px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          return gridjs.html(`
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-locacao" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
            </div>
          `);
        },
      },
    ];

    if (!grid) {
      grid = new gridjs.Grid({
        columns,
        pagination: { limit: 5 },
        sort: true,
        search: true,
        language: ptBR,
        data: rows,
      }).render(tableEl);

      setupFiltrosUI();
      return;
    }

    grid.updateConfig({ columns, data: rows }).forceRender();
  };

  const reload = async () => {
    const json = await fetchJson(`${window.location.origin}/admin/locacoes/listar`);
    allData = json.data || [];
    applyFilters();
  };

  const resetLocacaoForm = () => {
    const form = document.getElementById("formLocacao");
    form?.reset();
    document.getElementById("locacao_id").value = "";
    document.getElementById("loc_cli_id").value = "";
    document.getElementById("loc_vei_id").value = "";
    document.getElementById("loc_cli_display").value = "";
    document.getElementById("loc_vei_display").value = "";
    setupMasks();
  };

  const setLocacaoModalMode = (mode) => {
    const titleEl = document.getElementById("modalLocacaoLabel");
    const btn = document.getElementById("btnSalvarLocacao");
    const label = btn?.querySelector(".btn-label");

    if (mode === "edit") {
      if (titleEl) titleEl.textContent = "Editar locação";
      if (label) label.textContent = "Salvar";
    } else {
      if (titleEl) titleEl.textContent = "Cadastrar locação";
      if (label) label.textContent = "Adicionar";
    }
  };

  const preencherLocacaoModal = (l) => {
    document.getElementById("locacao_id").value = l.id ?? "";
    document.getElementById("loc_cli_id").value = l.loc_cli_id ?? "";
    document.getElementById("loc_vei_id").value = l.loc_vei_id ?? "";
    document.getElementById("loc_cli_display").value = l.cli_nome ? `${l.cli_nome} (${formatCpfCnpj(l.cli_cpf_cnpj)})` : "";
    document.getElementById("loc_vei_display").value = l.vei_placa ? `${l.vei_placa} - ${l.vei_modelo || ""}`.trim() : "";

    const setVal = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value ?? "";
    };

    setVal("loc_data_inicio", (l.loc_data_inicio || "").slice(0, 10));
    setVal("loc_data_fim_prevista", (l.loc_data_fim_prevista || "").slice(0, 10));
    setVal("loc_valor_locacao", toMoneyBR(l.loc_valor_locacao ?? 0));
    setVal("loc_valor_caucao", l.loc_valor_caucao !== null && l.loc_valor_caucao !== "" ? toMoneyBR(l.loc_valor_caucao) : "");
    setVal("loc_data_inicio_pagamento", (l.loc_data_inicio_pagamento || "").slice(0, 10));
    setVal("loc_recorrencia_pagamento", l.loc_recorrencia_pagamento ?? "");
    setVal("loc_taxa_juros", l.loc_taxa_juros !== null && l.loc_taxa_juros !== "" ? toMoneyBR(l.loc_taxa_juros) : "");
    setVal("loc_taxa_multa", l.loc_taxa_multa !== null && l.loc_taxa_multa !== "" ? toMoneyBR(l.loc_taxa_multa) : "");
    setVal("loc_km_retirada", l.loc_km_retirada ?? "");
    setVal("loc_status", l.loc_status ?? "reservada");
    const chk = document.getElementById("loc_valores_recebidos");
    if (chk) chk.checked = String(l.loc_valores_recebidos || "0") === "1";

    setupMasks();
  };

  const openLocacaoModal = async (id = null) => {
    const modalEl = document.getElementById("modalLocacao");
    if (!modalEl) return;

    resetLocacaoForm();

    if (!id) {
      setLocacaoModalMode("new");
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    setLocacaoModalMode("edit");
    const json = await fetchJson(`${window.location.origin}/admin/locacoes/editar/${id}`);
    preencherLocacaoModal(json.data || {});
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  const moneyToDecimal = (s) => String(s || "").replace(/[^\d,]/g, "").replace(",", ".");

  const saveLocacao = async () => {
    const form = document.getElementById("formLocacao");
    const btn = document.getElementById("btnSalvarLocacao");
    const modalEl = document.getElementById("modalLocacao");
    if (!form || !modalEl) return;

    // valida campos visuais + hidden ids
    const cliId = document.getElementById("loc_cli_id")?.value || "";
    const veiId = document.getElementById("loc_vei_id")?.value || "";
    if (!cliId) return alert("Selecione o locatário.");
    if (!veiId) return alert("Selecione o veículo.");

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const fd = new FormData(form);
    // garantir ids
    fd.set("loc_cli_id", cliId);
    fd.set("loc_vei_id", veiId);

    // normalizar moedas
    const vLoc = document.getElementById("loc_valor_locacao");
    const vCau = document.getElementById("loc_valor_caucao");
    const vJuros = document.getElementById("loc_taxa_juros");
    const vMulta = document.getElementById("loc_taxa_multa");
    if (vLoc) fd.set("loc_valor_locacao", moneyToDecimal(vLoc.value) || "0");
    if (vCau && vCau.value) fd.set("loc_valor_caucao", moneyToDecimal(vCau.value));
    if (vJuros && vJuros.value) fd.set("loc_taxa_juros", moneyToDecimal(vJuros.value));
    if (vMulta && vMulta.value) fd.set("loc_taxa_multa", moneyToDecimal(vMulta.value));

    const id = document.getElementById("locacao_id")?.value || "";
    const url = id
      ? `${window.location.origin}/admin/locacoes/atualizar/${id}`
      : `${window.location.origin}/admin/locacoes/criar`;

    try {
      setButtonLoading(btn, true);
      const json = await fetchJson(url, { method: "POST", body: fd });

      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      await reload();
      alert(json?.message || "Salvo com sucesso.");
    } catch (e) {
      alert(e?.message || "Erro ao salvar.");
    } finally {
      setButtonLoading(btn, false);
    }
  };

  // ======= Lupas =======
  const renderVeiculosGrid = (items) => {
    const rows = (items || []).map((v) => [
      String(v.id),
      v.vei_placa || "-",
      v.vei_modelo || "-",
      v.vei_marca || "-",
      v.vei_status || "disponivel",
      String(v.id),
    ]);

    const statusLabelV = (dbValue) => {
      switch (dbValue) {
        case "disponivel":
          return "Disponível";
        case "locado":
          return "Locado";
        case "manutencao":
          return "Manutenção";
        case "inativo":
          return "Inativo";
        default:
          return "Disponível";
      }
    };
    const statusBadgeV = (label) => {
      if (label === "Locado") return "bg-warning-subtle text-warning";
      if (label === "Manutenção") return "bg-danger-subtle text-danger";
      if (label === "Inativo") return "bg-secondary-subtle text-secondary";
      return "bg-success-subtle text-success";
    };

    const columns = [
      { name: "ID", width: "80px", formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`) },
      { name: "Placa", width: "140px", formatter: (cell) => gridjs.html(`<span class="badge bg-primary-subtle text-primary">${cell}</span>`) },
      { name: "Modelo", width: "200px" },
      { name: "Marca", width: "180px" },
      {
        name: "Status",
        width: "150px",
        formatter: (cell) => {
          const label = statusLabelV(cell);
          return gridjs.html(`<span class="badge ${statusBadgeV(label)}">${label}</span>`);
        },
      },
      {
        name: "Ações",
        width: "120px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          return gridjs.html(`
            <button type="button" class="btn btn-sm btn-primary btn-select-veiculo" data-id="${id}">Selecionar</button>
          `);
        },
      },
    ];

    const el = document.getElementById("table-escolher-veiculo");
    if (!el) return;

    if (!gridVeiculos) {
      gridVeiculos = new gridjs.Grid({
        columns,
        pagination: { limit: 8 },
        sort: true,
        search: false,
        language: ptBR,
        data: rows,
      }).render(el);
      return;
    }
    gridVeiculos.updateConfig({ columns, data: rows }).forceRender();
  };

  const renderLocatariosGrid = (items) => {
    const rows = (items || []).map((c) => [
      String(c.id),
      c.cli_nome || "-",
      c.cli_cpf_cnpj || "",
      c.cli_telefone || "",
      String(c.id),
    ]);

    const columns = [
      { name: "ID", width: "80px", formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`) },
      { name: "Nome", width: "260px" },
      {
        name: "CPF/CNPJ",
        width: "180px",
        formatter: (cell) => gridjs.html(`<span class="text-muted">${formatCpfCnpj(cell)}</span>`),
      },
      {
        name: "Telefone",
        width: "160px",
        formatter: (cell) => gridjs.html(`<span class="text-muted">${cell || "-"}</span>`),
      },
      {
        name: "Ações",
        width: "120px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          return gridjs.html(`
            <button type="button" class="btn btn-sm btn-primary btn-select-locatario" data-id="${id}">Selecionar</button>
          `);
        },
      },
    ];

    const el = document.getElementById("table-escolher-locatario");
    if (!el) return;

    if (!gridLocatarios) {
      gridLocatarios = new gridjs.Grid({
        columns,
        pagination: { limit: 8 },
        sort: true,
        search: false,
        language: ptBR,
        data: rows,
      }).render(el);
      return;
    }
    gridLocatarios.updateConfig({ columns, data: rows }).forceRender();
  };

  const filterVeiculosLocal = () => {
    const q = document.getElementById("filtro-veiculo-geral")?.value?.trim()?.toUpperCase() || "";
    const st = document.getElementById("filtro-veiculo-status")?.value || "";

    const filtered = (veiculosData || []).filter((v) => {
      if (st && String(v.vei_status) !== st) return false;
      if (!q) return true;
      const blob = `${v.vei_placa || ""} ${v.vei_modelo || ""} ${v.vei_marca || ""}`.toUpperCase();
      return blob.includes(q);
    });

    renderVeiculosGrid(filtered);
  };

  const filterLocatariosLocal = () => {
    const qNome = document.getElementById("filtro-locatario-nome")?.value?.trim()?.toUpperCase() || "";
    const qCpf = parseDigits(document.getElementById("filtro-locatario-cpfcnpj")?.value || "");

    const filtered = (locatariosData || []).filter((c) => {
      if (qNome) {
        const nome = String(c.cli_nome || "").toUpperCase();
        if (!nome.includes(qNome)) return false;
      }
      if (qCpf) {
        const cpf = parseDigits(c.cli_cpf_cnpj || "");
        if (!cpf.includes(qCpf)) return false;
      }
      return true;
    });

    renderLocatariosGrid(filtered);
  };

  const openVeiculoLookup = async () => {
    const modalEl = document.getElementById("modalEscolherVeiculo");
    if (!modalEl) return;

    if (!veiculosData) {
      const json = await fetchJson(`${window.location.origin}/admin/veiculos/listar`);
      veiculosData = json.data || [];
    }

    document.getElementById("filtro-veiculo-geral").value = "";
    document.getElementById("filtro-veiculo-status").value = "";
    renderVeiculosGrid(veiculosData);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  const openLocatarioLookup = async () => {
    const modalEl = document.getElementById("modalEscolherLocatario");
    if (!modalEl) return;

    if (!locatariosData) {
      const json = await fetchJson(`${window.location.origin}/admin/locatarios/listar`);
      locatariosData = json.data || [];
    }

    document.getElementById("filtro-locatario-nome").value = "";
    document.getElementById("filtro-locatario-cpfcnpj").value = "";
    renderLocatariosGrid(locatariosData);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  const selectVeiculoById = (id) => {
    const v = (veiculosData || []).find((x) => String(x.id) === String(id));
    if (!v) return;

    document.getElementById("loc_vei_id").value = v.id;
    document.getElementById("loc_vei_display").value = `${v.vei_placa || ""} - ${v.vei_modelo || ""}`.trim();

    const modalEl = document.getElementById("modalEscolherVeiculo");
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
  };

  const selectLocatarioById = (id) => {
    const c = (locatariosData || []).find((x) => String(x.id) === String(id));
    if (!c) return;

    document.getElementById("loc_cli_id").value = c.id;
    document.getElementById("loc_cli_display").value = `${c.cli_nome || ""} (${formatCpfCnpj(c.cli_cpf_cnpj)})`.trim();

    const modalEl = document.getElementById("modalEscolherLocatario");
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
  };

  // ======= Eventos =======
  tableEl.addEventListener("click", (e) => {
    const btn = e.target?.closest?.(".btn-edit-locacao");
    if (!btn) return;
    e.preventDefault();
    openLocacaoModal(btn.getAttribute("data-id"));
  });

  document.getElementById("table-escolher-veiculo")?.addEventListener("click", (e) => {
    const btn = e.target?.closest?.(".btn-select-veiculo");
    if (!btn) return;
    e.preventDefault();
    selectVeiculoById(btn.getAttribute("data-id"));
  });

  document.getElementById("table-escolher-locatario")?.addEventListener("click", (e) => {
    const btn = e.target?.closest?.(".btn-select-locatario");
    if (!btn) return;
    e.preventDefault();
    selectLocatarioById(btn.getAttribute("data-id"));
  });

  document.getElementById("filtro-veiculo-geral")?.addEventListener("input", filterVeiculosLocal);
  document.getElementById("filtro-veiculo-status")?.addEventListener("change", filterVeiculosLocal);
  document.getElementById("filtro-locatario-nome")?.addEventListener("input", filterLocatariosLocal);
  document.getElementById("filtro-locatario-cpfcnpj")?.addEventListener("input", filterLocatariosLocal);

  // Sugestão de fim previsto quando escolher tempo mínimo
  const tempoEl = document.getElementById("loc_tempo_minimo");
  const iniEl = document.getElementById("loc_data_inicio");
  const fimEl = document.getElementById("loc_data_fim_prevista");
  const suggestFim = () => {
    const days = Number(tempoEl?.value || 0);
    const ini = iniEl?.value;
    if (!days || !ini) return;
    if (fimEl && !fimEl.value) {
      const d = new Date(ini + "T00:00:00");
      d.setDate(d.getDate() + days);
      fimEl.value = d.toISOString().slice(0, 10);
    }
  };
  tempoEl?.addEventListener("change", suggestFim);
  iniEl?.addEventListener("change", suggestFim);

  // Reset UI ao fechar modal principal
  document.getElementById("modalLocacao")?.addEventListener("hidden.bs.modal", () => {
    resetLocacaoForm();
    setLocacaoModalMode("new");
  });

  // Expor funções globais usadas pela view
  window.abrirModalLocacao = (id = null) => openLocacaoModal(id);
  window.salvarLocacao = () => saveLocacao();
  window.abrirModalEscolherVeiculo = () => openVeiculoLookup();
  window.abrirModalEscolherLocatario = () => openLocatarioLookup();

  // boot
  setupMasks();
  updateKpis(allData);
  renderGrid(allData);
})();
