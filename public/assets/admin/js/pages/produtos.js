(() => {
  const tableEl = document.getElementById("table-produtos");
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

  const moneyBR = (val) => {
    const n = Number(val || 0);
    return "R$ " + n.toFixed(2).replace(".", ",");
  };

  const statusLabel = (ativo) => (String(ativo) === "1" ? "Ativo" : "Inativo");
  const statusBadge = (label) =>
    label === "Ativo" ? "bg-success-subtle text-success" : "bg-secondary-subtle text-secondary";

  const estoqueBadge = (atual, minimo) => {
    const a = Number(atual || 0);
    const m = Number(minimo || 0);
    if (a < m) return "bg-danger-subtle text-danger";
    if (a <= m + 5) return "bg-warning-subtle text-warning";
    return "bg-success-subtle text-success";
  };

  const toRows = (items) =>
    (items || []).map((p) => [
      String(p.id),
      p.pro_sku || "-",
      p.pro_nome || "-",
      p.pro_categoria || "-",
      moneyBR(p.pro_preco_venda),
      `${p.pro_estoque_atual ?? 0}|${p.pro_estoque_minimo ?? 0}`,
      statusLabel(p.pro_ativo),
      String(p.id),
    ]);

  let grid = null;
  let currentData = Array.isArray(window.__PRODUTOS__) ? window.__PRODUTOS__ : [];

  const renderGrid = (items) => {
    currentData = items || [];
    const rows = toRows(currentData);

    const columns = [
      { name: "ID", width: "80px", formatter: (c) => gridjs.html(`<span class="fw-semibold">${c}</span>`) },
      {
        name: "SKU",
        width: "120px",
        formatter: (c) => gridjs.html(`<span class="badge bg-primary-subtle text-primary">${c}</span>`),
      },
      "Nome",
      "Categoria",
      { name: "Preço Venda", width: "130px", formatter: (c) => gridjs.html(`<span class="fw-semibold">${c}</span>`) },
      {
        name: "Estoque",
        width: "120px",
        formatter: (cell) => {
          const [estoque, minimo] = String(cell || "0|0").split("|");
          return gridjs.html(`<span class="badge ${estoqueBadge(estoque, minimo)}">${estoque}</span>`);
        },
      },
      {
        name: "Status",
        width: "120px",
        formatter: (c) => gridjs.html(`<span class="badge ${statusBadge(c)}">${c}</span>`),
      },
      {
        name: "Ações",
        width: "160px",
        formatter: (_cell, row) => {
          const id = row.cells[0].data;
          const nome = (row.cells[2]?.data || "").toString().replace(/"/g, "&quot;");
          const iconTrash = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
          return gridjs.html(`
            <div class="d-flex gap-2 align-items-center">
              <button type="button" class="btn btn-sm btn-outline-primary btn-edit-produto" data-id="${id}" title="Editar">
                <iconify-icon icon="iconamoon:edit-duotone" class="fs-18"></iconify-icon>
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center btn-delete-produto" data-id="${id}" data-nome="${nome}" title="Excluir">${iconTrash}</button>
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
    const res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" }, ...options });
    const json = await res.json().catch(() => null);
    if (!res.ok) throw new Error(json?.message || "Erro na requisição.");
    return json;
  };

  const reload = async () => {
    const json = await fetchJson(`${getBaseUrl()}admin/cadastro/produtos/listar`);
    renderGrid(json.data || []);
  };

  const modalEl = document.getElementById("modalProduto");
  const formEl = document.getElementById("formProduto");
  const alertEl = document.getElementById("pro-form-alert");
  const btnSave = document.getElementById("btnSalvarProduto");
  const btnAdd = document.getElementById("btn-add-produto");
  const titleEl = document.getElementById("modalProdutoLabel");

  const getBsModal = () => {
    if (!modalEl) return null;
    if (!window.bootstrap?.Modal) return null;
    return window.bootstrap.Modal.getOrCreateInstance(modalEl);
  };

  const setAlert = (msg) => {
    if (!alertEl) return;
    if (!msg) {
      alertEl.classList.add("d-none");
      alertEl.textContent = "";
      return;
    }
    alertEl.textContent = msg;
    alertEl.classList.remove("d-none");
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

  const resetForm = () => {
    setAlert("");
    formEl?.reset();
    const idEl = document.getElementById("pro_id");
    if (idEl) idEl.value = "";
    if (titleEl) titleEl.textContent = "Cadastrar produto";
    btnSave?.querySelector(".btn-text") && (btnSave.querySelector(".btn-text").textContent = "Adicionar");
  };

  const fillForm = (p) => {
    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val ?? "";
    };
    setVal("pro_id", p.id);
    setVal("pro_nome", p.pro_nome);
    setVal("pro_sku", p.pro_sku);
    setVal("pro_categoria", p.pro_categoria);
    setVal("pro_marca", p.pro_marca);
    setVal("pro_preco_venda", p.pro_preco_venda ? String(p.pro_preco_venda).replace(".", ",") : "");
    setVal("pro_preco_custo", p.pro_preco_custo ? String(p.pro_preco_custo).replace(".", ",") : "");
    setVal("pro_estoque_atual", p.pro_estoque_atual ?? "");
    setVal("pro_estoque_minimo", p.pro_estoque_minimo ?? "");
    setVal("pro_ativo", String(p.pro_ativo ?? "1"));

    if (titleEl) titleEl.textContent = "Editar produto";
    btnSave?.querySelector(".btn-text") && (btnSave.querySelector(".btn-text").textContent = "Salvar alterações");
  };

  // Enter: avançar campo a campo
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

  const openCreate = () => {
    resetForm();
    getBsModal()?.show();
    setTimeout(() => document.getElementById("pro_nome")?.focus?.(), 150);
  };

  const openEdit = async (id) => {
    resetForm();
    lockButton(true, "Carregando...");
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/cadastro/produtos/editar/${id}`);
      fillForm(json.data);
      getBsModal()?.show();
      setTimeout(() => document.getElementById("pro_nome")?.focus?.(), 150);
    } catch (e) {
      setAlert(e.message || "Erro ao carregar produto.");
      getBsModal()?.show();
    } finally {
      lockButton(false);
    }
  };

  const submit = async () => {
    if (!formEl) return;
    setAlert("");

    const required = ["pro_nome"];
    for (const r of required) {
      const el = document.getElementById(r);
      if (el && !String(el.value || "").trim()) {
        setAlert("Preencha os campos obrigatórios.");
        return;
      }
    }

    const id = document.getElementById("pro_id")?.value || "";
    const fd = new FormData(formEl);

    // Normalizar money para backend
    const pv = document.getElementById("pro_preco_venda")?.value || "";
    if (pv) fd.set("pro_preco_venda", pv.replace(/\./g, "").replace(",", "."));
    const pc = document.getElementById("pro_preco_custo")?.value || "";
    if (pc) fd.set("pro_preco_custo", pc.replace(/\./g, "").replace(",", "."));

    lockButton(true, id ? "Salvando..." : "Adicionando...");
    try {
      const url = id
        ? `${getBaseUrl()}admin/cadastro/produtos/atualizar/${id}`
        : `${getBaseUrl()}admin/cadastro/produtos/criar`;

      await fetchJson(url, { method: "POST", body: fd });
      getBsModal()?.hide();
      await reload();
      resetForm();
    } catch (e) {
      setAlert(e.message || "Erro ao salvar produto.");
    } finally {
      lockButton(false);
    }
  };

  btnAdd?.addEventListener("click", openCreate);
  btnSave?.addEventListener("click", submit);

  document.addEventListener("click", (e) => {
    const btnEdit = e.target.closest?.(".btn-edit-produto");
    if (btnEdit) {
      const id = btnEdit.getAttribute("data-id");
      if (id) openEdit(id);
      return;
    }

    const btnDelete = e.target.closest?.(".btn-delete-produto");
    if (btnDelete) {
      const id = btnDelete.getAttribute("data-id");
      const nome = btnDelete.getAttribute("data-nome") || "";
      if (!id) return;

      const msg = nome ? `Você está prestes a excluir o produto "${nome}".` : "Você está prestes a excluir este produto.";

      const executeDelete = async () => {
        btnDelete.disabled = true;
        btnDelete.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        try {
          const res = await fetch(`${getBaseUrl()}admin/cadastro/produtos/excluir/${id}`, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest", "Content-Type": "application/json" },
          });
          const json = await res.json().catch(() => null);
          if (res.ok && json?.success) {
            if (typeof Swal !== "undefined") {
              await Swal.fire({
                icon: "success",
                title: "Produto excluído",
                text: json.message || "O produto foi excluído com sucesso.",
                confirmButtonText: "OK",
              });
            } else {
              alert(json.message || "Produto excluído com sucesso.");
            }
            await reload();
          } else {
            const errMsg = json?.message || "Não foi possível excluir o produto.";
            if (typeof Swal !== "undefined") {
              Swal.fire({
                icon: "error",
                title: "Não foi possível excluir",
                text: errMsg,
                confirmButtonText: "OK",
              });
            } else {
              alert(errMsg);
            }
          }
        } catch (err) {
          if (typeof Swal !== "undefined") {
            Swal.fire({
              icon: "error",
              title: "Erro",
              text: "Erro ao excluir produto. Tente novamente.",
              confirmButtonText: "OK",
            });
          } else {
            alert("Erro ao excluir produto. Tente novamente.");
          }
        } finally {
          btnDelete.disabled = false;
          btnDelete.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';
        }
      };

      if (typeof Swal !== "undefined") {
        Swal.fire({
          icon: "warning",
          title: "Excluir produto?",
          text: msg,
          showCancelButton: true,
          confirmButtonText: "Sim, excluir",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) executeDelete();
        });
      } else if (confirm("Tem certeza que deseja excluir este produto?")) {
        executeDelete();
      }
    }
  });

  modalEl?.addEventListener("hidden.bs.modal", () => {
    resetForm();
    lockButton(false);
  });

  // Máscara monetária (jQuery Mask Plugin)
  if (typeof window.$ !== "undefined" && window.$.fn?.mask) {
    window.$(".money").mask("000.000.000.000.000,00", { reverse: true });
    window.$(".money").focusout(function () {
      if (window.$(this).val().length <= 2 && window.$(this).val().length > 0) {
        const temp = window.$(this).val();
        window.$(this).val(temp + ",00");
      }
    });
  }

  renderGrid(currentData);
  if (!currentData || currentData.length === 0) reload().catch(() => {});
  enableEnterNavigation();
})();
