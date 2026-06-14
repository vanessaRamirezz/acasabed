import { alertaError } from "../metodos/metodos.js";

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
    const tipo = $("#tipo").val();
    const search = $("#searchReporteFactura").val().trim();

    const params = new URLSearchParams({ tipo });

    if (periodo === null || periodo === '') {
        alertaError('Debe seleccionar un período');
        return;
    }

    if (periodo) {
        params.append("periodo", periodo);
    }

    if (search) {
        params.append("search", search);
    }

    const iframe = document.getElementById("visorPDF");
    const message = document.getElementById("pdfMessage");
    const loading = document.getElementById("loadingFacturas");

    message.style.display = "none";
    iframe.style.display = "none";
    loading.style.display = "flex";

    try {

        const response = await fetch(
            `${baseURL}reporte-facturas/pdf?${params.toString()}`
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

function eventosUsuarios() {
    $("#btnGenerarReporteFacturas").on("click", generarReporte);

    $("#searchReporteFactura").on("keypress", function (e) {
        if (e.which === 13) {
            generarReporte();
        }
    });
}

function iniciarTodo() {
    $.fn.select2.defaults.set("width", "100%");
    cargarPeriodos();
    eventosUsuarios();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);