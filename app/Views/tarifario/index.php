<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Tarifario <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    /* label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    } */
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Lista de Tarifarios</h1>
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
                        <input type="text" id="customSearchTarifas" placeholder="Buscar por código, desde ó hasta" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnTarifas" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnTarifas" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-tarifas">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Tipo Cliente</th>
                                <th>Valor por m³</th>
                                <th>Desde (m³)</th>
                                <th>Hasta (m³)</th>
                                <th>Pago mínimo ($)</th>
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
        <div class="modal fade" id="model-tarifario" tabindex="-1" aria-labelledby="model-tarifario-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="model-tarifario-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body">

                        <!-- DATOS GENERALES -->
                        <h6 class="text-primary mb-3">Datos Generales</h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo">
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo-cliente">Tipo de Cliente</label>
                                    <select class="form-control select2" style="width: 100%;" id="tipo-cliente">
                                        <option value="">Seleccione</option>
                                        <?php
                                        foreach ($tipoClientes as $tipoCliente) {
                                            echo '<option value="' . $tipoCliente['id_tipo_cliente'] . '">' . $tipoCliente['nombre'] . '</option>';
                                        }

                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="valor-metro-cubico" class="form-label">Valor por m³ ($)</label>
                                <input type="number" step="0.0001" class="form-control" id="valor-metro-cubico">
                            </div>
                            <div class="col-md-4">
                                <label for="pago-minimo" class="form-label">Pago mínimo ($)</label>
                                <input type="number" step="0.01" class="form-control" id="pago-minimo">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- TARIFA -->
                        <h6 class="text-primary mb-3">Rango de Consumo (m³)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="desde-n-metros" class="form-label">Desde (m³)</label>
                                <input type="number" step="0.01" class="form-control" id="desde-n-metros">
                            </div>

                            <div class="col-md-4">
                                <label for="hasta-n-metros" class="form-label">Hasta (m³)</label>
                                <input type="number" step="0.01" class="form-control" id="hasta-n-metros">
                            </div>
                        </div>

                        <input type="hidden" id="id-tarifa">

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
<script type="module" src="<?= base_url('dist/js/tarifario/tarifario.js') ?>"></script>
<?= $this->endSection() ?>