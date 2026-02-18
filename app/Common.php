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

/**
 * Retorna o ID da empresa logada (sessão). 0 se não houver sessão.
 */
if (!function_exists('get_empresa_id')) {
    function get_empresa_id(): int
    {
        return (int) session()->get('empresa_id');
    }
}

/**
 * Retorna as iniciais do nome da empresa (fantasia ou razão social) para exibição em avatar.
 * Ex.: "MOBILI LOCACOES" -> "ML", "Empresa" -> "EM"
 *
 * @return string Máximo 2 caracteres em maiúsculo
 */
if (!function_exists('empresa_iniciais')) {
    function empresa_iniciais(): string
    {
        $id = get_empresa_id();
        if ($id < 1) {
            return 'E';
        }
        $model = new \App\Models\EmpresaModel();
        $empresa = $model->find($id);
        if (! $empresa) {
            return 'E';
        }
        $nome = trim($empresa['emp_fantasia'] ?? $empresa['emp_nome'] ?? '');
        if ($nome === '') {
            return 'E';
        }
        $palavras = preg_split('/\s+/u', $nome, 3, PREG_SPLIT_NO_EMPTY);
        if (count($palavras) >= 2) {
            $a = mb_substr($palavras[0], 0, 1);
            $b = mb_substr($palavras[1], 0, 1);
            return mb_strtoupper($a . $b);
        }
        return mb_strtoupper(mb_substr($nome, 0, 2));
    }
}

/**
 * Formata data para formato brasileiro (DD/MM/YYYY)
 * 
 * @param string|null $data Data no formato YYYY-MM-DD ou datetime
 * @return string Data formatada ou '-' se vazia
 */
if (!function_exists('formatarDataBR')) {
    function formatarDataBR(?string $data): string
    {
        if (empty($data)) {
            return '-';
        }
        
        // Se já está no formato brasileiro, retorna como está
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            return $data;
        }
        
        // Extrair apenas a parte da data (ignorar hora se presente)
        $dataParte = substr($data, 0, 10);
        
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dataParte, $matches)) {
            return $matches[3] . '/' . $matches[2] . '/' . $matches[1];
        }
        
        return $data;
    }
}

/**
 * Formata valor monetário para formato brasileiro (R$ X.XXX,XX)
 * 
 * @param float|string|null $valor Valor numérico
 * @return string Valor formatado ou 'R$ 0,00' se vazio
 */
if (!function_exists('formatarMoedaBR')) {
    function formatarMoedaBR($valor): string
    {
        if ($valor === null || $valor === '' || $valor === false) {
            return 'R$ 0,00';
        }
        
        $numero = (float) $valor;
        return 'R$ ' . number_format($numero, 2, ',', '.');
    }
}

/**
 * Formata CPF/CNPJ
 * 
 * @param string|null $cpfcnpj CPF ou CNPJ sem formatação
 * @return string CPF/CNPJ formatado ou '-' se vazio
 */
if (!function_exists('formatarCPFCNPJ')) {
    function formatarCPFCNPJ(?string $cpfcnpj): string
    {
        if (empty($cpfcnpj)) {
            return '-';
        }
        
        // Remove formatação existente
        $digitos = preg_replace('/\D/', '', $cpfcnpj);
        
        if (strlen($digitos) === 11) {
            // CPF: XXX.XXX.XXX-XX
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos);
        } elseif (strlen($digitos) === 14) {
            // CNPJ: XX.XXX.XXX/XXXX-XX
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digitos);
        }
        
        return $cpfcnpj;
    }
}
