import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo, colorEnInputConFocusSelect, eliminarColorYfocusSelect } from "../metodos/metodos.js";


function eventosUsuarios() {
    // EXPORAR
    $("#btnExportarExcel").on("click", function () {
        window.location.href = baseURL + 'facturas/exportar-excel';
    });

    // IMPORTAR
    $("#btnImportarExcel").on("click", function () {

        const fileInput = document.getElementById("inputExcelPagos");

        if (!fileInput.files.length) {
            alert("Debes seleccionar un archivo Excel");
            return;
        }

        let formData = new FormData();
        formData.append("excel", fileInput.files[0]);

        fetch(baseURL + 'facturas/importar-excel', {
            method: 'POST',
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error('Error en la respuesta del servidor');
                return res.json();
            })
            .then(data => {

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
                        icon: data.errores && data.errores.length > 0 ? 'warning' : 'success',
                        title: 'Resultado de importación',
                        html: `
                <p><b>Procesados:</b> ${data.procesados ?? 0}</p>
                ${erroresHtml}
            `,
                        width: 600,
                        confirmButtonText: 'Aceptar'
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error desconocido'
                    });
                }

            })
            .catch(error => {
                console.error(error);

                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'No se pudo procesar el archivo'
                });
            });

    });

    $("#btnCancelarImportacionExcel").on("click", function () {
        Swal.fire({
            icon: "warning",
            title: "Cancelar importacion",
            html: `
                <p>Se revertiran los cambios hechos por la importacion del Excel en el periodo activo.</p>
                <p class="mb-0"><b>Esto restaurara estados de facturas, pagos y cobros relacionados.</b></p>
            `,
            showCancelButton: true,
            confirmButtonText: "Si, cancelar importacion",
            cancelButtonText: "No"
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            fetch(baseURL + 'facturas/cancelar-importacion-excel', {
                method: 'POST'
            })
                .then(res => {
                    if (!res.ok) throw new Error('Error en la respuesta del servidor');
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo revertir',
                            text: data.message || 'No fue posible cancelar la importacion'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Importacion revertida',
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
                        text: 'No se pudieron revertir los cambios de la importacion'
                    });
                });
        });
    });
}

function iniciarTodo() {
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
