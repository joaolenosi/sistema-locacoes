<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin/veiculos', 'Veiculos::index');
$routes->get('admin/locatarios', 'Locatarios::index');
$routes->get('admin/locacoes', 'Locoes::index');
$routes->get('admin/contratos', 'Contratos::index');
$routes->get('admin/financeiro', 'Financeiro::index');
$routes->get('admin/financeiro/movimentacoes', 'Financeiro::movimentacoes');
$routes->get('admin/manutencao', 'Manutencao::index');
$routes->get('admin/manutencao-inteligente', 'ManutencaoInteligente::index');
$routes->get('admin/configuracoes', 'Configuracoes::index');
$routes->get('admin/cadastro/produtos', 'Produtos::index');
$routes->get('admin/cadastro/servicos', 'Servicos::index');
$routes->get('admin/cadastro/categorias-financeiras', 'CategoriasFinanceiras::index');