<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Solicitudes <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    /* label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    } */
    .dropdown-item-custom {
        font-size: 13px;
        /* ajusta a tu gusto */
    }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Solicitudes</h1>
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
                <a href="<?php echo base_url('nueva_solicitud'); ?>" class="btn btn-primary" id="btn-nueva-solicitud">Crear Nueva Solicitud</a>
            </div>
            <div class="card-body">
                <h5 class="m-0">Solicitudes en estado creado</h5>

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchSolicitudes" placeholder="Buscar por numero solicitud ó nombre de cliente" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnSolicitudes" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnSolicitudes" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-solicitudes">
                        <thead>
                            <tr>
                                <th>Numero Solicitud</th>
                                <th>Nombre / Razón social</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="m-0">Solicitudes aceptadas</h5>

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchSolicitudesAceptadas" placeholder="Buscar por numero solicitud ó nombre de cliente" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnSolicitudesAceptadas" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnSolicitudesAceptadas" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-solicitudes-aceptadas">
                        <thead>
                            <tr>
                                <th>Numero Solicitud</th>
                                <th>Nombre / Razón social</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/solicitudes/solicitudes.js') ?>"></script>
<?= $this->endSection() ?>