<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Dirección <?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Direcciones</h1>
                <p>Podra Visualizar, Agregar Colonias, Barrios, Cantones y Caserios asociadas a cada distrito</p>
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
                <h6 class="m-0 font-weight-bold text-primary">Direcciones Agregadas</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm">
                        <div class="form-row">
                            <div class="form-group col-sm">
                                <label for="departamentos">Departamentos</label>
                                <select id="departamentos" class="form-control">
                                    <option selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="form-group col-sm">
                                <label for="municipios">Municipios</label>
                                <select id="municipios" class="form-control">
                                    <option value="-1" selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="form-group col-sm">
                                <label for="distritos">Distritos</label>
                                <select id="distritos" class="form-control" data-distrito-seleccionado="-1">
                                    <option value="-1" selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="form-group col-sm">
                                <label for="direccion">Zonas</label>
                                <select id="direccion" class="form-control">
                                    <option value="-1" selected>Seleccione...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row" id="opcion-nueva-direccion" style="display: none;">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="nueva-direccion">Agregar</label>
                                    <input type="text" class="form-control" id="nueva-direccion">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-primary" id="agregar-direccion">Guardar</button>
                            </div>
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
<script type="module" src="<?= base_url('dist/js/direcciones/direcciones.js') ?>"></script>
<?= $this->endSection() ?>