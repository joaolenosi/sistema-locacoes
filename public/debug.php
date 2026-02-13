<?php
/**
 * Arquivo de debug para verificar configuração
 * Acesse: https://mobilelocacoes.com/sistema/debug.php
 * 
 * REMOVA ESTE ARQUIVO APÓS O DIAGNÓSTICO!
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Debug - Configuração CodeIgniter</h1>";
echo "<hr>";

echo "<h2>1. Informações do Servidor</h2>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Não definido') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Não definido') . "<br>";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'Não definido') . "<br>";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'Não definido') . "<br>";

echo "<h2>2. Caminhos</h2>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Diretório raiz: " . dirname(__DIR__) . "<br>";

echo "<h2>3. Carregando CodeIgniter...</h2>";

try {
    // Carregar Paths
    require_once __DIR__ . '/../app/Config/Paths.php';
    $paths = new Config\Paths();
    echo "✓ Paths carregado<br>";
    echo "System Directory: " . $paths->systemDirectory . "<br>";
    
    // Carregar bootstrap
    require_once rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
    echo "✓ Bootstrap carregado<br>";
    
    // Carregar DotEnv
    require_once SYSTEMPATH . 'Config/DotEnv.php';
    $dotEnv = new class(ROOTPATH) extends CodeIgniter\Config\DotEnv {
        protected function setVariable(string $name, $value = null): void
        {
            $_SERVER[$name] = $value;
            $_ENV[$name]    = $value;
            if (function_exists('putenv')) {
                $disabledFunctions = ini_get('disable_functions');
                $isPutenvDisabled = $disabledFunctions && in_array('putenv', explode(',', $disabledFunctions), true);
                if (!$isPutenvDisabled) {
                    @putenv("{$name}={$value}");
                }
            }
        }
    };
    $dotEnv->load();
    echo "✓ DotEnv carregado<br>";
    
    // Verificar baseURL
    $appConfig = new Config\App();
    echo "<h2>4. Configuração da Aplicação</h2>";
    echo "baseURL: " . $appConfig->baseURL . "<br>";
    echo "indexPage: " . $appConfig->indexPage . "<br>";
    echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'Não definido') . "<br>";
    
    // Verificar rotas
    echo "<h2>5. Rotas</h2>";
    $routes = Config\Services::routes();
    echo "Total de rotas: " . count($routes->getRoutes()) . "<br>";
    echo "Rotas GET:<br>";
    foreach ($routes->getRoutes('get') as $route => $handler) {
        echo "  - {$route} => {$handler}<br>";
    }
    
    echo "<h2>6. Teste de base_url()</h2>";
    echo "base_url(): " . base_url() . "<br>";
    echo "base_url('login'): " . base_url('login') . "<br>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>⚠️ REMOVA ESTE ARQUIVO APÓS O DIAGNÓSTICO!</strong></p>";
