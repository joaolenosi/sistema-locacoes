(function () {
    const getBaseUrl = () => {
        const base = window.__BASE_URL__ || (window.location.origin + '/');
        return base.endsWith('/') ? base : base + '/';
    };
    const fetchJson = (url, options = {}) => {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, ...options })
            .then(r => r.json().catch(() => null))
            .then(json => {
                if (json && !json.success && json.message) throw new Error(json.message);
                return json;
            });
    };

    const previewEl = document.getElementById('preview-imagem-checklist');
    const placeholderEl = document.getElementById('preview-placeholder');
    const inputImagem = document.getElementById('input-imagem-checklist');
    const btnUpload = document.getElementById('btn-upload-imagem-checklist');
    const alertImagem = document.getElementById('alert-imagem');
    const tbody = document.getElementById('tbody-itens-checklist');
    const modal = document.getElementById('modalItemChecklist');
    const modalLabel = document.getElementById('modalItemChecklistLabel');
    const itemIdEl = document.getElementById('item-checklist-id');
    const itemNomeEl = document.getElementById('item-checklist-nome');
    const btnSalvarItem = document.getElementById('btn-salvar-item-checklist');
    const alertItem = document.getElementById('alert-item');

    function setAlertImagem(msg, isError) {
        if (!alertImagem) return;
        alertImagem.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertImagem.classList.add(isError ? 'alert-danger' : 'alert-success');
        alertImagem.textContent = msg || '';
        if (!msg) alertImagem.classList.add('d-none');
    }

    function loadConfig() {
        fetchJson(getBaseUrl() + 'admin/cadastro/checklist/config').then(json => {
            const config = json && json.data;
            if (config && config.cfc_imagem_caminho) {
                previewEl.src = getBaseUrl() + 'admin/cadastro/checklist/imagem?t=' + Date.now();
                previewEl.style.display = 'block';
                placeholderEl.style.display = 'none';
            } else {
                previewEl.style.display = 'none';
                placeholderEl.style.display = 'flex';
            }
        }).catch(() => {
            previewEl.style.display = 'none';
            placeholderEl.style.display = 'flex';
        });
    }

    if (btnUpload && inputImagem) {
        btnUpload.addEventListener('click', async () => {
            const file = inputImagem.files && inputImagem.files[0];
            if (!file) {
                setAlertImagem('Selecione uma imagem.', true);
                return;
            }
            setAlertImagem('');
            const fd = new FormData();
            fd.append('imagem', file);
            try {
                await fetch(getBaseUrl() + 'admin/cadastro/checklist/config/imagem', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                }).then(r => r.json().then(j => { if (!j.success) throw new Error(j.message); return j; }));
                setAlertImagem('Imagem salva com sucesso.', false);
                inputImagem.value = '';
                loadConfig();
                if (window.toastr) window.toastr.success('Imagem salva.');
            } catch (e) {
                setAlertImagem(e.message || 'Erro ao salvar imagem.', true);
            }
        });
    }

    function loadItens() {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Carregando...</td></tr>';
        fetchJson(getBaseUrl() + 'admin/cadastro/checklist/itens').then(json => {
            const itens = (json && json.data) || [];
            if (itens.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhum item. Clique em Adicionar item.</td></tr>';
                return;
            }
            tbody.innerHTML = itens.map((item, i) => `
                <tr data-id="${item.id}">
                    <td>${(item.chi_ordem ?? i) + 1}</td>
                    <td>${escapeHtml(item.chi_nome || '')}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-item" data-id="${item.id}" data-nome="${escapeHtml(item.chi_nome || '')}">Editar</button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item" data-id="${item.id}">Excluir</button>
                    </td>
                </tr>
            `).join('');
        }).catch(() => {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar itens.</td></tr>';
        });
    }
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    document.addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.btn-edit-item');
        const delBtn = e.target.closest('.btn-delete-item');
        if (editBtn) {
            itemIdEl.value = editBtn.getAttribute('data-id');
            itemNomeEl.value = editBtn.getAttribute('data-nome') || '';
            modalLabel.textContent = 'Editar item';
            if (window.bootstrap && modal) window.bootstrap.Modal.getOrCreateInstance(modal).show();
        }
        if (delBtn) {
            if (!confirm('Excluir este item?')) return;
            const id = delBtn.getAttribute('data-id');
            try {
                await fetch(getBaseUrl() + 'admin/cadastro/checklist/itens/deletar/' + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json().then(j => { if (!j.success) throw new Error(j.message); }));
                loadItens();
                if (window.toastr) window.toastr.success('Item removido.');
            } catch (err) {
                if (window.toastr) window.toastr.error(err.message);
            }
        }
    });

    if (btnSalvarItem && itemNomeEl) {
        btnSalvarItem.addEventListener('click', async () => {
            const nome = itemNomeEl.value.trim();
            if (!nome) {
                alertItem.textContent = 'Informe o nome do item.';
                alertItem.classList.remove('d-none', 'alert-success');
                alertItem.classList.add('alert-danger');
                return;
            }
            alertItem.classList.add('d-none');
            const id = itemIdEl.value;
            const url = id ? getBaseUrl() + 'admin/cadastro/checklist/itens/' + id : getBaseUrl() + 'admin/cadastro/checklist/itens';
            const fd = new FormData();
            fd.append('chi_nome', nome);
            try {
                await fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                }).then(r => r.json().then(j => { if (!j.success) throw new Error(j.message); }));
                if (window.bootstrap && modal) window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                itemIdEl.value = '';
                itemNomeEl.value = '';
                loadItens();
                if (window.toastr) window.toastr.success(id ? 'Item atualizado.' : 'Item adicionado.');
            } catch (err) {
                alertItem.textContent = err.message || 'Erro ao salvar.';
                alertItem.classList.remove('d-none', 'alert-success');
                alertItem.classList.add('alert-danger');
            }
        });
    }

    document.getElementById('btn-add-item-checklist')?.addEventListener('click', () => {
        itemIdEl.value = '';
        itemNomeEl.value = '';
        modalLabel.textContent = 'Adicionar item';
        alertItem.classList.add('d-none');
    });

    loadConfig();
    loadItens();
})();
