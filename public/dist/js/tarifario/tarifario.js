import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaTarifas;

const inputs = {
    codigo: $("#codigo"),
    tipoCliente: $("#tipo-cliente"),
    idTarifa: $("#id-tarifa")
};


function abrirModalNuevaTarifa() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    // 🔥 limpiar tabla
    $("#tabla-rangos tbody").html('');
    inputs.codigo.val('').prop('disabled', false);
    inputs.tipoCliente.val('');

    eliminarColorYfocus(inputs.codigo[0]);
    eliminarColorYfocus(inputs.tipoCliente[0]);

    $('#model-tarifario').modal('show');
}

function abrirModalEditarTarifa(elemento) {

    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataTarifas = JSON.parse(
        decodeURIComponent($(elemento).attr('data-tarifa'))
    );

    $('#codigo').val(dataTarifas.codigo).prop('disabled', true);

    $('#tipo-cliente').val(dataTarifas.id_tipo_cliente);

    $('#id-tarifa').val(dataTarifas.id_tarifa);

    // 🔥 limpiar tabla
    $("#tabla-rangos tbody").html('');

    // 🔥 traer detalles desde backend
    $.get(baseURL + 'getTarifaDetalle/' + dataTarifas.id_tarifa, function (detalles) {

        detalles.forEach(d => {

            let fila = `
            <tr>
                <td>
                    <input type="hidden" class="id_detalle" value="${d.id_tarifa_detalle}">
                    <input type="number" class="form-control desde" value="${d.desde_n_metros}">
                </td>
                <td><input type="number" class="form-control hasta" value="${d.hasta_n_metros}"></td>
                <td><input type="number" class="form-control valor" value="${d.valor_metro_cubico}"></td>
                <td><input type="number" class="form-control minimo" value="${d.pago_minimo}"></td>
                <td><button class="btn btn-danger btn-sm eliminar">X</button></td>
            </tr>
            `;

            $("#tabla-rangos tbody").append(fila);
        });

    });

    $('#model-tarifario').modal('show');
}

function getData() {

    let detalles = [];

    $("#tabla-rangos tbody tr").each(function () {

        let desde = $(this).find(".desde").val();
        let hasta = $(this).find(".hasta").val();
        let valor = $(this).find(".valor").val();
        let minimo = $(this).find(".minimo").val();

        if (desde !== "") {
            detalles.push({
                id: $(this).find(".id_detalle").val() || null,
                desde: $(this).find(".desde").val(),
                hasta: $(this).find(".hasta").val(),
                valor_metro_cubico: $(this).find(".valor").val(),
                pago_minimo: $(this).find(".minimo").val()
            });
        }
    });

    return {
        codigo: inputs.codigo.val().trim(),
        tipoCliente: inputs.tipoCliente.val(),
        detalles: detalles,
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

function agregarFilaRango() {
    let fila = `
    <tr>
        <td><input type="number" class="form-control desde"></td>
        <td><input type="number" class="form-control hasta"></td>
        <td><input type="number" step="0.01" class="form-control valor"></td>
        <td><input type="number" step="0.01" class="form-control minimo"></td>
        <td><button class="btn btn-danger btn-sm eliminar">X</button></td>
    </tr>
    `;

    $("#tabla-rangos tbody").append(fila);
}

function guardarOeditarTarifa(tipoProceso) {

    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevaTarifa' : 'editarTarifa';

    if (!validarCampo(data.codigo, 'El codigo es requerido', inputs.codigo)) return;
    if (!validarCampo(data.tipoCliente, 'Seleccione un tipo de cliente', inputs.tipoCliente)) return;

    if (data.detalles.length === 0) {
        alertEnSweet('error', 'Error', 'Debe agregar al menos un rango');
        return;
    }

    // 🔥 ordenar rangos
    data.detalles.sort((a, b) => a.desde - b.desde);

    // 🔥 validar rangos
    for (let d of data.detalles) {

        if (d.desde === "" || d.desde == null) {
            alertEnSweet('error', 'Error', 'El campo "Desde" es obligatorio');
            return;
        }

        if (d.hasta !== null && d.hasta <= d.desde) {
            alertEnSweet('error', 'Error', 'El rango "Hasta" debe ser mayor que "Desde"');
            return;
        }

        if (d.valor_metro_cubico == 0 && d.pago_minimo == 0) {
            alertEnSweet('error', 'Error', 'Debe ingresar pago mínimo o valor por m³');
            return;
        }
    }

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
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                tablaTarifas.ajax.reload();
                Swal.close();
                $('#model-tarifario').modal('hide');
            } else {
                Swal.close();
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            Swal.close();
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

    // eventos para botones de agregar rango de tarifas
    $(document).on("click", "#btn-add-rango", agregarFilaRango);

    $(document).on("click", ".eliminar", function () {
        $(this).closest("tr").remove();
    });
}

function iniciarTodo() {
    cargarTarifas();
    eventosUsuario();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);