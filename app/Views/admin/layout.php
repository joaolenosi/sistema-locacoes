<!doctype html>
<html lang="pt-BR">
<head>
    <?= $this->include('admin/partials/head') ?>
</head>
<body>
    <!-- START Wrapper -->
    <div class="wrapper">
        <!-- ========== Topbar Start ========== -->
        <?= $this->include('admin/partials/header') ?>

        <!-- ========== App Menu Start ========== -->
        <?= $this->include('admin/partials/sidebar') ?>

        <!-- ========== App Menu End ========== -->

        <!-- ==================================================== -->
        <!-- Start right Content here -->
        <!-- ==================================================== -->
        <div class="page-content">
            <!-- Start Container -->
            <div class="container-xxl">
                <?= $this->renderSection('content') ?>
            </div>
            <!-- End Container -->

            <!-- ========== Footer Start ========== -->
            <?= $this->include('admin/partials/footer') ?>
            <!-- ========== Footer End ========== -->
        </div>
        <!-- ==================================================== -->
        <!-- End Page Content -->
        <!-- ==================================================== -->
    </div>
    <!-- END Wrapper -->

    <?= $this->include('admin/partials/scripts') ?>
</body>
</html>
