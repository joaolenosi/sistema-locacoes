<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanoModel extends Model
{
    protected $table            = 'planos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'pla_nome',
        'pla_slug',
        'pla_descricao',
        'pla_preco_mensal',
        'pla_preco_anual',
        'pla_desconto_anual_percentual',
        'pla_limite_veiculos',
        'pla_limite_locatarios',
        'pla_limite_locacoes',
        'pla_suporte_tipo',
        'pla_backup_diario',
        'pla_relatorios_avancados',
        'pla_acesso_antecipado',
        'pla_status',
        'pla_ordem',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

