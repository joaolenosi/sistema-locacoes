<?php

namespace App\Models;

use CodeIgniter\Model;

class VeiculoModel extends Model
{
    protected $table            = 'veiculos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'vei_empresa_id',
        'vei_tipo',
        'vei_marca',
        'vei_modelo',
        'vei_ano',
        'vei_placa',
        'vei_cor',
        'vei_renavam',
        'vei_chassi',
        'vei_data_licenciamento',
        'vei_km_atual',
        'vei_data_compra',
        'vei_valor_compra',
        'vei_status',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

