<?php

/**
 * Retorna as iniciais do nome da empresa (fantasia ou razão social) para exibição em avatar.
 * Ex.: "MOBILI LOCACOES" -> "ML", "Empresa" -> "EM"
 *
 * @return string Máximo 2 caracteres em maiúsculo
 */
function empresa_iniciais(): string
{
    $model = new \App\Models\EmpresaModel();
    $empresa = $model->find(1);
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
