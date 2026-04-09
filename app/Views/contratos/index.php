<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Contratos <?= $this->endSection() ?>
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
                <h1 class="m-0 text-dark texto">Contratos</h1>
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
                <a href="<?php echo base_url('nueva_solicitud'); ?>" class="btn btn-primary" id="btn-nueva-solicitud">Crear Contrato</a>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchContratos" placeholder="Buscar numero contrato o nombre de cliente" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnContratos" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnContratos" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-contratos">
                        <thead>
                            <tr>
                                <th>Código Contrato</th>
                                <th>Código Solicitud</th>
                                <th>Nombre / Razón social</th>
                                <th>Estado</th>
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
<script type="module" src="<?= base_url('dist/js/contratos/contratos.js') ?>"></script>
<?= $this->endSection() ?>