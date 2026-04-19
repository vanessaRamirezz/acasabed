import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, colorEnInputConFocusSelect, eliminarColorYfocus, eliminarColorYfocusSelect } from "../metodos/metodos.js";

let tablaCobrosInstalacion;
let validacionLista = false;

const inputs = {
    idContrato: $("#id-contrato-cobro"),
    buscarCliente: $("#buscar-cliente"),
    montoPago: $("#monto-pago"),
    btnProcesarPago: $("#btn-procesar-pago"),
    btnValidarCobro: $("#btn-validar-cobro"),
};

let detalleCobroActual = null; // para guardar los datos de la solicitud que se editar
let cuotasSeleccionadas = []; // para guardar las cuotas que se pagaran y se marquen en la tabla
let detalleSeleccionado = null;

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
            url: baseURL + 'getCobrosRealizados',
            data: function (d) {
                d.searchValue = $('#customSearchCobros').val();
            }
        },
        columns: [
            { data: 'correlativo' },
            { data: 'codigo_solicitud' },
            { data: 'numero_contrato' },
            { data: 'cliente' },
            // {
            //     data: 'monto_cobrado',
            //     render: data => formatearMonto(data)
            // },
            // {
            //     data: 'mora',
            //     render: data => formatearMonto(data)
            // },
            {
                data: 'fecha_creacion',
                render: data => formatearFecha(data) || '-'
            },
            {
                data: null,
                render: function (data, type, row) {

                    return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                Acciones
                            </button>
                            <div class="dropdown-menu">

                                <a class="dropdown-item dropdown-item-custom btn-ver-factura-cobro-intalacion-pdf" href="#"
                                    data-id="${row.id}">
                                    <i class="fas fa-file-pdf mr-2 text-danger" ></i> Factura
                                </a>

                            </div>
                        </div>
                        `;
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

function limpiarEstadoValidacion() {
    validacionLista = false;
    $('#contenedor-validacion').hide().removeClass('alert-success alert-danger').empty();
    inputs.btnProcesarPago.hide();
}

function formatearFecha(fecha) {
    if (!fecha) return '-';

    const f = new Date(fecha);

    const dia = String(f.getDate()).padStart(2, '0');
    const mes = String(f.getMonth() + 1).padStart(2, '0');
    const anio = f.getFullYear();

    return `${dia}-${mes}-${anio}`;
}

function formatearMonto(valor) {
    const numero = parseFloat(valor || 0);
    return `$${numero.toFixed(2)}`;
}

function renderizarTablaCuenta(detalles) {
    const tbody = $('#tbl-cuentas-cobro tbody');

    if (!detalles || detalles.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="6" class="text-center">No hay registros</td>
            </tr>
        `);
        return;
    }

    let html = '';

    detalles.forEach((detalle, index) => {
        const resumen = detalle.resumen || {};

        const saldo = parseFloat(resumen.saldo_pendiente) || 0;
        const claseSaldo = saldo === 0 ? 'text-success' : 'text-danger';

        html += `
        <tr>
            <td>${resumen.codigo_solicitud || '-'}</td>
            <td>${resumen.numero_contrato || '-'}</td>
            <td>${resumen.nombre_completo || '-'}</td>
            <td>${formatearFecha(resumen.fecha_generacion)}</td>
            <td class="${claseSaldo}">${formatearMonto(saldo)}</td>
            <td>
                <button 
                    type="button" 
                    class="btn btn-info btn-sm btn-ver-cuotas-modal"
                    data-index="${index}"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `;
    });

    tbody.html(html);
}

function cargarDetalleCobro(idCliente) {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDetalleCobroCliente',
        data: { idCliente },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                detalleCobroActual = response.data;
                renderizarTablaCuenta(response.data); // ahora es array
            } else {
                alertaError(response.mensaje || 'No se pudo cargar el detalle del cobro');
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error al cargar el detalle');
        }
    });
}

function cargarClientes() {
    $('#buscar-cliente').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getClientesSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(cliente => ({
                        id: cliente.id_cliente,
                        text: cliente.nombre_completo,
                    }))
                };
            },
            cache: true
        }
    })
}


function validarMontoIncluyeMora(montoPago, moras) {
    const totalMora = moras.reduce((acc, m) => acc + parseFloat(m.mora || 0), 0);

    if (totalMora > 0 && montoPago <= totalMora) {
        throw new Error(`El monto debe ser mayor a la mora total ($${totalMora.toFixed(2)})`);
    }

    return totalMora;
}


function obtenerMorasPorCuota() {
    let moras = [];

    $('.input-mora').each(function () {
        const id = $(this).attr('data-id'); // 🔥 FIX REAL
        const valor = parseFloat($(this).val()) || 0;

        if (valor < 0) {
            throw new Error('La mora no puede ser negativa');
        }

        if (valor > 0) {
            moras.push({
                id_cobro_instalacion: parseInt(id),
                mora: valor
            });
        }
    });

    return moras;
}
function validarFormularioCobro() {
    const idContrato = inputs.idContrato.val().trim();
    const montoPago = inputs.montoPago.val().trim();

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

    try {
        obtenerMorasPorCuota(); // valida negativas
    } catch (e) {
        alertaError(e.message);
        return false;
    }

    return true;
}

function obtenerMapaMoras() {
    let mapa = {};

    $('.input-mora').each(function () {
        const id = $(this).attr('data-id'); // 🔥 igual aquí
        const valor = $(this).val();

        if (valor) {
            mapa[id] = valor;
        }
    });

    return mapa;
}

function validarCobro() {
    if (!validarFormularioCobro()) {
        return;
    }

    const moras = obtenerMorasPorCuota();
    const montoPago = parseFloat(inputs.montoPago.val());

    try {
        validarMontoIncluyeMora(montoPago, moras);
    } catch (e) {
        alertaError(e.message);
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'validarCobroInstalacion',
        data: {
            idContrato: inputs.idContrato.val().trim(),
            montoPago: inputs.montoPago.val().trim(),
            moras: JSON.stringify(moras)
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                const d = response.data;
                cuotasSeleccionadas = d.cuotasAplicadas || [];
                validacionLista = true;
                $('#contenedor-validacion')
                    .show()
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .html(`
                    Validacion correcta.
                    Monto a cancelar: <strong>${formatearMonto(d.montoPago)}</strong>.
                    Recargo: <strong>${formatearMonto(d.moraTotal)}</strong>.
                    Total aplicado a cuotas: <strong>${formatearMonto(d.montoCuotas)}</strong>.
                `);
                inputs.btnProcesarPago.show();

                if (detalleSeleccionado) {
                    renderizarModal(detalleSeleccionado, false);
                }
            } else {
                validacionLista = false;
                inputs.btnProcesarPago.hide();
                cuotasSeleccionadas = [];

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

function renderizarModal(detalle, resetFormulario) {
    const resumen = detalle.resumen || {};
    const cuotas = detalle.cuotas || [];

    inputs.idContrato.val(resumen.id_contrato || '');

    $('#resumen-cliente').text(resumen.nombre_completo || '-');
    $('#resumen-solicitud').text(resumen.codigo_solicitud || '-');
    $('#resumen-contrato').text(resumen.numero_contrato || '-');
    $('#resumen-costo-instalacion').text(formatearMonto(resumen.costo));
    $('#resumen-saldo-pendiente').text(formatearMonto(resumen.saldo_pendiente));
    $('#resumen-cuotas-pendientes').text(resumen.cuotas_pendientes || 0);

    const mapaMoras = obtenerMapaMoras();
    const tbody = $('#tbl-detalle-cuotas tbody');
    tbody.empty();

    if (resetFormulario) {
        cuotasSeleccionadas = [];
    }

    let hayCuotasActivas = false;

    cuotas.forEach(cuota => {

        const esCancelada = cuota.estado === 'CANCELADO';

        if (!esCancelada) {
            hayCuotasActivas = true;
        }

        const esSeleccionada = cuotasSeleccionadas.includes(cuota.id_cobro_instalacion);
        const claseFila = esSeleccionada ? 'table-success' : '';

        const valorMora = mapaMoras[cuota.id_cobro_instalacion] || '';
        const estadoColor = esCancelada ? 'text-success' : 'text-danger';

        tbody.append(`
            <tr class="${claseFila} ${esCancelada ? 'table-secondary' : ''}">
                <td>${cuota.numero_cuota}</td>
                <td>${cuota.descripcion}</td>
                <td>${formatearFecha(cuota.fecha_vencimiento)}</td>
                <td>${formatearFecha(cuota.fecha_pago)}</td>
                <td>${formatearMonto(cuota.monto_cuota)}</td>
                <td>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0"
                        class="form-control input-mora"
                        data-id="${cuota.id_cobro_instalacion}"
                        value="${valorMora}"
                        ${esCancelada ? 'disabled readonly' : ''}
                        style="${esCancelada ? 'background:#f1f1f1; cursor:not-allowed;' : ''}"
                    >
                </td>

                <td class="${estadoColor}">
                    ${cuota.estado || '-'}
                </td>
            </tr>
        `);
    });

    // 🔥 CONTROL DEL MONTO GLOBAL
    if (!hayCuotasActivas) {
        inputs.montoPago.val('');
        inputs.montoPago.prop('disabled', true);
        inputs.btnValidarCobro.prop('disabled', true);
        inputs.btnProcesarPago.prop('disabled', true);
    } else {
        inputs.montoPago.prop('disabled', false);
        inputs.btnValidarCobro.prop('disabled', false);
    }

    if (resetFormulario) {
        // inputs.montoPago.val('');
        $('.monto-ocultar').hide();
        $('#btn-validar-cobro').hide();
        $('.input-mora').prop('disabled', true)
        // $('.input-mora').val('');
        limpiarEstadoValidacion();
    }
}

function registrarPago() {
    if (!validacionLista) {
        alertaError('Primero debe validar el cobro');
        return;
    }

    const moras = obtenerMorasPorCuota();

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
            moras: JSON.stringify(moras)
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                Swal.close();
                alertaOk(response.mensaje || 'Pago aplicado correctamente');
                $('#modal-cobro-cuotas').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 800);
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

function generarFacturas() {

    let interval; // 👈 lo declaras fuera

    Swal.fire({
        title: 'Generando facturas...',
        html: 'Iniciando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();

            let mensajes = [
                'Validando contratos...',
                'Calculando cuotas...',
                'Aplicando recargos...',
                'Generando facturas...',
                'Finalizando...'
            ];

            let i = 0;

            interval = setInterval(() => {
                if (i < mensajes.length) {
                    Swal.update({ html: mensajes[i] });
                    i++;
                } else {
                    clearInterval(interval);
                }
            }, 800);
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + 'generarFacturasCobros',
        dataType: 'json',

        success: function (response) {

            clearInterval(interval); // 🔥 detener siempre
            if (response.status === 'success') {

                Swal.close();

                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.data
                }).then(() => {
                    location.reload(); // 🔥 recarga después de OK
                });

            } else {

                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje || 'No se pudieron generar las facturas'
                });
            }
        },

        error: function () {

            clearInterval(interval); // 🔥 detener en error

            Swal.close();
            alertEnSweet('error', 'Ups..', 'Ocurrió un error al generar las facturas');
        }
    });
}

function imprimirFacturasPeriodoActivo() {
    const ventana = window.open(
        baseURL + 'imprimirFacturasCobroPeriodoActivo?autoPrint=1',
        '_blank'
    );

    if (!ventana) {
        alertaError('El navegador bloqueó la ventana de impresión. Permite ventanas emergentes e inténtalo nuevamente.');
        return;
    }

    ventana.focus();
}

function eventosUsuarios() {
    // evento que al seleccionar un cliente del select se listaran sus solicitudes
    inputs.buscarCliente.on('change', function () {
        const idCliente = $(this).val();

        if (idCliente) {
            cargarDetalleCobro(idCliente);
        }
    });

    // 🔥 cuando limpia con la "X" (Select2)
    inputs.buscarCliente.on('select2:clear', function () {
        const tbody = $('#tbl-cuentas-cobro tbody');

        tbody.empty();
    });

    // evento para cobrar y abrir el modal con la informacion de la solicitud
    $(document).on('click', '.btn-ver-cuotas-modal', function () {
        if (!detalleCobroActual || detalleCobroActual.length === 0) {
            alertaError('No hay detalle para mostrar');
            return;
        }

        const index = $(this).data('index');
        detalleSeleccionado = detalleCobroActual[index];

        if (!detalleSeleccionado) {
            alertaError('No se encontró el detalle seleccionado');
            return;
        }

        // console.log(detalleSeleccionado);
        renderizarModal(detalleSeleccionado, true); // ✅ SOLO UNO
        $('#modal-cobro-cuotas').modal('show');
    });

    inputs.montoPago.on('input', limpiarEstadoValidacion);

    inputs.btnValidarCobro.on('click', validarCobro);
    inputs.btnProcesarPago.on('click', registrarPago);

    $(document).on('click', '.btn-ver-factura-cobro-intalacion-pdf', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        // abrir PDF en nueva pestaña
        window.open(baseURL + 'facturaCobroInstalacion/' + id, '_blank');
    });


    // eventos para generar las facturas
    $("#btn-generar-facturas").on("click", function () {
        generarFacturas();
    });

    // eventos para imprimir las facturas
    $("#btn-imprimir-facturas-periodo").on("click", function () {
        imprimirFacturasPeriodoActivo();
    });
}

function iniciarTodo() {
    cargarClientes();
    eventosUsuarios();
    cargarTablaCobros();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
