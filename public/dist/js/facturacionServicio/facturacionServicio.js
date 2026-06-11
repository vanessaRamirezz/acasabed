import { alertaError, alertaOk, alertEnSweet } from "../metodos/metodos.js";

let tablaFacturacionServicio;

const inputs = {
    filtroRutaImpresion: $("#filtro-ruta-impresion"),
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

function renderTipo(tipo) {
    if (tipo === "OTRO") {
        return `<span class="badge badge-info">${tipo}</span>`;
    }

    return `<span class="badge badge-primary">${tipo || "-"}</span>`;
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
            {
                data: "tipo",
                render: data => renderTipo(data)
            },
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

function cargarSelectContratoFacturaOtro() {
    $("#select-contrato-factura-otro").select2({
        placeholder: "Busque cliente o contrato",
        allowClear: true,
        theme: "bootstrap4",
        width: "100%",
        ajax: {
            url: baseURL + "getContratosFacturacionOtro",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (response) {
                const results = (response.data || []).map(function (item) {
                    return {
                        ...item,
                        id: item.id ?? item.id_contrato,
                        text: item.text ?? `${item.numero_contrato || ""} - ${item.nombre_completo || ""}`.trim()
                    };
                });

                return {
                    results
                };
            },
            cache: true
        }
    });
}

function inicializarSelectServicio($select) {
    $select.select2({
        placeholder: "Seleccione un servicio",
        allowClear: true,
        theme: "bootstrap4",
        width: "100%",
        ajax: {
            url: baseURL + "getServiciosFacturacionOtro",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (response) {
                const results = (response.data || []).map(function (item) {
                    return {
                        ...item,
                        id: item.id ?? item.id_servicio,
                        text: item.text ?? `${item.codigo || ""} - ${item.nombre || ""}`.trim()
                    };
                });

                return {
                    results
                };
            },
            cache: true
        }
    });

    $select.on("select2:select", function (e) {
        const data = e.params.data || {};
        const $row = $(this).closest("tr");
        const $concepto = $row.find(".concepto-factura-otro");
        const $monto = $row.find(".monto-factura-otro");
        const operacion = (data.operacion || "SUMA").toUpperCase();

        $row.attr("data-operacion", operacion);

        if (!$concepto.val().trim()) {
            const nombre = data.nombre || data.text || "";
            $concepto.val(nombre.replace(/^[^-]+-\s*/, "").trim());
        }

        if ((!$monto.val() || Number($monto.val()) <= 0) && Number(data.valor || 0) > 0) {
            $monto.val(Number(data.valor).toFixed(2));
        }

        recalcularTotalFacturaOtro();
    });

    $select.on("select2:clear", function () {
        const $row = $(this).closest("tr");
        $row.attr("data-operacion", "SUMA");
        recalcularTotalFacturaOtro();
    });
}

function recalcularTotalFacturaOtro() {
    let total = 0;

    $("#factura-otro-detalle-body .fila-factura-otro").each(function () {
        const $row = $(this);
        const monto = parseFloat($row.find(".monto-factura-otro").val() || 0);
        const operacion = String($row.attr("data-operacion") || "SUMA").toUpperCase();

        if (monto <= 0) {
            return;
        }

        total += operacion === "RESTA" ? (monto * -1) : monto;
    });

    $("#total-factura-otro").text(total.toFixed(2));
}

function agregarFilaFacturaOtro(data = {}) {
    const template = document.getElementById("template-fila-factura-otro");
    const clone = template.content.cloneNode(true);
    const $row = $(clone).find("tr");

    $("#factura-otro-detalle-body").append($row);

    const $selectServicio = $row.find(".servicio-factura-otro");
    const $concepto = $row.find(".concepto-factura-otro");
    const $monto = $row.find(".monto-factura-otro");

    $row.attr("data-operacion", String(data.operacion || "SUMA").toUpperCase());

    inicializarSelectServicio($selectServicio);

    if (data.id_servicio && data.text) {
        const option = new Option(data.text, data.id_servicio, true, true);
        $selectServicio.append(option).trigger("change");
    }

    if (data.concepto) {
        $concepto.val(data.concepto);
    }

    if (data.monto) {
        $monto.val(data.monto);
    }

    recalcularTotalFacturaOtro();
}

function obtenerDetalleFacturaOtro() {
    const items = [];

    $("#factura-otro-detalle-body .fila-factura-otro").each(function () {
        const $row = $(this);
        const idServicio = $row.find(".servicio-factura-otro").val();
        const concepto = $row.find(".concepto-factura-otro").val().trim();
        const monto = parseFloat($row.find(".monto-factura-otro").val() || 0);

        if (idServicio) {
            items.push({
                id_servicio: idServicio,
                concepto,
                monto
            });
        }
    });

    return items;
}

function limpiarFacturaOtro() {
    $("#select-contrato-factura-otro").val(null).trigger("change");
    $("#factura-otro-detalle-body").empty();
    agregarFilaFacturaOtro();
    recalcularTotalFacturaOtro();
}

function crearFacturaOtro() {
    const idContrato = $("#select-contrato-factura-otro").val();
    const items = obtenerDetalleFacturaOtro();
    const totalFinal = parseFloat($("#total-factura-otro").text() || 0);

    if (!idContrato) {
        alertaError("Debes seleccionar un cliente o contrato");
        return;
    }

    if (!items.length) {
        alertaError("Debes agregar al menos un servicio");
        return;
    }

    const incompletos = items.some(item => !item.id_servicio || !item.monto || item.monto <= 0);
    if (incompletos) {
        alertaError("Revisa que cada fila tenga servicio y monto mayor a 0");
        return;
    }

    if (totalFinal <= 0) {
        alertaError("El total final de la factura debe ser mayor a 0");
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "crearFacturaOtro",
        dataType: "json",
        data: {
            idContrato,
            items: JSON.stringify(items)
        },
        success: function (response) {
            if (response.status !== "success") {
                alertaError(response.mensaje || "No se pudo crear la factura");
                return;
            }

            const data = response.data || {};
            const idFactura = data.id_factura;
            const mensaje = data.mensaje || "Factura creada correctamente";

            Swal.fire({
                icon: "success",
                title: "Factura creada",
                text: mensaje,
                showCancelButton: true,
                confirmButtonText: "Ver PDF",
                cancelButtonText: "Cerrar"
            }).then((result) => {
                if (result.isConfirmed && idFactura) {
                    window.open(baseURL + "facturaCobroServicio/" + idFactura, "_blank");
                }
            });

            limpiarFacturaOtro();
            if (tablaFacturacionServicio) {
                tablaFacturacionServicio.draw(false);
            }
        },
        error: function () {
            alertaError("Ocurrió un error al crear la factura tipo OTRO");
        }
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

function cargarRutasFiltroReporteLectura() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'selectRuta',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar las rutas');
                return;
            }

            inputs.filtroRutaImpresion.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (rutas) {
                inputs.filtroRutaImpresion.append(
                    $('<option></option>')
                        .attr('value', rutas.id_ruta)
                        .text(rutas.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar las rutas');
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
    inputs.filtroRutaImpresion.val("-1");
    inputs.filtroDepartamentoImpresion.val("-1");
    resetSelect(inputs.filtroMunicipioImpresion, "Todos");
    resetSelect(inputs.filtroDistritoImpresion, "Todos");
    resetSelect(inputs.filtroColoniaImpresion, "Todos");
}

function imprimirFacturasPeriodoActivo() {
    const params = new URLSearchParams({
        autoPrint: "1"
    });

    const ruta = inputs.filtroRutaImpresion.val();
    const departamento = inputs.filtroDepartamentoImpresion.val();
    const municipio = inputs.filtroMunicipioImpresion.val();
    const distrito = inputs.filtroDistritoImpresion.val();
    const colonia = inputs.filtroColoniaImpresion.val();

    if (ruta && ruta !== "-1") {
        params.append("ruta", ruta);
    }

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

    $("#btn-agregar-servicio-factura-otro").on("click", function () {
        agregarFilaFacturaOtro();
    });

    $("#btn-crear-factura-otro").on("click", function () {
        crearFacturaOtro();
    });

    $(document).on("click", ".btn-eliminar-fila-factura-otro", function () {
        $(this).closest("tr").remove();

        if (!$("#factura-otro-detalle-body .fila-factura-otro").length) {
            agregarFilaFacturaOtro();
        }

        recalcularTotalFacturaOtro();
    });

    $(document).on("input", ".monto-factura-otro", function () {
        recalcularTotalFacturaOtro();
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
    cargarRutasFiltroReporteLectura();
    cargarDepartamentosImpresion();
    cargarSelectContratoFacturaOtro();
    agregarFilaFacturaOtro();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
