<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Rango de Facturas <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Rango de Facturas</h1>
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
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Agregar Rango</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchRangoFacturas" placeholder="Buscar estado" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnRangoFacturas" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnRangoFacturas" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-rango-de-facturas">
                        <thead>
                            <tr>
                                <th>Numero Inicio</th>
                                <th>Numero Final</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Numero Actual</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-rango-de-facturas" tabindex="-1" aria-labelledby="modal-rango-de-facturas-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-rango-de-facturas-label">Opciones del usuario</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero-de-inicio" class="form-label">Numero de inicio</label>
                                    <input type="number" class="form-control" id="numero-de-inicio" name="numero-de-inicio">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="numero-final" class="form-label">Numero final</label>
                                <input type="number" class="form-control" id="numero-final" name="numero-final">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="id-periodo">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary modal-guardar" id="guardar-registro">Guardar registro</button>
                        <!-- <button type="button" class="btn btn-warning modal-editar" id="actualizar-registro">Guardar registro</button> -->
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
<script type="module" src="<?= base_url('dist/js/rango_de_facturas/rango_de_facturas.js') ?>"></script>


<?= $this->endSection() ?>