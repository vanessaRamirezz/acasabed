<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Instaladores <?= $this->endSection() ?>
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
                <h1 class="m-0 text-dark texto">Registro de Instaladores</h1>
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
                    <div class="input-group col-md-5">
                        <input type="text" id="customSearchInstaladores" placeholder="Buscar por nombre o DUI" class="form-control">
                        <button class="btn btn-outline-secondary" id="searchBtnInstaladores" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary" id="clearSearchBtnInstaladores" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-instaladores">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>DUI</th>
                                <th>Dirección</th>
                                <th>Correo</th>
                                <th>Activo</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-instaladores" tabindex="-1" aria-labelledby="modal-instaladores-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-instaladores-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">
                        <div class="user">
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="estado">Estado Activo</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-info-circle"></i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="estado" id="estado" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="nombre" id="nombre">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="telefono">Télefono</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="telefono" id="telefono">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="dui">DUI</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="dui" id="dui">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="direccion" id="direccion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="email" class="form-control" name="correo" id="correo">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" value="id-instalador" id="id-instalador">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-danger modal-editar" id="actualizar-estado">Desactivar Instalador</button>
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
<script type="module" src="<?= base_url('dist/js/instaladores/instaladores.js') ?>"></script>
<?= $this->endSection() ?>