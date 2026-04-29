<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Reporte de Clientes <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .report-card {
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Reporte de clientes</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card report-card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Departamento</label>
                        <select id="departamento" class="form-control">
                            <option value="-1">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Municipio</label>
                        <select id="municipio" class="form-control">
                            <option value="-1">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Distrito</label>
                        <select id="distrito" class="form-control">
                            <option value="-1">Seleccione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Colonia</label>
                        <select id="colonia" class="form-control">
                            <option value="-1">Seleccione...</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo de cliente</label>
                        <select id="tipo-cliente" class="form-control">
                            <option value="-1">Todos</option>
                            <?php foreach (($tiposCliente ?? []) as $tipo): ?>
                                <option value="<?= esc($tipo['id_tipo_cliente']) ?>"><?= esc($tipo['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 ml-auto">
                        <button id="btnGenerarReporteClientes" class="btn btn-primary btn-block">
                            Generar reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <iframe id="visorPDFClientes" style="width:100%; height:650px; border:none;"></iframe>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/reporte_clientes/rp_clientes.js') ?>"></script>
<?= $this->endSection() ?>
