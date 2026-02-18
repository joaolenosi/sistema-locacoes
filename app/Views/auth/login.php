<?php helper('asset'); ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <title><?= esc($title ?? 'Login') ?> | Sistema Agenda Miau</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="<?= asset_url('assets/admin/images/favicon.ico') ?>" />
    <link href="<?= asset_url('assets/admin/css/vendor.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('assets/admin/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('assets/admin/css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    <script src="<?= asset_url('assets/admin/js/config.js') ?>"></script>
    <style>
        body.login-page { background-color: #e9ecef; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .login-card { max-width: 420px; width: 100%; }
    </style>
</head>
<body class="login-page">
    <div class="login-card card shadow-sm">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <a href="<?= base_url() ?>" class="text-decoration-none">
                    <img src="<?= asset_url('assets/admin/images/logo-rentix-car.png') ?>" height="60" alt="Rentix Car" style="max-width: 100%;" />
                </a>
            </div>
            <h4 class="text-center fw-semibold mb-1">Entrar</h4>
            <p class="text-muted text-center mb-4">Telefone e senha</p>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login/processar') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" value="<?= esc(old('telefone')) ?>" required autofocus />
                </div>
                <div class="mb-4">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Senha" required />
                </div>
                <button class="btn btn-primary w-100" type="submit">Entrar</button>
            </form>
        </div>
    </div>

    <script src="<?= base_url('assets/admin/js/vendor.js') ?>"></script>
    <script src="<?= base_url('assets/admin/js/app.js') ?>"></script>
    <script>
    (function() {
        var el = document.getElementById('telefone');
        if (!el) return;
        function getDigits(s) { return String(s || '').replace(/\D/g, ''); }
        function mask() {
            var d = getDigits(el.value).slice(0, 11);
            if (d.length > 10) el.value = '(' + d.slice(0,2) + ') ' + d.slice(2,7) + '-' + d.slice(7);
            else if (d.length > 6) el.value = '(' + d.slice(0,2) + ') ' + d.slice(2,6) + '-' + d.slice(6);
            else if (d.length > 2) el.value = '(' + d.slice(0,2) + ') ' + d.slice(2);
            else el.value = d.length ? '(' + d : '';
        }
        el.addEventListener('input', mask);
    })();
    </script>
</body>
</html>
