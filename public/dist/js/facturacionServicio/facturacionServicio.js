import { alertaError, alertaOk, alertEnSweet } from "../metodos/metodos.js";

let tablaFacturacionServicio;

const inputs = {
    filtroDepartamentoImpresion: $("#filtro-departamento-impresion"),
    filtroMunicipioImpresion: $("#filtro-municipio-impresion"),
    filtroDistritoImpresion: $("#filtro-distrito-impresion"),
    filtroColoniaImpresion: $("#filtro-colonia-impresion"),
};

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
    if (estado === "PAGADA" || estado === "CANCELADA") {
        return `<span class="badge badge-success">${estado}</span>`;
    }

    if (estado === "NO PAGADA") {
        return `<span class="badge badge-danger">${estado}</span>`;
    }

    return `<span class="badge badge-warning">${estado || "-"}</span>`;
}


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

            $("#estado-excel").removeClass("alert-info");

            if (response.status === "success") {

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

function cargarDepartamentosImpresion() {
    $.ajax({
        type: "GET",
        url: baseURL + "getDepartamentos",
        dataType: "json",
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar los departamentos");
                return;
            }

            inputs.filtroDepartamentoImpresion.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (departamento) {
                inputs.filtroDepartamentoImpresion.append(
                    $("<option></option>")
                        .attr("value", departamento.id_departamento)
                        .text(departamento.nombre)
                );
            });
        },
        error: function () {
            alertaError("Error al cargar los departamentos para impresión");
        }
    });
}

function cargarMunicipiosImpresion(idDepartamento) {
    resetSelect(inputs.filtroMunicipioImpresion, "Todos");
    resetSelect(inputs.filtroDistritoImpresion, "Todos");
    resetSelect(inputs.filtroColoniaImpresion, "Todos");

    if (!idDepartamento || idDepartamento === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getMunicipios",
        data: { idDepartamento },
        dataType: "json",
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar los municipios");
                return;
            }

            response.data.forEach(function (municipio) {
                inputs.filtroMunicipioImpresion.append(
                    $("<option></option>")
                        .attr("value", municipio.id_municipio)
                        .text(municipio.nombre)
                );
            });
        },
        error: function () {
            alertaError("Error al cargar los municipios");
        }
    });
}

function cargarDistritosImpresion(idMunicipio) {
    resetSelect(inputs.filtroDistritoImpresion, "Todos");
    resetSelect(inputs.filtroColoniaImpresion, "Todos");

    if (!idMunicipio || idMunicipio === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getDistritos",
        data: { idMunicipio },
        dataType: "json",
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar los distritos");
                return;
            }

            response.data.forEach(function (distrito) {
                inputs.filtroDistritoImpresion.append(
                    $("<option></option>")
                        .attr("value", distrito.id_distrito)
                        .text(distrito.nombre)
                );
            });
        },
        error: function () {
            alertaError("Error al cargar los distritos");
        }
    });
}

function cargarColoniasImpresion(idDistrito) {
    resetSelect(inputs.filtroColoniaImpresion, "Todos");

    if (!idDistrito || idDistrito === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getColonias",
        data: { idDistrito },
        dataType: "json",
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudieron cargar las colonias");
                return;
            }

            response.data.forEach(function (colonia) {
                inputs.filtroColoniaImpresion.append(
                    $("<option></option>")
                        .attr("value", colonia.id_colonia)
                        .text(colonia.nombre)
                );
            });
        },
        error: function () {
            alertaError("Error al cargar las colonias");
        }
    });
}

function resetSelect(selector, placeholder = "Todos") {
    $(selector).empty().append(`<option value="-1">${placeholder}</option>`);
}

function reiniciarFiltrosImpresion() {
    inputs.filtroDepartamentoImpresion.val("-1");
    resetSelect(inputs.filtroMunicipioImpresion, "Todos");
    resetSelect(inputs.filtroDistritoImpresion, "Todos");
    resetSelect(inputs.filtroColoniaImpresion, "Todos");
}

function imprimirFacturasPeriodoActivo() {
    const params = new URLSearchParams({
        autoPrint: "1"
    });

    const departamento = inputs.filtroDepartamentoImpresion.val();
    const municipio = inputs.filtroMunicipioImpresion.val();
    const distrito = inputs.filtroDistritoImpresion.val();
    const colonia = inputs.filtroColoniaImpresion.val();

    if (departamento && departamento !== "-1") {
        params.append("departamento", departamento);
    }

    if (municipio && municipio !== "-1") {
        params.append("municipio", municipio);
    }

    if (distrito && distrito !== "-1") {
        params.append("distrito", distrito);
    }

    if (colonia && colonia !== "-1") {
        params.append("colonia", colonia);
    }

    const ventana = window.open(
        `${baseURL}imprimirFacturasConsumoPeriodoActivo?${params.toString()}`,
        '_blank'
    );

    if (!ventana) {
        alertaError('El navegador bloqueó la ventana de impresión. Permite ventanas emergentes e inténtalo nuevamente.');
        return;
    }

    // $('#modal-imprimir-facturas-direccion').modal('hide');
    ventana.focus();
}

function eventosUsuarios() {

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

    //evento para cargar el excel
    $("#btn-cargar-excel").on("click", function () {
        cargarExcelAlcaldia();
    });

    // eventos para imprimir las facturas
    $("#btn-imprimir-facturas-periodo").on("click", function () {
        reiniciarFiltrosImpresion();
        $("#modal-imprimir-facturas-direccion").modal("show");
    });

    $("#btn-confirmar-imprimir-facturas").on("click", function () {
        imprimirFacturasPeriodoActivo();
    });

    inputs.filtroDepartamentoImpresion.on("change", function () {
        cargarMunicipiosImpresion($(this).val());
    });

    inputs.filtroMunicipioImpresion.on("change", function () {
        cargarDistritosImpresion($(this).val());
    });

    inputs.filtroDistritoImpresion.on("change", function () {
        cargarColoniasImpresion($(this).val());
    });
}

function iniciarTodo() {
    eventosUsuarios();
    cargarTablaFacturas();
    cargarDepartamentosImpresion();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
