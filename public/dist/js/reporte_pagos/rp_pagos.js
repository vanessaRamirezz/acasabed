import { alertaError, alertEnSweet } from "../metodos/metodos.js";

function cargarPeriodos() {
    $("#periodo").select2({
        placeholder: "Busque y seleccione un período",
        allowClear: true,
        theme: "bootstrap4",
        ajax: {
            url: baseURL + "getPeriodosReporteSelect",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (response) {
                return {
                    results: response.data || []
                };
            },
            cache: true
        }
    });
}

async function generarReporte() {
    const periodo = $("#periodo").val();
    const estado = $("#estado").val();

    const params = new URLSearchParams();

    // =======================
    // VALIDACIÓN
    // =======================


    if (!periodo || periodo === "") {
        alertaError("Debe seleccionar un período.");
        return;
    }

    if (!estado || estado === "") {
        alertaError("Debe seleccionar un estado de facturas.");
        return;
    }

    if (periodo) {
        params.append("periodo", periodo);
    }

    if (estado) {
        params.append("estado", estado);
    }

    const iframe = document.getElementById("visorPDF");
    const message = document.getElementById("pdfMessage");
    const loading = document.getElementById("loadingPagos");

    message.style.display = "none";
    iframe.style.display = "none";
    loading.style.display = "flex";

    try {
        const response = await fetch(
            `${baseURL}reporte-pagos/pdf?${params.toString()}`
        );

        if (!response.ok) {
            throw new Error("No fue posible generar el reporte");
        }

        const blob = await response.blob();

        if (!blob.type.includes("pdf")) {
            throw new Error("El servidor no devolvió un PDF válido");
        }

        const pdfUrl = URL.createObjectURL(blob);

        iframe.onload = () => {
            loading.style.display = "none";
            iframe.style.display = "block";
        };

        iframe.src = pdfUrl;
    } catch (error) {
        loading.style.display = "none";

        alertEnSweet(
            "error",
            "Error",
            error.message || "Ocurrió un error al generar el reporte"
        );

        message.style.display = "flex";

        console.error(error);
    }

}

async function generarReporteExcel() {

    const periodo = $("#periodo").val();

    if (!periodo) {
        alertaError("Debe seleccionar un período.");
        return;
    }

    const params = new URLSearchParams();
    params.append("periodo", periodo);

    try {

        const response = await fetch(
            `${baseURL}reporte-pagos/excel?${params.toString()}`
        );

        if (!response.ok) {
            throw new Error("No fue posible generar el archivo Excel.");
        }

        const blob = await response.blob();

        const url = window.URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.href = url;
        link.download = "Reporte_Pagos.xlsx";

        document.body.appendChild(link);
        link.click();

        link.remove();

        window.URL.revokeObjectURL(url);

    } catch (error) {

        alertEnSweet(
            "error",
            "Error",
            error.message || "No fue posible generar el Excel."
        );

        console.error(error);
    }

}

function eventosUsuarios() {
    $("#btnGenerarReportePagos").on("click", generarReporte);

    $("#btnGenerarReportePagosExcel").on("click", generarReporteExcel);
}

function iniciarTodo() {
    $.fn.select2.defaults.set("width", "100%");
    cargarPeriodos();
    eventosUsuarios();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);