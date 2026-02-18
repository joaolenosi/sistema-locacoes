<?php

namespace App\Models;

use CodeIgniter\Model;

class VeiculoControleModel extends Model
{
    protected $table            = 'veiculo_controles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'vec_empresa_id',
        'vec_veiculo_id',
        'vec_tipo_item',
        'vec_produto_id',
        'vec_servico_id',
        'vec_intervalo_km',
        'vec_status',
        'vec_ultimo_km',
        'vec_proximo_km',
        'vec_ultima_manutencao_id',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
