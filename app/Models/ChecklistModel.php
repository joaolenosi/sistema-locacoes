<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistModel extends Model
{
    protected $table            = 'checklists';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'chk_empresa_id',
        'chk_locacao_id',
        'chk_veiculo_id',
        'chk_data',
        'chk_hodometro_saida',
        'chk_hodometro_chegada',
        'chk_data_saida',
        'chk_data_chegada',
        'chk_responsavel_entrega',
        'chk_responsavel_devolucao',
        'chk_imagem_desenho_caminho',
        'chk_anotacoes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
