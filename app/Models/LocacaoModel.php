<?php

namespace App\Models;

use CodeIgniter\Model;

class LocacaoModel extends Model
{
    protected $table            = 'locacoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'loc_empresa_id',
        'loc_cli_id',
        'loc_vei_id',
        'loc_data_inicio',
        'loc_data_fim_prevista',
        'loc_data_fim_real',
        'loc_status',
        'loc_valor_locacao',
        'loc_valor_caucao',
        'loc_valor_total',
        'loc_recorrencia_pagamento',
        'loc_data_inicio_pagamento',
        'loc_taxa_juros',
        'loc_taxa_multa',
        'loc_km_retirada',
        'loc_km_devolucao',
        'loc_responsavel_entrega',
        'loc_responsavel_devolucao',
        'loc_obs_operacionais',
        'loc_obs_financeiras',
        'loc_valores_recebidos',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Builder com joins para exibição (cliente + veículo).
     */
    public function builderWithJoins()
    {
        return $this->builder()
            ->select('locacoes.*')
            ->select('clientes.cli_nome AS cli_nome')
            ->select('clientes.cli_cpf_cnpj AS cli_cpf_cnpj')
            ->select('clientes.cli_telefone AS cli_telefone')
            ->select('veiculos.vei_placa AS vei_placa')
            ->select('veiculos.vei_modelo AS vei_modelo')
            ->select('veiculos.vei_marca AS vei_marca')
            ->select('veiculos.vei_status AS vei_status')
            ->join('clientes', 'clientes.id = locacoes.loc_cli_id', 'left')
            ->join('veiculos', 'veiculos.id = locacoes.loc_vei_id', 'left');
    }
}

