<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Reporte de contratos <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Reporte de contratos</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Estado</label>
                <select id="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>

            <div class="col-md-3 align-self-end">
                <button id="btnGenerar" class="btn btn-primary">
                    Generar Reporte
                </button>
            </div>
        </div>

        <!-- 👇 Vista previa -->
        <div class="row">
            <div class="col-12">

                <div id="pdfBox" style="position:relative; width:100%; height:600px; border:1px solid #ddd; background:#f8f9fa;">

                    <div id="loadingContratos" style="
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
                        <h4 style="margin:0;">Contratos</h4>
                        <p style="margin-top:8px;">Selecciona un estado y presiona <b>Generar Reporte</b></p>
                    </div>

                    <!-- 👇 iframe -->
                    <iframe id="visorPDF"
                        style="width:100%; height:100%; border:none; display:none;">
                    </iframe>

                </div>

            </div>
        </div>


    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script type="module" src="<?= base_url('dist/js/reporte_contratos/rp_contratos.js') ?>"></script>

<?= $this->endSection() ?>