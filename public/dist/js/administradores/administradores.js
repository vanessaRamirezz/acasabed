import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus } from "../metodos/metodos.js";

let nombre;
let rol;
let ocupacion;
let direccion;
let edad;
let dui;
let idAdministrador;

let tablaAdministradores;

let inputNombre = document.getElementById('nombre');
let inputDui = document.getElementById('dui');
let inputEstado = document.getElementById('estado');

function abrirModalEditarAdministrador(elemento) {
    $('.modal-guardar-admin').hide();
    $('.modal-editar-admin').show();

    var dataAdministradores = JSON.parse(
        decodeURIComponent($(elemento).attr('data-administradores'))
    );

    $('#estado').val(dataAdministradores.estado_administrador);
    $('#nombre').val(dataAdministradores.nombre_administrador);
    $('#rol').val(dataAdministradores.rol_administrador);
    $('#ocupacion').val(dataAdministradores.ocupacion_administrador);
    $('#direccion').val(dataAdministradores.direccion_administrador);
    $('#edad').val(dataAdministradores.edad_administrador);
    $('#dui').val(dataAdministradores.dui_administrador);
    $('#id-administrador').val(dataAdministradores.id);

    eliminarColorYfocus(inputNombre);
    eliminarColorYfocus(inputDui);

    var $btnEstado = $('#actualizar-estado');
    if ((dataAdministradores.estado_administrador || '').trim().toUpperCase() === 'SI') {
        $btnEstado.removeClass('btn-success').addClass('btn-danger').text('Desactivar administrador');
    } else {
        $btnEstado.removeClass('btn-danger').addClass('btn-success').text('Activar administrador');
    }

    $('#modal-administradores').modal('show');
}

function cargarAdministradores() {
    tablaAdministradores = $('#tbl-administradores').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getAdministradores',
            data: function (d) {
                d.searchValue = $('#customSearchAdministradores').val();
            }
        },
        columns: [
            { data: 'nombre_administrador' },
            { data: 'rol_administrador' },
            { data: 'ocupacion_administrador' },
            { data: 'direccion_administrador' },
            { data: 'edad_administrador' },
            { data: 'dui_administrador' },
            { data: 'estado_administrador' },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-administradores='${encodeURIComponent(JSON.stringify(row))}'>
                                <i class="fa fa-edit"></i>
                            </button>`;
                }
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false,
        initComplete: function () {
            let searchInput = $('.dataTables_filter input');
            searchInput.val('').trigger('input');
        }
    });

    $('#customSearchAdministradores').on('keypress', function (e) {
        if (e.which == 13) {
            tablaAdministradores.draw();
        }
    });

    $('#searchBtnAdministradores').off('click').on('click', function () {
        tablaAdministradores.draw();
    });

    $('#clearSearchBtnAdministradores').on('click', function () {
        $('#customSearchAdministradores').val('');
        tablaAdministradores.draw();
    });
}

function abrirModalNuevoAdministrador() {
    $('.modal-guardar-admin').show();
    $('.modal-editar-admin').hide();

    $('#nombre').val('');
    $('#rol').val('');
    $('#ocupacion').val('');
    $('#direccion').val('');
    $('#edad').val('');
    $('#dui').val('');
    $('#id-administrador').val('');
    inputEstado.value = 'SI';

    eliminarColorYfocus(inputNombre);
    eliminarColorYfocus(inputDui);

    $('#modal-administradores').modal('show');
}

function validarCampoDui() {
    $(inputDui).mask('00000000-0');
}

function guardarOeditarAdministrador(tipoProceso) {
    nombre = $("#nombre").val().trim();
    rol = $('#rol').val().trim();
    ocupacion = $('#ocupacion').val().trim();
    direccion = $('#direccion').val().trim();
    edad = $('#edad').val().trim();
    dui = $('#dui').val().trim();
    idAdministrador = $('#id-administrador').val().trim();

    var tipo_proceso = tipoProceso === '1' ? 'nuevoAdministrador' : 'editarAdministrador';

    if (nombre === "") {
        alertaError('El Nombre es requerido');
        colorEnInputConFocus(inputNombre);
        return false;
    } else {
        eliminarColorYfocus(inputNombre);
    }

    if (dui === "") {
        alertaError('El DUI es requerido');
        colorEnInputConFocus(inputDui);
        return false;
    } else {
        eliminarColorYfocus(inputDui);
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: {
            nombre,
            rol,
            ocupacion,
            direccion,
            edad,
            dui,
            idAdministrador
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                tablaAdministradores.ajax.reload();
                Swal.close();
                $('#modal-administradores').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    })
}

function actualizarEstado() {
    idAdministrador = $("#id-administrador").val().trim();
    var estadoActual = $('#estado').val().trim().toUpperCase();
    var nuevoEstado = (estadoActual === "SI") ? "NO" : "SI";

    var accionTexto = (estadoActual === "SI")
        ? "desactivar al administrador"
        : "activar al administrador";

    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas ${accionTexto}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Espere...',
                html: 'Procesando actualización...',
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: baseURL + 'actualizarEstadoAdministrador',
                type: 'POST',
                data: {
                    idAdministrador,
                    nuevoEstado
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.close();
                        alertEnSweet('success', 'Éxito', response.mensaje)
                        tablaAdministradores.ajax.reload();
                        $('#modal-administradores').modal('hide');
                    } else {
                        Swal.close();
                        alertaError(response.mensaje);
                    }
                },
                error: function () {
                    Swal.close();
                    alertEnSweet('error', 'Error', 'No se logró actualizar el estado del administrador');
                }
            });
        }
    });
}

let tablaFirmantes;
let inputNombreFirmante = document.getElementById('nombre-firmante-crud');
let inputRolFirmante = document.getElementById('rol-firmante-crud');

function abrirModalEditarFirmante(elemento) {
    $('#guardar-firmante').hide();
    $('#actualizar-firmante').show();

    var dataFirmantes = JSON.parse(
        decodeURIComponent($(elemento).attr('data-firmantes'))
    );

    $('#nombre-firmante-crud').val(dataFirmantes.nombre_firmante);
    $('#rol-firmante-crud').val(dataFirmantes.rol_firmante);
    $('#id-firmante-crud').val(dataFirmantes.id);

    eliminarColorYfocus(inputNombreFirmante);
    eliminarColorYfocus(inputRolFirmante);

    $('#modal-firmantes').modal('show');
}

function cargarFirmantes() {
    tablaFirmantes = $('#tbl-firmantes').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getFirmantes',
            data: function (d) {
                d.searchValue = $('#customSearchFirmantes').val();
            }
        },
        columns: [
            { data: 'nombre_firmante' },
            { data: 'rol_firmante' },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-firmante"
                                data-firmantes='${encodeURIComponent(JSON.stringify(row))}'>
                                <i class="fa fa-edit"></i>
                            </button>`;
                }
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false
    });

    $('#customSearchFirmantes').on('keypress', function (e) {
        if (e.which == 13) {
            tablaFirmantes.draw();
        }
    });

    $('#searchBtnFirmantes').off('click').on('click', function () {
        tablaFirmantes.draw();
    });

    $('#clearSearchBtnFirmantes').on('click', function () {
        $('#customSearchFirmantes').val('');
        tablaFirmantes.draw();
    });
}

function abrirModalNuevoFirmante() {
    $('#guardar-firmante').show();
    $('#actualizar-firmante').hide();

    $('#nombre-firmante-crud').val('');
    $('#rol-firmante-crud').val('');
    $('#id-firmante-crud').val('');

    eliminarColorYfocus(inputNombreFirmante);
    eliminarColorYfocus(inputRolFirmante);

    $('#modal-firmantes').modal('show');
}

function guardarOeditarFirmante(tipoProceso) {
    const nombreFirmante = $("#nombre-firmante-crud").val().trim();
    const rolFirmante = $('#rol-firmante-crud').val().trim();
    const idFirmante = $('#id-firmante-crud').val().trim();
    const tipo_proceso = tipoProceso === '1' ? 'nuevoFirmante' : 'editarFirmante';

    if (nombreFirmante === "") {
        alertaError('El Nombre es requerido');
        colorEnInputConFocus(inputNombreFirmante);
        return false;
    } else {
        eliminarColorYfocus(inputNombreFirmante);
    }

    if (rolFirmante === "") {
        alertaError('El Rol es requerido');
        colorEnInputConFocus(inputRolFirmante);
        return false;
    } else {
        eliminarColorYfocus(inputRolFirmante);
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: {
            nombre: nombreFirmante,
            rol: rolFirmante,
            idFirmante
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                tablaFirmantes.ajax.reload();
                Swal.close();
                $('#modal-firmantes').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    })
}

function eventosUsuarios() {
    $("#btn-agregar").on("click", function () {
        abrirModalNuevoAdministrador();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarAdministrador('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarAdministrador('2');
    });

    $("#tbl-administradores tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarAdministrador(this);
    });

    $("#actualizar-estado").on("click", function () {
        actualizarEstado();
    });

    $("#btn-agregar-firmante").on("click", function () {
        abrirModalNuevoFirmante();
    });

    $("#guardar-firmante").on("click", function () {
        guardarOeditarFirmante('1');
    });

    $("#actualizar-firmante").on("click", function () {
        guardarOeditarFirmante('2');
    });

    $("#tbl-firmantes tbody").on("click", '.btn-ver-firmante', function () {
        abrirModalEditarFirmante(this);
    });
}

function iniciarTodo() {
    cargarAdministradores();
    cargarFirmantes();
    eventosUsuarios();
    validarCampoDui();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
