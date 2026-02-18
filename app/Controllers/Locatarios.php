<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\LancamentoFinanceiroModel;
use App\Models\LocacaoModel;

class Locatarios extends BaseController
{
    public function index(): string
    {
        $clienteModel = new ClienteModel();

        $empresaId = get_empresa_id();
        $locatarios = $clienteModel
            ->where('cli_empresa_id', $empresaId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Listagem de Locatários',
            // Dados iniciais (primeira renderização, sem consulta na view)
            'locatarios' => $locatarios,
        ];

        try {
            return view('admin/locatarios/index', $data);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Ficha do cliente (detalhes do locatário): cards resumo, contas a receber, histórico de locações, infrações (placeholder).
     */
    public function detalhes($id)
    {
        $empresaId = get_empresa_id();
        $cliId = (int) $id;
        if ($cliId < 1) {
            return redirect()->to(base_url('admin/locatarios'))->with('error', 'Locatário inválido.');
        }

        $clienteModel = new ClienteModel();
        $cliente = $clienteModel
            ->where('id', $cliId)
            ->where('cli_empresa_id', $empresaId)
            ->first();

        if (!$cliente) {
            return redirect()->to(base_url('admin/locatarios'))->with('error', 'Locatário não encontrado.');
        }

        $lancamentoModel = new LancamentoFinanceiroModel();
        $locacaoModel = new LocacaoModel();

        // Total em aberto: receita pendente vinculada a locações deste cliente
        $totalAberto = $lancamentoModel
            ->selectSum('lancamentos_financeiros.lan_valor')
            ->join('locacoes', 'locacoes.id = lancamentos_financeiros.lan_locacao_id', 'inner')
            ->where('lancamentos_financeiros.lan_empresa_id', $empresaId)
            ->where('lancamentos_financeiros.lan_tipo', 'receita')
            ->where('lancamentos_financeiros.lan_status', 'pendente')
            ->where('locacoes.loc_cli_id', $cliId)
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->first();
        $totalAberto = (float) ($totalAberto['lan_valor'] ?? 0);

        // Total de veículos alugados (histórico: todas as locações)
        $totalLocacoes = $locacaoModel
            ->where('loc_cli_id', $cliId)
            ->where('loc_empresa_id', $empresaId)
            ->countAllResults();

        // Locação ativa (ativa, reservada ou atrasada)
        $locacaoAtiva = $locacaoModel
            ->builderWithJoins()
            ->where('locacoes.loc_cli_id', $cliId)
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->whereIn('locacoes.loc_status', ['ativa', 'reservada', 'atrasada'])
            ->orderBy('locacoes.loc_data_inicio', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Contas a receber do cliente (lista)
        $lancamentos = $lancamentoModel
            ->select('lancamentos_financeiros.*')
            ->select('locacoes.id as loc_id, locacoes.loc_recorrencia_pagamento')
            ->select('veiculos.vei_placa, veiculos.vei_modelo')
            ->join('locacoes', 'locacoes.id = lancamentos_financeiros.lan_locacao_id', 'left')
            ->join('veiculos', 'veiculos.id = locacoes.loc_vei_id', 'left')
            ->where('lancamentos_financeiros.lan_empresa_id', $empresaId)
            ->where('lancamentos_financeiros.lan_tipo', 'receita')
            ->where('lancamentos_financeiros.lan_status', 'pendente')
            ->where('locacoes.loc_cli_id', $cliId)
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->orderBy('lancamentos_financeiros.lan_data_vencimento', 'ASC')
            ->findAll();

        $contasReceber = [];
        $hoje = date('Y-m-d');
        foreach ($lancamentos as $lan) {
            $competencia = $this->calcularCompetencia($lan['lan_data_vencimento'], $lan['loc_recorrencia_pagamento'] ?? 'mensal');
            $recorrencia = $this->formatarRecorrencia($lan['loc_recorrencia_pagamento'] ?? 'mensal');
            $status = $lan['lan_data_vencimento'] < $hoje ? 'Em atraso' : 'Pendente';
            $locacaoNum = 'LC-' . date('Y', strtotime($lan['lan_data_vencimento'])) . '-' . str_pad($lan['loc_id'] ?? 0, 3, '0', STR_PAD_LEFT);
            $contasReceber[] = [
                'id' => $lan['id'],
                'locacao' => $locacaoNum,
                'veiculo' => ($lan['vei_placa'] ?? '-') . ' (' . ($lan['vei_modelo'] ?? '-') . ')',
                'recorrencia' => $recorrencia,
                'competencia' => $competencia,
                'vencimento' => date('d/m/Y', strtotime($lan['lan_data_vencimento'])),
                'vencimento_raw' => $lan['lan_data_vencimento'],
                'valor' => (float) ($lan['lan_valor'] ?? 0),
                'descricao' => $lan['lan_descricao'] ?? '',
                'status' => $status,
            ];
        }

        // Histórico de veículos locados
        $historicoLocacoes = $locacaoModel
            ->builderWithJoins()
            ->where('locacoes.loc_cli_id', $cliId)
            ->where('locacoes.loc_empresa_id', $empresaId)
            ->orderBy('locacoes.loc_data_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Ficha do cliente - ' . ($cliente['cli_nome'] ?? 'Locatário'),
            'cliente' => $cliente,
            'total_aberto' => $totalAberto,
            'total_locacoes' => $totalLocacoes,
            'locacao_ativa' => $locacaoAtiva,
            'tem_veiculo_locado' => !empty($locacaoAtiva),
            'contas_receber' => $contasReceber,
            'historico_locacoes' => $historicoLocacoes,
        ];

        return view('admin/locatarios/detalhes', $data);
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

    public function listar()
    {
        try {
            $clienteModel = new ClienteModel();
            $locatarios = $clienteModel
                ->where('cli_empresa_id', get_empresa_id())
                ->orderBy('created_at', 'DESC')
                ->findAll();
            return $this->response->setJSON([
                'success' => true,
                'data' => $locatarios,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao listar locatários.',
            ]);
        }
    }

    public function editar($id)
    {
        try {
            $clienteModel = new ClienteModel();
            $locatario = $clienteModel->find((int) $id);
            if (!$locatario) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locatário não encontrado.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $locatario,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar locatário.',
            ]);
        }
    }

    public function criar()
    {
        try {
            $payload = (array) $this->request->getPost();

            $data = $this->normalizeClientePayload($payload);
            $validationError = $this->validateClientePayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['cli_empresa_id'] = get_empresa_id();
            if (!array_key_exists('cli_ativo', $data) || $data['cli_ativo'] === null || $data['cli_ativo'] === '') {
                $data['cli_ativo'] = 1;
            }

            $clienteModel = new ClienteModel();
            $id = $clienteModel->insert($data, true);

            if (!$id) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível cadastrar o locatário.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locatário cadastrado com sucesso.',
                'id' => $id,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao cadastrar locatário.',
            ]);
        }
    }

    public function atualizar($id)
    {
        try {
            $clienteModel = new ClienteModel();
            $existing = $clienteModel->find((int) $id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Locatário não encontrado.',
                ]);
            }

            $payload = (array) $this->request->getPost();
            $data = $this->normalizeClientePayload($payload);
            $validationError = $this->validateClientePayload($data);
            if ($validationError) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $validationError,
                ]);
            }

            $data['cli_empresa_id'] = get_empresa_id();

            $ok = $clienteModel->update((int) $id, $data);
            if (!$ok) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Não foi possível atualizar o locatário.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Locatário atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar locatário.',
            ]);
        }
    }

    private function normalizeClientePayload(array $payload): array
    {
        $tipo = (string) ($payload['cli_tipo_pessoa'] ?? 'fisica');
        $allowedTipos = ['fisica', 'juridica', 'estrangeiro'];
        if (!in_array($tipo, $allowedTipos, true)) {
            $tipo = 'fisica';
        }

        $cpfCnpj = null;
        if (array_key_exists('cli_cpf_cnpj', $payload) && $payload['cli_cpf_cnpj'] !== '' && $payload['cli_cpf_cnpj'] !== null) {
            $cpfCnpj = preg_replace('/\D/', '', (string) $payload['cli_cpf_cnpj']) ?: null;
        }

        $email = null;
        if (array_key_exists('cli_email', $payload) && $payload['cli_email'] !== '' && $payload['cli_email'] !== null) {
            $email = trim((string) $payload['cli_email']);
        }

        $ativo = null;
        if (array_key_exists('cli_ativo', $payload)) {
            $raw = $payload['cli_ativo'];
            // checkbox: "on" / "1" / 1
            $ativo = ($raw === 'on' || $raw === '1' || $raw === 1 || $raw === true) ? 1 : 0;
        }

        return [
            'cli_tipo_pessoa' => $tipo,
            'cli_nome' => trim((string) ($payload['cli_nome'] ?? '')),
            'cli_cpf_cnpj' => $cpfCnpj,
            'cli_data_nascimento' => ($payload['cli_data_nascimento'] ?? '') ?: null,
            'cli_email' => $email,
            'cli_telefone' => preg_replace('/\D/', '', (string) ($payload['cli_telefone'] ?? '')) ?: null,
            'cli_whatsapp' => preg_replace('/\D/', '', (string) ($payload['cli_whatsapp'] ?? '')) ?: null,
            'cli_cnh_numero' => preg_replace('/\D/', '', (string) ($payload['cli_cnh_numero'] ?? '')) ?: null,
            'cli_cnh_validade' => ($payload['cli_cnh_validade'] ?? '') ?: null,
            'cli_cep' => preg_replace('/\D/', '', (string) ($payload['cli_cep'] ?? '')) ?: null,
            'cli_estado' => trim((string) ($payload['cli_estado'] ?? '')) ?: null,
            'cli_cidade' => trim((string) ($payload['cli_cidade'] ?? '')) ?: null,
            'cli_bairro' => trim((string) ($payload['cli_bairro'] ?? '')) ?: null,
            'cli_rua' => trim((string) ($payload['cli_rua'] ?? '')) ?: null,
            'cli_numero' => trim((string) ($payload['cli_numero'] ?? '')) ?: null,
            'cli_complemento' => trim((string) ($payload['cli_complemento'] ?? '')) ?: null,
            'cli_obs' => trim((string) ($payload['cli_obs'] ?? '')) ?: null,
            'cli_ativo' => $ativo,
        ];
    }

    private function validateClientePayload(array $data): ?string
    {
        if (($data['cli_tipo_pessoa'] ?? '') === '') return 'Informe o tipo de pessoa.';
        if (($data['cli_nome'] ?? '') === '') return 'Informe o nome.';

        $tipo = (string) ($data['cli_tipo_pessoa'] ?? 'fisica');
        $cpfCnpj = (string) ($data['cli_cpf_cnpj'] ?? '');

        // Estrangeiro pode não ter CPF/CNPJ
        if ($tipo !== 'estrangeiro') {
            if ($cpfCnpj === '') return 'Informe o CPF/CNPJ.';
            $len = strlen($cpfCnpj);
            if ($len !== 11 && $len !== 14) return 'CPF/CNPJ inválido.';
        } else {
            // se preencher, valida tamanho também
            if ($cpfCnpj !== '') {
                $len = strlen($cpfCnpj);
                if ($len !== 11 && $len !== 14) return 'CPF/CNPJ inválido.';
            }
        }

        $email = (string) ($data['cli_email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido.';
        }

        return null;
    }
}
