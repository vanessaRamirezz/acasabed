<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Solicitud <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    /* label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    } */

    /* Quitar separación entre secciones */
    #accordion .card {
        margin-bottom: 0;
        border-radius: 0;
        border: 1px solid #dee2e6;
        border-bottom: none;
    }

    /* Última sección con borde inferior */
    #accordion .card:last-child {
        border-bottom: 1px solid #dee2e6;
    }

    /* Header más limpio */
    #accordion .card-header {
        background: #f8f9fa;
        padding: 8px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    /* Botón ocupa todo el ancho */
    #accordion .card-header .btn {
        width: 100%;
        text-align: left;
        padding: 5px 0;
        font-weight: 600;
        color: #343a40;
    }

    /* Quitar estilo link feo */
    #accordion .btn-link {
        text-decoration: none;
    }

    #accordion .btn-link:hover {
        text-decoration: none;
        color: #007bff;
    }

    /* Cuerpo más uniforme */
    #accordion .card-body {
        padding: 20px;
        background: #fff;
    }

    /* Contenedor de fecha */
    .fecha-container {
        display: flex;
        justify-content: flex-end;
        /* lo manda a la derecha */
        align-items: center;
        margin-bottom: 15px;
    }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <!-- <h1 class="m-0 text-dark texto">Solicitud</h1> -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <h5 class="text-center mb-3">
            <b>SOLICITUD DE CONEXIÓN DOMICILIAR</b>
        </h5>

        <div class="row justify-content-end mb-3">
            <div class="col-auto d-flex align-items-center">
                <label class="mr-2 mb-0">Fecha</label>
                <input type="date" id="fecha-creacion" class="form-control" style="width: 180px;">
            </div>
        </div>

        <input type="hidden" id="id-solicitud">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group numero-de-solicitud">
                    <label for="numero-de-solicitud" class="mb-1">Numero Solicitud</label>
                    <input type="text" class="form-control" name="numero-de-solicitud" id="numero-de-solicitud">
                </div>
            </div>
            <div class="col-md-2 numero-de-contrato">
                <div class="form-group">
                    <label for="numero-de-contrato" class="mb-1">Numero Contrato</label>
                    <input type="text" class="form-control" name="numero-de-contrato" id="numero-de-contrato">
                </div>
            </div>
        </div>


        <div id="accordion">

            <!-- SECCIÓN 1  DATOS PERSONALES DEL CLIENTE-->
            <div class="card">
                <div class="card-header" id="headingOne">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#seccion1">
                        Datos personales del cliente
                    </button>
                </div>

                <div id="seccion1" class="collapse show" data-parent="#accordion">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-1">Buscar cliente</label>
                                    <select name="buscar-cliente" id="buscar-cliente" class="form-control">
                                        <option value="">...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="id-cliente">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edad">Edad</label>
                                    <input type="number" class="form-control" id="edad" name="edad">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dui">DUI</label>
                                    <input type="text" class="form-control" id="dui" name="dui">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="extendido">Extendido en</label>
                                    <input type="text" class="form-control" id="extendido" name="extendido">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nit">NIT</label>
                                    <input type="text" class="form-control" id="nit" name="nit">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="lugar-nacimiento">Lugar de Nacimiento</label>
                                    <input type="text" class="form-control" id="lugar-nacimiento" name="lugar-nacimiento">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha-nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha-nacimiento" name="lugar-nacimiento">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado-familiar">Estado Familiar</label>
                                    <input type="text" class="form-control" id="estado-familiar" name="estado-familiar">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero-grupo-familiar">No. Grupo Familiar</label>
                                    <input type="number" class="form-control" id="numero-grupo-familiar" name="numero-grupo-familiar">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lugar-de-trabajo">Lugar de Trabajo</label>
                                    <input type="text" class="form-control" id="lugar-de-trabajo" name="lugar-de-trabajo">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="ocupacion">Profesión u Oficio</label>
                                    <input type="text" class="form-control" id="ocupacion" name="ocupacion">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="telefonos">Teléfonos</label>
                                    <input type="text" class="form-control" id="telefonos" name="telefonos">
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary btn-siguiente" data-target="#seccion2">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2 BENEFICIARIO/A-->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion2">
                            Beneficiarios /a
                        </button>
                    </h5>
                </div>

                <div id="seccion2" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <input type="hidden" id="id-beneficiario">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="beneficiarios-registratos">Beneficiarios en la base</label>
                                    <select id="beneficiarios-registratos" class="form-control" name="beneficiarios-registratos">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre-beneficiario">Nombre</label>
                                    <input type="text" class="form-control" id="nombre-beneficiario" name="nombre-beneficiario">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edad-beneficiario">Edad</label>
                                    <input type="number" class="form-control" id="edad-beneficiario" name="edad-beneficiario">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="parentesco-beneficiario">Parentesco</label>
                                    <input type="text" class="form-control" id="parentesco-beneficiario" name="parentesco-beneficiario">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direccion-beneficiario">Dirección</label>
                                    <input type="text" class="form-control" id="direccion-beneficiario" name="direccion-beneficiario">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary btn-anterior" data-target="#seccion1">
                                Anterior →
                            </button>

                            <button id="btn-editar" class="btn btn-info" style="display:none;">
                                Editar
                            </button>

                            <button id="btn-limpiar" class="btn btn-warning" style="display:none;">
                                Limpiar
                            </button>

                            <button class="btn btn-primary btn-siguiente" data-target="#seccion3">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3 DATOS DEL INMUEBLE-->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion3">
                            Datos del inmueble donde se instalara la acometida
                        </button>
                    </h5>
                </div>

                <div id="seccion3" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <div class="row">
                            <!-- Dirección -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direccion-inmueble">Dirección del Inmueble</label>
                                    <input type="text" class="form-control" id="direccion-inmueble" name="direccion-inmueble">
                                </div>
                            </div>
                        </div>
                        <h5 class="mt-3">Calidad del inmueble</h5>
                        <div class="row mt-2">
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="propietario" name="propietario">
                                    <label class="form-check-label" for="propietario">
                                        Propietario
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="inquilino" name="inquilino">
                                    <label class="form-check-label" for="inquilino">
                                        Inquilino
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="representante" name="representante">
                                    <label class="form-check-label" for="representante">
                                        Representante
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <label class="form-check-label d-flex align-items-center" for="otro-check">
                                        <span class="mr-2">Otro</span>
                                        <input type="text" class="form-control form-control-sm" style="width: 150px;" id="otro-check" name="otro-check">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <br>
                        <hr>

                        <h6 class="mt-3">Tiene letrina</h6>
                        <div class="row mt-2">
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="abonera" name="abonera">
                                    <label class="form-check-label" for="abonera">
                                        Abonera
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="hoyo-seco" name="hoyo-seco">
                                    <label class="form-check-label" for="hoyo-seco">
                                        Hoyo Seco
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="lavable" name="lavable">
                                    <label class="form-check-label" for="lavable">
                                        Lavable
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <label class="form-check-label d-flex align-items-center" for="otro-baño">
                                        <span class="mr-2">Otro</span>
                                        <input type="text" class="form-control form-control-sm" style="width: 150px;" id="otro-baño" name="otro-baño">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary  btn-anterior" data-target="#seccion2">
                                Anterior →
                            </button>
                            <button class="btn btn-primary btn-siguiente" data-target="#seccion4">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4 DATOS DE ENTREVISTA DIRIGIDA-->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion4">
                            Datos de Entrevista Dirigida
                        </button>
                    </h5>
                </div>

                <div id="seccion4" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <input type="hidden" id="id-plan-pago">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label><strong>Si no tiene letrina, ¿acepta usted el compromiso de construir la que la Junta Directiva le recomiende?</strong></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="si" name="si">
                                        <label class="form-check-label" for="si">Sí</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="no" name="no">
                                        <label class="form-check-label" for="no">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="tiempo">¿En cuánto tiempo construiría su letrina?</label>
                                    <input type="text" class="form-control" id="tiempo" name="tiempo">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="monto">Monto a cancelar por el derecho de conexión</label>
                                    <input type="number" class="form-control" placeholder="$" min="0" id="monto" name="monto">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Forma de pago</label>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pago" id="contado">
                                        <label class="form-check-label" for="contado">Contado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="otro">Otro</label>
                                    <input type="text" class="form-control" id="otro" name="otro">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad-pagos">Cantidad de pagos</label>
                                    <input type="number" class="form-control" min="0" id="cantidad-pagos" name="cantidad-pagos">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="total-cuota"> de $ </label>
                                    <input type="number" class="form-control" min="0" id="total-cuota" name="total-cuota">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="interes">Interés que cancelara por retraso</label>
                                    <input type="text" class="form-control" id="interes" name="interes">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary  btn-anterior" data-target="#seccion3">
                                Anterior →
                            </button>
                            <button class="btn btn-primary btn-siguiente" data-target="#seccion5">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 5 DATOS COMISION MUNICIPAL-->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion5">
                            Espacio a comision municipal administradora
                        </button>
                    </h5>
                </div>

                <div id="seccion5" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="acuerdo">Se tomó el acuerdo de</label>
                                    <input type="text" class="form-control" id="acuerdo" name="acuerdo">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha-session">Sesión realizada el dia</label>
                                    <input type="date" class="form-control" id="fecha-session" name="fecha-session">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero-acta">Numero de Acta</label>
                                    <input type="text" class="form-control" id="numero-acta" name="numero-acta">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary  btn-anterior" data-target="#seccion4">
                                Anterior →
                            </button>
                            <button class="btn btn-primary btn-siguiente" data-target="#seccion6">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 6 DATOS DE LOS QUE FIRMAN-->
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion6">
                            Nombre de los que firmaran
                        </button>
                    </h5>
                </div>

                <div id="seccion6" class="collapse" data-parent="#accordion">
                    <div class="card-body">

                        <!-- Firmante 1 -->
                        <div class="card mb-3 shadow-sm">
                            <!-- <div class="card-header bg-light">
                                <strong>Administrador de ACASABED</strong>
                            </div> -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="mb-1">Seleccionar Nombre</label>
                                            <select name="buscar-firmante-1" id="buscar-firmante-1" class="form-control">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id-firmante-1">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="nombre-firmante-1">Nombre</label>
                                            <input type="text" class="form-control" id="nombre-firmante-1" name="nombre-firmante-1">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="puesto-firmante-1">Rol</label>
                                            <input type="text" class="form-control" id="puesto-firmante-1" name="puesto-firmante-1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Firmante 2 -->
                        <div class="card mb-3 shadow-sm">
                            <!-- <div class="card-header bg-light">
                                <strong> Comicion Municipal Administradora</strong>
                            </div> -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="mb-1">Seleccionar Nombre</label>
                                            <select name="buscar-firmante-2" id="buscar-firmante-2" class="form-control">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id-firmante-2">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="nombre-firmante-2">Nombre</label>
                                            <input type="text" class="form-control" id="nombre-firmante-2" name="nombre-firmante-2">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="puesto-firmante-2">Rol</label>
                                            <input type="text" class="form-control" id="puesto-firmante-2" name="puesto-firmante-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Firmante 3 -->
                        <div class="card mb-3 shadow-sm">
                            <!-- <div class="card-header bg-light">
                                <strong>Comicion Municipal Administradora</strong>
                            </div> -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="mb-1">Seleccionar Nombre</label>
                                            <select name="buscar-firmante-3" id="buscar-firmante-3" class="form-control">
                                                <option value="">...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id-firmante-3">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="nombre-firmante-3">Nombre</label>
                                            <input type="text" class="form-control" id="nombre-firmante-3" name="nombre-firmante-3">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="puesto-firmante-3">Rol</label>
                                            <input type="text" class="form-control" id="puesto-firmante-3" name="puesto-firmante-3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary btn-anterior" data-target="#seccion5">
                                Anterior →
                            </button>
                            <button id="btn-siguiente-final" class="btn btn-primary btn-siguiente-final" data-target="#seccion7">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <br>
            <!-- SECCIÓN 7 -->
            <div class="card vista-editar">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#seccion7">
                            Datos del contrato
                        </button>
                    </h5>
                </div>

                <div id="seccion7" class="collapse" data-parent="#accordion">
                    <div class="card-body">
                        <input type="hidden" id="numero-contrato">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ficha-alcaldia">Ficha Alcaldia</label>
                                    <input type="text" class="form-control" id="ficha-alcaldia" name="ficha-alcaldia">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha-inicio">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="fecha-inicio" name="fecha-inicio" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha-vencimiento">Fecha de vencimiento</label>
                                    <input type="date" class="form-control" id="fecha-vencimiento" name="fecha-vencimiento">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="rutas">Ruta</label>
                                    <select id="rutas" class="form-control" name="rutas">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="medidores">Medidor</label>
                                    <select id="medidores" class="form-control" name="medidores">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="direccion-medidor">Dirección del medidor</label>
                                    <input type="text" class="form-control" id="direccion-medidor" name="direccion-medidor">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="tarifas">Tarifa</label>
                                    <select id="tarifas" class="form-control" name="tarifas">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-secondary  btn-anterior" data-target="#seccion6">
                                Anterior →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div>
            <button type="button" class="btn btn-secondary btn-vista-previa">
                Vista Previa solicitud
            </button>
            <button type="button" class="btn btn-secondary btn-vista-previa-contrato">
                Vista Previa Contrato
            </button>
        </div>
        <div class="mt-5 d-flex justify-content-end gap-4">
            <button type="button" class="btn btn-primary modal-guardar mr-3" id="guardar-registro">Crear Solicitud</button>
            <button type="button" class="btn btn-danger modal-guardar mr-3" id="anular-solicitud">Anular Solicitud</button>
            <button type="button" class="btn btn-success modal-editar mr-3" id="actualizar-registro">Aprobar Solicitud</button>
        </div>

        <br>
    </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/solicitudes/solicitudes.js') ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const inputFecha = document.getElementById("fecha-creacion");
        const fechaInicio = document.getElementById("fecha-inicio");

        if (inputFecha && fechaInicio) {
            const hoy = new Date();

            const año = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');

            inputFecha.value = `${año}-${mes}-${dia}`;
            fechaInicio.value = `${año}-${mes}-${dia}`;
        }
    });
</script>
<?= $this->endSection() ?>