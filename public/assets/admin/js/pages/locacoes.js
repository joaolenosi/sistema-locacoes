(() => {
  const tableEl = document.getElementById("table-locacoes");
  if (!tableEl) return;

  // Helper para garantir base URL com barra final
  const getBaseUrl = () => {
    const base = window.__BASE_URL__ || window.location.origin;
    return base.endsWith('/') ? base : base + '/';
  };

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
    // Garantir que items seja um array válido
    if (!Array.isArray(items)) {
      items = [];
    }
    
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
        width: "180px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          return gridjs.html(`
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-info btn-detalhes-locacao" data-id="${id}" title="Ver detalhes">
                <iconify-icon icon="iconamoon:eye-duotone" class="fs-18"></iconify-icon>
              </button>
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-locacao" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
            </div>
          `);
        },
      },
    ];

    if (!grid) {
      try {
        // GridJS precisa de pelo menos uma linha vazia se não houver dados
        const gridData = rows.length > 0 ? rows : [];
        
        grid = new gridjs.Grid({
          columns,
          pagination: { limit: 5 },
          sort: true,
          search: true,
          language: ptBR,
          data: gridData,
        }).render(tableEl);

        setupFiltrosUI();
        return;
      } catch (error) {
        console.error('Erro ao renderizar grid:', error);
        if (tableEl) {
          tableEl.innerHTML = '<div class="alert alert-danger">Erro ao carregar a tabela. Por favor, recarregue a página.</div>';
        }
        return;
      }
    }

    try {
      const gridData = rows.length > 0 ? rows : [];
      grid.updateConfig({ columns, data: gridData }).forceRender();
    } catch (error) {
      console.error('Erro ao atualizar grid:', error);
      // Tentar recriar o grid em caso de erro
      try {
        grid = null;
        renderGrid(items);
      } catch (retryError) {
        console.error('Erro ao tentar recriar grid:', retryError);
        if (tableEl) {
          tableEl.innerHTML = '<div class="alert alert-danger">Erro ao atualizar a tabela. Por favor, recarregue a página.</div>';
        }
      }
    }
  };

  const reload = async () => {
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/locacoes/listar`);
      allData = Array.isArray(json.data) ? json.data : [];
      applyFilters();
    } catch (error) {
      console.error('Erro ao recarregar dados:', error);
      allData = [];
      applyFilters();
    }
  };

  // Funções auxiliares para buscar relacionamentos
  const buscarVeiculosPorCliente = async (cliId) => {
    if (!cliId) return [];
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/locacoes/veiculos-por-cliente/${cliId}`);
      return json.data || [];
    } catch (e) {
      console.warn("Erro ao buscar veículos do cliente:", e);
      return [];
    }
  };

  const buscarClientePorVeiculo = async (veiId) => {
    if (!veiId) return null;
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/locacoes/cliente-por-veiculo/${veiId}`);
      return json.data || null;
    } catch (e) {
      console.warn("Erro ao buscar cliente do veículo:", e);
      return null;
    }
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
    const json = await fetchJson(`${getBaseUrl()}admin/locacoes/editar/${id}`);
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
    
    // Remover campos que não devem ser enviados
    fd.delete("locacao_id"); // Não está no allowedFields do modelo
    fd.delete("loc_cli_display"); // Campo apenas para exibição
    fd.delete("loc_vei_display"); // Campo apenas para exibição
    fd.delete("loc_tempo_minimo"); // Campo apenas para cálculo, não é salvo
    
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
      ? `${getBaseUrl()}admin/locacoes/atualizar/${id}`
      : `${getBaseUrl()}admin/locacoes/criar`;

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
      
      // Adicionar event listener após o grid ser renderizado
      setTimeout(() => {
        const gridContainer = el.querySelector('.gridjs-wrapper');
        if (gridContainer) {
          gridContainer.addEventListener('click', (e) => {
            const btn = e.target?.closest?.('.btn-select-veiculo');
            if (btn) {
              e.preventDefault();
              e.stopPropagation();
              const id = btn.getAttribute('data-id');
              if (id) selectVeiculoById(id);
            }
          });
        }
      }, 100);
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
      
      // Adicionar event listener após o grid ser renderizado
      setTimeout(() => {
        const gridContainer = el.querySelector('.gridjs-wrapper');
        if (gridContainer) {
          gridContainer.addEventListener('click', (e) => {
            const btn = e.target?.closest?.('.btn-select-locatario');
            if (btn) {
              e.preventDefault();
              e.stopPropagation();
              const id = btn.getAttribute('data-id');
              if (id) selectLocatarioById(id);
            }
          });
        }
      }, 100);
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

    const cliId = document.getElementById("loc_cli_id")?.value;
    let veiculosParaExibir = [];

    if (cliId && cliId.trim() !== "") {
      // Se há locatário selecionado, buscar apenas veículos desse cliente
      veiculosParaExibir = await buscarVeiculosPorCliente(cliId);
      
      // Se não houver veículos no histórico, buscar todos os veículos disponíveis
      if (veiculosParaExibir.length === 0) {
        if (!veiculosData) {
          const json = await fetchJson(`${getBaseUrl()}admin/veiculos/listar`);
          veiculosData = json.data || [];
        }
        veiculosParaExibir = veiculosData || [];
      }
    } else {
      // Se não há locatário selecionado, buscar todos os veículos
      if (!veiculosData) {
        const json = await fetchJson(`${getBaseUrl()}admin/veiculos/listar`);
        veiculosData = json.data || [];
      }
      veiculosParaExibir = veiculosData || [];
    }

    document.getElementById("filtro-veiculo-geral").value = "";
    document.getElementById("filtro-veiculo-status").value = "";
    renderVeiculosGrid(veiculosParaExibir);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  const openLocatarioLookup = async () => {
    const modalEl = document.getElementById("modalEscolherLocatario");
    if (!modalEl) return;

    if (!locatariosData) {
      const json = await fetchJson(`${getBaseUrl()}admin/locatarios/listar`);
      locatariosData = json.data || [];
    }

    document.getElementById("filtro-locatario-nome").value = "";
    document.getElementById("filtro-locatario-cpfcnpj").value = "";
    renderLocatariosGrid(locatariosData);
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  };

  const selectVeiculoById = async (id) => {
    if (!id) return;
    
    // Buscar veículo - tentar primeiro nos dados já carregados, depois via API
    let v = (veiculosData || []).find((x) => String(x.id) === String(id));
    
    // Se não encontrou nos dados locais, buscar via API
    if (!v) {
      try {
        const json = await fetchJson(`${getBaseUrl()}admin/veiculos/listar`);
        const allVeiculos = json.data || [];
        v = allVeiculos.find((x) => String(x.id) === String(id));
        // Atualizar cache
        if (!veiculosData) veiculosData = allVeiculos;
      } catch (e) {
        console.warn("Erro ao buscar veículo:", e);
      }
    }

    if (!v) {
      console.warn("Veículo não encontrado com ID:", id);
      return;
    }

    document.getElementById("loc_vei_id").value = v.id;
    document.getElementById("loc_vei_display").value = `${v.vei_placa || ""} - ${v.vei_modelo || ""}`.trim();

    const modalEl = document.getElementById("modalEscolherVeiculo");
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();

    // Buscar cliente que locou esse veículo e preencher automaticamente
    // Mas só se o locatário ainda não estiver preenchido
    const cliIdEl = document.getElementById("loc_cli_id");
    if (!cliIdEl || !cliIdEl.value || cliIdEl.value.trim() === "") {
      const cliente = await buscarClientePorVeiculo(id);
      if (cliente) {
        const cliDisplayEl = document.getElementById("loc_cli_display");
        if (cliIdEl) cliIdEl.value = cliente.id;
        if (cliDisplayEl) {
          const displayText = `${cliente.cli_nome || ""} (${formatCpfCnpj(cliente.cli_cpf_cnpj || "")})`.trim();
          cliDisplayEl.value = displayText;
        }
      }
    }
  };

  const selectLocatarioById = async (id) => {
    if (!id) return;
    
    const c = (locatariosData || []).find((x) => String(x.id) === String(id));
    if (!c) {
      console.warn("Locatário não encontrado com ID:", id);
      return;
    }

    const cliIdEl = document.getElementById("loc_cli_id");
    const cliDisplayEl = document.getElementById("loc_cli_display");
    
    if (cliIdEl) cliIdEl.value = c.id;
    if (cliDisplayEl) {
      const displayText = `${c.cli_nome || ""} (${formatCpfCnpj(c.cli_cpf_cnpj || "")})`.trim();
      cliDisplayEl.value = displayText;
    }

    const modalEl = document.getElementById("modalEscolherLocatario");
    if (modalEl && window.bootstrap?.Modal) {
      const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.hide();
    }

    // Buscar veículos desse cliente e selecionar automaticamente o primeiro
    const veiculosDoCliente = await buscarVeiculosPorCliente(id);
    if (veiculosDoCliente && veiculosDoCliente.length > 0) {
      // Selecionar o primeiro veículo automaticamente
      const primeiroVeiculo = veiculosDoCliente[0];
      const veiIdEl = document.getElementById("loc_vei_id");
      const veiDisplayEl = document.getElementById("loc_vei_display");
      
      if (veiIdEl) veiIdEl.value = primeiroVeiculo.id;
      if (veiDisplayEl) {
        const displayText = `${primeiroVeiculo.vei_placa || ""} - ${primeiroVeiculo.vei_modelo || ""}`.trim();
        veiDisplayEl.value = displayText;
      }
      
      // Atualizar cache de veículos se necessário
      if (!veiculosData) {
        veiculosData = [];
      }
      // Adicionar veículo ao cache se não estiver lá
      const veiculoNoCache = veiculosData.find(v => String(v.id) === String(primeiroVeiculo.id));
      if (!veiculoNoCache) {
        veiculosData.push(primeiroVeiculo);
      }
    } else {
      // Se não houver veículos no histórico, limpar campo de veículo
      const veiIdEl = document.getElementById("loc_vei_id");
      const veiDisplayEl = document.getElementById("loc_vei_display");
      if (veiIdEl) veiIdEl.value = "";
      if (veiDisplayEl) veiDisplayEl.value = "";
    }
  };

  // ======= Eventos =======
  tableEl.addEventListener("click", (e) => {
    const btnEdit = e.target?.closest?.(".btn-edit-locacao");
    if (btnEdit) {
      e.preventDefault();
      openLocacaoModal(btnEdit.getAttribute("data-id"));
      return;
    }
    
    const btnDetalhes = e.target?.closest?.(".btn-detalhes-locacao");
    if (btnDetalhes) {
      e.preventDefault();
      openDetalhesLocacao(btnDetalhes.getAttribute("data-id"));
      return;
    }
  });

  // Usar delegação de eventos no documento para garantir que funcione mesmo quando o grid é renderizado dinamicamente
  document.addEventListener("click", (e) => {
    // Verificar se o clique foi em um botão de selecionar veículo
    const btnVeiculo = e.target?.closest?.(".btn-select-veiculo");
    if (btnVeiculo) {
      e.preventDefault();
      e.stopPropagation();
      const id = btnVeiculo.getAttribute("data-id");
      if (id) {
        selectVeiculoById(id);
      }
      return;
    }

    // Verificar se o clique foi em um botão de selecionar locatário
    const btnLocatario = e.target?.closest?.(".btn-select-locatario");
    if (btnLocatario) {
      e.preventDefault();
      e.stopPropagation();
      const id = btnLocatario.getAttribute("data-id");
      if (id) {
        selectLocatarioById(id);
      }
      return;
    }
  }, true); // Usar capture phase para garantir que o evento seja capturado antes de qualquer outro handler

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

  const openDetalhesLocacao = async (id) => {
    if (!id) return;
    
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/locacoes/editar/${id}`);
      if (!json.success || !json.data) {
        alert("Erro ao carregar detalhes da locação.");
        return;
      }
      
      const loc = json.data;
      preencherDetalhesLocacao(loc, id);
      
      const modalEl = document.getElementById("modalDetalhesLocacao");
      if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    } catch (e) {
      alert("Erro ao carregar detalhes da locação: " + (e?.message || "Erro desconhecido"));
    }
  };

  const preencherDetalhesLocacao = (l, id) => {
    // Formatar datas
    const formatDate = (dateStr) => {
      if (!dateStr) return "-";
      const date = new Date(dateStr + "T00:00:00");
      const days = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
      const dayName = days[date.getDay()];
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      return `${day}/${month}/${year} (${dayName})`;
    };

    const formatDateSimple = (dateStr) => {
      if (!dateStr) return "-";
      const date = new Date(dateStr + "T00:00:00");
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    };

    // Status labels
    const statusLabels = {
      reservada: "Reservada",
      ativa: "Ativa",
      atrasada: "Atrasada",
      inadimplente: "Inadimplente",
      finalizada: "Finalizada",
      cancelada: "Cancelada",
    };

    const statusBadges = {
      reservada: "bg-info-subtle text-info",
      ativa: "bg-success-subtle text-success",
      atrasada: "bg-warning-subtle text-warning",
      inadimplente: "bg-danger-subtle text-danger",
      finalizada: "bg-secondary-subtle text-secondary",
      cancelada: "bg-dark-subtle text-dark",
    };

    // Preencher campos
    document.getElementById("detalhes-loc-id").textContent = `#${String(l.id || "").padStart(6, "0")}`;
    document.getElementById("detalhes-loc-cliente").textContent = l.cli_nome || "-";
    document.getElementById("detalhes-loc-veiculo-placa").textContent = l.vei_placa || "-";
    document.getElementById("detalhes-loc-veiculo-modelo").textContent = l.vei_marca && l.vei_modelo 
      ? `${l.vei_marca} ${l.vei_modelo}` 
      : (l.vei_modelo || "-");
    
    document.getElementById("detalhes-loc-data-inicio").textContent = formatDateSimple(l.loc_data_inicio);
    document.getElementById("detalhes-loc-data-fim-prevista").textContent = formatDateSimple(l.loc_data_fim_prevista);
    document.getElementById("detalhes-loc-data-fim-real").textContent = l.loc_data_fim_real 
      ? formatDateSimple(l.loc_data_fim_real) 
      : "-";
    
    document.getElementById("detalhes-loc-status").textContent = statusLabels[l.loc_status] || l.loc_status || "-";
    document.getElementById("detalhes-loc-status").className = `badge ${statusBadges[l.loc_status] || "bg-secondary-subtle text-secondary"}`;
    
    document.getElementById("detalhes-loc-valor-locacao").textContent = toMoneyBR(l.loc_valor_locacao || 0);
    document.getElementById("detalhes-loc-valor-caucao").textContent = l.loc_valor_caucao 
      ? toMoneyBR(l.loc_valor_caucao) 
      : "-";
    document.getElementById("detalhes-loc-valor-total").textContent = toMoneyBR(l.loc_valor_total || l.loc_valor_locacao || 0);
    
    document.getElementById("detalhes-loc-recorrencia").textContent = l.loc_recorrencia_pagamento 
      ? l.loc_recorrencia_pagamento.charAt(0).toUpperCase() + l.loc_recorrencia_pagamento.slice(1)
      : "-";
    document.getElementById("detalhes-loc-data-inicio-pagamento").textContent = l.loc_data_inicio_pagamento 
      ? formatDateSimple(l.loc_data_inicio_pagamento) 
      : "-";
    
    document.getElementById("detalhes-loc-taxa-juros").textContent = l.loc_taxa_juros 
      ? toMoneyBR(l.loc_taxa_juros) 
      : "-";
    document.getElementById("detalhes-loc-taxa-multa").textContent = l.loc_taxa_multa 
      ? toMoneyBR(l.loc_taxa_multa) 
      : "-";
    
    document.getElementById("detalhes-loc-km-retirada").textContent = l.loc_km_retirada || "-";
    document.getElementById("detalhes-loc-km-devolucao").textContent = l.loc_km_devolucao || "-";
    
    const valoresRecebidos = l.loc_valores_recebidos == 1;
    document.getElementById("detalhes-loc-valores-recebidos").textContent = valoresRecebidos ? "Sim" : "Não";
    const valoresRecebidosBadge = document.getElementById("detalhes-loc-valores-recebidos-badge");
    if (valoresRecebidosBadge) {
      valoresRecebidosBadge.textContent = valoresRecebidos ? "Sim" : "Não";
      valoresRecebidosBadge.className = valoresRecebidos 
        ? "badge bg-success-subtle text-success" 
        : "badge bg-warning-subtle text-warning";
    }
    
    document.getElementById("detalhes-loc-obs-operacionais").textContent = l.loc_obs_operacionais || "-";
    document.getElementById("detalhes-loc-obs-financeiras").textContent = l.loc_obs_financeiras || "-";
    
    // Duplicar datas para exibição em dois lugares
    const dataInicio = formatDateSimple(l.loc_data_inicio);
    const dataFimPrevista = formatDateSimple(l.loc_data_fim_prevista);
    const dataInicioDup = document.getElementById("detalhes-loc-data-inicio-duplicado");
    const dataFimPrevistaDup = document.getElementById("detalhes-loc-data-fim-prevista-duplicado");
    if (dataInicioDup) dataInicioDup.textContent = dataInicio;
    if (dataFimPrevistaDup) dataFimPrevistaDup.textContent = dataFimPrevista;
    
    // Configurar botão de editar
    const btnEditar = document.getElementById("btnEditarLocacaoDetalhes");
    if (btnEditar) {
      btnEditar.onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById("modalDetalhesLocacao"))?.hide();
        setTimeout(() => openLocacaoModal(id), 300);
      };
    }

    // Calcular próximo pagamento
    if (l.loc_data_inicio_pagamento && l.loc_recorrencia_pagamento) {
      const dataInicio = new Date(l.loc_data_inicio_pagamento + "T00:00:00");
      const hoje = new Date();
      hoje.setHours(0, 0, 0, 0);
      
      let diasRecorrencia = 0;
      switch (l.loc_recorrencia_pagamento) {
        case "diaria": diasRecorrencia = 1; break;
        case "semanal": diasRecorrencia = 7; break;
        case "quinzenal": diasRecorrencia = 15; break;
        case "mensal": diasRecorrencia = 30; break;
      }
      
      let proximaData = new Date(dataInicio);
      while (proximaData <= hoje) {
        proximaData.setDate(proximaData.getDate() + diasRecorrencia);
      }
      
      const proximoPagamentoEl = document.getElementById("detalhes-loc-proximo-pagamento");
      const valorPagamentoEl = document.getElementById("detalhes-loc-valor-proximo-pagamento");
      
      if (proximoPagamentoEl) {
        proximoPagamentoEl.textContent = formatDate(proximaData.toISOString().slice(0, 10));
      }
      if (valorPagamentoEl && l.loc_valor_locacao) {
        valorPagamentoEl.textContent = toMoneyBR(l.loc_valor_locacao);
      }
    } else {
      const proximoPagamentoEl = document.getElementById("detalhes-loc-proximo-pagamento");
      const valorPagamentoEl = document.getElementById("detalhes-loc-valor-proximo-pagamento");
      if (proximoPagamentoEl) proximoPagamentoEl.textContent = "-";
      if (valorPagamentoEl) valorPagamentoEl.textContent = "-";
    }
  };

  // Expor funções globais usadas pela view
  window.abrirModalLocacao = (id = null) => openLocacaoModal(id);
  window.salvarLocacao = () => saveLocacao();
  window.abrirModalEscolherVeiculo = () => openVeiculoLookup();
  window.abrirModalEscolherLocatario = () => openLocatarioLookup();

  // boot
  try {
    setupMasks();
    updateKpis(allData);
    // Garantir que allData seja um array válido antes de renderizar
    renderGrid(Array.isArray(allData) ? allData : []);
  } catch (error) {
    console.error('Erro na inicialização:', error);
    if (tableEl) {
      tableEl.innerHTML = '<div class="alert alert-danger">Erro ao inicializar a tabela. Por favor, recarregue a página.</div>';
    }
  }
})();
