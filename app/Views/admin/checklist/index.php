<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<?php helper('asset'); ?>
<link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" type="text/css" />

<style>
    .gridjs-search input[type="search"] { min-height: 38px !important; padding-left: 40px !important; }
    .gridjs-pagination .gridjs-pages button[aria-current="page"],
    .gridjs-pagination .gridjs-pages button.gridjs-currentPage {
        background-color: #0d6efd !important; color: #fff !important; border-color: #0d6efd !important;
    }
    .gridjs-container { padding: 0 !important; }
    .gridjs-footer { box-shadow: none !important; }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold"><?= esc($title ?? 'Checklists') ?></h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Checklist</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0">Listagem de checklists de veículos (com ou sem vínculo a locação).</p>
                    <a href="<?= base_url('admin/checklist/novo') ?>" class="btn btn-primary">
                        <iconify-icon icon="iconamoon:plus-duotone" class="fs-18"></iconify-icon>
                        Novo Checklist
                    </a>
                </div>
                <div id="table-checklist"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>
<script>
(function () {
    const baseUrl = '<?= base_url() ?>';
    const listarUrl = baseUrl + 'admin/checklist/listar';

    fetch(listarUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success || !res.data) {
                document.getElementById('table-checklist').innerHTML = '<p class="text-danger">Erro ao carregar dados.</p>';
                return;
            }
            const rows = res.data.map(function (row) {
                const dataFmt = row.chk_data ? row.chk_data.split('-').reverse().join('/') : '-';
                const veiculo = (row.vei_placa || '-') + (row.vei_modelo ? ' / ' + row.vei_modelo : '');
                const locacao = row.chk_locacao_id
                    ? '<a href="' + baseUrl + 'admin/locacoes/editar/' + row.chk_locacao_id + '" class="text-primary">Sim #' + row.chk_locacao_id + '</a>'
                    : 'Não';
                const responsavel = row.chk_responsavel_entrega || row.chk_responsavel_devolucao || '-';
                const acoes = '<a href="' + baseUrl + 'admin/checklist/editar/' + row.id + '" class="btn btn-sm btn-outline-primary me-1">Editar</a>' +
                    '<a href="' + baseUrl + 'admin/checklist/' + row.id + '/pdf" target="_blank" class="btn btn-sm btn-outline-secondary me-1">Imprimir</a>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-excluir-checklist" data-id="' + row.id + '">Excluir</button>';
                return [row.id, dataFmt, veiculo, locacao, responsavel, acoes];
            });

            var grid = new gridjs.Grid({
                columns: [
                    { name: 'ID', width: '60px' },
                    { name: 'Data', width: '100px' },
                    { name: 'Veículo' },
                    { name: 'Locação', width: '120px', formatter: function (cell) { return gridjs.html(cell); } },
                    { name: 'Responsável', width: '140px' },
                    { name: 'Ações', width: '240px', sort: false, formatter: function (cell) { return gridjs.html(cell); } }
                ],
                data: rows,
                search: true,
                pagination: { limit: 15, summary: true },
                language: { search: { placeholder: 'Buscar...' }, pagination: { previous: 'Anterior', next: 'Próxima', showing: 'Exibindo', results: function() { return 'registros'; }, to: 'a', of: 'de' } }
            });
            grid.render(document.getElementById('table-checklist'));

            document.getElementById('table-checklist').addEventListener('click', function (e) {
                var btn = e.target && e.target.closest && e.target.closest('.btn-excluir-checklist');
                if (!btn) return;
                var id = btn.getAttribute('data-id');
                if (!id || !confirm('Excluir este checklist? Esta ação não pode ser desfeita.')) return;
                btn.disabled = true;
                fetch(baseUrl + 'admin/checklist/excluir/' + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.success) {
                            if (typeof window.toastr !== 'undefined') window.toastr.success('Checklist excluído.');
                            window.location.reload();
                        } else {
                            btn.disabled = false;
                            alert(res.message || 'Erro ao excluir.');
                        }
                    })
                    .catch(function () { btn.disabled = false; alert('Erro de conexão.'); });
            });
        })
        .catch(function () {
            document.getElementById('table-checklist').innerHTML = '<p class="text-danger">Erro ao carregar dados.</p>';
        });
})();
</script>
<?= $this->endSection() ?>
