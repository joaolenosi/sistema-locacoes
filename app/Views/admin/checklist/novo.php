<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php helper('asset'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Novo Checklist') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/checklist') ?>">Checklist</a></li>
                <li class="breadcrumb-item active">Novo</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="formNovoChecklist" novalidate>
                    <input type="hidden" name="chk_id" value="0">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="vincular_locacao" name="vincular_locacao">
                                <label class="form-check-label" for="vincular_locacao">Vincular a uma locação</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="wrap_locacao" style="display: none;">
                            <label class="form-label" for="chk_locacao_id">Locação <span class="text-danger">*</span></label>
                            <select class="form-select" id="chk_locacao_id" name="chk_locacao_id">
                                <option value="">Selecione a locação</option>
                                <?php foreach ($locacoes ?? [] as $loc): ?>
                                <option value="<?= (int)$loc['id'] ?>" data-vei="<?= (int)($loc['loc_vei_id'] ?? 0) ?>">
                                    #<?= (int)$loc['id'] ?> - <?= esc($loc['vei_placa'] ?? '-') ?> - <?= esc($loc['cli_nome'] ?? '-') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="chk_veiculo_id" id="chk_veiculo_id" value="">
                        </div>
                        <div class="col-md-6" id="wrap_veiculo">
                            <label class="form-label" for="chk_veiculo_id_select">Veículo <span class="text-danger">*</span></label>
                            <select class="form-select" id="chk_veiculo_id_select" name="chk_veiculo_id_select">
                                <option value="">Selecione o veículo</option>
                                <?php foreach ($veiculos ?? [] as $vei): ?>
                                <option value="<?= (int)$vei['id'] ?>"><?= esc($vei['vei_placa'] ?? '-') ?> - <?= esc($vei['vei_modelo'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="chk_data">Data <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="chk_data" name="chk_data" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Criar e preencher checklist</button>
                            <a href="<?= base_url('admin/checklist') ?>" class="btn btn-light">Cancelar</a>
                        </div>
                    </div>
                </form>
                <div id="form-alert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('formNovoChecklist');
    const vincular = document.getElementById('vincular_locacao');
    const wrapLocacao = document.getElementById('wrap_locacao');
    const wrapVeiculo = document.getElementById('wrap_veiculo');
    const chkLocacaoId = document.getElementById('chk_locacao_id');
    const chkVeiculoId = document.getElementById('chk_veiculo_id');
    const chkVeiculoIdSelect = document.getElementById('chk_veiculo_id_select');
    const alertEl = document.getElementById('form-alert');

    function toggleVinculo() {
        if (vincular.checked) {
            wrapLocacao.style.display = 'block';
            wrapVeiculo.style.display = 'none';
            chkVeiculoIdSelect.removeAttribute('required');
            chkLocacaoId.setAttribute('required', 'required');
            chkVeiculoId.value = '';
        } else {
            wrapLocacao.style.display = 'none';
            wrapVeiculo.style.display = 'block';
            chkLocacaoId.removeAttribute('required');
            chkVeiculoIdSelect.setAttribute('required', 'required');
            chkLocacaoId.value = '';
        }
    }
    vincular.addEventListener('change', toggleVinculo);
    toggleVinculo();

    chkLocacaoId.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        chkVeiculoId.value = opt && opt.dataset.vei ? opt.dataset.vei : '';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alertEl.classList.add('d-none');
        let veiculoId = '';
        if (vincular.checked) {
            const opt = chkLocacaoId.options[chkLocacaoId.selectedIndex];
            veiculoId = opt && opt.dataset.vei ? opt.dataset.vei : '';
            if (!chkLocacaoId.value) {
                alertEl.textContent = 'Selecione a locação.';
                alertEl.classList.remove('d-none');
                return;
            }
        } else {
            veiculoId = chkVeiculoIdSelect.value;
            if (!veiculoId) {
                alertEl.textContent = 'Selecione o veículo.';
                alertEl.classList.remove('d-none');
                return;
            }
        }
        const fd = new FormData(form);
        fd.delete('chk_veiculo_id_select');
        fd.delete('vincular_locacao');
        fd.set('chk_veiculo_id', veiculoId);
        fd.set('chk_locacao_id', vincular.checked ? chkLocacaoId.value : '');
        fetch('<?= base_url('admin/checklist/salvar') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success && res.id) {
                window.location.href = '<?= base_url('admin/checklist/editar/') ?>' + res.id;
            } else {
                alertEl.textContent = res.message || 'Erro ao criar checklist.';
                alertEl.classList.remove('d-none');
            }
        })
        .catch(function () {
            alertEl.textContent = 'Erro de conexão.';
            alertEl.classList.remove('d-none');
        });
    });
})();
</script>
<?= $this->endSection() ?>
