<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

/**
 * Retorna a URL completa para um asset, incluindo public/ quando necessário
 * 
 * @param string $path Caminho do asset (ex: 'assets/admin/css/app.min.css')
 * @return string URL completa do asset
 */
if (!function_exists('asset_url')) {
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
}
