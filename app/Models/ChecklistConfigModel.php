<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistConfigModel extends Model
{
    protected $table            = 'checklist_config';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'cfc_empresa_id',
        'cfc_imagem_caminho',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByEmpresa(int $empresaId): ?array
    {
        return $this->where('cfc_empresa_id', $empresaId)->first();
    }
}
