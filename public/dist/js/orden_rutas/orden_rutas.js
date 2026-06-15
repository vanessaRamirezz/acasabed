import { alertaError, alertaOk, alertEnSweet } from "../metodos/metodos.js";

const inputs = {
    ruta: $("#select-ruta-orden"),
    resumen: $("#resumen-orden-ruta"),
    tbody: $("#tbl-orden-rutas tbody"),
    guardar: $("#btn-guardar-orden-ruta"),
};

// function renderEstado(estado) {
//     if (estado === "APROBADO") {
//         return `<span class="badge badge-success">${estado}</span>`;
//     }

//     if (estado === "SUSPENDIDO") {
//         return `<span class="badge badge-warning">${estado}</span>`;
//     }

//     return `<span class="badge badge-secondary">${estado || "-"}</span>`;
// }

function cargarRutas() {
    $.ajax({
        type: "GET",
        url: baseURL + "selectRuta",
        dataType: "json",
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar las rutas");
                return;
            }

            inputs.ruta.empty().append('<option value="-1">Seleccione una ruta</option>');

            response.data.forEach(function (ruta) {
                inputs.ruta.append(
                    $("<option></option>")
                        .attr("value", ruta.id_ruta)
                        .text(ruta.nombre)
                );
            });
        },
        error: function () {
            alertaError("Error al cargar las rutas");
        }
    });
}

function cargarContratosRuta() {
    const idRuta = inputs.ruta.val();

    if (!idRuta || idRuta === "-1") {
        alertaError("Debe seleccionar una ruta");
        return;
    }

    inputs.guardar.prop("disabled", true);
    inputs.resumen.text("Cargando contratos...");
    inputs.tbody.html(`
        <tr>
            <td colspan="4" class="py-4">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <div>Cargando contratos...</div>
            </td>
        </tr>
    `);

    $.ajax({
        type: "GET",
        url: baseURL + "getContratosOrdenRuta",
        dataType: "json",
        data: { idRuta },
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar los contratos");
                return;
            }

            renderContratos(response.data || []);
        },
        error: function () {
            alertaError("Error al cargar contratos de la ruta");
        }
    });
}

function renderContratos(contratos) {
    inputs.tbody.empty();

    if (!contratos.length) {
        inputs.tbody.html(`
            <tr>
                <td colspan="4">No hay contratos asignados a esta ruta.</td>
            </tr>
        `);
        inputs.resumen.text("No hay contratos para ordenar.");
        inputs.guardar.prop("disabled", true);
        return;
    }

    contratos.forEach(function (contrato, index) {
        const orden = Number(contrato.orden_ruta || 0) > 0
            ? Number(contrato.orden_ruta)
            : index + 1;

        inputs.tbody.append(`
            <tr>
                <td>${contrato.numero_contrato || "-"}</td>
                <td class="text-left">${contrato.cliente || "-"}</td>
                <td>
                    <input type="number"
                        class="form-control form-control-sm orden-ruta-input"
                        min="1"
                        value="${orden}"
                        data-id="${contrato.id_contrato}"
                        data-original="${orden}">
                </td>
            </tr>
        `);
    });

    inputs.resumen.text(`${contratos.length} contrato(s) cargado(s).`);
    inputs.guardar.prop("disabled", false);
}

function obtenerOrdenes() {
    const ordenes = [];
    let valido = true;

    $(".orden-ruta-input").each(function () {
        const orden = parseInt($(this).val(), 10);

        if (!orden || orden <= 0) {
            valido = false;
            return false;
        }

        ordenes.push({
            idContrato: $(this).data("id"),
            orden,
            ordenOriginal: parseInt($(this).data("original"), 10) || 0
        });
    });

    if (!valido) {
        alertaError("Todos los contratos deben tener un orden mayor a cero");
        return null;
    }

    return ordenes;
}

function guardarOrdenRuta() {
    const idRuta = inputs.ruta.val();
    const ordenes = obtenerOrdenes();

    if (!idRuta || idRuta === "-1") {
        alertaError("Debe seleccionar una ruta");
        return;
    }

    if (!ordenes) {
        return;
    }

    Swal.fire({
        title: "Espere...",
        html: "Guardando orden...",
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: "POST",
        url: baseURL + "guardarOrdenContratosRuta",
        dataType: "json",
        data: {
            idRuta,
            ordenes: JSON.stringify(ordenes)
        },
        success: function (response) {
            if (response.status !== "success") {
                alertEnSweet("error", "Uups..", response.mensaje || "No se pudo guardar el orden");
                return;
            }

            alertaOk(response.mensaje);
            Swal.close();
            cargarContratosRuta();
        },
        error: function () {
            alertEnSweet("error", "Ups..", "Ocurrió un error al guardar el orden");
        }
    });
}

function eventos() {
    $("#btn-cargar-contratos-ruta").on("click", function () {
        cargarContratosRuta();
    });

    $("#btn-guardar-orden-ruta").on("click", function () {
        guardarOrdenRuta();
    });

    inputs.ruta.on("change", function () {
        inputs.guardar.prop("disabled", true);
        inputs.resumen.text("Presione cargar contratos para ver la ruta seleccionada.");
        inputs.tbody.html(`
            <tr>
                <td colspan="4">Seleccione una ruta y cargue los contratos.</td>
            </tr>
        `);
    });
}

function iniciarTodo() {
    cargarRutas();
    eventos();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
