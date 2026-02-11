(() => {
  const planos = Array.isArray(window.__CONFIG_PLANOS__) ? window.__CONFIG_PLANOS__ : [];

  // ---- Planos: toggle mensal/anual ----
  const atualizarPrecos = () => {
    const periodo = document.querySelector('input[name="periodo"]:checked')?.value || "mensal";
    planos.forEach((plano) => {
      const precoOriginal = document.getElementById(`preco-original-${plano.id}`);
      const precoAtual = document.getElementById(`preco-atual-${plano.id}`);
      const periodoTexto = document.getElementById(`periodo-texto-${plano.id}`);
      if (!precoOriginal || !precoAtual || !periodoTexto) return;

      if (periodo === "anual") {
        const precoMensalAnual = Number(plano.preco_anual || 0) / 12;
        precoOriginal.textContent = `de R$ ${precoMensalAnual.toFixed(2).replace(".", ",")}`;
        precoAtual.textContent = `R$ ${Number(plano.preco_anual || 0).toFixed(2).replace(".", ",")}`;
        periodoTexto.textContent = "/ Ano";
      } else {
        const precoOriginalMensal = Number(plano.preco_mensal || 0) * 1.2;
        precoOriginal.textContent = `de R$ ${precoOriginalMensal.toFixed(2).replace(".", ",")}`;
        precoAtual.textContent = `R$ ${Number(plano.preco_mensal || 0).toFixed(2).replace(".", ",")}`;
        periodoTexto.textContent = "/ Mês";
      }
    });
  };

  document.querySelectorAll('input[name="periodo"]').forEach((r) => r.addEventListener("change", atualizarPrecos));
  atualizarPrecos();

  window.assinarPlano = function (planoId) {
    const periodo = document.querySelector('input[name="periodo"]:checked')?.value || "mensal";
    const plano = planos.find((p) => Number(p.id) === Number(planoId));
    if (!plano) return;
    if (
      window.confirm(
        `Deseja assinar o plano ${plano.nome} no período ${periodo === "mensal" ? "mensal" : "anual"}?`
      )
    ) {
      console.log("Assinar plano:", planoId, periodo);
      window.alert("Assinatura iniciada! (Implementar pagamento)");
    }
  };
})();
