<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Lecturas <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    /* label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    } */

    #tbl-lecturas-lote .select2-container {
        min-width: 180px;
        width: 100% !important;
    }

    #tbl-lecturas-lote .select2-container {
        min-width: 180px;
        width: 100% !important;
    }

    #tbl-lecturas-lote input,
    #tbl-lecturas-lote select {
        min-width: 120px;
    }

    #modal-lecturas-lote .modal-body {
        overflow: hidden;
    }

    #modal-lecturas-lote .table-responsive {
        max-height: 55vh;
        overflow-y: auto;
        overflow-x: auto;
    }

    #tbl-lecturas-lote {
        min-width: 1000px;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Lecturas</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Lectura Individual</button>
                <button type="button" id="btn-hacer-pdf-lecturas" class="btn bg-gradient-danger btn-flat ml-2">Generar Documento toma de lecturas</button>
            </div>
            <div class="card-header py-3">
                <button type="button" id="btn-agregar-lote" class="btn bg-gradient-primary btn-flat">Lectura Completa</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-8">
                        <input type="text" id="customSearchLecturas" placeholder="Buscar por contrato, instalador o periodo" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnLecturas" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnLecturas" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-lecturas">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Código de contrato</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                                <th>Instalador</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-lecturas" tabindex="-1" aria-labelledby="modal-lecturas-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-lecturas-label">Opciones del usuario</h5>
                    </div>
                    <div class="modal-body">
                        <div class="user">
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="periodo">Periodo</label>
                                        <select id="periodo" class="form-control"></select>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="contrato">Código de contrato</label>
                                        <select id="contrato" class="form-control">
                                            <option value="">Seleccione...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="fecha">Fecha de la lectura tomada</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="date" class="form-control" name="fecha" id="fecha">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="valor">Valor</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="number" class="form-control" name="valor" id="valor">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-7">
                                    <div class="form-group">
                                        <label for="instalador">Nombre de instalador</label>
                                        <select id="instalador" class="form-control">
                                            <option value="">Seleccione...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <input type="hidden" value="id-lectura" id="id-lectura">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary modal-guardar" id="guardar-registro">Guardar registro</button>
                        <button type="button" class="btn btn-warning modal-editar" id="actualizar-registro">Guardar registro</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal por lote -->
        <!-- Modal Lecturas Lote -->
        <div class="modal fade" id="modal-lecturas-lote" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Registro masivo de lecturas</h5>
                    </div>

                    <div class="modal-body">

                        <!-- FILTROS SUPERIORES -->
                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label>Periodo</label>
                                <select id="periodo-lote" class="form-control">
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Fecha de lectura</label>
                                <input type="date" id="fecha-lote" class="form-control">
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="btn-cargar-contratos">
                                    Cargar contratos
                                </button>
                            </div>

                        </div>

                        <!-- TABLA PRINCIPAL -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="tbl-lecturas-lote">
                                <thead>
                                    <tr>
                                        <th>Contrato</th>
                                        <th>Cliente</th>
                                        <th>Instalador</th>
                                        <th>Lectura</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success" id="btn-guardar-lecturas">Guardar todo</button>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-reporte-lecturas-direccion" tabindex="-1" aria-labelledby="modal-reporte-lecturas-direccion-label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-reporte-lecturas-direccion-label">Filtro de dirección para toma de lecturas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            Si no seleccionas ningún filtro, el PDF incluirá todos los contratos pendientes de lectura del período actual.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filtro-ruta-reporte-lectura">Rutas</label>
                                <select id="filtro-ruta-reporte-lectura" class="form-control">
                                    <option value="-1">Todas</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filtro-departamento-reporte-lectura">Departamento</label>
                                <select id="filtro-departamento-reporte-lectura" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-municipio-reporte-lectura">Municipio</label>
                                <select id="filtro-municipio-reporte-lectura" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-distrito-reporte-lectura">Distrito</label>
                                <select id="filtro-distrito-reporte-lectura" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-colonia-reporte-lectura">Colonia</label>
                                <select id="filtro-colonia-reporte-lectura" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-danger" id="btn-generar-pdf-lecturas-direccion">Generar PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-filtro-contratos-direccion" tabindex="-1" aria-labelledby="modal-filtro-contratos-direccion-label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-filtro-contratos-direccion-label">Filtro para cargar contratos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            Si no seleccionas dirección, se cargarán todos los contratos pendientes del período activo.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filtro-ruta-cargar-contratos">Ruta</label>
                                <select id="filtro-ruta-cargar-contratos" class="form-control">
                                    <option value="-1">Todas</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filtro-departamento-cargar-contratos">Departamento</label>
                                <select id="filtro-departamento-cargar-contratos" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-municipio-cargar-contratos">Municipio</label>
                                <select id="filtro-municipio-cargar-contratos" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-distrito-cargar-contratos">Distrito</label>
                                <select id="filtro-distrito-cargar-contratos" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="filtro-colonia-cargar-contratos">Colonia</label>
                                <select id="filtro-colonia-cargar-contratos" class="form-control">
                                    <option value="-1">Todos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary" id="btn-confirmar-cargar-contratos">Aceptar y cargar</button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/lecturas/lecturas.js') ?>"></script>
<?= $this->endSection() ?>