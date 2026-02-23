<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistItemModel extends Model
{
    protected $table            = 'checklist_itens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'chi_empresa_id',
        'chi_nome',
        'chi_ordem',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByEmpresa(int $empresaId): array
    {
        return $this->where('chi_empresa_id', $empresaId)
            ->orderBy('chi_ordem', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
