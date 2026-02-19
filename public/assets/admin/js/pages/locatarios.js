(() => {
  const tableEl = document.getElementById("table-locatarios");
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
    return dbValue === 1 || dbValue === '1' || dbValue === true ? "Ativo" : "Inativo";
  };

  const statusBadge = (label) => {
    return label === "Ativo" ? "bg-success-subtle text-success" : "bg-secondary-subtle text-secondary";
  };

  const toGridRows = (items) =>
    (items || []).map((l) => [
      String(l.id),
      l.cli_nome || "-",
      l.cli_cpf_cnpj || "-",
      l.cli_telefone || "-",
      l.cli_email || "-",
      statusLabel(l.cli_ativo),
      String(l.id), // ações
    ]);

  let grid = null;
  let currentData = Array.isArray(window.__LOCATARIOS__) ? window.__LOCATARIOS__ : [];

  const renderGrid = (items) => {
    currentData = items || [];

    const rows = toGridRows(currentData);

    const columns = [
      {
        name: "ID",
        width: "80px",
        formatter: (cell) => gridjs.html(`<span class="fw-semibold">${cell}</span>`),
      },
      "Nome",
      {
        name: "CPF/CNPJ",
        width: "150px",
        formatter: (cell) => gridjs.html(`<span class="text-muted">${cell}</span>`),
      },
      {
        name: "Telefone",
        width: "140px",
        formatter: (cell) => gridjs.html(`<span class="text-muted">${cell}</span>`),
      },
      {
        name: "Email",
        width: "200px",
        formatter: (cell) => {
          if (cell === "-") return gridjs.html(`<span class="text-muted">${cell}</span>`);
          return gridjs.html(`<a href="mailto:${cell}" class="text-reset">${cell}</a>`);
        },
      },
      {
        name: "Status",
        width: "120px",
        formatter: (cell) => gridjs.html(`<span class="badge ${statusBadge(cell)}">${cell}</span>`),
      },
      {
        name: "Ações",
        width: "160px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          const base = (window.__BASE_URL__ || "").replace(/\/$/, "") + "/";
          const iconInfo = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';
          const iconEdit = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
          return gridjs.html(`
            <div class="d-flex gap-2 align-items-center">
              <a href="${base}admin/locatarios/detalhes/${id}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" title="Detalhes">${iconInfo}</a>
              <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center btn-edit-locatario" data-id="${id}" title="Editar">${iconEdit}</button>
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
    const json = await fetchJson(`${getBaseUrl()}admin/locatarios/listar`);
    renderGrid(json.data || []);
  };

  const modalEl = document.getElementById("modalLocatario");
  const formEl = document.getElementById("formLocatario");
  const btnAdd = document.getElementById("btn-add-locatario");
  const btnSave = document.getElementById("btnSalvarLocatario");
  const alertEl = document.getElementById("loc-form-alert");

  if (!modalEl || !formEl || !btnSave) return;

  const getBsModal = () => {
    return bootstrap.Modal.getOrCreateInstance(modalEl);
  };

  const setAlert = (msg) => {
    if (!alertEl) return;
    if (msg) {
      alertEl.textContent = msg;
      alertEl.classList.remove("d-none");
    } else {
      alertEl.classList.add("d-none");
    }
  };

  const lockButton = (lock, text = "") => {
    if (!btnSave) return;
    const btnText = btnSave.querySelector(".btn-text");
    if (lock) {
      btnSave.disabled = true;
      if (btnText) btnText.textContent = text || "Salvando...";
    } else {
      btnSave.disabled = false;
      const id = document.getElementById("cli_id")?.value || "";
      if (btnText) btnText.textContent = id ? "Salvar alterações" : "Adicionar";
    }
  };

  const resetForm = () => {
    if (!formEl) return;
    formEl.reset();
    document.getElementById("cli_id").value = "";
    document.getElementById("cli_ativo").checked = true;
    setAlert("");
    lockButton(false);
  };

  const formatCPFCNPJ = (value, tipo) => {
    if (!value) return "";
    const numbers = value.replace(/\D/g, "");
    if (tipo === "juridica" && numbers.length === 14) {
      return numbers.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
    } else if (tipo === "fisica" && numbers.length === 11) {
      return numbers.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4");
    }
    return value;
  };

  const formatPhone = (value, isWhatsApp = false) => {
    if (!value) return "";
    const numbers = value.replace(/\D/g, "");
    if (isWhatsApp && numbers.length === 11) {
      return numbers.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
    } else if (!isWhatsApp && numbers.length === 10) {
      return numbers.replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
    }
    return value;
  };

  const formatCEP = (value) => {
    if (!value) return "";
    const numbers = value.replace(/\D/g, "");
    if (numbers.length === 8) {
      return numbers.replace(/^(\d{5})(\d{3})$/, "$1-$2");
    }
    return value;
  };

  const fillForm = (data) => {
    if (!data) return;
    const setVal = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value ?? "";
    };
    const setCheck = (id, checked) => {
      const el = document.getElementById(id);
      if (el) el.checked = checked;
    };

    const tipoPessoa = data.cli_tipo_pessoa || "fisica";

    setVal("cli_id", data.id);
    setVal("cli_tipo_pessoa", tipoPessoa);
    setVal("cli_nome", data.cli_nome);
    setVal("cli_cpf_cnpj", formatCPFCNPJ(data.cli_cpf_cnpj, tipoPessoa));
    setVal("cli_data_nascimento", data.cli_data_nascimento ? data.cli_data_nascimento.slice(0, 10) : "");
    setCheck("cli_ativo", data.cli_ativo === 1 || data.cli_ativo === '1' || data.cli_ativo === true);
    setVal("cli_email", data.cli_email);
    setVal("cli_telefone", formatPhone(data.cli_telefone, false));
    setVal("cli_whatsapp", formatPhone(data.cli_whatsapp, true));
    setVal("cli_cnh_numero", data.cli_cnh_numero);
    setVal("cli_cnh_validade", data.cli_cnh_validade ? data.cli_cnh_validade.slice(0, 10) : "");
    setVal("cli_cep", formatCEP(data.cli_cep));
    setVal("cli_estado", data.cli_estado);
    setVal("cli_cidade", data.cli_cidade);
    setVal("cli_bairro", data.cli_bairro);
    setVal("cli_rua", data.cli_rua);
    setVal("cli_numero", data.cli_numero);
    setVal("cli_complemento", data.cli_complemento);
    setVal("cli_obs", data.cli_obs);

    // Aplicar máscaras novamente após preencher
    setupMasks();
  };

  const setupMasks = () => {
    if (typeof window.$ === "undefined" || !window.$.fn?.mask) return;

    const cpfCnpjEl = document.getElementById("cli_cpf_cnpj");
    const telefoneEl = document.getElementById("cli_telefone");
    const whatsappEl = document.getElementById("cli_whatsapp");
    const cepEl = document.getElementById("cli_cep");

    if (cpfCnpjEl) {
      const tipoPessoa = document.getElementById("cli_tipo_pessoa")?.value || "fisica";
      if (tipoPessoa === "fisica") {
        window.$(cpfCnpjEl).mask("000.000.000-00");
      } else if (tipoPessoa === "juridica") {
        window.$(cpfCnpjEl).mask("00.000.000/0000-00");
      } else {
        window.$(cpfCnpjEl).unmask();
      }
    }

    if (telefoneEl) window.$(telefoneEl).mask("(00) 0000-0000");
    if (whatsappEl) window.$(whatsappEl).mask("(00) 00000-0000");
    if (cepEl) window.$(cepEl).mask("00000-000");
  };

  const openCreate = () => {
    resetForm();
    getBsModal()?.show();
    setTimeout(() => document.getElementById("cli_nome")?.focus?.(), 150);
  };

  const openEdit = async (id) => {
    resetForm();
    lockButton(true, "Carregando...");
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/locatarios/editar/${id}`);
      fillForm(json.data);
      getBsModal()?.show();
      setTimeout(() => document.getElementById("cli_nome")?.focus?.(), 150);
    } catch (e) {
      setAlert(e.message || "Erro ao carregar locatário.");
      getBsModal()?.show();
    } finally {
      lockButton(false);
    }
  };

  const submit = async () => {
    if (!formEl) return;
    setAlert("");

    if (!formEl.checkValidity()) {
      formEl.reportValidity();
      return;
    }

    const id = document.getElementById("cli_id")?.value || "";
    const fd = new FormData(formEl);

    // Normalizar CPF/CNPJ removendo formatação (o backend espera apenas números)
    const cpfCnpjEl = document.getElementById("cli_cpf_cnpj");
    if (cpfCnpjEl && cpfCnpjEl.value) {
      const cpfCnpj = cpfCnpjEl.value.replace(/\D/g, "");
      fd.set("cli_cpf_cnpj", cpfCnpj);
    }

    // Normalizar telefones removendo formatação
    const telefoneEl = document.getElementById("cli_telefone");
    if (telefoneEl && telefoneEl.value) {
      fd.set("cli_telefone", telefoneEl.value.replace(/\D/g, ""));
    }

    const whatsappEl = document.getElementById("cli_whatsapp");
    if (whatsappEl && whatsappEl.value) {
      fd.set("cli_whatsapp", whatsappEl.value.replace(/\D/g, ""));
    }

    // Normalizar CEP removendo formatação
    const cepEl = document.getElementById("cli_cep");
    if (cepEl && cepEl.value) {
      fd.set("cli_cep", cepEl.value.replace(/\D/g, ""));
    }

    // Normalizar CNH removendo formatação
    const cnhEl = document.getElementById("cli_cnh_numero");
    if (cnhEl && cnhEl.value) {
      fd.set("cli_cnh_numero", cnhEl.value.replace(/\D/g, ""));
    }

    // Garantir que cli_ativo seja enviado corretamente
    const ativoEl = document.getElementById("cli_ativo");
    if (ativoEl) {
      fd.set("cli_ativo", ativoEl.checked ? "1" : "0");
    }

    lockButton(true, id ? "Salvando..." : "Adicionando...");
    try {
      const url = id
        ? `${getBaseUrl()}admin/locatarios/atualizar/${id}`
        : `${getBaseUrl()}admin/locatarios/criar`;

      const json = await fetchJson(url, { method: "POST", body: fd });
      getBsModal()?.hide();
      await reload();
      resetForm();
      if (json?.message) {
        // Feedback simples - pode melhorar com toast/alert
        alert(json.message);
      }
    } catch (e) {
      setAlert(e.message || "Erro ao salvar locatário.");
    } finally {
      lockButton(false);
    }
  };

  // Enter: avançar campo a campo (Shift+Enter volta)
  const enableEnterNavigation = () => {
    if (!formEl) return;
    formEl.addEventListener("keydown", (e) => {
      if (e.key !== "Enter") return;
      const tag = (e.target?.tagName || "").toLowerCase();
      if (tag === "textarea") return;
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
      if (next && next.tagName?.toLowerCase() === "input") {
        try { next.select?.(); } catch (_) {}
      }
    });
  };

  // Eventos
  btnAdd?.addEventListener("click", openCreate);
  btnSave?.addEventListener("click", submit);

  document.addEventListener("click", (e) => {
    const btn = e.target.closest?.(".btn-edit-locatario");
    if (!btn) return;
    const id = btn.getAttribute("data-id");
    if (id) openEdit(id);
  });

  modalEl?.addEventListener("hidden.bs.modal", () => {
    resetForm();
    lockButton(false);
  });

  // Máscaras iniciais
  document.getElementById("cli_tipo_pessoa")?.addEventListener("change", () => {
    setupMasks();
  });

  setupMasks();
  enableEnterNavigation();

  // Render inicial
  renderGrid(currentData);
  // Se não veio nada da primeira renderização, busca do backend
  if (!currentData || currentData.length === 0) {
    reload().catch(() => {});
  }
})();
