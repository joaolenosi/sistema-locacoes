<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <title><?= esc($title ?? 'Login') ?> | Sistema Agenda Miau</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Login do sistema" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="<?= base_url('assets/admin/images/favicon.ico') ?>" />
    <link href="<?= base_url('assets/admin/css/vendor.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/admin/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= base_url('assets/admin/css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    <script src="<?= base_url('assets/admin/js/config.js') ?>"></script>
</head>
<body class="authentication-bg">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-12">
                    <div class="card auth-card">
                        <div class="card-body p-0">
                            <div class="row align-items-center g-0">
                                <div class="col-lg-6 d-none d-lg-inline-block border-end">
                                    <div class="auth-page-sidebar">
                                        <img src="<?= base_url('assets/admin/images/sign-in.svg') ?>" alt="auth" class="img-fluid" />
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="p-4">
                                        <div class="mx-auto mb-4 text-center auth-logo">
                                            <a href="<?= base_url() ?>" class="logo-dark">
                                                <img src="<?= base_url('assets/admin/images/logo-sm.png') ?>" height="30" class="me-1" alt="logo sm" />
                                                <img src="<?= base_url('assets/admin/images/logo-dark.png') ?>" height="24" alt="logo dark" />
                                            </a>
                                            <a href="<?= base_url() ?>" class="logo-light">
                                                <img src="<?= base_url('assets/admin/images/logo-sm.png') ?>" height="30" class="me-1" alt="logo sm" />
                                                <img src="<?= base_url('assets/admin/images/logo-light.png') ?>" height="24" alt="logo light" />
                                            </a>
                                        </div>
                                        <h2 class="fw-bold text-center fs-18">Entrar</h2>
                                        <p class="text-muted text-center mt-1 mb-4">
                                            Informe o telefone e a senha para acessar o painel.
                                        </p>

                                        <?php if (session()->getFlashdata('error')): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= esc(session()->getFlashdata('error')) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                                        </div>
                                        <?php endif; ?>

                                        <div class="row justify-content-center">
                                            <div class="col-12 col-md-8">
                                                <form action="<?= base_url('login/processar') ?>" method="post" class="authentication-form">
                                                    <?= csrf_field() ?>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="telefone">Telefone</label>
                                                        <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" value="<?= esc(old('telefone')) ?>" required autofocus />
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="senha">Senha</label>
                                                        <input type="password" id="senha" name="senha" class="form-control" placeholder="Sua senha" required />
                                                    </div>
                                                    <div class="mb-1 text-center d-grid">
                                                        <button class="btn btn-primary" type="submit">Entrar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
