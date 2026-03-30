<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Colonias <?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Colonias</h1>
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
                <h6 class="m-0 font-weight-bold text-primary">Agregadas</h6>
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
                                <label for="colonia">Colonias</label>
                                <select id="colonia" class="form-control">
                                    <option value="-1" selected>Seleccione...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row" id="opcion-nueva-colonia" style="display: none;">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="nueva-colonia">Agregar</label>
                                    <input type="text" class="form-control" id="nueva-colonia">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-primary" id="agregar-colonia">Guardar</button>
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
<script type="module" src="<?= base_url('dist/js/colonias/colonias.js') ?>"></script>
<?= $this->endSection() ?>