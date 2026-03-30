import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus } from "../metodos/metodos.js";

let nombre;
let telefono;
let dui;
let direccion;
let correo;
let idInstalador;

let tablaIstaladores;

let inputNombre = document.getElementById('nombre');
let inputDui = document.getElementById('dui');
let inputCorreo = document.getElementById('correo');
let inputEstado = document.getElementById('estado');

function abrirModelEditarInstalador(elemento) {
    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataInstaladores = JSON.parse(
        decodeURIComponent($(elemento).attr('data-instaladores'))
    );

    $('#estado').val(dataInstaladores.estado_instalador);
    $('#nombre').val(dataInstaladores.nombre_instalador);
    $('#telefono').val(dataInstaladores.telefono_instalador);
    $('#dui').val(dataInstaladores.dui_instalador);
    $('#direccion').val(dataInstaladores.direccion_instalador);
    $('#correo').val(dataInstaladores.correo_instalador);
    $('#id-instalador').val(dataInstaladores.id);

    eliminarColorYfocus(inputNombre);
    eliminarColorYfocus(inputDui);
    eliminarColorYfocus(inputCorreo);

    // Botón estado
    var $btnEstado = $('#actualizar-estado');
    if (dataInstaladores.estado_instalador.trim().toUpperCase() === 'SI') {
        $btnEstado.removeClass('btn-success').addClass('btn-danger').text('Desactivar instalador');
    } else {
        $btnEstado.removeClass('btn-danger').addClass('btn-success').text('Activar instalador');
    }

    $('#modal-instaladores').modal('show');
}

function cargarInstaladores() {
    tablaIstaladores = $('#tbl-instaladores').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getInstaladores',
            data: function (d) {
                d.searchValue = $('#customSearchInstaladores').val();
            }
        },
        columns: [
            {
                data: 'nombre_instalador'
            },
            {
                data: 'telefono_instalador'
            },
            {
                data: 'dui_instalador'
            },
            {
                data: 'direccion_instalador'
            },
            {
                data: 'correo_instalador'
            },
            {
                data: 'estado_instalador'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-instaladores='${encodeURIComponent(JSON.stringify(row))}'>
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

    //Buscar al presionar Enter en tu input
    $('#customSearchInstaladores').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaIstaladores.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnInstaladores').off('click').on('click', function () {
        tablaIstaladores.draw();
    });

    $('#clearSearchBtnInstaladores').on('click', function (e) {
        $('#customSearchInstaladores').val('');
        tablaIstaladores.draw();
    });
}

function abrirModalNuevoInstalador() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    // limpiar campos del formulario
    $('#nombre').val('');
    $('#telefono').val('');
    $('#dui').val('');
    $('#direccion').val('');
    $('#correo').val('');
    $('#id-instalador').val('');
    inputEstado.value = 'SI';

    eliminarColorYfocus(inputNombre);
    eliminarColorYfocus(inputDui);
    eliminarColorYfocus(inputCorreo);

    $('#modal-instaladores').modal('show');
}

function validarCampoDui() {
    $(inputDui).mask('00000000-0');
}

function guardarOeditarInstalador(tipoProceso) {
    nombre = $("#nombre").val().trim();
    telefono = $('#telefono').val().trim();
    dui = $('#dui').val().trim();
    direccion = $('#direccion').val().trim();
    correo = $('#correo').val().trim();
    idInstalador = $('#id-instalador').val().trim();

    const emailRegex = /^\S+@\S+\.\S+$/;

    var tipo_proceso = tipoProceso === '1' ? 'nuevoInstalador' : 'editarInstalador';

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

    if (correo && !emailRegex.test(correo)) {
        alertaError('Por favor ingrese un correo válido');
        colorEnInputConFocus(inputCorreo);
        return false;
    } else {
        eliminarColorYfocus(inputCorreo);
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
            telefono,
            dui,
            direccion,
            correo,
            idInstalador
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                tablaIstaladores.ajax.reload();
                Swal.close();
                $('#modal-instaladores').modal('hide');
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
    idInstalador = $("#id-instalador").val().trim();
    var estadoActual = $('#estado').val().trim().toUpperCase();
    var nuevoEstado = (estadoActual === "SI") ? "NO" : "SI";

    var accionTexto = (estadoActual === "SI")
        ? "desactivar al instalador"
        : "activar al instalador";

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
            // Aquí mandas la petición AJAX
            $.ajax({
                url: baseURL + 'actualizarEstadoInstalador',
                type: 'POST',
                data: {
                    idInstalador,
                    nuevoEstado
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.close();
                        alertEnSweet('success', 'Éxito', response.mensaje)
                        tablaIstaladores.ajax.reload();
                        $('#modal-instaladores').modal('hide');
                    } else {
                        Swal.close();
                        alertaError(response.mensaje);
                    }
                },
                error: function () {
                    Swal.close();
                    alertEnSweet('error', 'Error', 'No se logro actualizar el estado del usuarios');
                }
            });
        }
    });
}

function eventosUsuarios() {
    $("#btn-agregar").on("click", function () {
        abrirModalNuevoInstalador();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarInstalador('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarInstalador('2');
    });

    $("#tbl-instaladores tbody").on("click", '.btn-ver-opciones', function () {
        abrirModelEditarInstalador(this);
    });

    $("#actualizar-estado").on("click", function () {
        actualizarEstado();
    });
}


function iniciarTodo() {
    cargarInstaladores();
    eventosUsuarios();
    validarCampoDui();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);