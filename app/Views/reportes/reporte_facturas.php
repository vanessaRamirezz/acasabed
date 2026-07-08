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
                        <label>Estado</label>
                        <select id="tipo" class="form-control">
                            <option value="Todos">Todos</option>
                            <option value="PAGADA">PAGADAS</option>
                            <option value="NO PAGADA">NO PAGADAS</option>
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

        <div class="row">
            <div class="col-12">

                <div id="pdfBox" style="position:relative; width:100%; height:600px; border:1px solid #ddd; background:#f8f9fa;">

                    <div id="loadingFacturas" style="
                        position:absolute;
                        inset:0;
                        display:none;
                        align-items:center;
                        justify-content:center;
                        flex-direction:column;
                        background:rgba(255,255,255,0.85);
                        z-index:1000;
                    ">
                        <div class="spinner-border text-primary" role="status"></div>
                        <h5 class="mt-3">Generando reporte...</h5>
                    </div>

                    <!-- 👇 Mensaje inicial -->
                    <div id="pdfMessage" style="
                        position:absolute;
                        inset:0;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        flex-direction:column;
                        font-family:Arial;
                        color:#666;
                        text-align:center;
                    ">
                        <h4 style="margin:0;">Facturas</h4>
                        <p style="margin-top:8px;">Selecciona un estado y presiona <b>Generar Reporte</b></p>
                    </div>

                    <!-- 👇 iframe -->
                    <iframe id="visorPDF"
                        style="width:100%; height:100%; border:none; display:none;">
                    </iframe>

                </div>

            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/reporte_facturas/rp_facturas.js') ?>"></script>
<?= $this->endSection() ?>