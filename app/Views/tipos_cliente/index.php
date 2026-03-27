<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Tipos de Cliente <?= $this->endSection() ?>
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
                <h1 class="m-0 text-dark texto">Tipos de cliente</h1>
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
                    <table class="table table-bordered table-sm" id="tbl-tipos-cliente">
                        <thead>
                            <tr>
                                <th>Tipo</th>
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
        <div class="modal fade" id="modal-tipo-cliente" tabindex="-1" aria-labelledby="mantenimiento-tipo-cliente-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mantenimiento-tipo-cliente-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">
                        <div class="user">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <label for="tipo-cliente">Tipo de Cliente</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="tipo-cliente" id="tipo-cliente">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="id-tipo-cliente" id="id-tipo-cliente">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary modal-guardar" id="guardar-registro">Guardar registro</button>
                            <button type="button" class="btn btn-warning modal-editar" id="actualizar-registro">Guardar registro</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/tipos_cliente/tipos_cliente.js') ?>"></script>
<?= $this->endSection() ?>