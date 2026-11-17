<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php
$tel = preg_replace('/\D/', '', (string) ($empresa['emp_telefone'] ?? ''));
$telFmt = strlen($tel) === 11
    ? sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 5), substr($tel, 7))
    : (strlen($tel) === 10 ? sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 4), substr($tel, 6)) : $tel);
?>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold">Meu acesso</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Meu acesso</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body p-4">
                <form id="form-meu-acesso" novalidate autocomplete="off">
                    <h5 class="fw-semibold mb-1">Telefone de acesso</h5>
                    <p class="text-muted mb-3">É o número que você digita na tela de login.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone com DDD <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= esc($telFmt) ?>"
                                   data-original="<?= esc($tel) ?>" placeholder="(84) 99999-9999" inputmode="numeric" maxlength="15" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-semibold mb-1">Nova senha</h5>
                    <p class="text-muted mb-3">Deixe em branco para manter a senha atual. Mínimo de 8 caracteres.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nova_senha" class="form-label">Nova senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" autocomplete="new-password" minlength="8">
                                <button class="btn btn-light border" type="button" data-toggle-senha="nova_senha" aria-label="Mostrar senha">
                                    <iconify-icon icon="iconamoon:eye-duotone" class="fs-18 align-middle"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmar_senha" class="form-label">Repita a nova senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" autocomplete="new-password" minlength="8">
                                <button class="btn btn-light border" type="button" data-toggle-senha="confirmar_senha" aria-label="Mostrar senha">
                                    <iconify-icon icon="iconamoon:eye-duotone" class="fs-18 align-middle"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3 bg-light p-3 mb-4">
                        <label for="senha_atual" class="form-label fw-semibold mb-1">Senha atual <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Por segurança, confirme com a senha que você usa hoje.</p>
                        <div class="input-group" style="max-width: 420px;">
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" autocomplete="current-password" required>
                            <button class="btn btn-light border" type="button" data-toggle-senha="senha_atual" aria-label="Mostrar senha">
                                <iconify-icon icon="iconamoon:eye-duotone" class="fs-18 align-middle"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url() ?>" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btn-salvar-acesso">
                            <iconify-icon icon="iconamoon:check-circle-1-duotone" class="align-middle me-1"></iconify-icon>
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-md bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                        <iconify-icon icon="iconamoon:lock-duotone" class="fs-28 text-primary"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small">Você entra no sistema com</p>
                        <h5 class="mb-0 fw-semibold" id="tel-atual"><?= esc($telFmt) ?></h5>
                    </div>
                </div>
                <p class="text-muted mb-2 small">
                    Estas alterações valem só para a sua conta
                    (<strong><?= esc($empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? '') ?></strong>).
                </p>
                <p class="text-muted mb-0 small">
                    Depois de salvar, você continua conectado. Use o telefone e a senha novos no próximo login.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('form-meu-acesso');
    var btn = document.getElementById('btn-salvar-acesso');
    var telInput = document.getElementById('telefone');
    var csrfName = <?= json_encode(csrf_token()) ?>;
    var csrfHash = <?= json_encode(csrf_hash()) ?>;
    var url = <?= json_encode(base_url('admin/meu-acesso/atualizar')) ?>;
    // Botões do SweetAlert com o mesmo estilo dos botões do sistema
    var SWAL_BTN = { buttonsStyling: false, customClass: { confirmButton: 'btn btn-primary mx-1', cancelButton: 'btn btn-light mx-1' } };
    function swal(opts) { return Swal.fire(Object.assign({}, SWAL_BTN, opts)); }

    function digitos(v) { return String(v || '').replace(/\D/g, '').slice(0, 11); }
    function mascara(v) {
        var d = digitos(v);
        if (d.length <= 2) return d.length ? '(' + d : '';
        if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
        if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
        return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    }
    telInput.addEventListener('input', function () { telInput.value = mascara(telInput.value); });

    document.querySelectorAll('[data-toggle-senha]').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = document.getElementById(b.getAttribute('data-toggle-senha'));
            var mostrar = i.type === 'password';
            i.type = mostrar ? 'text' : 'password';
            b.querySelector('iconify-icon').setAttribute('icon', mostrar ? 'iconamoon:eye-off-duotone' : 'iconamoon:eye-duotone');
            b.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });
    });

    function aviso(icon, title, html) {
        return swal({ icon: icon, title: title, html: html, confirmButtonText: 'Entendi' });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var tel = digitos(telInput.value);
        var nova = document.getElementById('nova_senha').value;
        var conf = document.getElementById('confirmar_senha').value;
        var atual = document.getElementById('senha_atual').value;

        // Validação rápida antes de enviar
        if (tel.length < 10) { aviso('warning', 'Telefone incompleto', 'Informe o telefone com DDD, por exemplo (84) 99999-9999.'); return; }
        if ((nova || conf) && nova.length < 8) { aviso('warning', 'Senha muito curta', 'A nova senha precisa ter pelo menos 8 caracteres.'); return; }
        if ((nova || conf) && nova !== conf) { aviso('warning', 'Senhas diferentes', 'A confirmação não é igual à nova senha.'); return; }
        if (!atual) { aviso('warning', 'Falta a senha atual', 'Digite sua senha atual para confirmar as alterações.'); document.getElementById('senha_atual').focus(); return; }

        var mudaTel = tel !== telInput.getAttribute('data-original');
        var mudaSenha = nova !== '';
        if (!mudaTel && !mudaSenha) { aviso('info', 'Nada para salvar', 'O telefone é o mesmo e nenhuma senha nova foi digitada.'); return; }

        var itens = [];
        if (mudaTel) itens.push('Seu login passa a ser <strong>' + mascara(tel) + '</strong>');
        if (mudaSenha) itens.push('Sua senha será trocada');

        swal({
            icon: 'question',
            title: 'Alterar seu acesso?',
            html: itens.join('<br>') + '<br><br><span class="text-muted small">Guarde os novos dados: você vai precisar deles no próximo login.</span>',
            showCancelButton: true,
            confirmButtonText: 'Sim, alterar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (r) {
            if (!r.isConfirmed) return;

            var dados = new FormData(form);
            dados.set('telefone', tel);
            dados.set(csrfName, csrfHash);
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';

            fetch(url, { method: 'POST', body: dados, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfHash }, credentials: 'same-origin' })
                .then(function (res) {
                    return res.json().catch(function () { return { success: false, message: res.status === 403 ? 'A página ficou aberta muito tempo. Recarregue e tente de novo.' : 'Erro inesperado. Tente novamente.' }; });
                })
                .then(function (data) {
                    if (data.csrf) csrfHash = data.csrf;
                    if (data.logout) {
                        return aviso('error', 'Você foi desconectado', data.message).then(function () { window.location.href = <?= json_encode(base_url('login')) ?>; });
                    }
                    if (!data.success) { return aviso('error', 'Não foi possível salvar', data.message); }
                    document.getElementById('senha_atual').value = '';
                    document.getElementById('nova_senha').value = '';
                    document.getElementById('confirmar_senha').value = '';
                    if (data.telefone) {
                        telInput.value = data.telefone;
                        telInput.setAttribute('data-original', digitos(data.telefone));
                        document.getElementById('tel-atual').textContent = data.telefone;
                    }
                    return swal({ icon: 'success', title: data.changed ? 'Acesso atualizado' : 'Tudo certo', html: data.message, confirmButtonText: 'Ok' });
                })
                .catch(function () { aviso('error', 'Sem conexão', 'Não foi possível falar com o servidor. Verifique a internet e tente de novo.'); })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="iconamoon:check-circle-1-duotone" class="align-middle me-1"></iconify-icon> Salvar alterações';
                });
        });
    });
})();
</script>

<?= $this->endSection() ?>
