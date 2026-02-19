<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoModel extends Model
{
    protected $table            = 'contratos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'con_empresa_id',
        'con_locacao_id',
        'con_modelo_id',
        'con_numero',
        'con_status',
        'con_token',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Builder com joins para listagem (locação, cliente, veículo).
     */
    public function builderWithJoins()
    {
        return $this->builder()
            ->select('contratos.*')
            ->select('locacoes.loc_data_inicio AS loc_data_inicio')
            ->select('locacoes.loc_data_fim_prevista AS loc_data_fim_prevista')
            ->select('locacoes.loc_valor_total AS loc_valor_total')
            ->select('locacoes.loc_valor_locacao AS loc_valor_locacao')
            ->select('clientes.cli_nome AS cli_nome')
            ->select('veiculos.vei_placa AS vei_placa')
            ->join('locacoes', 'locacoes.id = contratos.con_locacao_id', 'left')
            ->join('clientes', 'clientes.id = locacoes.loc_cli_id', 'left')
            ->join('veiculos', 'veiculos.id = locacoes.loc_vei_id', 'left');
    }
}
