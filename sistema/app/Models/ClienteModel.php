<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'cli_empresa_id',
        'cli_tipo_pessoa',
        'cli_nome',
        'cli_cpf_cnpj',
        'cli_data_nascimento',
        'cli_email',
        'cli_telefone',
        'cli_whatsapp',
        'cli_cnh_numero',
        'cli_cnh_validade',
        'cli_cep',
        'cli_estado',
        'cli_cidade',
        'cli_bairro',
        'cli_rua',
        'cli_numero',
        'cli_complemento',
        'cli_obs',
        'cli_ativo',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

