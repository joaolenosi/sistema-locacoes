<?php

/**
 * Retorna a quantidade de cobranças pendentes de locações para a empresa logada.
 * Usado no badge do menu lateral.
 * Nota: A função está definida em Common.php (carregado no bootstrap).
 * Este helper existe para compatibilidade quando helper('cobrancas') for carregado.
 *
 * @return int
 */
if (!function_exists('get_cobrancas_pendentes_count')) {
    function get_cobrancas_pendentes_count(): int
    {
        $empresaId = get_empresa_id();
        if ($empresaId < 1) {
            return 0;
        }

        $model = new \App\Models\LancamentoFinanceiroModel();
        return (int) $model
            ->where('lan_empresa_id', $empresaId)
            ->where('lan_tipo', 'receita')
            ->where('lan_status', 'pendente')
            ->where('lan_locacao_id IS NOT NULL', null, false)
            ->countAllResults();
    }
}
