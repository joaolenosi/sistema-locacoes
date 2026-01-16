<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<style>
    .mov-container {
        max-width: 820px;
        margin: 0 auto;
    }

    .mov-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .mov-subtitle {
        color: #64748b;
    }

    .mov-divider {
        border-top: 1px solid rgba(148, 163, 184, 0.35);
    }

    .mov-link {
        font-weight: 500;
        text-decoration: underline;
    }
</style>

<!-- ========== Page Title Start ========== -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Movimentações') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('admin/financeiro') ?>">Financeiro</a>
                </li>
                <li class="breadcrumb-item active">Movimentações</li>
            </ol>
        </div>
    </div>
</div>
<!-- ========== Page Title End ========== -->

<div class="row justify-content-center">
    <div class="col-12">
        <div class="mov-container">
            <div class="card mov-card">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 fw-semibold">Nova movimentação</h4>
                            <div class="mov-subtitle">Layout limpo e focado para cadastrar rapidamente.</div>
                        </div>
                        <a class="btn btn-outline-secondary" href="<?= base_url('admin/financeiro') ?>">
                            Voltar
                        </a>
                    </div>

                    <div class="mov-divider my-4"></div>

                    <form id="formMovimentacao" novalidate>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                            <div class="btn-group" role="group" aria-label="Tipo de movimentação">
                                <input type="radio" class="btn-check" name="lan_tipo" id="tipoReceita" autocomplete="off" value="receita" checked>
                                <label class="btn btn-outline-success" for="tipoReceita">
                                    <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                                    Receita
                                </label>

                                <input type="radio" class="btn-check" name="lan_tipo" id="tipoDespesa" autocomplete="off" value="despesa">
                                <label class="btn btn-outline-danger" for="tipoDespesa">
                                    <iconify-icon icon="iconamoon:minus-duotone" class="fs-18"></iconify-icon>
                                    Despesa
                                </label>
                            </div>

                            <span class="text-muted small">Campos com <span class="text-danger">*</span> são obrigatórios.</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="lan_categoria_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select" id="lan_categoria_id" name="lan_categoria_id" required>
                                    <option value="" id="categoriaPlaceholder">Selecione a categoria da receita</option>
                                    <?php foreach (($categorias_receita ?? []) as $cat): ?>
                                        <option value="<?= esc($cat['id']) ?>"><?= esc($cat['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecione uma categoria.</div>
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <a
                                    class="mov-link text-primary"
                                    href="<?= base_url('admin/cadastro/categorias-financeiras') ?>"
                                    target="_blank"
                                    rel="noopener"
                                >Cadastrar nova categoria</a>
                            </div>

                            <div class="col-12">
                                <label for="lan_descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lan_descricao" name="lan_descricao" placeholder="Ex.: Venda de um pneu" required>
                                <div class="invalid-feedback">Informe uma descrição.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lan_data_vencimento" class="form-label">Data vencimento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="lan_data_vencimento" name="lan_data_vencimento" required>
                                <div class="invalid-feedback">Informe a data de vencimento.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="lan_valor" class="form-label">Valor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control money" id="lan_valor" name="lan_valor" placeholder="Ex.: 150,00" required>
                                <div class="invalid-feedback">Informe o valor.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lan_locacao_id" class="form-label">Locação</label>
                                <select class="form-select" id="lan_locacao_id" name="lan_locacao_id">
                                    <option value="">Selecione a locação</option>
                                    <?php foreach (($locacoes ?? []) as $loc): ?>
                                        <option value="<?= esc($loc['id']) ?>"><?= esc($loc['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="lan_veiculo" class="form-label">Veículo</label>
                                <input type="text" class="form-control" id="lan_veiculo" name="lan_veiculo" placeholder="Ex.: ABC-1234 ou ABC-1A23" style="text-transform: uppercase;">
                            </div>

                            <div class="col-12">
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="lan_marcar_pago" name="marcar_pago">
                                    <label class="form-check-label" for="lan_marcar_pago" id="labelMarcarPago">
                                        Marcar como recebida
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="#" class="text-primary mov-link" id="toggleCampos">
                                <iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon>
                                Ver mais
                            </a>
                        </div>

                        <div id="camposAdicionais" class="mt-3" style="display:none;">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="lan_data_lancamento" class="form-label">Data lançamento</label>
                                    <input type="date" class="form-control" id="lan_data_lancamento" name="lan_data_lancamento">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="lan_data_pagamento" class="form-label">Data pagamento</label>
                                    <input type="date" class="form-control" id="lan_data_pagamento" name="lan_data_pagamento">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="lan_valor_pago" class="form-label">Valor pago</label>
                                    <input type="text" class="form-control money" id="lan_valor_pago" name="lan_valor_pago" placeholder="Ex.: 150,00">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="lan_forma_pagamento" class="form-label">Forma de pagamento</label>
                                    <select class="form-select" id="lan_forma_pagamento" name="lan_forma_pagamento">
                                        <option value="">Selecione...</option>
                                        <?php foreach (($formas_pagamento ?? []) as $fp): ?>
                                            <option value="<?= esc($fp['id']) ?>"><?= esc($fp['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="lan_referencia" class="form-label">Referência</label>
                                    <input type="text" class="form-control" id="lan_referencia" name="lan_referencia" placeholder="NSU, código PIX, comprovante, etc.">
                                </div>
                                <div class="col-12">
                                    <label for="lan_observacoes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="lan_observacoes" name="lan_observacoes" rows="3" placeholder="Observações adicionais..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mov-divider my-4"></div>

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a class="btn btn-light" href="<?= base_url('admin/financeiro') ?>">Cancelar</a>
                            <button type="submit" class="btn btn-success" id="btnSubmit">
                                <span id="btnSubmitLabel">
                                    <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                                    Adicionar receita
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery Mask Plugin (mesmo padrão do Financeiro) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formMovimentacao');
    const radios = document.querySelectorAll('input[name="lan_tipo"]');
    const selectCategoria = document.getElementById('lan_categoria_id');
    const placeholderCategoria = document.getElementById('categoriaPlaceholder');
    const labelMarcar = document.getElementById('labelMarcarPago');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');
    const toggleCampos = document.getElementById('toggleCampos');
    const camposAdicionais = document.getElementById('camposAdicionais');

    const categoriasReceita = <?= json_encode($categorias_receita ?? []) ?>;
    const categoriasDespesa = <?= json_encode($categorias_despesa ?? []) ?>;

    function setCategorias(tipo) {
        const cats = tipo === 'despesa' ? categoriasDespesa : categoriasReceita;

        // limpar options mantendo placeholder
        while (selectCategoria.options.length > 1) {
            selectCategoria.remove(1);
        }

        cats.forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nome;
            selectCategoria.appendChild(opt);
        });

        placeholderCategoria.textContent = tipo === 'despesa'
            ? 'Selecione a categoria da despesa'
            : 'Selecione a categoria da receita';
    }

    function setUIByTipo(tipo) {
        const isDespesa = tipo === 'despesa';
        labelMarcar.textContent = isDespesa ? 'Marcar como paga' : 'Marcar como recebida';

        btnSubmit.classList.toggle('btn-success', !isDespesa);
        btnSubmit.classList.toggle('btn-danger', isDespesa);
        btnSubmitLabel.innerHTML = isDespesa
            ? '<iconify-icon icon="iconamoon:minus-duotone" class="fs-18"></iconify-icon> Adicionar despesa'
            : '<iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon> Adicionar receita';

        setCategorias(tipo);
    }

    // Toggle "Ver mais"
    let expandido = false;
    if (toggleCampos) {
        toggleCampos.addEventListener('click', function (e) {
            e.preventDefault();
            expandido = !expandido;
            camposAdicionais.style.display = expandido ? 'block' : 'none';
            toggleCampos.innerHTML = expandido
                ? '<iconify-icon icon="iconamoon:arrow-up-2-duotone"></iconify-icon> Ver menos'
                : '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
            if (expandido) setTimeout(aplicarMascaraMonetaria, 50);
        });
    }

    // Máscara monetária usando jQuery Mask Plugin (mesma lógica do Financeiro)
    function aplicarMascaraMonetaria() {
        if (typeof $ !== 'undefined' && $.fn.mask) {
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
            $('.money').focusout(function(){
                if($(this).val().length <= 2 && $(this).val().length > 0){
                    var temp = $(this).val();
                    var newNum = temp + ',00';
                    $(this).val(newNum);
                }
            });
        }
    }

    // Máscara de placa (aceita antigo e Mercosul)
    function aplicarMascaraPlaca(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
            value = value.replace(/-+/g, '-');
            if (value.length > 8) value = value.substring(0, 8);
            if (value.length > 3 && value.indexOf('-') === -1) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            e.target.value = value;
        });
    }

    // init
    const tipoInicial = document.querySelector('input[name="lan_tipo"]:checked')?.value || 'receita';
    setUIByTipo(tipoInicial);

    radios.forEach(function (r) {
        r.addEventListener('change', function () {
            setUIByTipo(this.value);
        });
    });

    const campoPlaca = document.getElementById('lan_veiculo');
    if (campoPlaca) aplicarMascaraPlaca(campoPlaca);

    if (typeof $ !== 'undefined') {
        $(document).ready(function(){ aplicarMascaraMonetaria(); });
    } else {
        setTimeout(aplicarMascaraMonetaria, 500);
    }

    // validação visual bootstrap
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Aqui é só para teste de layout (sem backend ainda)
        alert('Movimentação pronta para envio (teste de UI).');
        form.reset();
        form.classList.remove('was-validated');
        setUIByTipo('receita');
        camposAdicionais.style.display = 'none';
        expandido = false;
        toggleCampos.innerHTML = '<iconify-icon icon="iconamoon:arrow-down-2-duotone"></iconify-icon> Ver mais';
        setTimeout(aplicarMascaraMonetaria, 50);
    }, false);
});
</script>

<?= $this->endSection() ?>

