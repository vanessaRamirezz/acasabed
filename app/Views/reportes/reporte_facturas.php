<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Reporte de Facturas <?= $this->endSection() ?>

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
                <h1 class="m-0 text-dark">Reporte de facturas generadas</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card report-card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Período</label>
                        <select id="periodo" class="form-control"></select>
                    </div>
                    <div class="col-md-3">
                        <label>Tipo</label>
                        <select id="tipo" class="form-control">
                            <option value="Todos">Todos</option>
                            <option value="Consumo">Consumo</option>
                            <option value="Instalacion">Instalación</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Búsqueda opcional</label>
                        <input type="text" id="searchReporteFactura" class="form-control" placeholder="Cliente, contrato o correlativo">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button id="btnGenerarReporteFacturas" class="btn btn-primary btn-block">
                            Generar reporte
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <iframe id="visorPDFFacturas" style="width:100%; height:650px; border:none;"></iframe>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/reporte_facturas/rp_facturas.js') ?>"></script>
<?= $this->endSection() ?>