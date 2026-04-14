<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Lecturas <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    /* label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    } */
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
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Agregar Nueva Lectura</button>
            </div>
            <div class="card-header py-3">
                <button type="button" id="btn-agregar-lote" class="btn bg-gradient-primary btn-flat">Agregar Carga Masiva</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-8">
                        <input type="text" id="customSearchLecturas" placeholder="Buscar por contrato, instalador y numero de serie" class="form-control">
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
                                        <select id="periodo" class="form-control">
                                            <option value="">Seleccione...</option>
                                        </select>
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
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Instalador</label>
                                <select id="instalador-lote" class="form-control">
                                    <option value="">Seleccione...</option>
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
                                        <th>Solicitud</th>
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

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/lecturas/lecturas.js') ?>"></script>
<?= $this->endSection() ?>