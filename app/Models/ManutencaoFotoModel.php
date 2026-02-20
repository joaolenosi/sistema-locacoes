<?php

namespace App\Models;

use CodeIgniter\Model;

class ManutencaoFotoModel extends Model
{
    protected $table            = 'manutencoes_fotos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'maf_empresa_id',
        'maf_manutencao_id',
        'maf_nome_arquivo',
        'maf_caminho',
        'maf_tamanho',
        'maf_tipo',
        'maf_ordem',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Retorna todas as fotos de uma manutenção ordenadas por maf_ordem e id.
     */
    public function findByManutencao(int $manutencaoId, ?int $empresaId = null): array
    {
        $builder = $this->where('maf_manutencao_id', $manutencaoId);
        if ($empresaId !== null && $empresaId > 0) {
            $builder->where('maf_empresa_id', $empresaId);
        }
        return $builder->orderBy('maf_ordem', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
