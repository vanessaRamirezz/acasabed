import { alertaError } from "../metodos/metodos.js";

function resetSelect(selector, placeholder = "Seleccione...") {
    $(selector).empty().append(`<option value="-1">${placeholder}</option>`);
}

function cargarDepartamentos() {
    $.ajax({
        type: "GET",
        url: baseURL + "getDepartamentos",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                const select = $("#departamento");
                select.empty().append('<option value="-1">Seleccione...</option>');

                response.data.forEach(function (departamento) {
                    select.append(
                        $("<option></option>")
                            .attr("value", departamento.id_departamento)
                            .text(departamento.nombre)
                    );
                });
            } else {
                alertaError(response.mensaje || "No se pudieron cargar los departamentos");
            }
        },
        error: function () {
            alertaError("Error al cargar los departamentos");
        }
    });
}

function cargarMunicipios(idDepartamento) {
    resetSelect("#municipio");
    resetSelect("#distrito");
    resetSelect("#colonia");

    if (!idDepartamento || idDepartamento === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getMunicipios",
        data: { idDepartamento },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                const select = $("#municipio");
                response.data.forEach(function (municipio) {
                    select.append(
                        $("<option></option>")
                            .attr("value", municipio.id_municipio)
                            .text(municipio.nombre)
                    );
                });
            } else {
                alertaError(response.mensaje || "No se pudieron cargar los municipios");
            }
        },
        error: function () {
            alertaError("Error al cargar los municipios");
        }
    });
}

function cargarDistritos(idMunicipio) {
    resetSelect("#distrito");
    resetSelect("#colonia");

    if (!idMunicipio || idMunicipio === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getDistritos",
        data: { idMunicipio },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                const select = $("#distrito");
                response.data.forEach(function (distrito) {
                    select.append(
                        $("<option></option>")
                            .attr("value", distrito.id_distrito)
                            .text(distrito.nombre)
                    );
                });
            } else {
                alertaError(response.mensaje || "No se pudieron cargar los distritos");
            }
        },
        error: function () {
            alertaError("Error al cargar los distritos");
        }
    });
}

function cargarColonias(idDistrito) {
    resetSelect("#colonia");

    if (!idDistrito || idDistrito === "-1") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseURL + "getColonias",
        data: { idDistrito },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                const select = $("#colonia");
                response.data.forEach(function (colonia) {
                    select.append(
                        $("<option></option>")
                            .attr("value", colonia.id_colonia)
                            .text(colonia.nombre)
                    );
                });
            } else {
                alertaError(response.mensaje || "No se pudieron cargar las colonias");
            }
        },
        error: function () {
            alertaError("Error al cargar las colonias");
        }
    });
}

function generarReporte() {
    const params = new URLSearchParams({
        departamento: $("#departamento").val() || "-1",
        municipio: $("#municipio").val() || "-1",
        distrito: $("#distrito").val() || "-1",
        colonia: $("#colonia").val() || "-1",
        tipoCliente: $("#tipo-cliente").val() || "-1"
    });

    $("#visorPDFClientes").attr("src", `${baseURL}reporte-clientes/pdf?${params.toString()}`);
}

function eventosUsuarios() {
    $("#departamento").on("change", function () {
        cargarMunicipios($(this).val());
    });

    $("#municipio").on("change", function () {
        cargarDistritos($(this).val());
    });

    $("#distrito").on("change", function () {
        cargarColonias($(this).val());
    });

    $("#btnGenerarReporteClientes").on("click", generarReporte);
}

function iniciarTodo() {
    cargarDepartamentos();
    eventosUsuarios();
}

document.addEventListener("DOMContentLoaded", iniciarTodo);
