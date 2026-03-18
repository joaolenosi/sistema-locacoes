<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceiroModel extends Model
{
    protected $table            = 'financeiro';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'fin_emp_id',
        'fin_descricao',
        'fin_valor',
        'fin_data_vencimento',
        'fin_data_pagamento',
        'fin_status',
        'fin_codigo_pix',
        'fin_url_qrcode',
        'fin_forma_pagamento',
        'fin_referencia',
        'fin_mes_referencia',
        'fin_obs',
    ];

    protected bool $allowEmptyInserts = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getByEmpresa(int $empresaId): array
    {
        return $this->where('fin_emp_id', $empresaId)
            ->orderBy('fin_data_vencimento', 'DESC')
            ->findAll();
    }

    public function getFaturasAbertas(int $empresaId): array
    {
        return $this->where('fin_emp_id', $empresaId)
            ->whereIn('fin_status', ['pendente', 'vencido'])
            ->orderBy('fin_data_vencimento', 'ASC')
            ->findAll();
    }

    public function existeCobrancaMes(int $empresaId, string $mesReferencia): bool
    {
        $count = $this->where('fin_emp_id', $empresaId)
            ->where('fin_mes_referencia', $mesReferencia)
            ->countAllResults();

        return $count > 0;
    }

    public function criarCobrancaMensal(int $empresaId, string $descricao, float $valor, string $codigoPix): ?int
    {
        $dataVencimento = date('Y-m-d', strtotime('+7 days'));
        $mesReferencia = date('Y-m');

        if ($this->existeCobrancaMes($empresaId, $mesReferencia)) {
            return null;
        }

        $data = [
            'fin_emp_id' => $empresaId,
            'fin_descricao' => $descricao,
            'fin_valor' => $valor,
            'fin_data_vencimento' => $dataVencimento,
            'fin_status' => 'pendente',
            'fin_codigo_pix' => $codigoPix,
            'fin_forma_pagamento' => 'pix',
            'fin_mes_referencia' => $mesReferencia,
        ];

        $id = $this->insert($data);
        return $id ?: null;
    }

    public function marcarComoPago(int $id, ?string $referencia = null): bool
    {
        $data = [
            'fin_status' => 'pago',
            'fin_data_pagamento' => date('Y-m-d'),
            'fin_referencia' => $referencia,
        ];

        return $this->update($id, $data);
    }

    public function countPendente(int $empresaId): int
    {
        return $this->where('fin_emp_id', $empresaId)
            ->whereIn('fin_status', ['pendente', 'vencido'])
            ->countAllResults();
    }

    public function getUltimaCobranca(int $empresaId): ?array
    {
        return $this->where('fin_emp_id', $empresaId)
            ->orderBy('fin_data_vencimento', 'DESC')
            ->first();
    }

    public function marcarFaturasVencidas(): int
    {
        $hoje = date('Y-m-d');
        return $this->where('fin_status', 'pendente')
            ->where('fin_data_vencimento <', $hoje)
            ->set('fin_status', 'vencido')
            ->update();
    }

    public function getFaturaVencida(int $empresaId): ?array
    {
        $hoje = date('Y-m-d');
        
        $result = $this->where('fin_emp_id', $empresaId)
            ->whereIn('fin_status', ['pendente', 'vencido'])
            ->where('fin_data_vencimento <', $hoje)
            ->orderBy('fin_data_vencimento', 'ASC')
            ->first();
            
        return $result;
    }
}
