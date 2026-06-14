function cargarPeriodosResumen() {
    const $periodo = $("#periodoResumenFacturas");
    const activeId = $periodo.data("active-id");
    const activeText = $periodo.data("active-text");

    $periodo.select2({
        placeholder: "Busque y seleccione un período",
        allowClear: true,
        theme: "bootstrap4",
        ajax: {
            url: baseURL + "getPeriodosResumenFacturasSelect",
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

    if (activeId && activeText) {
        const option = new Option(activeText, activeId, true, true);
        $periodo.append(option).trigger("change");
    }
}

async function generarReporteResumenFacturas() {

    const periodo = $("#periodoResumenFacturas").val();
    const tipo = $("#tipoResumenFacturas").val();

    const params = new URLSearchParams({ tipo });

    if (periodo) {
        params.append("periodo", periodo);
    }

    const iframe = document.getElementById("visorPDFResumenFacturas");
    const message = document.getElementById("pdfMessageResumenFacturas");
    const loading = document.getElementById("loadingResumenFacturas");

    message.style.display = "none";
    iframe.style.display = "none";
    loading.style.display = "flex";

    try {

        const response = await fetch(
            `${baseURL}reporte-resumen-facturas/pdf?${params.toString()}`
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
    }
}

function eventosUsuarios() {
    $("#btnGenerarResumenFacturas").on("click", generarReporteResumenFacturas);
}

function iniciarTodo() {
    $.fn.select2.defaults.set("width", "100%");
    cargarPeriodosResumen();
    eventosUsuarios();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
