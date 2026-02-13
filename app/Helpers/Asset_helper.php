<?php

/**
 * Retorna a URL completa para um asset, incluindo public/ quando necessário
 * 
 * @param string $path Caminho do asset (ex: 'assets/admin/css/app.min.css')
 * @return string URL completa do asset
 */
function asset_url(string $path = ''): string
{
    // Remove barra inicial se presente
    $path = ltrim($path, '/');
    
    // Se o caminho começa com assets/, adiciona public/ antes
    if (strpos($path, 'assets/') === 0) {
        $path = 'public/' . $path;
    }
    
    return base_url($path);
}
