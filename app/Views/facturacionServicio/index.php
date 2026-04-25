<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Facturación de Servicio <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #accordion-facturacion-servicio .card-header {
        background: #f8f9fa;
        padding: 8px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    #accordion-facturacion-servicio .card-header .btn {
        width: 100%;
        text-align: left;
        padding: 5px 0;
        font-weight: 600;
        color: #343a40;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Facturación de servicio</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Carga de Archivo de Alcaldía</h6>
                <small class="text-muted">Sube el archivo Excel para validar cargos de alumbrado y aseo</small>
            </div>

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
                <div id="estado-excel" class="alert alert-secondary d-none">
                    <span id="estado-texto">Esperando archivo...</span>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2 flex-wrap">

                    <button type="button"
                        id="btn-cargar-excel"
                        class="btn btn-outline-success">
                        <i class="fas fa-upload me-1"></i>
                        Validar Excel
                    </button>

                    <button type="button"
                        id="btn-generar-facturas-servicio"
                        class="btn btn-primary">
                        <i class="fas fa-file-invoice-dollar me-1"></i>
                        Generar Facturas
                    </button>
                    <button type="button" id="btn-imprimir-facturas-periodo" class="btn bg-gradient-success btn-flat ml-2">Imprimir</button>
                </div>
            </div>
        </div>

        <input type="hidden" id="id-factura-servicio">

        <div id="accordion-facturacion-servicio">
            <div class="card">
                <div class="card-header" id="headingHistorialServicio">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseHistorialServicio" aria-expanded="true" aria-controls="collapseHistorialServicio">
                            Registros de facturas del servicio
                        </button>
                    </h5>
                </div>

                <div id="collapseHistorialServicio" class="collapse show" aria-labelledby="headingHistorialServicio" data-parent="#accordion-facturacion-servicio">
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-4">
                            <div class="input-group col-md-7">
                                <input type="text" id="customSearchFacturasServicio" placeholder="Buscar por cliente, contrato, correlativo o periodo" class="form-control">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="searchBtnFacturasServicio" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" id="clearSearchBtnFacturasServicio" type="button">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="tbl-facturacion-servicio">
                                <thead>
                                    <tr>
                                        <th>Correlativo</th>
                                        <th>Contrato</th>
                                        <th>Cliente</th>
                                        <th>Periodo</th>
                                        <th>F. emision</th>
                                        <th>F. vencimiento</th>
                                        <th>Estado</th>
                                        <th>Operaciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/facturacionServicio/facturacionServicio.js') ?>"></script>
<?= $this->endSection() ?>