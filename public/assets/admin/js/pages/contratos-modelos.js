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

    const initialContent = document.getElementById("contrato-modelo-conteudo")?.value || "";
    // Detectar se o conteúdo é HTML (contém tags) ou texto puro
    const isHtml = initialContent.includes("<") && initialContent.includes(">");
    if (isHtml) {
      // Se for HTML, usar dangerouslyPasteHTML para preservar formatação
      quill.clipboard.dangerouslyPasteHTML(0, initialContent);
    } else {
      // Se for texto puro, usar setText normalmente
      quill.setText(initialContent.replace(/\r\n/g, "\n"));
    }

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

    // Botão salvar: POST para backend e feedback
    document.getElementById("btnSalvarModeloContrato")?.addEventListener("click", async () => {
      const btn = document.getElementById("btnSalvarModeloContrato");
      const modeloIdEl = document.getElementById("con_modelo_id");
      const modeloId = modeloIdEl ? parseInt(modeloIdEl.value, 10) : 0;
      if (!modeloId) {
        alert("ID do modelo não encontrado.");
        return;
      }
      const conNome = (document.getElementById("con_nome")?.value || "").trim();
      const conDescricao = (document.getElementById("con_descricao")?.value || "").trim();
      const conConteudo = quill ? quill.root.innerHTML : (document.getElementById("contrato-modelo-conteudo-html")?.value || "");

      const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + (window.location.pathname.indexOf("/sistema") !== -1 ? "/sistema/" : "/"));
        return base.endsWith("/") ? base : base + "/";
      };
      const url = getBaseUrl() + "admin/contratos/modelo/atualizar/" + modeloId;

      if (btn) {
        btn.disabled = true;
        btn.textContent = "Salvando...";
      }
      try {
        const formData = new FormData();
        formData.set("con_nome", conNome);
        formData.set("con_descricao", conDescricao);
        formData.set("con_conteudo", conConteudo);
        const res = await fetch(url, {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
          body: formData,
        });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.success) {
          if (typeof toastr !== "undefined") toastr.success("Modelo salvo com sucesso.");
          else alert("Modelo salvo com sucesso.");
        } else {
          const msg = data.message || "Erro ao salvar o modelo.";
          if (typeof toastr !== "undefined") toastr.error(msg);
          else alert(msg);
        }
      } catch (err) {
        const msg = "Erro de conexão ao salvar.";
        if (typeof toastr !== "undefined") toastr.error(msg);
        else alert(msg);
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.textContent = "Salvar alterações";
        }
      }
    });
  };

  // Inicializa quando o tab abrir
  tabTrigger.addEventListener("shown.bs.tab", initEditor);

  // Se já estiver aberto por algum motivo
  if (tabTrigger.classList.contains("active")) {
    initEditor();
  }
})();

