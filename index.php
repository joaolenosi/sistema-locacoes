<?php
/**
 * Entry point quando o DocumentRoot aponta para a raiz do projeto
 * Este arquivo inclui o public/index.php diretamente
 */

// Define o caminho para o public/index.php
$publicIndexPath = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';

// Verifica se o arquivo existe
if (file_exists($publicIndexPath)) {
    // Muda o diretório de trabalho para public/ antes de incluir
    // Isso garante que os caminhos relativos funcionem corretamente
    chdir(__DIR__ . DIRECTORY_SEPARATOR . 'public');
    
    // Inclui o arquivo public/index.php
    require $publicIndexPath;
} else {
    // Se não encontrar, mostra erro
    http_response_code(500);
    die('Erro: Arquivo public/index.php não encontrado. Verifique a estrutura do projeto.');
}
   