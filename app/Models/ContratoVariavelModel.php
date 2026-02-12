<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoVariavelModel extends Model
{
    protected $table = 'contratos_variaveis';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'cov_chave',
        'cov_entidade',
        'cov_campo',
        'cov_label',
        'cov_descricao',
        'cov_origem_tabela',
        'cov_origem_campo',
        'cov_tipo',
        'cov_ativo',
        'created_at',
        'updated_at',
    ];
}

