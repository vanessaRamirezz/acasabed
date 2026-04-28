<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> INICIO <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .dashboard-shell {
        background: #f4f7fb;
        border-radius: 22px;
        padding: 26px;
        border: 1px solid #e2e8f0;
    }

    .dashboard-hero {
        background: #343A40;
        border-radius: 22px;
        padding: 28px;
        color: #fff;
        min-height: 210px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .dashboard-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        width: fit-content;
    }

    .dashboard-hero h1 {
        font-size: 2.25rem;
        font-weight: 800;
        margin: 18px 0 12px;
    }

    .dashboard-hero p {
        max-width: 680px;
        color: rgba(255, 255, 255, 0.82);
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .dashboard-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 8px;
    }

    .dashboard-hero__pill {
        background: rgba(255, 255, 255, 0.10);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 14px;
        padding: 12px 14px;
        min-width: 180px;
    }

    .dashboard-hero__pill span {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 4px;
    }

    .dashboard-hero__pill strong {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
    }

    .dashboard-section-title {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6c757d;
        margin: 24px 0 14px;
        font-weight: 700;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
    }

    .stat-card {
        border-radius: 20px;
        border: 1px solid #dbe4ec;
        padding: 20px;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 12px 24px rgba(20, 33, 61, 0.08);
        background: #fff;
    }

    .stat-card--clients {
        background: #fff;
    }

    .stat-card--routes {
        background: #fff;
    }

    .stat-card--contracts {
        background: #fff;
    }

    .stat-card--requests {
        background: #fff;
    }

    .stat-card__icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 18px;
        color: #fff;
    }

    .stat-card--clients .stat-card__icon {
        background: #1f7a8c;
    }

    .stat-card--routes .stat-card__icon {
        background: #2d6a4f;
    }

    .stat-card--contracts .stat-card__icon {
        background: #3d5a80;
    }

    .stat-card--requests .stat-card__icon {
        background: #6d597a;
    }

    .stat-card__label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .stat-card__value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 800;
        color: #1f2d3d;
        margin-bottom: 10px;
    }

    .stat-card__text {
        margin: 0;
        color: #495057;
        font-size: 0.92rem;
        max-width: 100%;
    }


    @media (max-width: 767.98px) {
        .dashboard-shell {
            padding: 18px;
            border-radius: 18px;
        }

        .dashboard-hero {
            padding: 22px 18px;
            min-height: auto;
        }

        .dashboard-hero h1 {
            font-size: 1.6rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <!-- <h1 class="m-0 text-dark texto">Dashboard prueba</h1> -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="dashboard-shell">
            <div class="dashboard-hero">
                <div class="dashboard-hero__eyebrow">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Periodo Activo</span>
                </div>

                <?php if (!empty($periodoActivo)): ?>
                    <h1 class="m-0"><?= esc($periodoActivo['nombre']) ?></h1>
                    <p>
                        Este es el período actualmente habilitado en el sistema para lectura,
                        facturación y control operativo.
                    </p>
                    <div class="dashboard-hero__meta">
                        <div class="dashboard-hero__pill">
                            <span>Fecha de inicio</span>
                            <strong><?= esc($periodoActivo['fecha_desde']) ?></strong>
                        </div>
                        <div class="dashboard-hero__pill">
                            <span>Fecha de cierre</span>
                            <strong><?= esc($periodoActivo['fecha_hasta']) ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <h1 class="m-0">Sin período activo</h1>
                    <p>
                        Aún no hay un período marcado como activo. Cuando se habilite uno,
                        se mostrará aquí como bloque principal del dashboard.
                    </p>
                <?php endif; ?>
            </div>

            <div class="dashboard-section-title">Resumen del sistema</div>

            <div class="stats-grid">
                <div class="stat-card stat-card--clients">
                    <div>
                        <div class="stat-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="stat-card__label">Clientes</span>
                        <div class="stat-card__value"><?= number_format($totalClientes ?? 0) ?></div>
                    </div>
                    <p class="stat-card__text">Total de clientes registrados actualmente en el sistema.</p>
                </div>

                <div class="stat-card stat-card--routes">
                    <div>
                        <div class="stat-card__icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <span class="stat-card__label">Rutas</span>
                        <div class="stat-card__value"><?= number_format($totalRutas ?? 0) ?></div>
                    </div>
                    <p class="stat-card__text">Cantidad total de rutas disponibles.</p>
                </div>

                <div class="stat-card stat-card--contracts">
                    <div>
                        <div class="stat-card__icon">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <span class="stat-card__label">Contratos activos</span>
                        <div class="stat-card__value"><?= number_format($totalContratosActivos ?? 0) ?></div>
                    </div>
                    <p class="stat-card__text">Total de contratos activos dentro del sistema.</p>
                </div>

                <div class="stat-card stat-card--requests">
                    <div>
                        <div class="stat-card__icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <span class="stat-card__label">Solicitudes activas</span>
                        <div class="stat-card__value"><?= number_format($totalSolicitudesActivas ?? 0) ?></div>
                    </div>
                    <p class="stat-card__text">Solicitudes activas dentro del sistema.</p>
                </div>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<!-- <script>
    console.log("Dashboard principal cargado");
</script> -->

<?= $this->endSection() ?>
