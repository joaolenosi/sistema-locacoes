<?php
/**
 * Arquivo de teste para diagnóstico
 * Acesse: https://mobilelocacoes.com/painel/test.php
 * 
 * Remova este arquivo após o diagnóstico por segurança!
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Teste de Diagnóstico</h1>";
echo "<hr>";

echo "<h2>1. PHP está funcionando?</h2>";
echo "✓ Sim! PHP versão: " . PHP_VERSION . "<br>";

echo "<h2>2. Diretórios</h2>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Diretório raiz: " . dirname(__DIR__) . "<br>";

echo "<h2>3. Arquivos importantes</h2>";
$files = [
    __DIR__ . '/index.php',
    __DIR__ . '/../app/Config/App.php',
    __DIR__ . '/../app/Config/Routes.php',
    __DIR__ . '/../app/Config/Filters.php',
    __DIR__ . '/../.env',
];

foreach ($files as $file) {
    $exists = file_exists($file) ? '✓ Existe' : '✗ Não existe';
    echo basename($file) . ": " . $exists . "<br>";
}

echo "<h2>4. Permissões</h2>";
$writable = dirname(__DIR__) . '/writable';
if (is_dir($writable)) {
    $isWritable = is_writable($writable) ? '✓ Gravável' : '✗ Não gravável';
    echo "writable/: " . $isWritable . "<br>";
} else {
    echo "writable/: ✗ Não existe<br>";
}

echo "<h2>5. Sessões</h2>";
$sessionPath = dirname(__DIR__) . '/writable/session';
if (is_dir($sessionPath)) {
    $isWritable = is_writable($sessionPath) ? '✓ Gravável' : '✗ Não gravável';
    echo "writable/session/: " . $isWritable . "<br>";
} else {
    echo "writable/session/: ✗ Não existe<br>";
}

echo "<h2>6. Teste de include</h2>";
try {
    require_once __DIR__ . '/../app/Config/Paths.php';
    echo "✓ Paths.php carregado com sucesso<br>";
    
    $paths = new Config\Paths();
    echo "✓ Objeto Paths criado<br>";
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Variáveis de ambiente</h2>";
if (file_exists(dirname(__DIR__) . '/.env')) {
    echo "✓ Arquivo .env existe<br>";
} else {
    echo "✗ Arquivo .env não existe<br>";
}

echo "<hr>";
echo "<p><strong>Se todos os testes passaram, o problema pode ser com o CodeIgniter ou rotas.</strong></p>";
echo "<p><strong>Se algum teste falhou, corrija antes de continuar.</strong></p>";
echo "<p style='color: red;'><strong>⚠️ REMOVA ESTE ARQUIVO APÓS O DIAGNÓSTICO!</strong></p>";
