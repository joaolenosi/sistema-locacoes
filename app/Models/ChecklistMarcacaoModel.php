<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistMarcacaoModel extends Model
{
    protected $table            = 'checklist_marcacoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'chm_empresa_id',
        'chm_checklist_id',
        'chm_item_id',
        'chm_valor',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByChecklist(int $checklistId, ?int $empresaId = null): array
    {
        $builder = $this->where('chm_checklist_id', $checklistId);
        if ($empresaId !== null && $empresaId > 0) {
            $builder->where('chm_empresa_id', $empresaId);
        }
        return $builder->findAll();
    }
}
