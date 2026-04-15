<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Cobros de Instalacion <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #accordion-cobros-instalacion .card {
        margin-bottom: 0;
        border-radius: 0;
        border: 1px solid #dee2e6;
        border-bottom: none;
    }

    /* Última sección con borde inferior */
    #accordion-cobros-instalacion .card:last-child {
        border-bottom: 1px solid #dee2e6;
    }

    /* Header más limpio */
    #accordion-cobros-instalacion .card-header {
        background: #f8f9fa;
        padding: 8px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    /* Botón ocupa todo el ancho */
    #accordion-cobros-instalacion .card-header .btn {
        width: 100%;
        text-align: left;
        padding: 5px 0;
        font-weight: 600;
        color: #343a40;
    }

    /* Quitar estilo link feo */
    #accordion-cobros-instalacion .btn-link {
        text-decoration: none;
    }

    #accordion-cobros-instalacion .btn-link:hover {
        text-decoration: none;
        color: #007bff;
    }

    /* Cuerpo más uniforme */
    #accordion-cobros-instalacion .card-body {
        padding: 20px;
        background: #fff;
    }

    .resumen-validacion {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px;
        background: #f8f9fa;
    }

    .resumen-validacion span {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
    }

    .resumen-validacion strong {
        font-size: 16px;
        color: #212529;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Cobros de instalacion</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <input type="hidden" id="id-contrato-cobro">

        <div id="accordion-cobros-instalacion">

            <!-- SECCIÓN 1  LISTAS DE FACTURAS DE COBRO -->
            <div class="card">
                <div class="card-header" id="headingHistorialCobros">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseHistorialCobros" aria-expanded="true" aria-controls="collapseHistorialCobros">
                            Registros de facturas de cobro
                        </button>
                    </h5>
                </div>

                <div id="collapseHistorialCobros" class="collapse show" aria-labelledby="headingHistorialCobros" data-parent="#accordion-cobros-instalacion">
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-4">
                            <div class="input-group col-md-6">
                                <input type="text" id="customSearchCobros" placeholder="Buscar por solicitud o cliente" class="form-control">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="searchBtnCobros" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" id="clearSearchBtnCobros" type="button">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="tbl-cobros-instalacion">
                                <thead>
                                    <tr>
                                        <th>Correlativo</th>
                                        <th>Solicitud</th>
                                        <th>Contrato</th>
                                        <th>Cliente</th>
                                        <!-- <th>Monto cuotas</th>
                                        <th>Mora</th>
                                        <th>Total pagado</th> -->
                                        <th>Fecha</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header" id="headingAplicarCobro">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseAplicarCobro" aria-expanded="false" aria-controls="collapseAplicarCobro">
                            Cobrar cuotas de instalacion
                        </button>
                    </h5>
                </div>

                <div id="collapseAplicarCobro" class="collapse" aria-labelledby="headingAplicarCobro" data-parent="#accordion-cobros-instalacion">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="buscar-cliente">Buscar por nombre de cliente</label>
                            <select name="buscar-cuenta-cobro" id="buscar-cliente" class="form-control">
                                <option value="">...</option>
                            </select>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-sm" id="tbl-cuentas-cobro">
                                <thead>
                                    <tr>
                                        <th>Numero solicitud</th>
                                        <th>Numero contrato</th>
                                        <th>Nombre cliente</th>
                                        <th>Fecha creacion</th>
                                        <th>Saldo</th>
                                        <th>Operacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">Seleccione una cuenta para mostrar información.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-cobro-cuotas" tabindex="-1" aria-labelledby="modal-cobro-cuotas-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-cobro-cuotas-label">Cobrar cuotas de instalacion</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Cliente</span>
                            <strong id="resumen-cliente">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Solicitud</span>
                            <strong id="resumen-solicitud">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Contrato</span>
                            <strong id="resumen-contrato">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Costo de Instalación</span>
                            <strong id="resumen-costo-instalacion">$0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Saldo pendiente</span>
                            <strong id="resumen-saldo-pendiente">$0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-validacion">
                            <span>Cuotas pendientes</span>
                            <strong id="resumen-cuotas-pendientes">0</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm" id="tbl-detalle-cuotas">
                        <thead>
                            <tr>
                                <th>Cuota</th>
                                <th>Descripción</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Fecha de Pago</th>
                                <th>Monto</th>
                                <th>Abonado</th>
                                <th>Recargo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="monto-pago">Monto a cancelar</label>
                        <input type="number" min="0.01" step="0.01" class="form-control" id="monto-pago" placeholder="0.00">
                    </div>
                    <!-- <div class="col-md-4 mb-3">
                        <label for="cobrar-mora">Desea cobrar algun recargo</label>
                        <select class="form-control" id="cobrar-mora">
                            <option value="no" selected>No</option>
                            <option value="si">Si</option>
                        </select>
                    </div> -->
                    <!-- <div class="col-md-4 mb-3">
                        <label for="mora-pago">________</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="mora-pago" placeholder="0.00" disabled>
                    </div> -->
                </div>

                <div id="contenedor-validacion" class="alert alert-light border" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-validar-cobro">Validar cobro de cuotas</button>
                <button type="button" class="btn btn-success" id="btn-procesar-pago" style="display: none;">Procesar el pago</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/cobrosInstalacion/cobrosInstalacion.js') ?>"></script>
<?= $this->endSection() ?>