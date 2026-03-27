<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Usuarios <?= $this->endSection() ?>
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
                <h1 class="m-0 text-dark texto">Usuarios del sistema</h1>
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
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="usuarios">
                        <thead>
                            <tr>
                                <th>DUI</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Correo</th>
                                <th>Telefono</th>
                                <th>Perfil</th>
                                <th>Activo</th>
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
        <div class="modal fade" id="mantenimiento-usuarios" tabindex="-1" aria-labelledby="mantenimiento-usuarios-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mantenimiento-usuarios-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">
                        <div class="user">
                            <div class="row">
                                <div class="col-sm-5">
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
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="nombres">Nombres</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="email" class="form-control" name="nombres" id="nombres">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="apellidos">Apellidos</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="apellidos" id="apellidos">
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
                                            <input type="text" class="form-control" name="correo" id="correo">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="perfil">Perfil</label>
                                        <select class="form-control select2" style="width: 100%;" id="perfil">
                                            <option value="">Seleccione un perfil</option>
                                            <?php
                                            foreach ($perfiles as $perfil) {
                                                echo '<option value="' . $perfil['id_perfil'] . '">' . $perfil['nombre'] . '</option>';
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="telefono">Telefono</label>
                                        <p>Puede agregar varios numeros separados por (,)</p>
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
                                <div class="col-sm-8">
                                    <div class="form-group generador-pwd">
                                        <label for="clave">Contraseña:</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control form-control-user" id="clave">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                                    👁️
                                                </button>
                                                <button class="btn btn-outline-primary" type="button" id="generate-password">
                                                    Generar
                                                </button>
                                            </div>
                                            <input type="hidden" value="id-usuario" id="id-usuario">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-danger modal-editar" id="actualizar-estado">Desactivar usuario</button>
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
<script type="module" src="<?= base_url('dist/js/usuarios/usuarios.js') ?>"></script>
<?= $this->endSection() ?>