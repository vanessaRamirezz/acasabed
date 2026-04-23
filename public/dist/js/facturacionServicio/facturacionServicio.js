import { alertaError, alertaOk, alertEnSweet } from "../metodos/metodos.js";

let tablaFacturacionServicio;
// let validacionLista = false;
// let detalleFacturacionActual = null;
// let detalleSeleccionado = null;

// const inputs = {
//     buscarCliente: $("#buscar-cliente-servicio"),
//     idFactura: $("#id-factura-servicio"),
//     montoPago: $("#monto-pago-servicio"),
//     btnValidar: $("#btn-validar-factura-servicio"),
//     btnProcesar: $("#btn-procesar-factura-servicio"),
// };

function formatearFecha(fecha) {
    if (!fecha) return "-";

    const f = new Date(fecha);
    const dia = String(f.getDate()).padStart(2, "0");
    const mes = String(f.getMonth() + 1).padStart(2, "0");
    const anio = f.getFullYear();

    return `${dia}-${mes}-${anio}`;
}

// function formatearMonto(valor) {
//     const numero = parseFloat(valor || 0);
//     return `$${numero.toFixed(2)}`;
// }

function renderEstado(estado) {
    if (estado === "PAGADA" || estado === "PAGADA VENCIDA") {
        return `<span class="badge badge-success">${estado}</span>`;
    }

    if (estado === "VENCIDA") {
        return `<span class="badge badge-danger">${estado}</span>`;
    }

    if (estado === "SALDO TRASLADADO") {
        return `<span class="badge badge-secondary">${estado}</span>`;
    }

    return `<span class="badge badge-warning">${estado || "-"}</span>`;
}

// function limpiarEstadoValidacion() {
//     validacionLista = false;
//     $("#contenedor-validacion-servicio").hide().removeClass("alert-success alert-danger").empty();
//     inputs.btnProcesar.hide();
// }

function cargarTablaFacturas() {
    tablaFacturacionServicio = $("#tbl-facturacion-servicio").DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: "GET",
            url: baseURL + "getFacturasServicio",
            data: function (d) {
                d.searchValue = $("#customSearchFacturasServicio").val();
            }
        },
        columns: [
            { data: "correlativo" },
            { data: "numero_contrato" },
            { data: "cliente" },
            { data: "periodo" },
            {
                data: "fecha_emision",
                render: data => formatearFecha(data)
            },
            {
                data: "fecha_vencimiento",
                render: data => formatearFecha(data)
            },
            {
                data: "estado",
                render: data => renderEstado(data)
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

                                <a class="dropdown-item dropdown-item-custom btn-ver-factura-cobro-servicio-pdf" href="#"
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

    $("#customSearchFacturasServicio").on("keypress", function (e) {
        if (e.which === 13) {
            tablaFacturacionServicio.draw();
        }
    });

    $("#searchBtnFacturasServicio").off("click").on("click", function () {
        tablaFacturacionServicio.draw();
    });

    $("#clearSearchBtnFacturasServicio").off("click").on("click", function () {
        $("#customSearchFacturasServicio").val("");
        tablaFacturacionServicio.draw();
    });
}

// function cargarClientes() {
//     inputs.buscarCliente.select2({
//         placeholder: "Busque y seleccione",
//         allowClear: true,
//         theme: "bootstrap4",
//         ajax: {
//             url: baseURL + "getClientesSelect",
//             dataType: "json",
//             delay: 250,
//             data: function (params) {
//                 return { q: params.term };
//             },
//             processResults: function (response) {
//                 return {
//                     results: response.data.map(cliente => ({
//                         id: cliente.id_cliente,
//                         text: cliente.nombre_completo,
//                     }))
//                 };
//             },
//             cache: true
//         }
//     });
// }

// function renderizarTablaFacturasCliente(detalles) {
//     const tbody = $("#tbl-cuentas-facturacion-servicio tbody");

//     if (!detalles || detalles.length === 0) {
//         tbody.html(`
//             <tr>
//                 <td colspan="7" class="text-center">No hay facturas pendientes</td>
//             </tr>
//         `);
//         return;
//     }

//     let html = "";

//     detalles.forEach((detalle, index) => {
//         const resumen = detalle.resumen || {};

//         html += `
//             <tr>
//                 <td>${resumen.codigo_solicitud || "-"}</td>
//                 <td>${resumen.numero_contrato || "-"}</td>
//                 <td>${resumen.cliente || "-"}</td>
//                 <td>${resumen.periodo || "-"}</td>
//                 <td>${formatearMonto(resumen.saldo_pendiente)}</td>
//                 <td>${renderEstado(resumen.estado)}</td>
//                 <td>
//                     <button
//                         type="button"
//                         class="btn btn-info btn-sm btn-ver-factura-servicio-modal"
//                         data-index="${index}"
//                     >
//                         <i class="fas fa-eye"></i>
//                     </button>
//                 </td>
//             </tr>
//         `;
//     });

//     tbody.html(html);
// }

// function cargarDetalleFacturacion(idCliente) {
//     $.ajax({
//         type: "GET",
//         url: baseURL + "getDetalleFacturaClienteServicio",
//         data: { idCliente },
//         dataType: "json",
//         success: function (response) {
//             if (response.status === "success") {
//                 detalleFacturacionActual = response.data;
//                 renderizarTablaFacturasCliente(response.data);
//             } else {
//                 alertaError(response.mensaje || "No se pudo cargar la facturacion pendiente");
//             }
//         },
//         error: function () {
//             alertEnSweet("error", "Ups..", "Ocurrió un error al cargar el detalle");
//         }
//     });
// }

// function renderizarModal(detalle, resetFormulario) {
//     const resumen = detalle.resumen || {};
//     const facturas = detalle.facturas || [];
//     const tbody = $("#tbl-detalle-facturas-servicio tbody");

//     inputs.idFactura.val(resumen.id_factura || "");

//     $("#resumen-servicio-cliente").text(resumen.cliente || "-");
//     $("#resumen-servicio-solicitud").text(resumen.codigo_solicitud || "-");
//     $("#resumen-servicio-contrato").text(resumen.numero_contrato || "-");
//     $("#resumen-servicio-medidor").text(resumen.numero_serie || "-");
//     $("#resumen-servicio-saldo").text(formatearMonto(resumen.saldo_pendiente));
//     $("#resumen-servicio-periodo").text(resumen.periodo || "-");
//     $("#resumen-servicio-vencimiento").text(formatearFecha(resumen.fecha_vencimiento));
//     $("#resumen-servicio-cadena").text(resumen.facturas_pendientes || 0);

//     tbody.empty();

//     if (facturas.length === 0) {
//         tbody.html(`
//             <tr>
//                 <td colspan="10" class="text-center">No hay detalle de facturas</td>
//             </tr>
//         `);
//     }

//     facturas.forEach(factura => {
//         tbody.append(`
//             <tr>
//                 <td>${factura.correlativo || "-"}</td>
//                 <td>${factura.periodo || "-"}</td>
//                 <td>${formatearFecha(factura.fecha_emision)}</td>
//                 <td>${formatearFecha(factura.fecha_vencimiento)}</td>
//                 <td>${parseFloat(factura.consumo_mes || 0).toFixed(2)}</td>
//                 <td>${formatearMonto(factura.monto_factura)}</td>
//                 <td>${formatearMonto(factura.saldo_anterior)}</td>
//                 <td>${formatearMonto(factura.mora)}</td>
//                 <td>${formatearMonto(factura.saldo_pendiente)}</td>
//                 <td>${renderEstado(factura.estado)}</td>
//             </tr>
//         `);
//     });

//     if (resetFormulario) {
//         inputs.montoPago.val("");
//         limpiarEstadoValidacion();
//     }
// }

// function validarFormularioPago() {
//     const idFactura = inputs.idFactura.val().trim();
//     const montoPago = parseFloat(inputs.montoPago.val().trim() || "0");

//     if (!idFactura) {
//         alertaError("Debe seleccionar una factura vigente");
//         return false;
//     }

//     if (!montoPago || montoPago <= 0) {
//         alertaError("Debe ingresar un monto válido");
//         return false;
//     }

//     return true;
// }

// function validarPagoFactura() {
//     if (!validarFormularioPago()) {
//         return;
//     }

//     $.ajax({
//         type: "POST",
//         url: baseURL + "validarPagoFacturaServicio",
//         data: {
//             idFactura: inputs.idFactura.val().trim(),
//             montoPago: inputs.montoPago.val().trim()
//         },
//         dataType: "json",
//         success: function (response) {
//             if (response.status === "success") {
//                 const data = response.data;

//                 validacionLista = true;
//                 $("#contenedor-validacion-servicio")
//                     .show()
//                     .removeClass("alert-danger")
//                     .addClass("alert-success")
//                     .html(`
//                         Validacion correcta.
//                         Factura vigente: <strong>${data.idFactura}</strong>.
//                         Monto a cancelar: <strong>${formatearMonto(data.montoPago)}</strong>.
//                     `);

//                 inputs.btnProcesar.show();
//             } else {
//                 validacionLista = false;
//                 inputs.btnProcesar.hide();

//                 $("#contenedor-validacion-servicio")
//                     .show()
//                     .removeClass("alert-success")
//                     .addClass("alert-danger")
//                     .text(response.mensaje || "La validacion no fue correcta");
//             }
//         },
//         error: function () {
//             alertEnSweet("error", "Ups..", "Ocurrió un error al validar el pago");
//         }
//     });
// }

// function registrarPagoFactura() {
//     if (!validacionLista) {
//         alertaError("Primero debe validar el pago");
//         return;
//     }

//     Swal.fire({
//         title: "Espere...",
//         html: "Procesando pago...",
//         allowEscapeKey: false,
//         allowOutsideClick: false,
//         didOpen: () => Swal.showLoading()
//     });

//     $.ajax({
//         type: "POST",
//         url: baseURL + "registrarPagoFacturaServicio",
//         data: {
//             idFactura: inputs.idFactura.val().trim(),
//             montoPago: inputs.montoPago.val().trim()
//         },
//         dataType: "json",
//         success: function (response) {
//             Swal.close();

//             if (response.status === "success") {
//                 alertaOk(response.mensaje || "Pago registrado correctamente");
//                 $("#modal-pago-factura-servicio").modal("hide");

//                 setTimeout(() => {
//                     location.reload();
//                 }, 800);
//             } else {
//                 alertaError(response.mensaje || "No se pudo registrar el pago");
//             }
//         },
//         error: function () {
//             Swal.close();
//             alertEnSweet("error", "Ups..", "Ocurrió un error al registrar el pago");
//         }
//     });
// }

function generarFacturasServicio() {
    let interval;

    Swal.fire({
        title: "Generando facturas...",
        html: "Iniciando...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();

            const mensajes = [
                "Validando lecturas...",
                "Calculando consumo mensual...",
                "Trasladando saldos pendientes...",
                "Generando facturas del servicio...",
                "Finalizando..."
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
        type: "POST",
        url: baseURL + "generarFacturasServicio",
        dataType: "json",
        success: function (response) {
            clearInterval(interval);
            Swal.close();

            if (response.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: response.data
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response.mensaje || "No se pudieron generar las facturas"
                });
            }
        },
        error: function () {
            clearInterval(interval);
            Swal.close();
            alertEnSweet("error", "Ups..", "Ocurrió un error al generar las facturas");
        }
    });
}

function eventosUsuarios() {
    // inputs.buscarCliente.on("change", function () {
    //     const idCliente = $(this).val();

    //     if (idCliente) {
    //         cargarDetalleFacturacion(idCliente);
    //     }
    // });

    // inputs.buscarCliente.on("select2:clear", function () {
    //     $("#tbl-cuentas-facturacion-servicio tbody").empty();
    // });

    // $(document).on("click", ".btn-ver-factura-servicio-modal", function () {
    //     if (!detalleFacturacionActual || detalleFacturacionActual.length === 0) {
    //         alertaError("No hay detalle para mostrar");
    //         return;
    //     }

    //     const index = $(this).data("index");
    //     detalleSeleccionado = detalleFacturacionActual[index];

    //     if (!detalleSeleccionado) {
    //         alertaError("No se encontró el detalle seleccionado");
    //         return;
    //     }

    //     renderizarModal(detalleSeleccionado, true);
    //     $("#modal-pago-factura-servicio").modal("show");
    // });

    // inputs.montoPago.on("input", limpiarEstadoValidacion);
    // inputs.btnValidar.on("click", validarPagoFactura);
    // inputs.btnProcesar.on("click", registrarPagoFactura);

    //evento para generar las facturas
    $("#btn-generar-facturas-servicio").on("click", function () {
        generarFacturasServicio();
    });

    // eventos para ver una factura
    $(document).on('click', '.btn-ver-factura-cobro-servicio-pdf', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        // abrir PDF en nueva pestaña
        window.open(baseURL + 'facturaCobroServicio/' + id, '_blank');
    });
}

function iniciarTodo() {
    // cargarClientes();
    eventosUsuarios();
    cargarTablaFacturas();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
