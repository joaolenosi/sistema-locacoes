<?php helper('asset'); ?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title><?= esc($title ?? 'Login') ?> | Sistema de Locações</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="<?= asset_url('assets/admin/images/fav.png') ?>" type="image/png" />
    <link href="<?= asset_url('assets/admin/css/vendor.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('assets/admin/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('assets/admin/css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    <script>
        // Forçar tema light na página de login, ignorando sessionStorage
        (function() {
            var html = document.documentElement;
            html.setAttribute('data-bs-theme', 'light');
            // Limpar qualquer configuração de tema do sessionStorage temporariamente
            var savedConfig = sessionStorage.getItem('__REBACK_CONFIG__');
            if (savedConfig) {
                try {
                    var config = JSON.parse(savedConfig);
                    config.theme = 'light';
                    sessionStorage.setItem('__REBACK_CONFIG__', JSON.stringify(config));
                } catch(e) {
                    // Ignorar erros
                }
            }
        })();
    </script>
    <script src="<?= asset_url('assets/admin/js/config.js') ?>"></script>
    <script>
        // Garantir que o tema permaneça light após config.js ser executado
        (function() {
            var html = document.documentElement;
            html.setAttribute('data-bs-theme', 'light');
            // Atualizar config se existir
            if (window.config) {
                window.config.theme = 'light';
                if (sessionStorage) {
                    try {
                        sessionStorage.setItem('__REBACK_CONFIG__', JSON.stringify(window.config));
                    } catch(e) {
                        // Ignorar erros
                    }
                }
            }
        })();
    </script>
    <style>
        body.login-page { background-color: #e9ecef; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .login-card { max-width: 480px; width: 100%; }
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
            <h3 class="text-center fw-semibold mb-1">Seja bem vindo ao sistema de locações</h3>
            <p class="text-muted text-center mb-4">Informe seu telefone e senha para acessar o sistema</p>

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
                <div class="mb-3">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Senha" required />
                </div>
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar" />
                        <label class="form-check-label" for="lembrar">Lembrar-me</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Entrar</button>
            </form>
        </div>
    </div>

    <script src="<?= base_url('assets/admin/js/vendor.js') ?>"></script>
    <script src="<?= base_url('assets/admin/js/app.js') ?>"></script>
    <script>
    (function() {
        var telefoneEl = document.getElementById('telefone');
        var senhaEl = document.getElementById('senha');
        var lembrarEl = document.getElementById('lembrar');
        var formEl = document.querySelector('form');
        
        if (!telefoneEl) return;
        
        // Função para obter apenas dígitos
        function getDigits(s) { 
            return String(s || '').replace(/\D/g, ''); 
        }
          
        // Função para aplicar máscara no telefone   
        function mask() {
            var d = getDigits(telefoneEl.value).slice(0, 11);
            if (d.length > 10) {
                telefoneEl.value = '(' + d.slice(0,2) + ') ' + d.slice(2,7) + '-' + d.slice(7);
            } else if (d.length > 6) {
                telefoneEl.value = '(' + d.slice(0,2) + ') ' + d.slice(2,6) + '-' + d.slice(6);
            } else if (d.length > 2) {
                telefoneEl.value = '(' + d.slice(0,2) + ') ' + d.slice(2);
            } else {
                telefoneEl.value = d.length ? '(' + d : '';
            }
        }
        
        // Carregar dados salvos ao carregar a página
        function carregarDadosSalvos() {
            try {
                var telefoneSalvo = localStorage.getItem('login_telefone');
                var lembrarSalvo = localStorage.getItem('login_lembrar');
                
                if (telefoneSalvo && lembrarSalvo === 'true') {
                    telefoneEl.value = telefoneSalvo;
                    if (lembrarEl) {
                        lembrarEl.checked = true;
                    }
                    // Focar no campo de senha se o telefone já estiver preenchido
                    if (senhaEl) {
                        senhaEl.focus();
                    }
                }
            } catch (e) {
                console.error('Erro ao carregar dados salvos:', e);
            }
        }
        
        // Salvar dados quando o formulário for enviado
        function salvarDados() {
            try {
                if (lembrarEl && lembrarEl.checked) {
                    localStorage.setItem('login_telefone', telefoneEl.value);
                    localStorage.setItem('login_lembrar', 'true');
                } else {
                    localStorage.removeItem('login_telefone');
                    localStorage.removeItem('login_lembrar');
                }
            } catch (e) {
                console.error('Erro ao salvar dados:', e);
            }
        }
        
        // Event listeners
        telefoneEl.addEventListener('input', mask);
        
        if (formEl) {
            formEl.addEventListener('submit', function(e) {
                salvarDados();
            });
        }
        
        // Carregar dados ao inicializar
        carregarDadosSalvos();
    })();
    </script>
</body>
</html>
