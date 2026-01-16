<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Configurações') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Configurações</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#minha-assinatura" role="tab" aria-selected="false">
                            <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                            <span class="d-none d-sm-block">Minha Assinatura</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#locadora" role="tab" aria-selected="true">
                            <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                            <span class="d-none d-sm-block">Locadora</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Minha Assinatura -->
                    <div class="tab-pane" id="minha-assinatura" role="tabpanel">
                        <!-- Plano Atual -->
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Plano Atual</h5>
                                <p class="mb-2"><strong>Nome do plano:</strong> <?= esc($plano_atual['nome'] ?? 'Período de Teste') ?></p>
                                <?php if (isset($plano_atual['dias_restantes']) && $plano_atual['dias_restantes'] > 0): ?>
                                <p class="mb-0">
                                    <iconify-icon icon="iconamoon:clock-duotone" class="text-warning"></iconify-icon>
                                    Seu teste grátis termina em <?= esc($plano_atual['dias_restantes']) ?> dias!
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Escolha seu plano -->
                        <h5 class="mb-3">Escolha seu plano</h5>
                        
                        <!-- Toggle Mensal/Anual -->
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group" role="group" id="toggle-periodo">
                                <input type="radio" class="btn-check" name="periodo" id="periodo-mensal" value="mensal" checked>
                                <label class="btn btn-outline-primary" for="periodo-mensal">Mensal</label>
                                
                                <input type="radio" class="btn-check" name="periodo" id="periodo-anual" value="anual">
                                <label class="btn btn-outline-primary" for="periodo-anual">
                                    Anual <span class="badge bg-success ms-1">(30% de desconto)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cards de Planos -->
                        <div class="row g-3">
                            <?php foreach ($planos ?? [] as $plano): ?>
                            <div class="col-lg-4">
                                <div class="card h-100 border <?= isset($plano['mais_escolhido']) && $plano['mais_escolhido'] ? 'border-primary' : '' ?>">
                                    <?php if (isset($plano['mais_escolhido']) && $plano['mais_escolhido']): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-success">
                                            <iconify-icon icon="iconamoon:like-1-duotone"></iconify-icon>
                                            Mais escolhido
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h3 class="card-title text-primary mb-3"><?= esc($plano['nome']) ?></h3>
                                        <div class="mb-3">
                                            <span class="text-muted text-decoration-line-through" id="preco-original-<?= $plano['id'] ?>">
                                                de R$ <?= number_format($plano['preco_mensal'] * 1.2, 2, ',', '.') ?>
                                            </span>
                                            <h2 class="mb-0">
                                                <span id="preco-atual-<?= $plano['id'] ?>">R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?></span>
                                                <small class="text-muted fs-14" id="periodo-texto-<?= $plano['id'] ?>">/ Mês</small>
                                            </h2>
                                        </div>
                                        
                                        <?php if ($plano['id'] > 1): ?>
                                        <p class="text-muted small mb-3">
                                            Inclui tudo do Plano <?= esc($planos[$plano['id'] - 2]['nome']) ?><?= $plano['id'] == 3 ? ' e ' . esc($planos[$plano['id'] - 3]['nome']) : '' ?>, mais:
                                        </p>
                                        <?php else: ?>
                                        <p class="text-muted small mb-3"><?= esc($plano['descricao']) ?></p>
                                        <?php endif; ?>
                                        
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Cadastro de <?= $plano['limite_veiculos'] ? 'até ' . $plano['limite_veiculos'] . ' veículos' : 'veículos ilimitados' ?>
                                            </li>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                <?= $plano['limite_locatarios'] ? 'Cadastro de até ' . $plano['limite_locatarios'] . ' locatários' : 'Cadastro ilimitado de locatários' ?>
                                            </li>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                <?= $plano['limite_locacoes'] ? 'Controle de até ' . $plano['limite_locacoes'] . ' locações' : 'Controle ilimitado de locações' ?>
                                            </li>
                                            <?php if ($plano['suporte_tipo'] == 'whatsapp'): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte via WhatsApp e e-mail
                                            </li>
                                            <?php elseif ($plano['suporte_tipo'] == 'prioritario'): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte prioritário 24/7
                                            </li>
                                            <?php else: ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Suporte via e-mail
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['backup_diario']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Backup diário automático
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['relatorios_avancados']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Relatórios avançados e personalizados
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($plano['acesso_antecipado']): ?>
                                            <li class="mb-2">
                                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon>
                                                Acesso antecipado a novas funcionalidades
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                        
                                        <div class="mt-auto">
                                            <button type="button" class="btn btn-primary w-100" onclick="assinarPlano(<?= $plano['id'] ?>)">
                                                Assinar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab Locadora -->
                    <div class="tab-pane active" id="locadora" role="tabpanel">
                        <p class="text-muted mb-4">Atualize os dados da sua locadora aqui.</p>
                        
                        <form id="form-locadora">
                            <!-- Tipo da Empresa -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Tipo da empresa <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipo_empresa" id="pessoa-fisica" value="fisica">
                                            <label class="form-check-label" for="pessoa-fisica">
                                                Pessoa Física
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipo_empresa" id="pessoa-juridica" value="juridica" checked>
                                            <label class="form-check-label" for="pessoa-juridica">
                                                Pessoa Jurídica
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nome da Empresa -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nome_empresa" class="form-label">Nome empresa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="Loca mais" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cpf_cnpj" class="form-label" id="label-cpf-cnpj">CNPJ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" placeholder="00.000.000/0000-00" required>
                                </div>
                            </div>

                            <!-- Inscrição Estadual e Municipal -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual">
                                </div>
                                <div class="col-md-6">
                                    <label for="inscricao_municipal" class="form-label">Inscrição Municipal</label>
                                    <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal">
                                </div>
                            </div>

                            <!-- CEP e Endereço -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="cep" class="form-label">CEP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000" value="01001-000" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="endereco" class="form-label">Endereço <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua, Avenida, etc." required>
                                </div>
                                <div class="col-md-3">
                                    <label for="numero" class="form-label">Número <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="numero" name="numero" required>
                                </div>
                            </div>

                            <!-- Complemento e Bairro -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="complemento" class="form-label">Complemento</label>
                                    <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Apto, Sala, etc.">
                                </div>
                                <div class="col-md-6">
                                    <label for="bairro" class="form-label">Bairro <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="bairro" name="bairro" required>
                                </div>
                            </div>

                            <!-- Cidade e Estado -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="cidade" name="cidade" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="">Selecione...</option>
                                        <option value="AC">Acre</option>
                                        <option value="AL">Alagoas</option>
                                        <option value="AP">Amapá</option>
                                        <option value="AM">Amazonas</option>
                                        <option value="BA">Bahia</option>
                                        <option value="CE">Ceará</option>
                                        <option value="DF">Distrito Federal</option>
                                        <option value="ES">Espírito Santo</option>
                                        <option value="GO">Goiás</option>
                                        <option value="MA">Maranhão</option>
                                        <option value="MT">Mato Grosso</option>
                                        <option value="MS">Mato Grosso do Sul</option>
                                        <option value="MG">Minas Gerais</option>
                                        <option value="PA">Pará</option>
                                        <option value="PB">Paraíba</option>
                                        <option value="PR">Paraná</option>
                                        <option value="PE">Pernambuco</option>
                                        <option value="PI">Piauí</option>
                                        <option value="RJ">Rio de Janeiro</option>
                                        <option value="RN">Rio Grande do Norte</option>
                                        <option value="RS">Rio Grande do Sul</option>
                                        <option value="RO">Rondônia</option>
                                        <option value="RR">Roraima</option>
                                        <option value="SC">Santa Catarina</option>
                                        <option value="SP" selected>São Paulo</option>
                                        <option value="SE">Sergipe</option>
                                        <option value="TO">Tocantins</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Telefone e Email -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <!-- Site e Observações -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="site" class="form-label">Site</label>
                                    <input type="url" class="form-control" id="site" name="site" placeholder="https://">
                                </div>
                                <div class="col-md-6">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="1"></textarea>
                                </div>
                            </div>

                            <!-- Botão Salvar -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<!-- Script para máscaras e validação -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF/CNPJ
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const tipoEmpresaRadios = document.querySelectorAll('input[name="tipo_empresa"]');
    const labelCpfCnpj = document.getElementById('label-cpf-cnpj');

    function atualizarMascara() {
        const tipo = document.querySelector('input[name="tipo_empresa"]:checked').value;
        if (tipo === 'fisica') {
            cpfCnpjInput.placeholder = '000.000.000-00';
            labelCpfCnpj.textContent = 'CPF *';
            cpfCnpjInput.maxLength = 14;
        } else {
            cpfCnpjInput.placeholder = '00.000.000/0000-00';
            labelCpfCnpj.textContent = 'CNPJ *';
            cpfCnpjInput.maxLength = 18;
        }
        cpfCnpjInput.value = '';
    }

    tipoEmpresaRadios.forEach(radio => {
        radio.addEventListener('change', atualizarMascara);
    });

    // Máscara para CEP
    const cepInput = document.getElementById('cep');
    cepInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
    });

    // Máscara para Telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
        } else if (value.length > 6) {
            value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 6) + '-' + value.substring(6, 10);
        } else if (value.length > 2) {
            value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
        } else if (value.length > 0) {
            value = '(' + value;
        }
        e.target.value = value;
    });

    // Máscara para CPF/CNPJ
    cpfCnpjInput.addEventListener('input', function(e) {
        const tipo = document.querySelector('input[name="tipo_empresa"]:checked').value;
        let value = e.target.value.replace(/\D/g, '');
        
        if (tipo === 'fisica') {
            if (value.length > 11) value = value.substring(0, 11);
            if (value.length > 9) {
                value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6, 9) + '-' + value.substring(9);
            } else if (value.length > 6) {
                value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + '.' + value.substring(3);
            }
        } else {
            if (value.length > 14) value = value.substring(0, 14);
            if (value.length > 12) {
                value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '/' + value.substring(8, 12) + '-' + value.substring(12);
            } else if (value.length > 8) {
                value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5, 8) + '/' + value.substring(8);
            } else if (value.length > 5) {
                value = value.substring(0, 2) + '.' + value.substring(2, 5) + '.' + value.substring(5);
            } else if (value.length > 2) {
                value = value.substring(0, 2) + '.' + value.substring(2);
            }
        }
        e.target.value = value;
    });

    // Inicializar máscara
    atualizarMascara();

    // Buscar CEP (opcional - pode ser implementado depois)
    cepInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            // Aqui pode ser implementada a busca de CEP via API
            console.log('Buscar CEP:', cep);
        }
    });

    // Submit do formulário
    document.getElementById('form-locadora').addEventListener('submit', function(e) {
        e.preventDefault();
        // Aqui será implementada a lógica de salvamento
        alert('Formulário enviado! (Implementar lógica de salvamento)');
    });

    // Toggle Mensal/Anual para planos
    const periodoRadios = document.querySelectorAll('input[name="periodo"]');
    const planos = <?= json_encode($planos ?? []) ?>;

    function atualizarPrecos() {
        const periodo = document.querySelector('input[name="periodo"]:checked').value;
        
        planos.forEach(function(plano) {
            const precoOriginal = document.getElementById('preco-original-' + plano.id);
            const precoAtual = document.getElementById('preco-atual-' + plano.id);
            const periodoTexto = document.getElementById('periodo-texto-' + plano.id);
            
            if (periodo === 'anual') {
                // Mostrar preço anual (preço original = mensal, preço atual = anual)
                const precoMensalAnual = plano.preco_anual / 12;
                precoOriginal.textContent = 'de R$ ' + precoMensalAnual.toFixed(2).replace('.', ',');
                precoAtual.textContent = 'R$ ' + plano.preco_anual.toFixed(2).replace('.', ',');
                periodoTexto.textContent = '/ Ano';
            } else {
                // Mostrar preço mensal (preço original maior, preço atual = mensal)
                const precoOriginalMensal = plano.preco_mensal * 1.2;
                precoOriginal.textContent = 'de R$ ' + precoOriginalMensal.toFixed(2).replace('.', ',');
                precoAtual.textContent = 'R$ ' + plano.preco_mensal.toFixed(2).replace('.', ',');
                periodoTexto.textContent = '/ Mês';
            }
        });
    }

    periodoRadios.forEach(function(radio) {
        radio.addEventListener('change', atualizarPrecos);
    });

    // Função para assinar plano
    window.assinarPlano = function(planoId) {
        const periodo = document.querySelector('input[name="periodo"]:checked').value;
        const plano = planos.find(p => p.id === planoId);
        
        if (confirm('Deseja assinar o plano ' + plano.nome + ' no período ' + (periodo === 'mensal' ? 'mensal' : 'anual') + '?')) {
            // Aqui será implementada a lógica de assinatura
            console.log('Assinar plano:', planoId, periodo);
            alert('Assinatura do plano ' + plano.nome + ' iniciada! (Implementar lógica de pagamento)');
        }
    };
});
</script>

<?= $this->endSection() ?>
