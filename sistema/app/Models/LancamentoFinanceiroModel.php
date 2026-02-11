<?php

namespace App\Models;

use CodeIgniter\Model;

class LancamentoFinanceiroModel extends Model
{
    protected $table            = 'lancamentos_financeiros';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'lan_empresa_id',
        'lan_tipo',
        'lan_categoria_id',
        'lan_descricao',
        'lan_data_lancamento',
        'lan_data_vencimento',
        'lan_data_pagamento',
        'lan_valor',
        'lan_valor_pago',
        'lan_status',
        'lan_forma_pagamento',
        'lan_referencia',
        'lan_locacao_id',
        'lan_veiculo_id',
        'lan_obs',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Builder com join em categorias para retornar o nome da categoria.
     */
    public function builderWithCategoria()
    {
        return $this->builder()
            ->select('lancamentos_financeiros.*')
            ->select('categorias_financeiras.cat_nome AS categoria_nome')
            ->select('categorias_financeiras.cat_tipo AS categoria_tipo')
            ->join('categorias_financeiras', 'categorias_financeiras.id = lancamentos_financeiros.lan_categoria_id', 'left');
    }
}

