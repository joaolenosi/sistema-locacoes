<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table            = 'produtos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'pro_empresa_id',
        'pro_nome',
        'pro_categoria',
        'pro_marca',
        'pro_obs',
        'pro_sku',
        'pro_preco_custo',
        'pro_preco_venda',
        'pro_controlado',
        'pro_intervalo_km',
        'pro_estoque_atual',
        'pro_estoque_minimo',
        'pro_ativo',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

