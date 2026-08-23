<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Administradores <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Administradores y Firmantes</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Agregar Administrador</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-5">
                        <input type="text" id="customSearchAdministradores" placeholder="Buscar por nombre, rol o DUI" class="form-control">
                        <button class="btn btn-outline-secondary" id="searchBtnAdministradores" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary" id="clearSearchBtnAdministradores" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-administradores">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Ocupación</th>
                                <th>Dirección</th>
                                <th>Edad</th>
                                <th>DUI</th>
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

        <div class="modal fade" id="modal-administradores" tabindex="-1" aria-labelledby="modal-administradores-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-administradores-label">Administrador</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="estado">Estado Activo</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="estado" id="estado" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
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
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="rol">Rol</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="rol" id="rol">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="ocupacion">Ocupación</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="ocupacion" id="ocupacion">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="edad">Edad</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i></i></span>
                                        </div>
                                        <input type="number" class="form-control" name="edad" id="edad" min="0">
                                    </div>
                                </div>
                            </div>
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
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
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
                            <input type="hidden" id="id-administrador">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-danger modal-editar-admin" id="actualizar-estado">Desactivar Administrador</button>
                        <button type="button" class="btn btn-primary modal-guardar-admin" id="guardar-registro">Guardar registro</button>
                        <button type="button" class="btn btn-warning modal-editar-admin" id="actualizar-registro">Guardar registro</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <button type="button" id="btn-agregar-firmante" class="btn bg-gradient-primary btn-flat">Agregar Firmante</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-5">
                        <input type="text" id="customSearchFirmantes" placeholder="Buscar por nombre o rol" class="form-control">
                        <button class="btn btn-outline-secondary" id="searchBtnFirmantes" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary" id="clearSearchBtnFirmantes" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-firmantes">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-firmantes" tabindex="-1" aria-labelledby="modal-firmantes-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-firmantes-label">Firmante</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="nombre-firmante-crud">Nombre</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="nombre-firmante-crud" id="nombre-firmante-crud">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="rol-firmante-crud">Rol</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="rol-firmante-crud" id="rol-firmante-crud">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="id-firmante-crud">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="guardar-firmante">Guardar registro</button>
                        <button type="button" class="btn btn-warning" id="actualizar-firmante">Guardar registro</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/administradores/administradores.js') ?>"></script>
<?= $this->endSection() ?>
