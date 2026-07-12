import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo, colorEnInputConFocusSelect, eliminarColorYfocusSelect } from "../metodos/metodos.js";

function validarExcelCargado() {

    $.ajax({
        url: baseURL + "validarExcelCargado",
        type: "GET",
        dataType: "json",

        success: function (response) {

            if (response.success && response.hayDatos) {

                $("#btn-cancelar-excel").removeClass("d-none");

                $("#estado-excel")
                    .removeClass("d-none alert-danger")
                    .addClass("alert-info");

                $("#estado-texto").text(
                    `Ya se importo un documento con: (${response.cantidad} registros).`
                );

            } else {
                $("#btn-cargar-excel").removeClass("d-none");
                $("#btn-cancelar-excel").addClass("d-none");
            }
        }
    });
}

function cancelarExcelAldaldia() {

    Swal.fire({
        title: '¿Está seguro?',
        text: 'Se eliminarán todos los registros temporales cargados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        heightAuto: false
    }).then((result) => {

        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: baseURL + "cancelarExcelAlcaldia",
            type: "POST",
            dataType: "json",
            beforeSend: function () {
                $("#btn-cancelar-excel").prop("disabled", true);
            },
            success: function (response) {

                if (response.success) {

                    alertEnSweet(
                        'success',
                        'Proceso cancelado',
                        response.message
                    );

                    // Opcional
                    setTimeout(() => {
                        location.reload();
                    }, 1500);

                } else {

                    alertEnSweet(
                        'error',
                        'Error',
                        response.message
                    );
                }
            },
            error: function () {

                alertEnSweet(
                    'error',
                    'Error',
                    'Ocurrió un error al procesar la solicitud.'
                );
            },
            complete: function () {
                $("#btn-cancelar-excel").prop("disabled", false);
            }
        });

    });
}

function cargarExcelAlcaldia() {
    let file = $("#input-excel")[0].files[0];

    if (!file) {
        Swal.fire("Atención", "Debes seleccionar un archivo Excel", "warning");
        return;
    }

    let formData = new FormData();
    formData.append("excel", file);

    // reset visual
    $("#estado-excel")
        .removeClass("d-none alert-success alert-danger")
        .addClass("alert-info");

    $("#estado-texto").text("Procesando archivo...");

    // $("#btn-generar-facturas-servicio").prop("disabled", true);

    $.ajax({
        url: baseURL + "cargarExcelAlcaldia",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",

        success: function (response) {

            // $("#estado-excel").removeClass("alert-info");

            if (response.status === "success") {
                $("#btn-cargar-excel").addClass("d-none");
                $("#btn-cancelar-excel").removeClass("d-none");

                $("#estado-excel").addClass("alert-success");
                $("#estado-texto").text(response.data);

                // ✅ habilita el botón
                $("#btn-generar-facturas-servicio").prop("disabled", false);

            } else {

                $("#estado-excel").addClass("alert-danger");
                $("#estado-texto").text(response.mensaje || "No se pudo validar el archivo");

                $("#btn-generar-facturas-servicio").prop("disabled", true);
            }
        },

        error: function () {

            $("#estado-excel")
                .removeClass("alert-info")
                .addClass("alert-danger");

            $("#estado-texto").text("Error al cargar el archivo");

            $("#btn-generar-facturas-servicio").prop("disabled", true);
        }
    });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatoDinero(value) {
    return `$${Number(value || 0).toFixed(2)}`;
}

function listarExcelsDiferencias() {
    const tbody = $("#tablaExcelsDiferencias tbody");

    tbody.html(`
        <tr>
            <td colspan="6" class="text-center text-muted py-4">Cargando diferencias...</td>
        </tr>
    `);

    fetch(baseURL + 'facturas/diferencias/listar')
        .then(res => res.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.message || 'No se pudo listar las diferencias');
            }

            if (!response.data.length) {
                tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay Excel de diferencias pendientes.
                        </td>
                    </tr>
                `);
                return;
            }

            tbody.html(response.data.map(item => `
                <tr>
                    <td>${escapeHtml(item.archivo)}</td>
                    <td>${escapeHtml(item.fecha)}</td>
                    <td>${item.total}</td>
                    <td>${item.resueltos}</td>
                    <td>
                        <span class="badge ${item.pendientes > 0 ? 'badge-warning' : 'badge-success'}">
                            ${item.pendientes}
                        </span>
                    </td>
                    <td class="text-center">
                        <button type="button"
                            class="btn btn-sm btn-outline-primary btnVerDiferencias"
                            data-archivo="${escapeHtml(item.archivo)}">
                            <i class="fas fa-search mr-1"></i>
                            Revisar
                        </button>
                        <a class="btn btn-sm btn-outline-secondary"
                            href="${baseURL}facturas/descargarExcelDiferencias/${encodeURIComponent(item.archivo)}"
                            target="_blank">
                            <i class="fas fa-download mr-1"></i>
                            Excel
                        </a>
                        <button type="button"
                            class="btn btn-sm btn-outline-danger btnEliminarExcelDiferencia"
                            data-archivo="${escapeHtml(item.archivo)}">
                            <i class="fas fa-trash mr-1"></i>
                            Eliminar
                        </button>
                    </td>
                </tr>
            `).join(''));
        })
        .catch(error => {
            console.error(error);
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        No se pudo cargar la bandeja de diferencias.
                    </td>
                </tr>
            `);
        });
}

function eliminarExcelDiferencia(archivo) {
    Swal.fire({
        icon: "warning",
        title: "Eliminar Excel",
        html: `
            <p>Se eliminará el archivo de diferencias:</p>
            <p class="mb-0"><b>${escapeHtml(archivo)}</b></p>
        `,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(result => {
        if (!result.isConfirmed) {
            return;
        }

        const formData = new FormData();
        formData.append("archivo", archivo);

        Swal.fire({
            title: "Eliminando...",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(baseURL + 'facturas/diferencias/eliminar', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(response => {
                Swal.close();

                if (!response.success) {
                    alertaError(response.message || 'No se pudo eliminar el Excel');
                    return;
                }

                alertaOk(response.message || 'Excel eliminado correctamente');
                $("#modalRowsDiferencias").modal("hide");
                listarExcelsDiferencias();
            })
            .catch(error => {
                console.error(error);
                Swal.close();
                alertaError('No se pudo eliminar el Excel de diferencias');
            });
    });
}

function importarExcelDiferencias() {
    const fileInput = document.getElementById("inputExcelDiferencias");

    if (!fileInput.files.length) {
        alertaError('Debes seleccionar un Excel de diferencias');
        return;
    }

    const formData = new FormData();
    formData.append("excel", fileInput.files[0]);

    Swal.fire({
        title: 'Importando diferencias...',
        html: 'Guardando el Excel para revisión',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(baseURL + 'facturas/diferencias/importar', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(response => {
            Swal.close();
            fileInput.value = "";

            if (!response.success) {
                alertaError(response.message || 'No se pudo importar el Excel');
                return;
            }

            alertaOk(response.message || 'Excel registrado correctamente');
            listarExcelsDiferencias();
        })
        .catch(error => {
            console.error(error);
            Swal.close();
            alertaError('No se pudo importar el Excel de diferencias');
        });
}

function abrirRowsDiferencias(archivo) {
    $("#archivoDiferenciasActual").text(archivo);
    $("#tablaRowsDiferencias tbody").html(`
        <tr>
            <td colspan="9" class="text-center text-muted py-4">Cargando facturas...</td>
        </tr>
    `);
    $("#modalRowsDiferencias").modal("show");

    fetch(baseURL + `facturas/diferencias/rows?archivo=${encodeURIComponent(archivo)}`)
        .then(res => res.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.message || 'No se encontraron filas');
            }

            const rows = response.rows || [];

            if (!rows.length) {
                $("#tablaRowsDiferencias tbody").html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Este Excel no tiene filas pendientes.</td>
                    </tr>
                `);
                return;
            }

            $("#tablaRowsDiferencias tbody").html(rows.map(row => {
                const resuelto = Boolean(row.resuelto);

                return `
                    <tr class="${resuelto ? 'table-light' : ''}">
                        <td>${row.id_factura}</td>
                        <td>${escapeHtml(row.correlativo)}</td>
                        <td>${escapeHtml(row.cliente)}</td>
                        <td>${escapeHtml(row.nombre)}</td>
                        <td>${formatoDinero(row.total_excel)}</td>
                        <td>${formatoDinero(row.total_bd)}</td>
                        <td>${escapeHtml(row.fecha_pago || '-')}</td>
                        <td>
                            <span class="badge ${resuelto ? 'badge-success' : 'badge-warning'}">
                                ${resuelto ? 'Resuelto' : 'Pendiente'}
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                class="btn btn-sm btn-primary btnResolverDiferencia"
                                data-archivo="${escapeHtml(response.archivo)}"
                                data-row-id="${row.row_id}"
                                data-id-factura="${row.id_factura}"
                                ${resuelto ? 'disabled' : ''}>
                                Resolver
                            </button>
                        </td>
                    </tr>
                `;
            }).join(''));
        })
        .catch(error => {
            console.error(error);
            $("#tablaRowsDiferencias tbody").html(`
                <tr>
                    <td colspan="9" class="text-center text-danger py-4">
                        No se pudo cargar el detalle del Excel.
                    </td>
                </tr>
            `);
        });
}

function calcularTotalDetalleDiferencia() {
    let total = 0;

    $("#tablaDetalleDiferencia tbody tr").each(function () {
        const monto = Number($(this).find(".difDetalleMonto").val() || 0);
        total += monto;
    });

    $("#difTotalDetalle").text(`Total detalle: ${formatoDinero(total)}`);
}

function abrirFacturaDiferencia(archivo, rowId, idFactura) {
    Swal.fire({
        title: 'Cargando factura...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(baseURL + `facturas/diferencias/factura?archivo=${encodeURIComponent(archivo)}&rowId=${rowId}&idFactura=${idFactura}`)
        .then(res => res.json())
        .then(response => {
            Swal.close();

            if (!response.success) {
                alertaError(response.message || 'No se pudo cargar la factura');
                return;
            }

            const factura = response.data.factura;
            const pago = response.data.pago || {};
            const row = response.row;

            $("#difArchivo").val(archivo);
            $("#difRowId").val(rowId);
            $("#difIdFactura").val(idFactura);
            $("#difTotalExcel").text(formatoDinero(row.total_excel));
            $("#difTotalBd").text(formatoDinero(row.total_bd));
            $("#difCliente").text(`${factura.codigo_cliente || ''} - ${factura.cliente || ''}`);
            $("#resumenFacturaDiferencia").text(`Factura ${factura.tiraje ? factura.tiraje + '-' : ''}${factura.correlativo || ''} | Contrato ${factura.numero_contrato || '-'}`);
            $("#difEstado").val(factura.estado || 'PENDIENTE');
            $("#difTotal").val(Number(factura.total || row.total_excel || 0).toFixed(2));
            $("#difMontoPagado").val(Number(pago.monto_pagado || row.total_excel || factura.total || 0).toFixed(2));
            $("#difFechaPago").val((factura.fecha_de_pago || pago.fecha_pago || row.fecha_pago || '').substring(0, 10));

            $("#tablaDetalleDiferencia tbody").html((response.data.detalle || []).map(detalle => {
                const esMora = Number(detalle.mora || 0) > 0
                    || String(detalle.concepto || '').toUpperCase().includes('MORA');
                const monto = esMora ? detalle.mora : detalle.monto;

                return `
                <tr data-id-detalle="${detalle.id_factura_detalle}" data-es-mora="${esMora ? '1' : '0'}">
                    <td>
                        <input type="text"
                            class="form-control form-control-sm difDetalleConcepto"
                            value="${escapeHtml(detalle.concepto)}">
                    </td>
                    <td>
                        <input type="number"
                            step="0.01"
                            min="0"
                            class="form-control form-control-sm detalle-diferencia-input difDetalleMonto"
                            value="${Number(monto || 0).toFixed(2)}">
                    </td>
                </tr>
                `;
            }).join(''));

            calcularTotalDetalleDiferencia();
            $("#modalResolverDiferencia").modal("show");
        })
        .catch(error => {
            console.error(error);
            Swal.close();
            alertaError('No se pudo cargar la factura');
        });
}

function guardarDiferenciaFactura() {
    const detalles = [];

    $("#tablaDetalleDiferencia tbody tr").each(function () {
        detalles.push({
            id_factura_detalle: $(this).data("id-detalle"),
            concepto: $(this).find(".difDetalleConcepto").val(),
            monto: Number($(this).find(".difDetalleMonto").val() || 0),
            es_mora: String($(this).data("es-mora")) === "1"
        });
    });

    const estado = $("#difEstado").val();

    if (estado === 'PAGADA' && !$("#difFechaPago").val()) {
        alertaError('Debes indicar la fecha de pago');
        return;
    }

    const formData = new FormData();
    formData.append("archivo", $("#difArchivo").val());
    formData.append("rowId", $("#difRowId").val());
    formData.append("idFactura", $("#difIdFactura").val());
    formData.append("estado", estado);
    formData.append("total", $("#difTotal").val());
    formData.append("fechaPago", $("#difFechaPago").val());
    formData.append("montoPagado", $("#difMontoPagado").val());
    formData.append("detalles", JSON.stringify(detalles));

    Swal.fire({
        title: 'Guardando cambios...',
        html: 'Actualizando factura, detalle y pago',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(baseURL + 'facturas/diferencias/resolver', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(response => {
            Swal.close();

            if (!response.success) {
                alertaError(response.message || 'No se pudo resolver la diferencia');
                return;
            }

            $("#modalResolverDiferencia").modal("hide");
            alertaOk(response.message || 'Diferencia resuelta correctamente');

            const archivo = $("#difArchivo").val();
            listarExcelsDiferencias();

            if (response.archivoEliminado) {
                $("#modalRowsDiferencias").modal("hide");
            } else {
                abrirRowsDiferencias(archivo);
            }
        })
        .catch(error => {
            console.error(error);
            Swal.close();
            alertaError('No se pudo resolver la diferencia');
        });
}

function eventosUsuarios() {
    // EXPORAR
    $("#btnExportarExcel").on("click", function () {
        window.location.href = baseURL + 'facturas/exportar-excel';
    });

    $("#btnExportarExcelAlcaldia").on("click", function () {
        window.location.href = baseURL + 'facturas/exportar-excel-alcaldia';
    });

    // IMPORTAR
    $("#btnImportarExcel").on("click", function () {

        const fileInput = document.getElementById("inputExcelPagos");

        if (!fileInput.files.length) {
            alertaError('Debes seleccionar un archivo Excel');
            return;
        }

        let formData = new FormData();
        formData.append("excel", fileInput.files[0]);

        // Loader
        Swal.fire({
            title: 'Importando Excel...',
            html: 'Procesando archivo, por favor espera',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(baseURL + 'facturas/importar-excel', {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return res.json();
            })
            .then(data => {

                // Cerrar únicamente el loader
                Swal.close();

                if (data.success) {

                    let erroresHtml = "";

                    if (data.errores && data.errores.length > 0) {
                        erroresHtml = `
                        <div style="text-align:left; max-height:200px; overflow:auto; margin-top:10px;">
                            <b>Errores:</b>
                            <ul style="margin-top:5px;">
                                ${data.errores.map(e => `<li>${e}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                    }

                    Swal.fire({
                        icon: (data.errores && data.errores.length > 0) ? 'warning' : 'success',
                        title: 'Resultado de importación',
                        html: `
                        <p><b>Procesados:</b> ${data.procesados ?? 0}</p>
                        ${erroresHtml}
                    `,
                        width: 600,
                        allowOutsideClick: false,
                        allowEscapeKey: false,

                        // showCancelButton: data.hayDiferencias,
                        confirmButtonText: data.hayDiferencias ? 'Descargar Excel' : 'Aceptar'
                        // cancelButtonText: 'Cerrar'

                    }).then((result) => {

                        fileInput.value = "";

                        if (result.isConfirmed && data.hayDiferencias) {

                            window.open(
                                baseURL + 'facturas/descargarExcelDiferencias/' + data.archivoDiferencias,
                                '_blank'
                            );

                        }

                        if (data.hayDiferencias) {
                            listarExcelsDiferencias();
                        }

                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error desconocido',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Limpiar el input al cerrar el mensaje
                        fileInput.value = "";
                    });

                }

            })
            .catch(error => {

                console.error(error);

                // Cerrar únicamente el loader
                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'No se pudo procesar el archivo',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Limpiar el input al cerrar el mensaje
                    fileInput.value = "";
                });
            });
    });

    $("#btnCancelarImportacionExcel").on("click", function () {
        const tbody = $("#tablaExcelsDiferencias tbody");
        Swal.fire({
            icon: "warning",
            title: "Cancelar importación",
            html: `
            <p>Se revertirán los cambios hechos por la importación del Excel en el periodo activo.</p>
            <p class="mb-0"><b>Esto restaurará estados de facturas, pagos y cobros relacionados.</b></p>
        `,
            showCancelButton: true,
            confirmButtonText: "Sí, cancelar importación",
            cancelButtonText: "No"
        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            // Modal de carga
            Swal.fire({
                title: 'Procesando...',
                html: 'Revirtiendo cambios de la importación',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(baseURL + 'facturas/cancelar-importacion-excel', {
                method: 'POST'
            })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }

                    return res.json();
                })
                .then(data => {

                    if (!data.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo revertir',
                            text: data.message || 'No fue posible cancelar la importación'
                        });

                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Importación revertida',
                        html: `
                                <p><b>Facturas revertidas:</b> ${data.facturas_revertidas ?? 0}</p>
                                <p class="mb-0"><b>Pagos eliminados:</b> ${data.pagos_eliminados ?? 0}</p>
                            `
                    }).then(() => {
                        tbody.html(`
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No hay Excel de diferencias pendientes.
                                </td>
                            </tr>
                        `);
                    })


                })
                .catch(error => {

                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error inesperado',
                        text: 'No se pudieron revertir los cambios de la importación'
                    });
                });
        });
    });

    //evento para cargar el excel
    $("#btn-cargar-excel").on("click", function () {
        cargarExcelAlcaldia();
    });

    // Evento para cancelar el excel
    $("#btn-cancelar-excel").on("click", function () {
        cancelarExcelAldaldia();
    });

    $("#btnRecargarDiferencias").on("click", function () {
        listarExcelsDiferencias();
    });

    $("#btnImportarExcelDiferencias").on("click", function () {
        importarExcelDiferencias();
    });

    $(document).on("click", ".btnVerDiferencias", function () {
        abrirRowsDiferencias($(this).data("archivo"));
    });

    $(document).on("click", ".btnEliminarExcelDiferencia", function () {
        eliminarExcelDiferencia($(this).data("archivo"));
    });

    $(document).on("click", ".btnResolverDiferencia", function () {
        abrirFacturaDiferencia(
            $(this).data("archivo"),
            $(this).data("row-id"),
            $(this).data("id-factura")
        );
    });

    $(document).on("input", ".difDetalleMonto", function () {
        calcularTotalDetalleDiferencia();
    });

    $("#btnGuardarDiferenciaFactura").on("click", function () {
        guardarDiferenciaFactura();
    });
}

function iniciarTodo() {
    eventosUsuarios();
    validarExcelCargado();
    listarExcelsDiferencias();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
