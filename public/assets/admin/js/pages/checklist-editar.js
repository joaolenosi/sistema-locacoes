(function () {
    const checklistId = window.__CHECKLIST_ID__;
    const baseUrl = (window.__CHECKLIST_BASE_URL__ || window.location.origin + '/').replace(/\/?$/, '/');
    const imagemInicial = window.__IMAGEM_INICIAL__ || '';

    if (!checklistId) return;

    const imgEl = document.getElementById('imgBaseChecklist');
    const canvasEl = document.getElementById('canvasDesenho');
    const wrapDesenho = document.getElementById('wrap-desenho');
    const btnSalvarDesenho = document.getElementById('btnSalvarDesenho');
    const desenhoStatus = document.getElementById('desenhoStatus');
    const form = document.getElementById('formChecklist');
    const formAlert = document.getElementById('formChecklistAlert');
    const inputAnexos = document.getElementById('inputAnexos');
    const anexosLista = document.getElementById('anexosLista');

    let ctx = null;
    let drawing = false;
    let lastX = 0, lastY = 0;

    function setStatus(msg) {
        if (desenhoStatus) desenhoStatus.textContent = msg || '';
    }

    function showSuccess(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Salvo!', text: msg || 'Registro salvo com sucesso.' });
        } else {
            alert(msg || 'Salvo.');
        }
    }

    function showError(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Erro', text: msg || 'Ocorreu um erro.' });
        } else {
            alert(msg || 'Erro.');
        }
    }

    function initCanvas() {
        if (!imgEl || !canvasEl) return;
        imgEl.crossOrigin = 'anonymous';
        imgEl.src = imagemInicial;
        imgEl.onerror = function () {
            setStatus('Imagem não carregada.');
            canvasEl.width = 400;
            canvasEl.height = 300;
            ctx = canvasEl.getContext('2d');
            ctx.fillStyle = '#f0f0f0';
            ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
        };
        imgEl.onload = function () {
            const w = imgEl.naturalWidth || imgEl.width || 800;
            const h = imgEl.naturalHeight || imgEl.height || 600;
            canvasEl.width = w;
            canvasEl.height = h;
            ctx = canvasEl.getContext('2d');
            ctx.drawImage(imgEl, 0, 0);
            setStatus('');
        };
    }

    function getPos(e) {
        const rect = canvasEl.getBoundingClientRect();
        const scaleX = canvasEl.width / rect.width;
        const scaleY = canvasEl.height / rect.height;
        if (e.touches && e.touches.length) {
            return {
                x: (e.touches[0].clientX - rect.left) * scaleX,
                y: (e.touches[0].clientY - rect.top) * scaleY
            };
        }
        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY
        };
    }

    function startDraw(e) {
        e.preventDefault();
        if (!ctx) return;
        drawing = true;
        const p = getPos(e);
        lastX = p.x;
        lastY = p.y;
    }

    function moveDraw(e) {
        e.preventDefault();
        if (!drawing || !ctx) return;
        const p = getPos(e);
        ctx.strokeStyle = '#e00';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        lastX = p.x;
        lastY = p.y;
    }

    function endDraw(e) {
        e.preventDefault();
        drawing = false;
    }

    if (canvasEl) {
        canvasEl.addEventListener('mousedown', startDraw);
        canvasEl.addEventListener('mousemove', moveDraw);
        canvasEl.addEventListener('mouseup', endDraw);
        canvasEl.addEventListener('mouseleave', endDraw);
        canvasEl.addEventListener('touchstart', startDraw, { passive: false });
        canvasEl.addEventListener('touchmove', moveDraw, { passive: false });
        canvasEl.addEventListener('touchend', endDraw, { passive: false });
    }

    if (btnSalvarDesenho) {
        btnSalvarDesenho.addEventListener('click', function () {
            if (!canvasEl || !ctx) return;
            setStatus('Salvando...');
            const dataUrl = canvasEl.toDataURL('image/png');
            const fd = new FormData();
            fd.append('imagem', dataUrl);
            fetch(baseUrl + 'admin/checklist/' + checklistId + '/salvar-imagem', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                setStatus(res.success ? 'Desenho salvo.' : (res.message || 'Erro'));
                if (res.success && typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Desenho salvo!', text: 'A imagem foi gravada.' });
                if (res.success && res.caminho) {
                    imgEl.src = baseUrl + 'admin/checklist/desenho/' + checklistId + '?t=' + Date.now();
                    imgEl.onload = function () {
                        ctx.drawImage(imgEl, 0, 0);
                    };
                }
            })
            .catch(function () { setStatus('Erro ao salvar.'); });
        });
    }

    initCanvas();

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (formAlert) {
                formAlert.classList.add('d-none');
                formAlert.textContent = '';
            }
            const fd = new FormData(form);
            fetch(baseUrl + 'admin/checklist/salvar', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (formAlert) formAlert.classList.add('d-none');
                    showSuccess(res.message || 'Registro salvo com sucesso.');
                } else {
                    if (formAlert) {
                        formAlert.textContent = res.message || 'Erro ao salvar.';
                        formAlert.classList.remove('d-none');
                    }
                    showError(res.message || 'Erro ao salvar.');
                }
            })
            .catch(function () {
                if (formAlert) {
                    formAlert.textContent = 'Erro de conexão.';
                    formAlert.classList.remove('d-none');
                }
                showError('Erro de conexão. Tente novamente.');
            });
        });
    }

    if (inputAnexos) {
        inputAnexos.addEventListener('change', function () {
            const files = this.files;
            if (!files || files.length === 0) return;
            const fd = new FormData();
            for (let i = 0; i < files.length; i++) {
                fd.append('anexos[]', files[i]);
            }
            fetch(baseUrl + 'admin/checklist/' + checklistId + '/anexos', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success && res.anexos && res.anexos.length) {
                    res.anexos.forEach(function (a) {
                        const div = document.createElement('div');
                        div.className = 'd-flex align-items-center justify-content-between py-1 border-bottom anexo-item';
                        div.setAttribute('data-id', a.id);
                        div.innerHTML = '<a href="' + baseUrl + 'admin/checklist/anexo/' + a.id + '" target="_blank" class="text-truncate me-2">' + (a.cha_nome_arquivo || '') + '</a><button type="button" class="btn btn-sm btn-outline-danger btn-remover-anexo">Remover</button>';
                        anexosLista.appendChild(div);
                    });
                    inputAnexos.value = '';
                }
            })
            .catch(function () {});
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest && e.target.closest('.btn-remover-anexo');
        if (!btn) return;
        const row = btn.closest('.anexo-item');
        const anexoId = row ? row.getAttribute('data-id') : null;
        if (!anexoId || !confirm('Remover este anexo?')) return;
        fetch(baseUrl + 'admin/checklist/anexos/deletar/' + anexoId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success && row) row.remove();
        });
    });
})();
