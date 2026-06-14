<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Resumen de Facturación <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .report-card {
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .report-note {
        background: #f8fbff;
        border: 1px solid #d9e8f5;
        border-radius: 12px;
        padding: 14px 16px;
        color: #334155;
        font-size: 0.95rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Resumen contable de facturas</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card report-card">
            <div class="card-body">
                <div class="report-note mb-3">
                    Este reporte resume la facturación por período, estado y servicio para facilitar la lectura contable del movimiento del mes.
                </div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label>Período</label>
                        <select
                            id="periodoResumenFacturas"
                            class="form-control"
                            data-active-id="<?= esc($periodoActivo['id_periodo'] ?? '') ?>"
                            data-active-text="<?= esc($periodoActivo['nombre'] ?? '') ?>"></select>
                    </div>
                    <div class="col-md-3">
                        <label>Tipo</label>
                        <select id="tipoResumenFacturas" class="form-control">
                            <option value="Todos">Todos</option>
                            <option value="Consumo">Consumo</option>
                            <option value="Instalacion">Instalación</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button id="btnGenerarResumenFacturas" class="btn btn-primary btn-block">
                            Generar reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div id="pdfBoxResumenFacturas" style="position:relative; width:100%; height:700px; border:1px solid #ddd; background:#f8f9fa;">

                    <div id="loadingResumenFacturas" style="
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

                    <div id="pdfMessageResumenFacturas" style="
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
                        <h4 style="margin:0;">Resumen de facturación</h4>
                        <p style="margin-top:8px;">Selecciona un período y presiona <b>Generar reporte</b>.</p>
                    </div>

                    <iframe id="visorPDFResumenFacturas" style="width:100%; height:100%; border:none; display:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/reporte_resumen_facturas/rp_resumen_facturas.js') ?>"></script>
<?= $this->endSection() ?>