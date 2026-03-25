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

function abrirModelEditarInstalador(elemento) {
    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataInstaladores = JSON.parse(
        decodeURIComponent($(elemento).attr('data-instaladores'))
    );

    $('#nombre').val(dataInstaladores.nombre_completo);
    $('#telefono').val(dataInstaladores.telefono);
    $('#dui').val(dataInstaladores.dui);
    $('#direccion').val(dataInstaladores.direccion);
    $('#correo').val(dataInstaladores.correo);
    $('#id-instalador').val(dataInstaladores.id_instalador);

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
                data: 'nombre_completo'
            },
            {
                data: 'telefono'
            },
            {
                data: 'dui'
            },
            {
                data: 'direccion'
            },
            {
                data: 'correo'
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
}


function iniciarTodo() {
    cargarInstaladores();
    eventosUsuarios();
    validarCampoDui();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);