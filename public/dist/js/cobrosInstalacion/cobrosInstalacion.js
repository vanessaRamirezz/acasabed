import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, colorEnInputConFocusSelect, eliminarColorYfocus, eliminarColorYfocusSelect } from "../metodos/metodos.js";

let tablaCobrosInstalacion;
let detalleCobroActual = null;
let validacionLista = false;

const inputs = {
    idContrato: $("#id-contrato-cobro"),
    buscarCuenta: $("#buscar-cuenta-cobro"),
    montoPago: $("#monto-pago"),
    cobrarMora: $("#cobrar-mora"),
    moraPago: $("#mora-pago"),
    btnProcesarPago: $("#btn-procesar-pago"),
    btnValidarCobro: $("#btn-validar-cobro"),
};

function formatearMonto(valor) {
    const numero = parseFloat(valor || 0);
    return `$${numero.toFixed(2)}`;
}

function cargarTablaCobros() {
    tablaCobrosInstalacion = $('#tbl-cobros-instalacion').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getCobrosInstalacion',
            data: function (d) {
                d.searchValue = $('#customSearchCobros').val();
            }
        },
        columns: [
            { data: 'codigo_solicitud' },
            { data: 'numero_contrato' },
            { data: 'cliente' },
            {
                data: 'monto_cobrado',
                render: data => formatearMonto(data)
            },
            {
                data: 'mora',
                render: data => formatearMonto(data)
            },
            {
                data: 'total_pagado',
                render: data => formatearMonto(data)
            },
            {
                data: 'fecha_creacion',
                render: data => data || '-'
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false
    });

    $('#customSearchCobros').on('keypress', function (e) {
        if (e.which === 13) {
            tablaCobrosInstalacion.draw();
        }
    });

    $('#searchBtnCobros').off('click').on('click', function () {
        tablaCobrosInstalacion.draw();
    });

    $('#clearSearchBtnCobros').off('click').on('click', function () {
        $('#customSearchCobros').val('');
        tablaCobrosInstalacion.draw();
    });
}

function cargarBuscadorCuentas() {
    inputs.buscarCuenta.select2({
        width: '100%',
        placeholder: 'Escriba nombre de cliente, solicitud o contrato',
        allowClear: true,
        ajax: {
            url: baseURL + 'buscarCuentasCobroInstalacion',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: (response.data || []).map(cuenta => ({
                        id: cuenta.id_contrato,
                        text: `${cuenta.codigo_solicitud} | ${cuenta.nombre_completo} | ${cuenta.numero_contrato}`
                    }))
                };
            },
            cache: true
        }
    });
}

function limpiarEstadoValidacion() {
    validacionLista = false;
    $('#contenedor-validacion').hide().removeClass('alert-success alert-danger').empty();
    inputs.btnProcesarPago.hide();
}

function renderizarTablaCuenta(detalle) {
    const resumen = detalle.resumen || {};
    const tbody = $('#tbl-cuentas-cobro tbody');

    tbody.html(`
        <tr>
            <td>${resumen.codigo_solicitud || '-'}</td>
            <td>${resumen.nombre_completo || '-'}</td>
            <td>${resumen.fecha_generacion || '-'}</td>
            <td>${formatearMonto(resumen.saldo_pendiente)}</td>
            <td>${resumen.cuotas_pendientes || 0}</td>
            <td>
                <button type="button" class="btn btn-primary btn-sm" id="btn-ver-cuotas-modal">
                    Ver / cobrar cuotas
                </button>
            </td>
        </tr>
    `);
}

function renderizarModal(detalle) {
    const resumen = detalle.resumen || {};
    const cuotas = detalle.cuotas || [];

    inputs.idContrato.val(resumen.id_contrato || '');
    $('#resumen-cliente').text(resumen.nombre_completo || '-');
    $('#resumen-solicitud').text(resumen.codigo_solicitud || '-');
    $('#resumen-saldo-pendiente').text(formatearMonto(resumen.saldo_pendiente));
    $('#resumen-cuotas-pendientes').text(resumen.cuotas_pendientes || 0);

    const tbody = $('#tbl-detalle-cuotas tbody');
    tbody.empty();

    cuotas.forEach(cuota => {
        tbody.append(`
            <tr>
                <td>${cuota.numero_cuota}</td>
                <td>${formatearMonto(cuota.monto_cuota)}</td>
                <td>${formatearMonto(cuota.cantidad_abonada)}</td>
                <td>${formatearMonto(cuota.saldo_cuota)}</td>
                <td>${cuota.estado || '-'}</td>
                <td>${cuota.fecha_vencimiento || '-'}</td>
                <td>${cuota.fecha_pago || '-'}</td>
            </tr>
        `);
    });

    inputs.montoPago.val('');
    inputs.cobrarMora.val('no');
    inputs.moraPago.val('').prop('disabled', true);
    limpiarEstadoValidacion();
}

function cargarDetalleCobro(idContrato) {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDetalleCobroInstalacion',
        data: { idContrato },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                detalleCobroActual = response.data;
                renderizarTablaCuenta(response.data);
                eliminarColorYfocusSelect(inputs.buscarCuenta[0]);
            } else {
                alertaError(response.mensaje || 'No se pudo cargar el detalle del cobro');
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error al cargar el detalle');
        }
    });
}

function validarFormularioCobro() {
    const idContrato = inputs.idContrato.val().trim();
    const montoPago = inputs.montoPago.val().trim();
    const cobrarMora = inputs.cobrarMora.val() === 'si';
    const mora = cobrarMora ? inputs.moraPago.val().trim() : '0';

    if (!idContrato) {
        alertaError('Debe seleccionar una cuenta de cobro');
        colorEnInputConFocusSelect(inputs.buscarCuenta[0]);
        return false;
    }

    if (!montoPago || parseFloat(montoPago) <= 0) {
        alertaError('Debe ingresar un monto válido');
        colorEnInputConFocus(inputs.montoPago[0]);
        return false;
    }

    eliminarColorYfocus(inputs.montoPago[0]);

    if (cobrarMora && (mora === '' || parseFloat(mora) < 0)) {
        alertaError('Debe ingresar una mora válida');
        colorEnInputConFocus(inputs.moraPago[0]);
        return false;
    }

    eliminarColorYfocus(inputs.moraPago[0]);
    return true;
}

function validarCobro() {
    if (!validarFormularioCobro()) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'validarCobroInstalacion',
        data: {
            idContrato: inputs.idContrato.val().trim(),
            montoPago: inputs.montoPago.val().trim(),
            cobrarMora: inputs.cobrarMora.val() === 'si',
            mora: inputs.moraPago.val().trim()
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const d = response.data;
                validacionLista = true;
                $('#contenedor-validacion')
                    .show()
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .html(`
                        Validacion correcta.
                        Saldo pendiente: <strong>${formatearMonto(d.saldoPendiente)}</strong>.
                        Monto a cancelar: <strong>${formatearMonto(d.montoPago)}</strong>.
                        Mora: <strong>${formatearMonto(d.mora)}</strong>.
                        Total recibido: <strong>${formatearMonto(d.totalRecibido)}</strong>.
                    `);
                inputs.btnProcesarPago.show();
            } else {
                validacionLista = false;
                inputs.btnProcesarPago.hide();
                $('#contenedor-validacion')
                    .show()
                    .removeClass('alert-success')
                    .addClass('alert-danger')
                    .text(response.mensaje || 'La validacion del cobro no fue correcta');
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error al validar el cobro');
        }
    });
}

function registrarPago() {
    if (!validacionLista) {
        alertaError('Primero debe validar el cobro');
        return;
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando pago...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'POST',
        url: baseURL + 'registrarPagoInstalacion',
        data: {
            idContrato: inputs.idContrato.val().trim(),
            montoPago: inputs.montoPago.val().trim(),
            cobrarMora: inputs.cobrarMora.val() === 'si',
            mora: inputs.moraPago.val().trim()
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                Swal.close();
                alertaOk(response.mensaje || 'Pago aplicado correctamente');
                $('#modal-cobro-cuotas').modal('hide');
                tablaCobrosInstalacion.draw();
                if (inputs.idContrato.val().trim()) {
                    cargarDetalleCobro(inputs.idContrato.val().trim());
                }
            } else {
                Swal.close();
                alertaError(response.mensaje || 'No se pudo registrar el pago');
            }
        },
        error: function () {
            Swal.close();
            alertEnSweet('error', 'Ups..', 'Ocurrió un error al registrar el pago');
        }
    });
}

function eventos() {
    inputs.buscarCuenta.on('change', function () {
        const idContrato = $(this).val();
        limpiarEstadoValidacion();

        if (idContrato) {
            cargarDetalleCobro(idContrato);
        } else {
            detalleCobroActual = null;
            inputs.idContrato.val('');
            $('#tbl-cuentas-cobro tbody').html(`
                <tr>
                    <td colspan="6" class="text-center">Seleccione una cuenta para mostrar información.</td>
                </tr>
            `);
        }
    });

    $(document).on('click', '#btn-ver-cuotas-modal', function () {
        if (!detalleCobroActual) {
            alertaError('No hay detalle para mostrar');
            return;
        }

        renderizarModal(detalleCobroActual);
        $('#modal-cobro-cuotas').modal('show');
    });

    inputs.cobrarMora.on('change', function () {
        const cobrar = $(this).val() === 'si';
        inputs.moraPago.prop('disabled', !cobrar);

        if (!cobrar) {
            inputs.moraPago.val('');
        }

        limpiarEstadoValidacion();
    });

    inputs.montoPago.on('input', limpiarEstadoValidacion);
    inputs.moraPago.on('input', limpiarEstadoValidacion);

    inputs.btnValidarCobro.on('click', validarCobro);
    inputs.btnProcesarPago.on('click', registrarPago);
}

function iniciarTodo() {
    cargarTablaCobros();
    cargarBuscadorCuentas();
    eventos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
