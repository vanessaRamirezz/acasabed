import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus } from "../metodos/metodos.js";

let tablaTipoCliente;
let inputTipoCliente = document.getElementById('tipo-cliente');
let tipoCliente;
let idTipoCliente;

function cargarTabla() {
    tablaTipoCliente = $('#tbl-tipos-cliente').DataTable({
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        searching: false,
        ordering: false
    });
    traerTiposCliente();
}

function traerTiposCliente() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getTipoCliente',
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                tablaTipoCliente.clear();

                response.data.forEach(function (row) {
                    var keysToShow = ['nombre']

                    var rowData = keysToShow.map(function (key) {
                        return row[key];
                    });

                    rowData.push(`<button class="btn btn-info btn-sm btn-ver-opciones"
                        data-tipo-cliente='${encodeURIComponent(JSON.stringify(row))}'>
                        <i class="fa fa-edit"></i>
                    </button>`);

                    tablaTipoCliente.row.add(rowData);
                });
                tablaTipoCliente.draw();

                // continua evento de editar
                $('#tbl-tipos-cliente tbody').off('click', '.btn-ver-opciones').on('click', '.btn-ver-opciones', function () {
                    $('.modal-guardar').hide();
                    $('.modal-editar').show();

                    // Obtener todo el objeto del usuario desde el data-attribute
                    var dataTipoCliente = JSON.parse(
                        decodeURIComponent($(this).attr('data-tipo-cliente'))
                    );

                    // Llenar los campos del formulario
                    $('#tipo-cliente').val(dataTipoCliente.nombre);
                    $('#id-tipo-cliente').val(dataTipoCliente.id_tipo_cliente);

                    $('#modal-tipo-cliente').modal('show');
                });
            } else {
                alertaError(response.mensaje);
            }
        }, error: function () {
            alertaError('Error al cargar los datos de la tabla')
        }
    })
}

function abrirModalNuevoTipoCliente() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    // limpiar campos del formulario
    $('#tipo-cliente').val('');
    $('#id-tipo-cliente').val('');

    $('#modal-tipo-cliente').modal('show');
}

function guardarOeditarTipoCliente(tipoProceso) {
    tipoCliente = $('#tipo-cliente').val().trim();
    idTipoCliente = $("#id-tipo-cliente").val().trim();

    var tipo_proceso = tipoProceso === '1' ? 'nuevoTipoCliente' : 'editarTipoCliente';

    if (tipoCliente === "") {
        alertaError('El nombre es requerido');
        colorEnInputConFocus(inputTipoCliente);
        return false;
    } else {
        eliminarColorYfocus(inputTipoCliente);
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
            tipoCliente,
            idTipoCliente
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                traerTiposCliente();
                Swal.close();
                $('#modal-tipo-cliente').modal('hide');
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
        abrirModalNuevoTipoCliente();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarTipoCliente('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarTipoCliente('2');
    });
}

function iniciarTodo() {
    cargarTabla();
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);