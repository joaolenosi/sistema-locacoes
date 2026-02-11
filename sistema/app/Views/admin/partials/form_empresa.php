<form id="form-locadora">
    <div id="empresa-form-alert" class="alert alert-danger d-none" role="alert"></div>

    <input type="hidden" id="emp_id" name="emp_id" value="<?= esc($empresa['id'] ?? 1) ?>">
    <input type="hidden" id="emp_tipo" name="emp_tipo" value="<?= esc($empresa['emp_tipo'] ?? 'locadora') ?>">

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
            <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="<?= esc($empresa['emp_nome'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cpf_cnpj" class="form-label" id="label-cpf-cnpj">CNPJ <span class="text-danger">*</span></label>
            <input type="text" class="form-control bg-light" id="cpf_cnpj" name="cpf_cnpj" placeholder="00.000.000/0000-00" value="<?= esc($empresa['emp_cpf_cnpj'] ?? '') ?>" required readonly>
        </div>
    </div>

    <!-- Inscrição Estadual e Municipal -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
            <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" value="<?= esc($empresa['emp_inscricao_estadual'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="inscricao_municipal" class="form-label">Inscrição Municipal</label>
            <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal" value="<?= esc($empresa['emp_inscricao_municipal'] ?? '') ?>">
        </div>
    </div>

    <!-- CEP e Endereço -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="cep" class="form-label">CEP <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000" value="<?= esc($empresa['emp_cep'] ?? '') ?>" required>
                <button type="button" class="btn btn-outline-secondary" id="btn-buscar-cep" title="Buscar CEP">Buscar</button>
            </div>
            <small id="cep-erro" class="text-danger d-none"></small>
        </div>
        <div class="col-md-6">
            <label for="endereco" class="form-label">Endereço <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua, Avenida, etc." value="<?= esc($empresa['emp_rua'] ?? '') ?>" required>
        </div>
        <div class="col-md-3">
            <label for="numero" class="form-label">Número <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="numero" name="numero" value="<?= esc($empresa['emp_numero'] ?? '') ?>" required>
        </div>
    </div>

    <!-- Complemento e Bairro -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="complemento" class="form-label">Complemento</label>
            <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Apto, Sala, etc." value="<?= esc($empresa['emp_complemento'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="bairro" class="form-label">Bairro</label>
            <input type="text" class="form-control" id="bairro" name="bairro" placeholder="Preenchido pelo CEP">
        </div>
    </div>

    <!-- Cidade e Estado -->
    <div class="row mb-3">
        <div class="col-md-8">
            <label for="cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cidade" name="cidade" value="<?= esc($empresa['emp_cidade'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" id="estado" name="estado" required>
                <option value="">Selecione...</option>
                <option value="AC" <?= (($empresa['emp_estado'] ?? '') === 'AC') ? 'selected' : '' ?>>Acre</option>
                <option value="AL" <?= (($empresa['emp_estado'] ?? '') === 'AL') ? 'selected' : '' ?>>Alagoas</option>
                <option value="AP" <?= (($empresa['emp_estado'] ?? '') === 'AP') ? 'selected' : '' ?>>Amapá</option>
                <option value="AM" <?= (($empresa['emp_estado'] ?? '') === 'AM') ? 'selected' : '' ?>>Amazonas</option>
                <option value="BA" <?= (($empresa['emp_estado'] ?? '') === 'BA') ? 'selected' : '' ?>>Bahia</option>
                <option value="CE" <?= (($empresa['emp_estado'] ?? '') === 'CE') ? 'selected' : '' ?>>Ceará</option>
                <option value="DF" <?= (($empresa['emp_estado'] ?? '') === 'DF') ? 'selected' : '' ?>>Distrito Federal</option>
                <option value="ES" <?= (($empresa['emp_estado'] ?? '') === 'ES') ? 'selected' : '' ?>>Espírito Santo</option>
                <option value="GO" <?= (($empresa['emp_estado'] ?? '') === 'GO') ? 'selected' : '' ?>>Goiás</option>
                <option value="MA" <?= (($empresa['emp_estado'] ?? '') === 'MA') ? 'selected' : '' ?>>Maranhão</option>
                <option value="MT" <?= (($empresa['emp_estado'] ?? '') === 'MT') ? 'selected' : '' ?>>Mato Grosso</option>
                <option value="MS" <?= (($empresa['emp_estado'] ?? '') === 'MS') ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                <option value="MG" <?= (($empresa['emp_estado'] ?? '') === 'MG') ? 'selected' : '' ?>>Minas Gerais</option>
                <option value="PA" <?= (($empresa['emp_estado'] ?? '') === 'PA') ? 'selected' : '' ?>>Pará</option>
                <option value="PB" <?= (($empresa['emp_estado'] ?? '') === 'PB') ? 'selected' : '' ?>>Paraíba</option>
                <option value="PR" <?= (($empresa['emp_estado'] ?? '') === 'PR') ? 'selected' : '' ?>>Paraná</option>
                <option value="PE" <?= (($empresa['emp_estado'] ?? '') === 'PE') ? 'selected' : '' ?>>Pernambuco</option>
                <option value="PI" <?= (($empresa['emp_estado'] ?? '') === 'PI') ? 'selected' : '' ?>>Piauí</option>
                <option value="RJ" <?= (($empresa['emp_estado'] ?? '') === 'RJ') ? 'selected' : '' ?>>Rio de Janeiro</option>
                <option value="RN" <?= (($empresa['emp_estado'] ?? '') === 'RN') ? 'selected' : '' ?>>Rio Grande do Norte</option>
                <option value="RS" <?= (($empresa['emp_estado'] ?? '') === 'RS') ? 'selected' : '' ?>>Rio Grande do Sul</option>
                <option value="RO" <?= (($empresa['emp_estado'] ?? '') === 'RO') ? 'selected' : '' ?>>Rondônia</option>
                <option value="RR" <?= (($empresa['emp_estado'] ?? '') === 'RR') ? 'selected' : '' ?>>Roraima</option>
                <option value="SC" <?= (($empresa['emp_estado'] ?? '') === 'SC') ? 'selected' : '' ?>>Santa Catarina</option>
                <option value="SP" <?= (($empresa['emp_estado'] ?? '') === 'SP') ? 'selected' : '' ?>>São Paulo</option>
                <option value="SE" <?= (($empresa['emp_estado'] ?? '') === 'SE') ? 'selected' : '' ?>>Sergipe</option>
                <option value="TO" <?= (($empresa['emp_estado'] ?? '') === 'TO') ? 'selected' : '' ?>>Tocantins</option>
            </select>
        </div>
    </div>

    <!-- Telefone, E-mail e Senha (credenciais de acesso) -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000" value="<?= esc($empresa['emp_telefone'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" value="<?= esc($empresa['emp_email'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" placeholder="Deixe em branco para não alterar" autocomplete="new-password">
        </div>
    </div>

    <!-- Site e Observações -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="site" class="form-label">Site</label>
            <input type="url" class="form-control" id="site" name="site" placeholder="https://" value="<?= esc($empresa['emp_site'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes" rows="1"><?= esc($empresa['emp_obs'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Botão Salvar -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSalvarEmpresa">Salvar alterações</button>
            </div>
        </div>
    </div>
</form>
