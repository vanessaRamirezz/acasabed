<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Permisos por perfil <?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Permisos por perfiles</h1>
                <p class="mb-4">Se visualizaran todos los perfiles con los permisos asignados.</p>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="permisos">
                        <thead class="text-center">
                            <tr>
                                <th style="width: 10px">Perfil</th>
                                <th>Permisos</th>
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
        <div class="modal fade" id="editarAccesosPerfil" tabindex="-1" aria-labelledby="editarAccesosPerfil" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPerfilNombre">Perfil seleccionado: </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="text" hidden id="id_perfil">
                        <div id="permisosContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="editar-acceso">Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->


<?= $this->endSection() ?>
<?= $this->section('scripts') ?>

<script type="module" src="<?= base_url('dist/js/permisos/permisos.js') ?>"></script>

<?= $this->endSection() ?>