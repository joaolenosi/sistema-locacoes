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
