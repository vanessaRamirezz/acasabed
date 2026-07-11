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

                        showCancelButton: data.hayDiferencias,
                        confirmButtonText: data.hayDiferencias ? 'Descargar Excel' : 'Aceptar',
                        cancelButtonText: 'Cerrar'

                    }).then((result) => {

                        fileInput.value = "";

                        if (result.isConfirmed && data.hayDiferencias) {

                            window.open(
                                baseURL + 'facturas/descargarExcelDiferencias/' + data.archivoDiferencias,
                                '_blank'
                            );

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
                    });
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
}

function iniciarTodo() {
    eventosUsuarios();
    validarExcelCargado();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
