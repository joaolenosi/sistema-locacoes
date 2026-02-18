<?php

namespace App\Models;

use CodeIgniter\Model;

class ManutencaoModel extends Model
{
    protected $table            = 'manutencoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'man_empresa_id',
        'man_veiculo_id',
        'man_locacao_id',
        'man_data',
        'man_km',
        'man_km_atual',
        'man_trigger_tipo',
        'man_tipo',
        'man_status',
        'man_total',
        'man_pago',
        'man_data_pagamento',
        'man_valor_pago',
        'man_forma_pagamento',
        'man_obs',
        'man_lancamento_id',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
