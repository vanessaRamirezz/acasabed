<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Reporte de Rutas <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Reporte de rutas</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- <div class="row mb-3">
            <div class="col-md-3 align-self-end">
                <button id="btnGenerar" class="btn btn-primary">
                    Generar
                </button>
            </div>
        </div> -->

        <!-- Vista previa -->
        <!-- <div class="row">
            <div class="col-12">
                <iframe id="visorPDF" style="width:100%; height:700px; border:none;"></iframe>
            </div>
        </div> -->

        <div class="row">
            <div class="col-12">

                <div id="contenedorPDF" style="position: relative; min-height: 700px;">

                    <div id="loadingRutas" style="
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255,255,255,0.85);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        flex-direction: column;
                        z-index: 9999;
                    ">
                        <div class="spinner-border text-primary" role="status"></div>
                        <h5 class="mt-3">Generando reporte...</h5>
                    </div>

                    <iframe
                        id="visorPDF"
                        style="width:100%; height:700px; border:none;">
                    </iframe>

                </div>

            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script type="module" src="<?= base_url('dist/js/reporte_rutas/rp_rutas.js') ?>"></script>

<?= $this->endSection() ?>