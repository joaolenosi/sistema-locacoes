<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\LocacaoModel;

class Cobrancas extends BaseController
{
    public function index(): string
    {
        $clienteModel = new ClienteModel();
        
        $empresaId = get_empresa_id();
        $locatarios = $clienteModel
            ->where('cli_empresa_id', $empresaId)
            ->where('cli_ativo', 1)
            ->orderBy('cli_nome', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Cobranças',
            'locatarios' => $locatarios,
        ];

        try {
            return view('admin/cobrancas/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function listar()
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $lancamentoModel = new LancamentoFinanceiroModel();
            $lancamentos = $lancamentoModel
                ->select('lancamentos_financeiros.*')
                ->select('locacoes.id as loc_id, locacoes.loc_recorrencia_pagamento, locacoes.loc_data_inicio_pagamento')
                ->select('clientes.cli_nome as locatario_nome')
                ->select('veiculos.vei_placa, veiculos.vei_modelo')
                ->join('locacoes', 'locacoes.id = lancamentos_financeiros.lan_locacao_id', 'left')
                ->join('clientes', 'clientes.id = locacoes.loc_cli_id', 'left')
                ->join('veiculos', 'veiculos.id = locacoes.loc_vei_id', 'left')
                ->where('lancamentos_financeiros.lan_empresa_id', $empresaId)
                ->where('lancamentos_financeiros.lan_tipo', 'receita')
                ->where('lancamentos_financeiros.lan_status', 'pendente')
                ->where('lancamentos_financeiros.lan_locacao_id IS NOT NULL')
                ->orderBy('lancamentos_financeiros.lan_data_vencimento', 'ASC')
                ->findAll();

            $resultados = [];
            $hoje = date('Y-m-d');

            foreach ($lancamentos as $lan) {
                // Calcular competência baseada na recorrência e data
                $competencia = $this->calcularCompetencia($lan['lan_data_vencimento'], $lan['loc_recorrencia_pagamento'] ?? 'mensal');
                
                // Formatar recorrência
                $recorrencia = $this->formatarRecorrencia($lan['loc_recorrencia_pagamento'] ?? 'mensal');
                
                // Calcular status
                $status = 'Pendente';
                if ($lan['lan_data_vencimento'] < $hoje) {
                    $status = 'Em atraso';
                }

                // Formatar número da locação
                $locacaoNum = 'LC-' . date('Y', strtotime($lan['lan_data_vencimento'])) . '-' . str_pad($lan['loc_id'] ?? 0, 3, '0', STR_PAD_LEFT);

                $resultados[] = [
                    'id' => $lan['id'],
                    'locacao' => $locacaoNum,
                    'locacao_id' => $lan['loc_id'],
                    'locatario' => $lan['locatario_nome'] ?? '-',
                    'veiculo' => ($lan['vei_placa'] ?? '-') . ' (' . ($lan['vei_modelo'] ?? '-') . ')',
                    'recorrencia' => $recorrencia,
                    'competencia' => $competencia,
                    'vencimento' => date('d/m/Y', strtotime($lan['lan_data_vencimento'])),
                    'vencimento_raw' => $lan['lan_data_vencimento'],
                    'valor' => (float) ($lan['lan_valor'] ?? 0),
                    'status' => $status,
                ];
            }

            return $this->response->setJSON(['success' => true, 'data' => $resultados]);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao listar cobranças: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao listar cobranças.']);
        }
    }

    public function quitar($id)
    {
        try {
            $empresaId = get_empresa_id();
            if ($empresaId < 1) {
                return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sessão inválida.']);
            }

            $lancamentoModel = new LancamentoFinanceiroModel();
            $lancamento = $lancamentoModel
                ->where('id', (int) $id)
                ->where('lan_empresa_id', $empresaId)
                ->where('lan_tipo', 'receita')
                ->first();

            if (!$lancamento) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Cobrança não encontrada.']);
            }

            $hoje = date('Y-m-d');
            $ok = $lancamentoModel->update((int) $id, [
                'lan_status' => 'pago',
                'lan_data_pagamento' => $hoje,
                'lan_valor_pago' => $lancamento['lan_valor'],
            ]);

            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Não foi possível quitar a cobrança.']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Cobrança quitada com sucesso.']);
        } catch (\Throwable $e) {
            log_message('error', 'Erro ao quitar cobrança: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Erro ao quitar cobrança.']);
        }
    }

    private function calcularCompetencia($dataVencimento, $recorrencia)
    {
        if (!$dataVencimento) return '-';
        
        $data = date_create($dataVencimento);
        if (!$data) return '-';

        switch ($recorrencia) {
            case 'diaria':
                return date_format($data, 'd/m/Y');
            case 'semanal':
                $semana = date('W', strtotime($dataVencimento));
                $ano = date('y', strtotime($dataVencimento));
                return "Semana {$semana}/{$ano}";
            case 'quinzenal':
            case 'mensal':
            default:
                $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                $mes = (int) date('m', strtotime($dataVencimento)) - 1;
                $ano = date('y', strtotime($dataVencimento));
                return $meses[$mes] . '/' . $ano;
        }
    }

    private function formatarRecorrencia($recorrencia)
    {
        $map = [
            'diaria' => 'Diária',
            'semanal' => 'Semanal',
            'quinzenal' => 'Quinzenal',
            'mensal' => 'Mensal',
        ];
        return $map[$recorrencia] ?? 'Mensal';
    }
}

