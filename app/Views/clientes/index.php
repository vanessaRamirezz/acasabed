<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Clientes <?= $this->endSection() ?>
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
                <h1 class="m-0 text-dark texto">Listado de Clientes</h1>
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
                <button type="button" id="btn-agregar" class="btn bg-gradient-primary btn-flat">Agregar Nuevo</button>
            </div>
            <div class="card-body">

                <div class="d-flex justify-content-end mb-4">
                    <div class="input-group col-md-6">
                        <input type="text" id="customSearchClientes" placeholder="Buscar código, nombre, tipo cliente" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="searchBtnClientes" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="clearSearchBtnClientes" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tbl-clientes">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre / Razón social</th>
                                <th>Tipo cliente</th>
                                <th>DUI</th>
                                <th>NIT</th>
                                <th>NRC</th>
                                <th>Operaciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-clientes" tabindex="-1" aria-labelledby="mantenimiento-usuarios-label" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mantenimiento-usuarios-label">Opciones del usuario</h5>
                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button> -->
                    </div>
                    <div class="modal-body ">
                        <div class="user">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label class="form-label" for="foto-cliente-input">Imagen</label>
                                        <input type="file" class="form-control mb-2" name="foto-cliente-input" id="foto-cliente-input" accept=".jpg,.jpeg,.png">

                                        <img id="vista-previa-foto-cliente"
                                            src=""
                                            class="img-fluid border rounded"
                                            style="max-height: 150px; display:none;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="codigo">Código</label>
                                        <input type="text" class="form-control" name="codigo" id="codigo">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="nombre">Nombre Completo / Razón Social</label>
                                        <input type="text" class="form-control" name="nombre" id="nombre">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="edad">Edad</label>
                                        <input type="number" class="form-control" name="edad" id="edad" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Sexo</label>
                                        <select class="form-control" name="sexo" id="sexo">
                                            <option value="">Seleccione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="ocupacion">Ocupación</label>
                                        <input type="text" class="form-control" name="ocupacion" id="ocupacion">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="estado-familiar">Estado Familiar</label>
                                        <input type="text" class="form-control" name="estado-familiar" id="estado-familiar">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label for="numero-grupo-familiar">N° personas grupo familiar</label>
                                        <input type="number" min="1" class="form-control" name="numero-grupo-familiar" id="numero-grupo-familiar">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-9">
                                    <label>Lugar y Fecha de Nacimiento</label>

                                    <div class="d-flex align-items-center">
                                        <input type="text"
                                            class="form-control flex-grow-1"
                                            name="lugar-de-nacimiento"
                                            id="lugar-de-nacimiento">

                                        <span class="mx-2">,</span>

                                        <input type="date"
                                            class="form-control"
                                            style="max-width: 180px;"
                                            name="fecha-de-nacimiento"
                                            id="fecha-de-nacimiento">
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="lugar-de-trabajo">Lugar de trabajo</label>
                                        <input type="text" class="form-control" name="lugar-de-trabajo" id="lugar-de-trabajo">
                                    </div>
                                </div>
                            </div>
                            <br>
                            <p>Puede agregar varios numeros separados por (,) sin guiones (-)</p>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="telefono">Telefonos</label>
                                        <input type="text" class="form-control" name="telefono" id="telefono">
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="correo">Correo</label>
                                        <input type="email" class="form-control" name="correo" id="correo">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="dui">DUI</label>
                                        <input type="text" class="form-control" name="dui" id="dui">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="extendido">Extendido en</label>
                                    <input type="text" class="form-control" name="extendido" id="extendido">
                                </div>
                                <div class="col-sm-3">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" name="fecha" id="fecha">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="nit">NIT</label>
                                        <input type="text" class="form-control" name="nit" id="nit">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="nrc">NRC</label>
                                        <input type="text" class="form-control" name="nrc" id="nrc">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <label for="actividad-economica">Actividad Económica</label>
                                        <select id="actividad-economica" class="form-control">
                                            <option value="">Seleccione...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo-cliente">Tipo de Cliente</label>
                                        <select class="form-control select2" style="width: 100%;" id="tipo-cliente">
                                            <option value="">Seleccione</option>
                                            <?php
                                            foreach ($tipoClientes as $tipoCliente) {
                                                echo '<option value="' . $tipoCliente['id_tipo_cliente'] . '">' . $tipoCliente['nombre'] . '</option>';
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <!-- DATOS DEL CONTACTO -->
                            <h6 class="text-primary mb-3">Datos del contacto</h6>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="contacto-nombre">Nombre contacto</label>
                                        <input type="text" class="form-control" name="contacto-nombre" id="contacto-nombre">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="contacto-dui">DUI</label>
                                        <input type="text" class="form-control" name="contacto-dui" id="contacto-dui">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="contacto-telefono">Telefonos</label>
                                        <input type="text" class="form-control" name="contacto-telefono" id="contacto-telefono">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h5>Dirección</h5>
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="form-row">
                                        <div class="form-group col-sm">
                                            <label for="departamentos">Departamentos</label>
                                            <select id="departamentos" class="form-control" name="departamentos">
                                                <option value="-1">Seleccione...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm">
                                            <label for="municipios">Municipios</label>
                                            <select id="municipios" class="form-control">
                                                <option value="-1" selected>Seleccione...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm">
                                            <label for="distritos">Distritos</label>
                                            <select id="distritos" class="form-control" data-distrito-seleccionado="-1">
                                                <option value="-1" selected>Seleccione...</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm">
                                            <label for="colonia">Colonias</label>
                                            <select id="colonia" class="form-control">
                                                <option value="-1" selected>Seleccione...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="complemento-direccion">Complemento</label>
                                        <input type="text" class="form-control" name="complemento-direccion" id="complemento-direccion">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="fecha-vencimiento-dui">Fecha de Vencimiento del DUI</label>
                                        <input type="date" class="form-control" name="fecha-vencimiento-dui" id="fecha-vencimiento-dui">
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <!-- DUI FRONTAL -->
                                <div class="col-md-6">
                                    <label class="form-label" for="dui-frontal-input">DUI Frontal</label>
                                    <input type="file" class="form-control mb-2" name="dui-frontal-input" id="dui-frontal-input" accept=".jpg,.jpeg,.png">

                                    <img id="vista-previa-frontal"
                                        src=""
                                        class="img-fluid border rounded"
                                        style="max-height: 200px; display:none;">
                                </div>

                                <!-- DUI REVERSO -->
                                <div class="col-md-6">
                                    <label class="form-label" for="dui-reverso-input">DUI Reverso</label>
                                    <input type="file" class="form-control mb-2" name="dui-reverso-input" id="dui-reverso-input" accept=".jpg,.jpeg,.png">

                                    <img id="vista-previa-reversa"
                                        src=""
                                        class="img-fluid border rounded"
                                        style="max-height: 200px; display:none;">
                                </div>

                            </div>
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="comentarios">Comentarios</label>
                                        <textarea name="comentarios" id="comentarios" class="form-control"></textarea>
                                    </div>
                                </div>
                                <input type="hidden" id="id-cliente">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary modal-guardar" id="guardar-registro">Guardar registro</button>
                        <button type="button" class="btn btn-warning modal-editar" id="actualizar-registro">Guardar registro</button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script type="module" src="<?= base_url('dist/js/clientes/clientes.js') ?>"></script>
<?= $this->endSection() ?>