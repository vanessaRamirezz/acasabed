<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Periodos <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">PERIODOS</h1>
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
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Agregar Nuevo</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchPeriodos" placeholder="Buscar nombre, desde, hasta ó estado" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnPeriodos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnPeriodos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-periodos">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Estado</th>
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
        <div class="modal fade" id="modal-periodos" tabindex="-1" aria-labelledby="modal-periodos-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-periodos-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="periodo" class="form-label">Nombre de periodo</label>
                                    <input type="text" class="form-control" id="periodo">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="fecha-desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha-desde">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha-hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha-hasta">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Estado</label>

                                <div class="form-check form-check-inline">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="estado"
                                        id="estado-activo"
                                        value="1"
                                        checked>
                                    <label class="form-check-label" for="estado-activo">
                                        Activo
                                    </label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="estado"
                                        id="estado-inactivo"
                                        value="0">
                                    <label class="form-check-label" for="estado-inactivo">
                                        Cerrado
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="id-periodo">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary modal-guardar" id="guardar-registro">Guardar registro</button>
                        <button type="button" class="btn btn-warning modal-editar" id="actualizar-registro">Guardar registro</button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/periodos/periodos.js') ?>"></script>
<?= $this->endSection() ?>