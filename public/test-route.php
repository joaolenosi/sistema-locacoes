<?php
/**
 * Teste de roteamento simples
 * Acesse: https://mobilelocacoes.com/sistema/test-route.php
 */

echo "<h1>Teste de Roteamento</h1>";
echo "<p>Se você está vendo esta página, o PHP está funcionando!</p>";
echo "<p>REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Não definido') . "</p>";
echo "<p>SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Não definido') . "</p>";

echo "<h2>Teste de acesso direto ao index.php</h2>";
echo "<p><a href='index.php'>Clique aqui para testar index.php diretamente</a></p>";
echo "<p><a href='index.php/login'>Clique aqui para testar index.php/login</a></p>";

echo "<hr>";
echo "<p><strong>⚠️ REMOVA ESTE ARQUIVO APÓS O TESTE!</strong></p>";
