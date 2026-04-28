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

function generarReporte() {
    const periodo = $("#periodo").val();
    const tipo = $("#tipo").val();
    const search = $("#searchReporteFactura").val().trim();

    if (!periodo) {
        alertaError("Debe seleccionar un período");
        return;
    }

    const params = new URLSearchParams({
        periodo,
        tipo
    });

    if (search) {
        params.append("search", search);
    }

    $("#visorPDFFacturas").attr("src", `${baseURL}reporte-facturas/pdf?${params.toString()}`);
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
    cargarPeriodos();
    eventosUsuarios();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
