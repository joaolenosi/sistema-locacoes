(() => {
  const tableEl = document.getElementById("table-veiculos");
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

  const statusLabel = (dbValue) => {
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

  const statusBadge = (label) => {
    if (label === "Locado") return "bg-warning-subtle text-warning";
    if (label === "Manutenção") return "bg-danger-subtle text-danger";
    if (label === "Inativo") return "bg-secondary-subtle text-secondary";
    return "bg-success-subtle text-success";
  };

  const toGridRows = (items) =>
    (items || []).map((v) => [
      String(v.id),
      v.vei_placa || "-",
      v.vei_modelo || "-",
      v.vei_marca || "-",
      v.vei_ano || "-",
      v.vei_cor || "-",
      statusLabel(v.vei_status),
      String(v.id), // ações
    ]);

  const updateKpis = (items) => {
    const total = (items || []).length;
    const ocupados = (items || []).filter((v) => v.vei_status === "locado").length;
    const livres = total - ocupados;

    const elTotal = document.getElementById("kpi-total-veiculos");
    const elLivres = document.getElementById("kpi-veiculos-livres");
    const elOcupados = document.getElementById("kpi-veiculos-ocupados");
    if (elTotal) elTotal.textContent = String(total);
    if (elLivres) elLivres.textContent = String(livres);
    if (elOcupados) elOcupados.textContent = String(ocupados);
  };

  let grid = null;
  let currentData = Array.isArray(window.__VEICULOS__) ? window.__VEICULOS__ : [];

  const renderGrid = (items) => {
    currentData = items || [];
    updateKpis(currentData);

    const rows = toGridRows(currentData);

    const columns = [
      {
        name: "ID",
        width: "80px",
        formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`),
      },
      {
        name: "Placa",
        width: "120px",
        formatter: (cell) =>
          gridjs.html(`<span class="badge bg-primary-subtle text-primary">${cell}</span>`),
      },
      "Modelo",
      "Marca",
      { name: "Ano", width: "100px" },
      "Cor",
      {
        name: "Status",
        width: "140px",
        formatter: (cell) => gridjs.html(`<span class="badge ${statusBadge(cell)}">${cell}</span>`),
      },
      {
        name: "Ações",
        width: "140px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          const placa = (row.cells[1].data || "").replace(/<[^>]+>/g, "").trim() || "-";
          return gridjs.html(`
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-veiculo" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete-veiculo" data-id="${id}" data-placa="${placa}" title="Excluir">
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
        pagination: { limit: 5 },
        sort: true,
        search: true,
        language: ptBR,
        data: rows,
      }).render(tableEl);
      return;
    }

    grid.updateConfig({ columns, data: rows }).forceRender();
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

  const reload = async () => {
    const json = await fetchJson(`${getBaseUrl()}admin/veiculos/listar`);
    renderGrid(json.data || []);
  };

  const modalEl = document.getElementById("modalVeiculo");
  const formEl = document.getElementById("formVeiculo");
  const alertEl = document.getElementById("vei-form-alert");
  const placaLoadingEl = document.getElementById("vei-placa-loading");
  const btnSave = document.getElementById("btnSalvarVeiculo");
  const btnAdd = document.getElementById("btn-add-veiculo");
  const titleEl = document.getElementById("modalVeiculoLabel");

  const getBsModal = () => {
    if (!modalEl) return null;
    if (!window.bootstrap?.Modal) return null;
    return window.bootstrap.Modal.getOrCreateInstance(modalEl);
  };

  const setAlert = (message) => {
    if (!alertEl) return;
    if (!message) {
      alertEl.classList.add("d-none");
      alertEl.textContent = "";
      return;
    }
    alertEl.textContent = message;
    alertEl.classList.remove("d-none");
  };

  const resetForm = () => {
    setAlert("");
    if (!formEl) return;
    formEl.reset();
    const idEl = document.getElementById("vei_id");
    if (idEl) idEl.value = "";
    if (titleEl) titleEl.textContent = "Cadastrar veículo";
    if (btnSave) btnSave.querySelector(".btn-text").textContent = "Adicionar";
  };

  const fillForm = (v) => {
    setAlert("");
    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val ?? "";
    };
    setVal("vei_id", v.id);
    setVal("vei_tipo", v.vei_tipo);
    setVal("vei_marca", v.vei_marca);
    setVal("vei_modelo", v.vei_modelo);
    setVal("vei_ano", v.vei_ano);
    setVal("vei_placa", v.vei_placa);
    setVal("vei_cor", v.vei_cor);
    setVal("vei_renavam", v.vei_renavam);
    setVal("vei_chassi", v.vei_chassi);
    setVal("vei_data_licenciamento", v.vei_data_licenciamento);
    setVal("vei_km_atual", v.vei_km_atual);
    setVal("vei_data_compra", v.vei_data_compra);
    setVal("vei_valor_compra", v.vei_valor_compra ? String(v.vei_valor_compra).replace(".", ",") : "");
    setVal("vei_status", v.vei_status || "disponivel");

    if (titleEl) titleEl.textContent = "Editar veículo";
    if (btnSave) btnSave.querySelector(".btn-text").textContent = "Salvar alterações";
  };

  // Enter: avançar campo a campo (Shift+Enter volta)
  const enableEnterNavigation = () => {
    if (!formEl) return;
    formEl.addEventListener("keydown", (e) => {
      if (e.key !== "Enter") return;
      const tag = (e.target?.tagName || "").toLowerCase();
      if (tag === "textarea") return;
      // Não submeter ao apertar Enter
      e.preventDefault();

      const focusables = Array.from(
        formEl.querySelectorAll(
          'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'
        )
      ).filter((el) => el.offsetParent !== null);

      const idx = focusables.indexOf(e.target);
      if (idx === -1) return;
      const nextIdx = e.shiftKey ? idx - 1 : idx + 1;
      const next = focusables[nextIdx] || focusables[0];
      next?.focus?.();
      // Se for input, seleciona conteúdo pra digitar mais rápido
      if (next && next.tagName?.toLowerCase() === "input") {
        try {
          next.select?.();
        } catch (_) {}
      }
    });
  };

  const formatPlateInput = (input) => {
    input.addEventListener("input", (e) => {
      let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, "");
      if (v.length > 7) v = v.slice(0, 7);
      if (v.length > 3) v = v.slice(0, 3) + "-" + v.slice(3);
      e.target.value = v;
    });
  };

  const lockButton = (locked, text) => {
    if (!btnSave) return;
    btnSave.disabled = locked;
    const span = btnSave.querySelector(".btn-text");
    if (span && text) span.textContent = text;
    if (locked) {
      if (!btnSave.querySelector(".spinner-border")) {
        btnSave.insertAdjacentHTML(
          "afterbegin",
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
        );
      }
    } else {
      btnSave.querySelector(".spinner-border")?.remove();
    }
  };

  const openCreate = () => {
    resetForm();
    getBsModal()?.show();
    setTimeout(() => document.getElementById("vei_tipo")?.focus?.(), 150);
  };

  const openEdit = async (id) => {
    resetForm();
    lockButton(true, "Carregando...");
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/veiculos/editar/${id}`);
      fillForm(json.data);
      getBsModal()?.show();
      setTimeout(() => document.getElementById("vei_tipo")?.focus?.(), 150);
    } catch (e) {
      setAlert(e.message || "Erro ao carregar veículo.");
      getBsModal()?.show();
    } finally {
      lockButton(false);
    }
  };

  const setPlacaLoading = (loading) => {
    if (!placaLoadingEl) return;
    if (loading) placaLoadingEl.classList.remove("d-none");
    else placaLoadingEl.classList.add("d-none");
  };

  const sanitizePlaca = (value) => String(value || "").toUpperCase().replace(/[^A-Z0-9]/g, "");

  const titleCase = (s) => {
    const str = String(s || "").trim();
    if (!str) return "";
    return str
      .toLowerCase()
      .split(" ")
      .filter(Boolean)
      .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
      .join(" ");
  };

  const inferTipo = (api) => {
    const t = String(api?.extra?.tipo_veiculo || api?.segmento || "").toLowerCase();
    if (t.includes("moto")) return "moto";
    if (t.includes("caminh")) return "caminhao";
    // automóvel/auto => carro
    if (t.includes("autom") || t.includes("auto")) return "carro";
    return "";
  };

  const consultPlaca = async () => {
    const placaEl = document.getElementById("vei_placa");
    if (!placaEl) return;

    const placa = sanitizePlaca(placaEl.value);
    if (placa.length < 7) return;

    setAlert("");
    setPlacaLoading(true);
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/veiculos/consultar-placa/${placa}`);
      const data = json?.data || {};

      // Mapeamento API -> form (preenche todos os campos disponíveis)
      const marca = String(data?.MARCA || data?.marca || "").trim();
      const modelo = String(data?.MODELO || data?.modelo || data?.SUBMODELO || "").trim();
      const cor = String(data?.cor || data?.COR || "").trim();
      const anoModelo = String(data?.anoModelo || data?.ano || "").trim();
      const chassi = String(data?.extra?.chassi || data?.chassi || "").trim();
      const renavam = String(data?.extra?.renavam || "").trim();

      const placaApi = String(data?.placa_modelo_novo || data?.placa || "").trim();
      const tipoInferido = inferTipo(data);

      const setIfHasValue = (id, val) => {
        const el = document.getElementById(id);
        if (!el) return;
        const v = String(val ?? "").trim();
        if (v === "") return;
        el.value = v;
      };

      // Placa: manter o que o usuário digitou, mas se a API retornar placa (mercosul), atualiza o input formatado
      if (placaApi) {
        const raw = sanitizePlaca(placaApi);
        if (raw.length >= 7) {
          placaEl.value = raw.slice(0, 3) + "-" + raw.slice(3);
        }
      }

      setIfHasValue("vei_marca", marca.toUpperCase());
      setIfHasValue("vei_modelo", modelo);
      setIfHasValue("vei_cor", titleCase(cor));
      setIfHasValue("vei_ano", anoModelo);
      setIfHasValue("vei_chassi", chassi);
      setIfHasValue("vei_renavam", renavam);

      // Tipo: se conseguir inferir, seta (não sobrescreve se o usuário já escolheu)
      const tipoEl = document.getElementById("vei_tipo");
      if (tipoEl && tipoInferido && !String(tipoEl.value || "").trim()) {
        tipoEl.value = tipoInferido;
      }

      // Feedback leve (sem alert modal)
      const msg = data?.mensagemRetorno;
      if (msg && msg !== "Sem erros.") {
        setAlert(msg);
      }
    } catch (e) {
      setAlert(e.message || "Não foi possível consultar a placa.");
    } finally {
      setPlacaLoading(false);
    }
  };

  const submit = async () => {
    if (!formEl) return;
    setAlert("");

    // validação básica
    const requiredIds = ["vei_tipo", "vei_marca", "vei_modelo", "vei_ano", "vei_placa", "vei_status"];
    for (const rid of requiredIds) {
      const el = document.getElementById(rid);
      if (el && !String(el.value || "").trim()) {
        setAlert("Preencha os campos obrigatórios.");
        return;
      }
    }

    const id = document.getElementById("vei_id")?.value || "";
    const fd = new FormData(formEl);

    // Normalizar valor compra para backend (aceita BR e EN, mas ajuda)
    const valCompra = document.getElementById("vei_valor_compra")?.value || "";
    if (valCompra) {
      const normalized = valCompra.replace(/\./g, "").replace(",", ".");
      fd.set("vei_valor_compra", normalized);
    }

    lockButton(true, id ? "Salvando..." : "Adicionando...");
    try {
      const url = id
        ? `${getBaseUrl()}admin/veiculos/atualizar/${id}`
        : `${getBaseUrl()}admin/veiculos/criar`;

      console.log('URL da requisição:', url);
      const json = await fetchJson(url, { method: "POST", body: fd });
      getBsModal()?.hide();
      await reload();
      resetForm();
      // feedback simples
      if (json?.message) console.log(json.message);
    } catch (e) {
      setAlert(e.message || "Erro ao salvar veículo.");
    } finally {
      lockButton(false);
    }
  };

  // Eventos
  btnAdd?.addEventListener("click", openCreate);
  btnSave?.addEventListener("click", submit);

  document.addEventListener("click", (e) => {
    const btnEdit = e.target.closest?.(".btn-edit-veiculo");
    if (btnEdit) {
      const id = btnEdit.getAttribute("data-id");
      if (id) openEdit(id);
      return;
    }
    const btnDelete = e.target.closest?.(".btn-delete-veiculo");
    if (btnDelete) {
      const id = btnDelete.getAttribute("data-id");
      const placa = btnDelete.getAttribute("data-placa") || id;
      if (!id) return;
      if (!confirm(`Deseja realmente excluir o veículo ${placa}? Esta ação não pode ser desfeita.`)) return;
      (async () => {
        try {
          const json = await fetchJson(`${getBaseUrl()}admin/veiculos/excluir/${id}`, { method: "POST" });
          if (json?.success) {
            await reload();
            if (typeof window.toastr !== "undefined") {
              window.toastr.success(json.message || "Veículo excluído.");
            } else {
              alert(json.message || "Veículo excluído.");
            }
          }
        } catch (err) {
          const msg = err?.message || "Erro ao excluir veículo.";
          if (typeof window.toastr !== "undefined") {
            window.toastr.error(msg);
          } else {
            alert(msg);
          }
        }
      })();
    }
  });

  modalEl?.addEventListener("hidden.bs.modal", () => {
    resetForm();
    lockButton(false);
    setPlacaLoading(false);
  });

  // Máscaras
  const placaEl = document.getElementById("vei_placa");
  if (placaEl) formatPlateInput(placaEl);
  placaEl?.addEventListener("blur", () => {
    // consulta somente quando tem placa completa (7 chars sem hífen)
    const placa = sanitizePlaca(placaEl.value);
    if (placa.length >= 7) consultPlaca();
  });

  // Monetário (jQuery Mask Plugin)
  if (typeof window.$ !== "undefined" && window.$.fn?.mask) {
    window.$("#vei_valor_compra").mask("000.000.000.000.000,00", { reverse: true });
    window.$("#vei_valor_compra").focusout(function () {
      if (window.$(this).val().length <= 2 && window.$(this).val().length > 0) {
        const temp = window.$(this).val();
        window.$(this).val(temp + ",00");
      }
    });
  }

  // Render inicial
  renderGrid(currentData);
  // Se não veio nada da primeira renderização, busca do backend (garante que nunca fica vazio)
  if (!currentData || currentData.length === 0) {
    reload().catch(() => {});
  }

  enableEnterNavigation();
})();
