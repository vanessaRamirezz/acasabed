<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Plantillas <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .dashboard-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .08);
        transition: .25s;
        overflow: hidden;
    }

    .card-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        background: #f4f6f9;
    }

    .card-icon i {
        font-size: 28px;
        color: #343a40;
    }

    .card-title-custom {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .card-description {
        color: #6c757d;
        min-height: 60px;
    }
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
            <div class="col-md-6 d-flex">
                <div class="card shadow-sm border-0 w-100 h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Importar archivo recibido de alcaldía</h6>
                        <small class="text-muted">Sube el archivo Excel para validar cargos de alumbrado y aseo</small>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-between">

                        <!-- <div class="card shadow-sm border-0"> -->


                        <div class="card-body">

                            <!-- Input file -->
                            <div class="mb-3">
                                <label for="input-excel" class="form-label fw-semibold">
                                    Archivo Excel
                                </label>

                                <input type="file"
                                    id="input-excel"
                                    class="form-control"
                                    accept=".xlsx, .xls">

                                <div class="form-text">
                                    Formatos permitidos: .xlsx, .xls
                                </div>
                            </div>

                            <!-- Estado -->
                            <div id="estado-excel" class="alert alert-info d-none">
                                <span id="estado-texto">Esperando archivo...</span>
                            </div>

                            <!-- Botones -->
                            <div class="mt-auto text-left">
                                <button
                                    type="button"
                                    id="btn-cancelar-excel"
                                    class="btn btn-outline-danger d-none">
                                    <i class="fas fa-trash me-1"></i>
                                    Cancelar
                                </button>

                                <button
                                    type="button"
                                    id="btn-cargar-excel"
                                    class="btn btn-dark d-none">
                                    <i class="fas fa-upload mr-1"></i>
                                    Importar
                                </button>
                            </div>
                        </div>
                        <!-- </div> -->

                    </div>
                </div>
            </div>

            <!-- TARJETA 2 -->
            <div class="col-md-6 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column">

                        <div class="card-icon">
                            <i class="fas fa-city"></i>
                        </div>

                        <h4 class="card-title-custom">
                            Plantilla enviada a la alcaldía
                        </h4>

                        <p class="card-description">
                            Genera el documento correspondiente al período activo que será enviado a la alcaldía.
                        </p>

                        <div class="mt-auto">
                            <button id="btnExportarExcelAlcaldia"
                                class="btn btn-dark">
                                <i class="fas fa-download mr-1"></i>
                                Generar archivo
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-3">

            <!-- TARJETA 3 -->
            <div class="col-md-6 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column">

                        <div class="card-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>

                        <h4 class="card-title-custom">Importar archivo recibido del banco</h4>

                        <p class="card-description">
                            Sube el archivo Excel devuelto por el banco para actualizar las facturas pagadas.
                        </p>

                        <input type="file" id="inputExcelPagos" class="form-control mb-3" accept=".xlsx, .xls">


                        <div class="mt-auto d-flex flex-column flex-md-row gap-2">

                            <button id="btnImportarExcel" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar pagos
                            </button>

                            <button id="btnCancelarImportacionExcel" class="btn btn-outline-danger">
                                <i class="fas fa-undo-alt"></i>
                                Cancelar importacion
                            </button>

                        </div>

                    </div>
                </div>
            </div>

            <!-- TARJETA 4 -->
            <div class="col-md-6 mb-3">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column">

                        <div class="card-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>

                        <h4 class="card-title-custom">
                            Plantilla que se emite al banco
                        </h4>

                        <p class="card-description">
                            Genera el documento correspondiente al período activo que se envia al banco.
                        </p>

                        <div class="mt-auto">
                            <button id="btnExportarExcel"
                                class="btn btn-dark">
                                <i class="fas fa-download mr-1"></i>
                                Generar archivo
                            </button>
                        </div>
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