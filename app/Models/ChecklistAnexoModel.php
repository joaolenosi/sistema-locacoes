<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistAnexoModel extends Model
{
    protected $table            = 'checklist_anexos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'cha_empresa_id',
        'cha_checklist_id',
        'cha_nome_arquivo',
        'cha_caminho',
        'cha_tamanho',
        'cha_tipo',
        'cha_ordem',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByChecklist(int $checklistId, ?int $empresaId = null): array
    {
        $builder = $this->where('cha_checklist_id', $checklistId);
        if ($empresaId !== null && $empresaId > 0) {
            $builder->where('cha_empresa_id', $empresaId);
        }
        return $builder->orderBy('cha_ordem', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
