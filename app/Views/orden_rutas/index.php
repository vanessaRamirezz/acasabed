<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Orden de contratos por ruta <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #tbl-orden-rutas {
        min-width: 760px;
    }

    .orden-ruta-input {
        max-width: 110px;
        margin: 0 auto;
        text-align: center;
        font-weight: 600;
    }

    .orden-ruta-resumen {
        font-size: 0.9rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1 class="m-0 text-dark texto">Orden de contratos por ruta</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label for="select-ruta-orden">Ruta</label>
                        <select id="select-ruta-orden" class="form-control">
                            <option value="-1">Seleccione una ruta</option>
                        </select>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <button type="button" id="btn-cargar-contratos-ruta" class="btn bg-gradient-primary btn-flat w-100">
                            Cargar contratos
                        </button>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0 text-md-right">
                        <span class="text-muted orden-ruta-resumen" id="resumen-orden-ruta">Seleccione una ruta para comenzar.</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-orden-rutas">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Orden</th>
                                <th style="width: 25%;">Contrato</th>
                                <th>Cliente</th>
                                <th style="width: 18%;">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr>
                                <td colspan="4">Seleccione una ruta y cargue los contratos.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" id="btn-guardar-orden-ruta" class="btn btn-success" disabled>
                        Guardar orden
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/orden_rutas/orden_rutas.js') ?>"></script>
<?= $this->endSection() ?>
