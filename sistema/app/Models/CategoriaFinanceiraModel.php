<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaFinanceiraModel extends Model
{
    protected $table            = 'categorias_financeiras';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'cat_nome',
        'cat_tipo',
        'cat_padrao',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * @return array<int, array{id:int, cat_nome:string, cat_tipo:string, cat_padrao?:int}>
     */
    public function getByTipo(string $tipo): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, ['receita', 'despesa'], true)) {
            return [];
        }

        return $this->where('cat_tipo', $tipo)
            ->orderBy('cat_nome', 'ASC')
            ->findAll();
    }
}

