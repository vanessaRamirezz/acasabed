import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaTarifas;

const inputs = {
    codigo: $("#codigo"),
    tipoCliente: $("#tipo-cliente"),
    valorMetro: $("#valor-metro-cubico"),
    pagoMinimo: $("#pago-minimo"),
    desde: $("#desde-n-metros"),
    hasta: $("#hasta-n-metros"),
    idTarifa: $("#id-tarifa")
};


function limpiarFormulario() {
    Object.values(inputs).forEach(input => {
        input.val('');
        eliminarColorYfocus(input[0]);
    });

    inputs.codigo.prop('disabled', false);
}

function abrirModalNuevaTarifa() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    eliminarColorYfocus(inputs.codigo[0]);
    eliminarColorYfocus(inputs.tipoCliente[0]);
    eliminarColorYfocus(inputs.valorMetro[0]);

    $('#model-tarifario').modal('show');
}

function abrirModalEditarTarifa(elemento) {
    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataTarifas = JSON.parse(
        decodeURIComponent($(elemento).attr('data-tarifa'))
    );

    $('#codigo').val(dataTarifas.codigo).prop('disabled', true);
    var $selectTipoCliente = $('#tipo-cliente');
    $selectTipoCliente.find('option').each(function () {
        $(this).prop('selected', $(this).text().trim() === dataTarifas.nombre_tipo_cliente.trim());
    });
    $('#valor-metro-cubico').val(dataTarifas.valor_metro_cubico);
    $('#pago-minimo').val(dataTarifas.pago_minimo);
    $('#desde-n-metros').val(dataTarifas.desde_n_metros);
    $('#hasta-n-metros').val(dataTarifas.hasta_n_metros);
    $('#id-tarifa').val(dataTarifas.id_tarifa);

    eliminarColorYfocus(inputs.codigo[0]);
    eliminarColorYfocus(inputs.tipoCliente[0]);
    eliminarColorYfocus(inputs.valorMetro[0]);

    $('#model-tarifario').modal('show');
}

function getData() {
    return {
        codigo: inputs.codigo.val().trim(),
        tipoCliente: inputs.tipoCliente.val(),
        valorMetro: inputs.valorMetro.val().trim(),
        pagoMinimo: inputs.pagoMinimo.val().trim(),
        desde: inputs.desde.val().trim(),
        hasta: inputs.hasta.val().trim(),
        idTarifa: inputs.idTarifa.val().trim()
    };
}

function cargarTarifas() {
    tablaTarifas = $('#tbl-tarifas').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getTarifas',
            data: function (d) {
                d.searchValue = $('#customSearchTarifas').val();
            }
        },
        columns: [
            {
                data: 'codigo'
            },
            {
                data: 'nombre_tipo_cliente'
            },
            {
                data: 'valor_metro_cubico'
            },
            {
                data: 'desde_n_metros'
            },
            {
                data: 'hasta_n_metros'
            },
            {
                data: 'pago_minimo'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-tarifa='${encodeURIComponent(JSON.stringify(row))}'>
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
    $('#customSearchTarifas').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaTarifas.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnTarifas').off('click').on('click', function () {
        tablaTarifas.draw();
    });

    $('#clearSearchBtnTarifas').on('click', function (e) {
        $('#customSearchTarifas').val('');
        tablaTarifas.draw();
    });
}

function guardarOeditarTarifa(tipoProceso) {

    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevaTarifa' : 'editarTarifa';

    if (!validarCampo(data.codigo, 'El codigo es requerido', inputs.codigo)) return;
    if (!validarCampo(data.tipoCliente, 'Seleccione un tipo de cliente', inputs.tipoCliente)) return;
    if (!validarCampo(data.valorMetro, 'El valor por metro es requerido', inputs.valorMetro)) return;

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: data,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                tablaTarifas.ajax.reload();
                Swal.close();
                $('#model-tarifario').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    });
}

function eventosUsuario() {
    $("#btn-agregar").on("click", function () {
        abrirModalNuevaTarifa();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarTarifa('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarTarifa('2');
    });

    $("#tbl-tarifas tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarTarifa(this);
    });
}

function iniciarTodo() {
    cargarTarifas();
    eventosUsuario();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);