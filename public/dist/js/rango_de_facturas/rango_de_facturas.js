import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaRangoFacturas;

const inputs = {
    numeroInicio: $("#numero-de-inicio"),
    numeroFin: $("#numero-final")
};

function getData() {
    return {
        numeroInicio: inputs.numeroInicio.val(),
        numeroFin: inputs.numeroFin.val(),
    }
}

function limpiarFormulario() {
    inputs.numeroInicio.val('');
    inputs.numeroFin.val('');

    eliminarColorYfocus(inputs.numeroInicio[0]);
    eliminarColorYfocus(inputs.numeroFin[0]);
}

function abrirModalNuevoRango() {
    $('.modal-guardar').show();

    limpiarFormulario();

    $('#modal-rango-de-facturas').modal('show');
}

function cargarRagoFacturas() {
    tablaRangoFacturas = $('#tbl-rango-de-facturas').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getRangoFacturas',
            data: function (d) {
                d.searchValue = $('#customSearchRangoFacturas').val();
            }
        },
        columns: [
            {
                data: 'numeroDeInicio'
            },
            {
                data: 'numeroFin'
            },
            {
                data: 'estado',
                render: function (data) {
                    if (data == 'Activo') {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-secondary">Finalizado</span>';
                    }
                }
            },
            {
                data: 'fechaCreacion'
            },
            {
                data: 'numeroActual'
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
    $('#customSearchRangoFacturas').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaRangoFacturas.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnRangoFacturas').off('click').on('click', function () {
        tablaRangoFacturas.draw();
    });

    $('#clearSearchBtnRangoFacturas').on('click', function (e) {
        $('#customSearchRangoFacturas').val('');
        tablaRangoFacturas.draw();
    });
}

function guardarRangoFactura() {
    const data = getData();

    if (!validarCampo(data.numeroInicio, 'El numero de inicio es requerido', inputs.numeroInicio)) return;
    if (!validarCampo(data.numeroFin, 'El numero final es requerido', inputs.numeroFin)) return;

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'POST',
        url: baseURL + 'guardarRango',
        data: data,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertEnSweet('success', 'Correcto', response.mensaje)
                // alertaOk(response.mensaje);
                tablaRangoFacturas.ajax.reload();
                // Swal.close();
                $('#modal-rango-de-facturas').modal('hide');
            } else {
                alertEnSweet('error', 'Ups...', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    });
}

function eventosUsuarios() {
    $("#btn-agregar").on("click", function () {
        abrirModalNuevoRango();
    });

    $("#guardar-registro").on("click", function () {
        guardarRangoFactura();
    });
}

function iniciarTodo() {
    eventosUsuarios();
    cargarRagoFacturas();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);