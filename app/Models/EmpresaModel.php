<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table            = 'empresas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'emp_nome',
        'emp_fantasia',
        'emp_cpf_cnpj',
        'emp_tipo',
        'emp_telefone',
        'emp_email',
        'emp_cep',
        'emp_estado',
        'emp_cidade',
        'emp_rua',
        'emp_numero',
        'emp_complemento',
        'emp_ativo',
        'emp_obs',
        'emp_inscricao_estadual',
        'emp_inscricao_municipal',
        'emp_site',
        'senha',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

