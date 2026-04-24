<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Plantillas <?= $this->endSection() ?>

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
                <h1 class="m-0 text-dark texto">Plantillas</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="row">

            <!-- TARJETA 1 -->
            <div class="col-md-4 d-flex">
                <div class="card shadow-sm border-0 w-100 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-between">

                        <div>
                            <div class="mb-3">
                                <i class="fas fa-file-excel fa-3x text-success"></i>
                            </div>

                            <h5 class="card-title">Exportar Excel</h5>

                            <p class="card-text text-muted">
                                Genera el archivo oficial del periodo activo para envío al banco.
                            </p>
                        </div>

                        <button id="btnExportarExcel" class="btn btn-success mt-3">
                            <i class="fas fa-download"></i>
                            Generar
                        </button>

                    </div>
                </div>
            </div>

            <!-- TARJETA 2 -->
            <div class="col-md-8 d-flex">
                <div class="card shadow-sm border-0 w-100 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-between">

                        <div>
                            <div class="mb-3">
                                <i class="fas fa-file-upload fa-3x text-primary"></i>
                            </div>

                            <h5 class="card-title">Importar Excel</h5>

                            <p class="card-text text-muted">
                                Sube el archivo Excel devuelto por el banco para actualizar las facturas pagadas.
                            </p>

                            <input type="file" id="inputExcelPagos" class="form-control mb-3" accept=".xlsx, .xls">
                        </div>

                        <button id="btnImportarExcel" class="btn btn-primary mt-3">
                            <i class="fas fa-sync-alt"></i>
                            Actualizar pagos
                        </button>

                    </div>
                </div>
            </div>

        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script type="module" src="<?= base_url('dist/js/cargar_generar_plantillas/cargar_generar_plantillas.js') ?>"></script>

<?= $this->endSection() ?>