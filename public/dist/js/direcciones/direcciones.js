import { alertaError, alertaInfo, alertaOk } from "../metodos/metodos.js";


var selectDepartamento;
var selectMunicipios;
var selectDistrito;
var selectDirecciones;
var municipioSeleccionado;
var departamentoSeleccionado;
var distritoSeleccionado;

function cargarDepartamentos() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDepartamentos',
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                selectDepartamento = $('#departamentos');
                selectDepartamento.empty();

                selectDepartamento.append($('<option>', {
                    value: -1,
                    text: 'Seleccione...'
                }));

                response.data.forEach(function (departamento) {
                    var option = $('<option></option>')
                        .attr('value', departamento.id_departamento)
                        .text(departamento.nombre);
                    selectDepartamento.append(option);
                });

                // selectDepartamento.trigger('change');
            } else {
                alertaError(response.mensaje);
            }
        },
        error: function () {
            alertaError('Error al cargar los departamentos');
        }
    });
}

function cargarMunicipios(idDepartamento) {
    Swal.fire({
        title: 'Espere...',
        html: 'Cargando municipios...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    $.ajax({
        type: 'POST',
        url: baseURL + 'getMunicipios',
        data: {
            idDepartamento
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                selectMunicipios = $('#municipios');
                selectMunicipios.empty();

                selectMunicipios.append($('<option>', {
                    value: -1,
                    text: 'Seleccione...'
                }));

                response.data.forEach(function (municipio) {
                    var option = $('<option></option>')
                        .attr('value', municipio.id_municipio)
                        .text(municipio.nombre);
                    selectMunicipios.append(option);
                });
                Swal.close();
            } else {
                Swal.close();
                alertaError(response.mensaje);
            }
        },
        error: function () {
            Swal.close();
            alertaError('Error al cargar los municipios');
        }
    });
}

function cargarDistritos(idMunicipio) {
    Swal.fire({
        title: 'Espere...',
        html: 'Cargando distritos...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    $.ajax({
        type: 'POST',
        url: baseURL + 'getDistritos',
        data: {
            idMunicipio
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                selectDistrito = $('#distritos');
                selectDistrito.empty();

                selectDistrito.append($('<option>', {
                    value: -1,
                    text: 'Seleccione...'
                }));

                response.data.forEach(function (distrito) {
                    var option = $('<option></option>')
                        .attr('value', distrito.id_distrito)
                        .text(distrito.nombre);
                    selectDistrito.append(option);
                });

                Swal.close();
            } else {
                Swal.close();
                alertaError(response.mensaje);
            }
        },
        error: function () {
            Swal.close();
            alertaError('Error al cargar los distritos');
        }
    });
}

function cargarDirecciones(idDistrito) {
    Swal.fire({
        title: 'Espere...',
        html: 'Cargando colonias...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + 'getDirecciones',
        data: {
            idDistrito
        },
        dataType: 'json',
        success: function (response) {

            if (response.status === 'success') {

                selectDirecciones = $('#direccion');
                selectDirecciones.empty();

                // Validar si viene vacío
                if (!response.data || response.data.length === 0) {
                    selectDirecciones.append('<option value="-1">No hay Zonas</option>');
                    Swal.close();
                    alertaInfo('No hay Zonas registradas aún');
                    return;
                }

                selectDirecciones.append('<option value="-1">Seleccione...</option>');

                response.data.forEach(function (direccion) {
                    let option = $('<option></option>')
                        .attr('value', direccion.id_direccion)
                        .text(direccion.nombre);
                    selectDirecciones.append(option);
                });

                Swal.close();

            } else {
                Swal.close();
                alertaError(response.mensaje);
            }
        },
        error: function () {
            Swal.close();
            alertaError('Error al cargar las zonas');
        }
    });
}

function guardarNuevaDireccion() {
    Swal.fire({
        title: 'Espere...',
        html: 'Agregando colonia...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    var nombreDireccion = $('#nueva-direccion').val();
    var idDistrito = $('#distritos').val();

    if (nombreDireccion === "" || idDistrito === "-1") {
        alertaError('Debes ingresar un nombre para la colonia y seleccionar un ditrito.')
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'guardarDireccion',
        data: {
            idDistrito,
            nombreDireccion
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                Swal.close();
                alertaOk(response.mensaje);
                $('#nueva-direccion').val('');
            } else {
                Swal.close();
                alertaError(response.mensaje);
            }
            cargarDirecciones(idDistrito);
        },
        error: function () {
            Swal.close();
            alertaError('Error al guardar la nueva dirección');
        }
    })
}

function eventosUsuarioSelects() {
    $('#departamentos').on('change', function () {
        departamentoSeleccionado = $(this).val();
        cargarMunicipios(departamentoSeleccionado);
    });

    $('#municipios').on('change', function () {
        municipioSeleccionado = $(this).val();
        cargarDistritos(municipioSeleccionado);
    });

    $('#distritos').on('change', function () {
        distritoSeleccionado = $(this).val();
        if (distritoSeleccionado !== "-1") {
            $('#opcion-nueva-direccion').show();
        } else {
            $('#opcion-nueva-direccion').hide();
        }

        cargarDirecciones(distritoSeleccionado);
    });

    $('#agregar-direccion').on('click', function () {
        guardarNuevaDireccion();
    })
}

function iniciarTodo() {
    cargarDepartamentos();
    eventosUsuarioSelects();

    $('#municipios').html('<option value="-1">Seleccione...</option>');

    $(document).ready(function () {
        $('#direccion').select2({
            theme: 'bootstrap4'
        });
    });
}

document.addEventListener('DOMContentLoaded', iniciarTodo);