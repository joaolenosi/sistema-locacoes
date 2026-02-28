(function () {
    const manutencaoId = window.__MANUTENCAO_ID__;
    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + '/');
        return base.endsWith('/') ? base : base + '/';
    };

    if (!manutencaoId) return;

    const galeriaEl = document.getElementById('galeria-fotos-manutencao');
    const countEl = document.getElementById('fotos-count');
    const vaziaEl = document.getElementById('galeria-vazia');
    const inputFotos = document.getElementById('input-fotos-manutencao');
    const uploadAlert = document.getElementById('upload-fotos-alert');

    const setUploadAlert = (msg, isError) => {
        if (!uploadAlert) return;
        uploadAlert.classList.remove('d-none', 'alert-success', 'alert-danger');
        uploadAlert.classList.add(isError ? 'alert-danger' : 'alert-success');
        uploadAlert.textContent = msg || '';
        if (!msg) uploadAlert.classList.add('d-none');
    };

    const atualizarContador = () => {
        const total = galeriaEl ? galeriaEl.querySelectorAll('[data-foto-id]').length : 0;
        if (countEl) countEl.textContent = total;
        if (vaziaEl) vaziaEl.classList.toggle('d-none', total > 0);
    };

    const renderFoto = (f) => {
        const url = getBaseUrl() + 'admin/manutencao-inteligente/foto/' + f.id;
        const div = document.createElement('div');
        div.className = 'col-6 col-md-4 col-lg-3';
        div.setAttribute('data-foto-id', f.id);
        div.innerHTML =
            '<a href="' + url + '" class="glightbox d-block rounded overflow-hidden border" data-gallery="manutencao-fotos">' +
            '<img src="' + url + '" alt="Foto" class="img-fluid w-100" style="object-fit:cover; height:120px;">' +
            '</a>' +
            '<button type="button" class="btn btn-sm btn-outline-danger mt-1 w-100 btn-remover-foto" data-foto-id="' + f.id + '">Remover</button>';
        return div;
    };

    const setGaleriaFotos = (fotos) => {
        if (!galeriaEl || !Array.isArray(fotos)) return;
        galeriaEl.innerHTML = '';
        fotos.forEach(function (f) {
            galeriaEl.appendChild(renderFoto(f));
        });
        atualizarContador();
        if (typeof GLightbox !== 'undefined') {
            GLightbox({ selector: '[data-gallery="manutencao-fotos"]' });
        }
    };

    const removerFotoDom = (fotoId) => {
        const node = galeriaEl && galeriaEl.querySelector('[data-foto-id="' + fotoId + '"]');
        if (node) node.remove();
        atualizarContador();
    };

    // Upload
    if (inputFotos) {
        inputFotos.addEventListener('change', async function () {
            const files = this.files;
            if (!files || files.length === 0) return;
            setUploadAlert('', false);
            const fd = new FormData();
            for (let i = 0; i < files.length; i++) {
                fd.append('fotos[]', files[i]);
            }
            try {
                const res = await fetch(getBaseUrl() + 'admin/manutencao/' + manutencaoId + '/fotos', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                const json = await res.json().catch(() => ({}));
                if (json.success && json.fotos) {
                    setGaleriaFotos(json.fotos);
                    setUploadAlert('Fotos enviadas com sucesso.', false);
                    inputFotos.value = '';
                    if (typeof window.toastr !== 'undefined') window.toastr.success('Fotos enviadas.');
                } else {
                    setUploadAlert(json.message || 'Erro ao enviar fotos.', true);
                }
            } catch (e) {
                setUploadAlert('Erro ao enviar fotos.', true);
            }
        });
    }

    // Remover foto
    document.addEventListener('click', async function (e) {
        const btn = e.target && e.target.closest && e.target.closest('.btn-remover-foto');
        if (!btn) return;
        const fotoId = btn.getAttribute('data-foto-id');
        if (!fotoId) return;
        if (!confirm('Remover esta foto?')) return;
        try {
            const res = await fetch(getBaseUrl() + 'admin/manutencao/fotos/deletar/' + fotoId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            });
            const json = await res.json().catch(() => ({}));
            if (json.success) {
                removerFotoDom(fotoId);
                if (typeof window.toastr !== 'undefined') window.toastr.success('Foto removida.');
            } else {
                if (typeof window.toastr !== 'undefined') window.toastr.error(json.message || 'Erro ao remover.');
            }
        } catch (err) {
            if (typeof window.toastr !== 'undefined') window.toastr.error('Erro ao remover foto.');
        }
    });

    // Lightbox
    if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '[data-gallery="manutencao-fotos"]' });
    }

    atualizarContador();
})();

// Itens da manutenção (produtos e serviços)
(function () {
    const manutencaoId = window.__MANUTENCAO_ID__;
    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + '/');
        return base.endsWith('/') ? base : base + '/';
    };
    if (!manutencaoId) return;

    const tbodyItens = document.getElementById('tbody-itens-manutencao');
    const trVazios = document.getElementById('tr-itens-vazios');
    const manTotalDisplay = document.getElementById('man-total-display');
    const modalAdicionar = document.getElementById('modalAdicionarItem');
    const formItem = document.getElementById('formAdicionarItem');
    const btnSalvarItem = document.getElementById('btn-salvar-item-manutencao');
    const itemTipo = document.getElementById('item-tipo');
    const itemProdutoId = document.getElementById('item-produto-id');
    const itemServicoId = document.getElementById('item-servico-id');
    const itemQuantidade = document.getElementById('item-quantidade');
    const itemValorUnitDisplay = document.getElementById('item-valor-unit-display');
    const itemSubtotalDisplay = document.getElementById('item-subtotal-display');
    const formItemAlert = document.getElementById('form-item-alert');
    const grupoProduto = document.getElementById('grupo-produto');
    const grupoServico = document.getElementById('grupo-servico');

    const formatMoney = (v) => 'R$ ' + (typeof v === 'number' ? v : parseFloat(v || 0)).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    const atualizarManTotalDisplay = (total) => {
        if (manTotalDisplay) manTotalDisplay.textContent = formatMoney(total);
    };

    const getValorUnitario = () => {
        const tipo = (itemTipo && itemTipo.value) || 'produto';
        if (tipo === 'produto' && itemProdutoId) {
            const opt = itemProdutoId.options[itemProdutoId.selectedIndex];
            return opt ? parseFloat(opt.getAttribute('data-preco') || 0) : 0;
        }
        if (tipo === 'servico' && itemServicoId) {
            const opt = itemServicoId.options[itemServicoId.selectedIndex];
            return opt ? parseFloat(opt.getAttribute('data-preco') || 0) : 0;
        }
        return 0;
    };

    const atualizarPreviewItem = () => {
        const qtd = parseInt(itemQuantidade && itemQuantidade.value ? itemQuantidade.value : 1, 10) || 1;
        const vu = getValorUnitario();
        const subtotal = qtd * vu;
        if (itemValorUnitDisplay) itemValorUnitDisplay.textContent = formatMoney(vu);
        if (itemSubtotalDisplay) itemSubtotalDisplay.textContent = formatMoney(subtotal);
    };

    const toggleGruposItem = () => {
        const tipo = (itemTipo && itemTipo.value) || 'produto';
        if (grupoProduto) grupoProduto.classList.toggle('d-none', tipo !== 'produto');
        if (grupoServico) grupoServico.classList.toggle('d-none', tipo !== 'servico');
        if (itemProdutoId) itemProdutoId.required = tipo === 'produto';
        if (itemServicoId) itemServicoId.required = tipo === 'servico';
        atualizarPreviewItem();
    };

    const setFormItemAlert = (msg) => {
        if (!formItemAlert) return;
        formItemAlert.textContent = msg || '';
        formItemAlert.classList.toggle('d-none', !msg);
    };

    const renderItemRow = (item, podeEditar) => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-item-id', item.id);
        const tipoLabel = (item.mai_tipo_item || '') === 'produto' ? 'Produto' : 'Serviço';
        const tipoCls = (item.mai_tipo_item || '') === 'produto' ? 'info' : 'success';
        let html = '<td>' + (item.mai_descricao || '—') + '</td>' +
            '<td class="text-center"><span class="badge bg-' + tipoCls + '-subtle text-' + tipoCls + '">' + tipoLabel + '</span></td>' +
            '<td class="text-center">' + (item.mai_quantidade || 1) + '</td>' +
            '<td class="text-end">' + formatMoney(item.mai_valor_unitario) + '</td>' +
            '<td class="text-end fw-semibold">' + formatMoney(item.mai_valor_total) + '</td>';
        if (podeEditar) {
            html += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remover-item-manutencao" data-item-id="' + item.id + '" title="Remover">×</button></td>';
        }
        tr.innerHTML = html;
        return tr;
    };

    const adicionarItemNaTabela = (item, podeEditar) => {
        if (!tbodyItens) return;
        if (trVazios) trVazios.remove();
        const tr = renderItemRow(item, podeEditar);
        tbodyItens.appendChild(tr);
    };

    const removerItemDaTabela = (itemId) => {
        const tr = tbodyItens && tbodyItens.querySelector('[data-item-id="' + itemId + '"]');
        if (tr) tr.remove();
        const restantes = tbodyItens ? tbodyItens.querySelectorAll('tr[data-item-id]') : [];
        if (restantes.length === 0 && tbodyItens) {
            const trEmpty = document.createElement('tr');
            trEmpty.id = 'tr-itens-vazios';
            trEmpty.innerHTML = '<td colspan="6" class="text-muted text-center py-3">Nenhum item adicionado. Clique em "Adicionar item" para incluir produtos ou serviços.</td>';
            tbodyItens.appendChild(trEmpty);
        }
    };

    if (itemTipo) itemTipo.addEventListener('change', toggleGruposItem);
    if (itemProdutoId) itemProdutoId.addEventListener('change', atualizarPreviewItem);
    if (itemServicoId) itemServicoId.addEventListener('change', atualizarPreviewItem);
    if (itemQuantidade) itemQuantidade.addEventListener('input', atualizarPreviewItem);

    if (modalAdicionar) {
        modalAdicionar.addEventListener('show.bs.modal', function () {
            setFormItemAlert('');
            if (formItem) formItem.reset();
            if (itemQuantidade) itemQuantidade.value = '1';
            toggleGruposItem();
        });
    }

    if (btnSalvarItem) {
        btnSalvarItem.addEventListener('click', async function () {
            const tipo = (itemTipo && itemTipo.value) || 'produto';
            const produtoId = tipo === 'produto' && itemProdutoId ? parseInt(itemProdutoId.value, 10) : 0;
            const servicoId = tipo === 'servico' && itemServicoId ? parseInt(itemServicoId.value, 10) : 0;
            const quantidade = parseInt(itemQuantidade && itemQuantidade.value ? itemQuantidade.value : 1, 10) || 1;

            if (tipo === 'produto' && produtoId < 1) {
                setFormItemAlert('Selecione um produto.');
                return;
            }
            if (tipo === 'servico' && servicoId < 1) {
                setFormItemAlert('Selecione um serviço.');
                return;
            }

            const payload = { tipo_item: tipo, quantidade };
            if (tipo === 'produto') payload.produto_id = produtoId;
            else payload.servico_id = servicoId;

            btnSalvarItem.disabled = true;
            const btnText = btnSalvarItem.querySelector('.btn-text');
            const originalText = btnText ? btnText.textContent : '';
            if (btnText) btnText.textContent = 'Aguarde...';

            try {
                const res = await fetch(getBaseUrl() + 'admin/manutencao/' + manutencaoId + '/itens', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });
                const json = await res.json().catch(() => ({}));
                if (json.success && json.item) {
                    adicionarItemNaTabela(json.item, true);
                    atualizarManTotalDisplay(json.man_total);
                    setFormItemAlert('');
                    if (typeof bootstrap !== 'undefined' && modalAdicionar) {
                        const m = bootstrap.Modal.getInstance(modalAdicionar);
                        if (m) m.hide();
                    }
                    if (typeof window.toastr !== 'undefined') window.toastr.success('Item adicionado.');
                } else {
                    setFormItemAlert(json.message || 'Erro ao adicionar item.');
                }
            } catch (e) {
                setFormItemAlert('Erro ao adicionar item.');
            } finally {
                btnSalvarItem.disabled = false;
                if (btnText) btnText.textContent = originalText;
            }
        });
    }

    document.addEventListener('click', async function (e) {
        const btn = e.target && e.target.closest && e.target.closest('.btn-remover-item-manutencao');
        if (!btn) return;
        const itemId = btn.getAttribute('data-item-id');
        if (!itemId || !confirm('Remover este item da manutenção?')) return;

        const btnEl = btn;
        if (btnEl) { btnEl.disabled = true; }

        try {
            const res = await fetch(getBaseUrl() + 'admin/manutencao/itens/deletar/' + itemId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            });
            const json = await res.json().catch(() => ({}));
            if (json.success) {
                removerItemDaTabela(itemId);
                atualizarManTotalDisplay(json.man_total);
                if (typeof window.toastr !== 'undefined') window.toastr.success('Item removido.');
            } else {
                if (typeof window.toastr !== 'undefined') window.toastr.error(json.message || 'Erro ao remover item.');
                if (btnEl) btnEl.disabled = false;
            }
        } catch (err) {
            if (typeof window.toastr !== 'undefined') window.toastr.error('Erro ao remover item.');
            if (btnEl) btnEl.disabled = false;
        }
    });
})();
