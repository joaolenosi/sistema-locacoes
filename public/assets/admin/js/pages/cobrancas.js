// Listagem de Cobranças com GridJS
(() => {
  const root = document.getElementById("table-cobrancas");
  if (!root || typeof gridjs === "undefined") return;

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

  // Dataset simulado
  let rows = [
    {
      id: 1,
      locacao: "LC-2026-001",
      locatario: "João Silva",
      veiculo: "ABC-1234 (Onix)",
      recorrencia: "Mensal",
      competencia: "Jan/26",
      vencimento: "10/01/2026",
      valor: 1500.0,
      status: "Pendente",
    },
    {
      id: 2,
      locacao: "LC-2026-002",
      locatario: "Maria Santos",
      veiculo: "XYZ-5678 (HB20)",
      recorrencia: "Mensal",
      competencia: "Jan/26",
      vencimento: "05/01/2026",
      valor: 1200.0,
      status: "Em atraso",
    },
    {
      id: 3,
      locacao: "LC-2026-003",
      locatario: "Pedro Oliveira",
      veiculo: "DEF-9012 (Corolla)",
      recorrencia: "Mensal",
      competencia: "Jan/26",
      vencimento: "15/01/2026",
      valor: 2200.0,
      status: "Pendente",
    },
    {
      id: 4,
      locacao: "LC-2026-004",
      locatario: "Ana Costa",
      veiculo: "GHI-3456 (Civic)",
      recorrencia: "Semanal",
      competencia: "Semana 02/26",
      vencimento: "12/01/2026",
      valor: 450.0,
      status: "Pendente",
    },
    {
      id: 5,
      locacao: "LC-2026-005",
      locatario: "Transportes ABC Ltda",
      veiculo: "MNO-2468 (Gol)",
      recorrencia: "Diária",
      competencia: "20/01/2026",
      vencimento: "20/01/2026",
      valor: 110.0,
      status: "Em atraso",
    },
    {
      id: 6,
      locacao: "LC-2026-006",
      locatario: "Fernanda Lima",
      veiculo: "PQR-1357 (Compass)",
      recorrencia: "Mensal",
      competencia: "Fev/26",
      vencimento: "01/02/2026",
      valor: 2000.0,
      status: "Pendente",
    },
    {
      id: 7,
      locacao: "LC-2026-007",
      locatario: "Logística XYZ EIRELI",
      veiculo: "VWX-4680 (Renegade)",
      recorrencia: "Mensal",
      competencia: "Jan/26",
      vencimento: "08/01/2026",
      valor: 1900.0,
      status: "Em atraso",
    },
    {
      id: 8,
      locacao: "LC-2026-008",
      locatario: "Juliana Ferreira",
      veiculo: "YZA-2468 (Creta)",
      recorrencia: "Semanal",
      competencia: "Semana 03/26",
      vencimento: "19/01/2026",
      valor: 520.0,
      status: "Pendente",
    },
    {
      id: 9,
      locacao: "LC-2026-009",
      locatario: "Carlos Pereira",
      veiculo: "JKL-7890 (Fiesta)",
      recorrencia: "Diária",
      competencia: "22/01/2026",
      vencimento: "22/01/2026",
      valor: 90.0,
      status: "Pendente",
    },
    {
      id: 10,
      locacao: "LC-2026-010",
      locatario: "Roberto Alves",
      veiculo: "STU-8024 (T-Cross)",
      recorrencia: "Mensal",
      competencia: "Jan/26",
      vencimento: "25/01/2026",
      valor: 1700.0,
      status: "Pendente",
    },
  ];

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

  const getFilteredRows = () => {
    const status = document.getElementById("filtro-status")?.value || "";
    const rec = document.getElementById("filtro-recorrencia")?.value || "";
    const loc = document.getElementById("filtro-locatario")?.value || "";

    return rows.filter((r) => {
      if (status && r.status !== status) return false;
      if (rec && r.recorrencia !== rec) return false;
      if (loc && r.locatario !== loc) return false;
      return true;
    });
  };

  let grid;
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

  // Eventos filtros
  ["filtro-status", "filtro-recorrencia", "filtro-locatario"].forEach((id) => {
    document.getElementById(id)?.addEventListener("change", () => renderGrid());
  });

  // Delegação: marcar quitada remove da lista
  root.addEventListener("click", (e) => {
    const btn = e.target.closest?.(".js-quitar");
    if (!btn) return;
    const id = Number(btn.getAttribute("data-id"));
    rows = rows.filter((r) => r.id !== id);
    renderGrid();
  });

  renderGrid();
})();

