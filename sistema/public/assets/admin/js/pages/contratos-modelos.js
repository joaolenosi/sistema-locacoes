(() => {
  const tabTrigger = document.getElementById("tabModelosContratos");
  if (!tabTrigger) return;

  let quill = null;
  let initialized = false;

  const initEditor = () => {
    if (initialized) return;
    initialized = true;

    if (typeof Quill === "undefined") {
      console.warn("Quill não está disponível (vendor.js).");
      return;
    }

    const editorEl = document.getElementById("contrato-modelo-editor");
    if (!editorEl) return;

    quill = new Quill("#contrato-modelo-editor", {
      theme: "snow",
      modules: {
        toolbar: [
          [{ font: [] }, { size: [] }],
          ["bold", "italic", "underline", "strike"],
          [{ color: [] }, { background: [] }],
          [{ header: [false, 1, 2, 3, 4] }, "blockquote", "code-block"],
          [{ list: "ordered" }, { list: "bullet" }, { indent: "-1" }, { indent: "+1" }],
          ["link"],
          ["clean"],
        ],
      },
    });

    const initialText = document.getElementById("contrato-modelo-conteudo")?.value || "";
    quill.setText(initialText.replace(/\r\n/g, "\n"));

    const htmlOut = document.getElementById("contrato-modelo-conteudo-html");
    if (htmlOut) {
      quill.on("text-change", () => {
        htmlOut.value = quill.root.innerHTML;
      });
      htmlOut.value = quill.root.innerHTML;
    }

    // Inserir variável no cursor
    document.addEventListener("click", async (e) => {
      const btn = e.target.closest?.("[data-insert-variable]");
      if (!btn || !quill) return;
      const variable = btn.getAttribute("data-insert-variable") || "";
      if (!variable) return;

      const range = quill.getSelection(true);
      const index = range ? range.index : quill.getLength();
      quill.insertText(index, variable);
      quill.setSelection(index + variable.length, 0);

      // Copiar também para clipboard (best-effort)
      try {
        await navigator.clipboard.writeText(variable);
      } catch (_) {
        // ignore
      }

      // Feedback visual: adicionar classe "copied" ao pill
      const pill = btn.querySelector('.contrato-variavel-pill');
      if (pill) {
        pill.classList.add('copied');
        setTimeout(() => {
          pill.classList.remove('copied');
        }, 2000); // Remove após 2 segundos
      }
    });

    // Botão salvar (UI apenas por enquanto)
    document.getElementById("btnSalvarModeloContrato")?.addEventListener("click", () => {
      alert("Modelo pronto para salvar (teste de UI).");
    });
  };

  // Inicializa quando o tab abrir
  tabTrigger.addEventListener("shown.bs.tab", initEditor);

  // Se já estiver aberto por algum motivo
  if (tabTrigger.classList.contains("active")) {
    initEditor();
  }
})();

