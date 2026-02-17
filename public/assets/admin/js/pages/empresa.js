(() => {
  const formEl = document.getElementById("form-locadora");
  if (!formEl) return;

  // Helper para garantir base URL com barra final
  const getBaseUrl = () => {
    const base = window.__BASE_URL__ || window.location.origin;
    return base.endsWith('/') ? base : base + '/';
  };

  const alertEl = document.getElementById("empresa-form-alert");
  const btnSave = document.getElementById("btnSalvarEmpresa");
  const cpfCnpjInput = document.getElementById("cpf_cnpj");
  const cepInput = document.getElementById("cep");
  const cepErroEl = document.getElementById("cep-erro");
  const btnBuscarCep = document.getElementById("btn-buscar-cep");
  const telefoneInput = document.getElementById("telefone");
  const tipoEmpresaRadios = document.querySelectorAll('input[name="tipo_empresa"]');
  const labelCpfCnpj = document.getElementById("label-cpf-cnpj");

  const getDigits = (s) => String(s || "").replace(/\D/g, "");

  const setAlert = (message, type = "danger") => {
    if (!alertEl) return;
    if (!message) {
      alertEl.classList.add("d-none");
      alertEl.textContent = "";
      alertEl.classList.remove("alert-success", "alert-danger");
      alertEl.classList.add("alert-danger");
      return;
    }
    alertEl.textContent = message;
    alertEl.classList.remove("d-none");
    alertEl.classList.remove("alert-success", "alert-danger");
    alertEl.classList.add(type === "success" ? "alert-success" : "alert-danger");
  };

  const setCepErro = (message) => {
    if (!cepErroEl) return;
    if (!message) {
      cepErroEl.classList.add("d-none");
      cepErroEl.textContent = "";
      return;
    }
    cepErroEl.textContent = message;
    cepErroEl.classList.remove("d-none");
  };

  const lockButton = (locked, text) => {
    if (!btnSave) return;
    btnSave.disabled = locked;
    if (text) btnSave.textContent = text;
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

  const fetchJson = async (url, options = {}) => {
    const res = await fetch(url, {
      headers: options.headers || { "X-Requested-With": "XMLHttpRequest" },
      ...options,
    });
    const json = await res.json().catch(() => null);
    if (!res.ok) {
      const msg = json?.message || "Erro na requisição.";
      throw new Error(msg);
    }
    return json;
  };

  // ---- Máscaras ----
  const formatCpf = (digits) => {
    let d = getDigits(digits).slice(0, 11);
    if (d.length > 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
    if (d.length > 6) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
    if (d.length > 3) return `${d.slice(0, 3)}.${d.slice(3)}`;
    return d;
  };

  const formatCnpj = (digits) => {
    let d = getDigits(digits).slice(0, 14);
    if (d.length > 12)
      return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8, 12)}-${d.slice(12)}`;
    if (d.length > 8) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5, 8)}/${d.slice(8)}`;
    if (d.length > 5) return `${d.slice(0, 2)}.${d.slice(2, 5)}.${d.slice(5)}`;
    if (d.length > 2) return `${d.slice(0, 2)}.${d.slice(2)}`;
    return d;
  };

  const getTipoPessoa = () => {
    const checked = document.querySelector('input[name="tipo_empresa"]:checked');
    return checked ? checked.value : "juridica";
  };

  const syncCpfCnpjUI = () => {
    if (!cpfCnpjInput) return;
    const tipo = getTipoPessoa();
    const digits = getDigits(cpfCnpjInput.value);
    if (tipo === "fisica") {
      cpfCnpjInput.placeholder = "000.000.000-00";
      cpfCnpjInput.maxLength = 14;
      if (labelCpfCnpj) labelCpfCnpj.textContent = "CPF *";
      if (!cpfCnpjInput.readOnly) cpfCnpjInput.value = formatCpf(digits);
    } else {
      cpfCnpjInput.placeholder = "00.000.000/0000-00";
      cpfCnpjInput.maxLength = 18;
      if (labelCpfCnpj) labelCpfCnpj.textContent = "CNPJ *";
      if (!cpfCnpjInput.readOnly) cpfCnpjInput.value = formatCnpj(digits);
    }
  };

  const maskCep = () => {
    if (!cepInput) return;
    const digits = getDigits(cepInput.value).slice(0, 8);
    cepInput.value = digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
  };

  const maskTelefone = () => {
    if (!telefoneInput) return;
    let d = getDigits(telefoneInput.value).slice(0, 11);
    if (d.length > 10) {
      telefoneInput.value = `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
      return;
    }
    if (d.length > 6) {
      telefoneInput.value = `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
      return;
    }
    if (d.length > 2) {
      telefoneInput.value = `(${d.slice(0, 2)}) ${d.slice(2)}`;
      return;
    }
    telefoneInput.value = d.length > 0 ? `(${d}` : "";
  };

  if (cpfCnpjInput) {
    const digits = getDigits(cpfCnpjInput.value);
    const shouldBeFisica = digits.length > 0 && digits.length <= 11;
    const radioFisica = document.getElementById("pessoa-fisica");
    const radioJuridica = document.getElementById("pessoa-juridica");
    if (shouldBeFisica && radioFisica) radioFisica.checked = true;
    if (!shouldBeFisica && radioJuridica) radioJuridica.checked = true;
  }

  tipoEmpresaRadios.forEach((r) => r.addEventListener("change", syncCpfCnpjUI));
  cpfCnpjInput?.addEventListener("input", syncCpfCnpjUI);
  cepInput?.addEventListener("input", maskCep);
  telefoneInput?.addEventListener("input", maskTelefone);

  syncCpfCnpjUI();
  maskCep();
  maskTelefone();

  // ---- ViaCEP ----
  const VIACEP_FORMATO = "json"; // ou "xml"

  const isValidCepFormat = (cep) => {
    const digits = getDigits(cep);
    return digits.length === 8 && /^\d{8}$/.test(digits);
  };

  const buscarCep = async () => {
    setCepErro("");
    const cep = getDigits(cepInput?.value || "");
    if (!isValidCepFormat(cep)) {
      setCepErro("CEP deve ter 8 dígitos.");
      return;
    }
    const url = `https://viacep.com.br/ws/${cep}/${VIACEP_FORMATO}/`;
    try {
      const res = await fetch(url);
      if (res.status === 400) {
        setCepErro("CEP inválido.");
        return;
      }
      const data = await res.json().catch(() => null);
      if (data && data.erro === true) {
        setCepErro("CEP não encontrado.");
        return;
      }
      if (data) {
        const enderecoEl = document.getElementById("endereco");
        const bairroEl = document.getElementById("bairro");
        const cidadeEl = document.getElementById("cidade");
        const estadoEl = document.getElementById("estado");
        if (enderecoEl) enderecoEl.value = data.logradouro || "";
        if (bairroEl) bairroEl.value = data.bairro || "";
        if (cidadeEl) cidadeEl.value = data.localidade || "";
        if (estadoEl) {
          const uf = (data.uf || "").toUpperCase();
          estadoEl.value = uf;
        }
      }
    } catch (e) {
      setCepErro("Erro ao buscar CEP.");
    }
  };

  if (btnBuscarCep) btnBuscarCep.addEventListener("click", buscarCep);
  if (cepInput) {
    cepInput.addEventListener("blur", () => {
      if (getDigits(cepInput.value).length === 8) buscarCep();
    });
  }

  // ---- Submit Empresa (AJAX) ----
  formEl.addEventListener("submit", async (e) => {
    e.preventDefault();
    setAlert("");

    const fd = new FormData(formEl);
    if (cpfCnpjInput) fd.set("cpf_cnpj", getDigits(cpfCnpjInput.value));
    if (cepInput) fd.set("cep", getDigits(cepInput.value));
    if (telefoneInput) fd.set("telefone", getDigits(telefoneInput.value));

    lockButton(true, "Salvando...");
    try {
      const json = await fetchJson(`${getBaseUrl()}admin/configuracoes/atualizar-empresa`, {
        method: "POST",
        body: fd,
      });
      setAlert(json?.message || "Salvo com sucesso.", "success");
    } catch (err) {
      setAlert(err?.message || "Erro ao salvar.", "danger");
    } finally {
      lockButton(false, "Salvar alterações");
    }
  });
})();
