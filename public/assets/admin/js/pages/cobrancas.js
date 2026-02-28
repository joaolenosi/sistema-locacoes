// Listagem de Cobranças com GridJS
(() => {
  const root = document.getElementById("table-cobrancas");
  if (!root || typeof gridjs === "undefined") return;

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

  const fmtBRL = (value) =>
    "R$ " + Number(value || 0).toFixed(2).replace(".", ",");

  const badgeStatus = (status) => {
    const cls =
      status === "Em atraso"
        ? "bg-danger-subtle text-danger"
        : "bg-warning-subtle text-warning";
    return gridjs.html(`<span class="badge ${cls}">${status}</span>`);
  };

  const badgeRec = (rec) => {
    const map = {
      Diária: "bg-info-subtle text-info",
      Semanal: "bg-primary-subtle text-primary",
      Mensal: "bg-success-subtle text-success",
      Quinzenal: "bg-secondary-subtle text-secondary",
    };
    const cls = map[rec] || "bg-secondary-subtle text-secondary";
    return gridjs.html(`<span class="badge ${cls}">${rec}</span>`);
  };

  const rowToGrid = (r) => [
    String(r.id),
    r.locacao,
    r.locatario,
    r.veiculo,
    r.competencia,
    r.vencimento,
    r.recorrencia,
    r.valor,
    r.status,
    r.id, // usado na coluna ações
  ];

  let currentData = [];
  let grid;

  const fetchJson = async (url, options = {}) => {
    const res = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      ...options
    });
    const json = await res.json().catch(() => null);
    if (!res.ok) throw new Error(json?.message || 'Erro na requisição.');
    return json;
  };

  const getFilteredRows = () => {
    const status = document.getElementById("filtro-status")?.value || "";
    const rec = document.getElementById("filtro-recorrencia")?.value || "";
    const loc = document.getElementById("filtro-locatario")?.value || "";

    return currentData.filter((r) => {
      if (status && r.status !== status) return false;
      if (rec && r.recorrencia !== rec) return false;
      if (loc && r.locatario !== loc) return false;
      return true;
    });
  };

  const renderGrid = () => {
    const data = getFilteredRows().map(rowToGrid);

    if (!grid) {
      grid = new gridjs.Grid({
        columns: [
          {
            name: "ID",
            width: "70px",
            formatter: (cell) =>
              gridjs.html(`<span class="fw-semibold">${cell}</span>`),
          },
          { name: "Locação", width: "120px" },
          "Locatário",
          { name: "Veículo", width: "180px" },
          { name: "Competência", width: "130px" },
          { name: "Vencimento", width: "120px" },
          {
            name: "Recorrência",
            width: "120px",
            formatter: (cell) => badgeRec(cell),
          },
          {
            name: "Valor",
            width: "120px",
            formatter: (cell) => gridjs.html(fmtBRL(cell)),
          },
          {
            name: "Status",
            width: "120px",
            formatter: (cell) => badgeStatus(cell),
          },
          {
            name: "Ações",
            width: "140px",
            formatter: (id) =>
              gridjs.html(
                `<button type="button" class="btn btn-sm btn-success js-quitar" data-id="${id}">Marcar quitada</button>`
              ),
          },
        ],
        pagination: { limit: 7 },
        sort: true,
        search: true,
        language: ptBR,
        data,
      }).render(root);
    } else {
      grid.updateConfig({ data }).forceRender();
    }
  };

  const reload = async () => {
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/cobrancas/listar`);
      currentData = Array.isArray(json?.data) ? json.data : [];
      renderGrid();
    } catch (e) {
      console.error('Erro ao recarregar dados:', e);
      currentData = [];
      renderGrid();
    }
  };

  const quitarCobranca = async (id) => {
    const result = await Swal.fire({
      icon: 'question',
      title: 'Confirmar',
      text: 'Deseja realmente marcar esta cobrança como quitada?',
      showCancelButton: true,
      confirmButtonText: 'Sim, quitar',
      cancelButtonText: 'Cancelar'
    });
    if (!result?.isConfirmed) return;

    try {
      await fetchJson(`${getBaseUrl()}admin/cobrancas/quitar/${id}`, { method: 'POST' });
      await reload();
      Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: 'Cobrança quitada com sucesso.'
      });
    } catch (e) {
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Erro ao quitar cobrança: ' + (e.message || 'Erro desconhecido')
      });
    }
  };

  // Eventos filtros
  ["filtro-status", "filtro-recorrencia", "filtro-locatario"].forEach((id) => {
    document.getElementById(id)?.addEventListener("change", () => renderGrid());
  });

  // Delegação: marcar quitada
  root.addEventListener("click", (e) => {
    const btn = e.target.closest?.(".js-quitar");
    if (!btn) return;
    const id = Number(btn.getAttribute("data-id"));
    quitarCobranca(id);
  });

  // Inicialização
  renderGrid();
  reload();
})();
