<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoModeloModel extends Model
{
    protected $table = 'contratos_modelos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'con_empresa_id',
        'con_nome',
        'con_descricao',
        'con_conteudo',
        'con_padrao',
        'con_ativo',
        'created_at',
        'updated_at',
    ];
}

