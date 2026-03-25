<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Dashboard <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .texto {
        background-color: yellow;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark texto">Dashboard prueba</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        


    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    console.log("JS solo del dashboard");
</script>

<?= $this->endSection() ?>