<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Facturacion de Servicio <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #accordion-facturacion-servicio .card {
        margin-bottom: 0;
        border-radius: 0;
        border: 1px solid #dee2e6;
        border-bottom: none;
    }

    #accordion-facturacion-servicio .card:last-child {
        border-bottom: 1px solid #dee2e6;
    }

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

    #accordion-facturacion-servicio .btn-link {
        text-decoration: none;
    }

    #accordion-facturacion-servicio .btn-link:hover {
        text-decoration: none;
        color: #007bff;
    }

    #accordion-facturacion-servicio .card-body {
        padding: 20px;
        background: #fff;
    }

    .resumen-servicio {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px;
        background: #f8f9fa;
    }

    .resumen-servicio span {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
    }

    .resumen-servicio strong {
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
                <h1 class="m-0 text-dark">Facturacion de servicio</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card-header py-3 d-flex flex-wrap align-items-center">
            <button type="button" id="btn-generar-facturas-servicio" class="btn bg-gradient-primary btn-flat">Generar Facturas del Servicio</button>
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

            <!-- <div class="card shadow mb-4">
                <div class="card-header" id="headingCobroServicio">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseCobroServicio" aria-expanded="false" aria-controls="collapseCobroServicio">
                            Cobrar facturas del servicio
                        </button>
                    </h5>
                </div>

                <div id="collapseCobroServicio" class="collapse" aria-labelledby="headingCobroServicio" data-parent="#accordion-facturacion-servicio">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="buscar-cliente-servicio">Buscar por nombre de cliente</label>
                            <select id="buscar-cliente-servicio" class="form-control">
                                <option value="">...</option>
                            </select>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-sm" id="tbl-cuentas-facturacion-servicio">
                                <thead>
                                    <tr>
                                        <th>Solicitud</th>
                                        <th>Contrato</th>
                                        <th>Cliente</th>
                                        <th>Periodo</th>
                                        <th>Saldo</th>
                                        <th>Estado</th>
                                        <th>Operacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center">Seleccione un cliente para mostrar informacion.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</section>

<!-- <div class="modal fade" id="modal-pago-factura-servicio" tabindex="-1" aria-labelledby="modal-pago-factura-servicio-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-pago-factura-servicio-label">Cobrar factura de servicio</h5>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <div class="resumen-servicio">
                            <span>Cliente</span>
                            <strong id="resumen-servicio-cliente">-</strong>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="resumen-servicio">
                            <span>Solicitud</span>
                            <strong id="resumen-servicio-solicitud">-</strong>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="resumen-servicio">
                            <span>Contrato</span>
                            <strong id="resumen-servicio-contrato">-</strong>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="resumen-servicio">
                            <span>Medidor</span>
                            <strong id="resumen-servicio-medidor">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-servicio">
                            <span>Saldo vigente</span>
                            <strong id="resumen-servicio-saldo">$0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-servicio">
                            <span>Periodo vigente</span>
                            <strong id="resumen-servicio-periodo">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-servicio">
                            <span>Vencimiento</span>
                            <strong id="resumen-servicio-vencimiento">-</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="resumen-servicio">
                            <span>Facturas ligadas</span>
                            <strong id="resumen-servicio-cadena">0</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm" id="tbl-detalle-facturas-servicio">
                        <thead>
                            <tr>
                                <th>Correlativo</th>
                                <th>Periodo</th>
                                <th>F. emision</th>
                                <th>F. vencimiento</th>
                                <th>Consumo m3</th>
                                <th>Cargo actual</th>
                                <th>Saldo anterior</th>
                                <th>Mora</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="monto-pago-servicio">Monto a cancelar</label>
                        <input type="number" min="0.01" step="0.01" class="form-control" id="monto-pago-servicio" placeholder="0.00">
                    </div>
                </div>

                <div id="contenedor-validacion-servicio" class="alert alert-light border" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-validar-factura-servicio">Validar pago</button>
                <button type="button" class="btn btn-success" id="btn-procesar-factura-servicio" style="display:none;">Procesar pago</button>
            </div>
        </div>
    </div>
</div> -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/facturacionServicio/facturacionServicio.js') ?>"></script>
<?= $this->endSection() ?>
