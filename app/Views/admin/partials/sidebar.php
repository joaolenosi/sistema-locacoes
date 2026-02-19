<?php helper('asset'); ?>
<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="<?= base_url() ?>" class="logo-dark">
            <img
                src="<?= asset_url('assets/admin/images/logo-rentix-car.png') ?>"
                class="logo-lg"
                alt="Rentix Car"
                style="max-width:80%; height: auto;"
            />
        </a>
 
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button
        type="button"
        class="button-sm-hover"
        aria-label="Show Full Sidebar"
    >
        <iconify-icon
            icon="iconamoon:arrow-left-4-square-duotone"
            class="button-sm-hover-icon"
        ></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
        <!--     <li class="menu-title">General</li> -->

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:home-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Dashboards </span>
                </a>
            </li>

            <li class="menu-title">Operações</li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/veiculos') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:box-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Veículos </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/locatarios') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:profile-circle-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Locatários </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/locacoes') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:calendar-1-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Locações </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/contratos') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:certificate-badge-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Contratos </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/cobrancas') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:cheque-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Cobranças </span>
                    <span class="badge badge-pill text-end bg-danger">1</span>
                </a>
            </li>

            <li class="menu-title">Financeiro</li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/financeiro') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:invoice-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Financeiro </span>
                </a>
            </li>

            <li class="menu-title">Manutenção</li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/manutencao') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:settings-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Manutenção </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/manutencao-inteligente') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:component-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Manuten. Inteligente </span>
                </a>
            </li>

          <!--   <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/relatorios') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:copy-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Relatórios </span>
                    <span class="badge badge-pill text-end bg-success">Novo</span>
                </a>
            </li>
 -->
            <li class="menu-title">Cadastro</li>

            <li class="nav-item">
                <a
                    class="nav-link menu-arrow"
                    href="#sidebarCadastro"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarCadastro"
                >
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:folder-add-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Cadastro </span>
                </a>
                <div class="collapse" id="sidebarCadastro">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="<?= base_url('admin/cadastro/servicos') ?>">
                                <iconify-icon icon="iconamoon:briefcase-duotone" class="me-1"></iconify-icon>
                                Serviços
                            </a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="<?= base_url('admin/cadastro/produtos') ?>">
                                <iconify-icon icon="iconamoon:shopping-bag-duotone" class="me-1"></iconify-icon>
                                Produtos
                            </a>
                        </li>
                     <!--    <li class="sub-nav-item">
                            <a class="sub-nav-link" href="<?= base_url('admin/cadastro/categorias-empresariais') ?>">
                                <iconify-icon icon="iconamoon:category-duotone" class="me-1"></iconify-icon>
                                Categorias Empresariais
                            </a>
                        </li> -->
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="<?= base_url('admin/cadastro/categorias-financeiras') ?>">
                                <iconify-icon icon="iconamoon:category-duotone" class="me-1"></iconify-icon>
                                Categorias  
                            </a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="<?= base_url('admin/empresa') ?>">
                                <iconify-icon icon="iconamoon:briefcase-duotone" class="me-1"></iconify-icon>
                                Empresa
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-title">Conta</li>

            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/configuracoes') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:settings-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Configurações </span>
                </a>
            </li>

            <li class="nav-item">   
                <a class="nav-link" href="<?= base_url('logout') ?>">
                    <span class="nav-icon">
                        <iconify-icon
                            icon="iconamoon:lock-duotone"
                        ></iconify-icon>
                    </span>
                    <span class="nav-text"> Sair do sistema</span>
                </a>
            </li>
        </ul>
    </div>
</div>
