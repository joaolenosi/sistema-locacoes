<!-- Vendor Javascript (Require in all Page) -->
<script src="<?= asset_url('assets/admin/js/vendor.js') ?>"></script>

<!-- App Javascript (Require in all Page) -->
<script src="<?= asset_url('assets/admin/js/app.js') ?>"></script>

<?php $path = service('uri')->getPath(); ?>
<?php if ($path === '' || $path === '/'): ?>
    <!-- Vector Map Js (apenas no Dashboard) -->
    <script src="<?= asset_url('assets/admin/vendor/jsvectormap/js/jsvectormap.min.js') ?>"></script>
    <script src="<?= asset_url('assets/admin/vendor/jsvectormap/maps/world-merc.js') ?>"></script>
    <script src="<?= asset_url('assets/admin/vendor/jsvectormap/maps/world.js') ?>"></script>

    <!-- Dashboard Js (apenas no Dashboard) -->
    <script src="<?= asset_url('assets/admin/js/pages/dashboard.analytics.js') ?>"></script>
<?php endif; ?>