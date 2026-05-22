<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Servicios <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Servicios</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <button type="button" id="btn-agregar-servicio" class="btn bg-gradient-primary btn-flat">
                    Agregar nuevo
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-servicios">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Operación</th>
                                <th>Tipo</th>
                                <th>Opcion</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-servicio" tabindex="-1" aria-labelledby="modal-servicio-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-servicio-label">Servicio</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="codigo-servicio">Código</label>
                                    <input type="text" class="form-control" id="codigo-servicio" maxlength="45">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="valor-servicio">Valor</label>
                                    <input type="number" class="form-control" id="valor-servicio" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="nombre-servicio">Nombre</label>
                                    <input type="text" class="form-control" id="nombre-servicio" maxlength="100">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="operacion-servicio">Operación</label>
                                    <select class="form-control" id="operacion-servicio">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="tipo-servicio">Tipo</label>
                                    <select class="form-control" id="tipo-servicio">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 contenedor-estado-servicio d-none">
                                <div class="form-group">
                                    <label for="estado-servicio">Estado</label>
                                    <select class="form-control" id="estado-servicio">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="id-servicio">

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary modal-guardar" id="guardar-servicio">Guardar registro</button>
                            <button type="button" class="btn btn-warning modal-editar" id="actualizar-servicio">Guardar cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/servicios/servicios.js') ?>"></script>
<?= $this->endSection() ?>
