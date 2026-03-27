import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaClientes;

const inputs = {
    codigo: $("#codigo"),
    nombre: $("#nombre"),
    sexo: $("#sexo"),
    ocupacion: $("#ocupacion"),
    fechaDeNacimiento: $("#fecha-de-nacimiento"),
    telefonos: $("#telefono"),
    correo: $("#correo"),
    dui: $("#dui"),
    nit: $("#nit"),
    nrc: $("#nrc"),
    actividadEconomica: $("#actividad-economica"),
    tipoCliente: $("#tipo-cliente"),
    contactoNombre: $("#contacto-nombre"),
    contactoDui: $("#contacto-dui"),
    contactoTelefonos: $("#contacto-telefono"),
    departamentos: $("#departamentos"),
    municipios: $("#municipios"),
    distritos: $("#distritos"),
    direccion: $("#direccion"),
    complementoDireccion: $("#complemento-direccion"),
    fechaDeVencimientoDui: $("#fecha-vencimiento-dui"),
    fotoDuiFrontal: $("#dui-frontal-input"),
    fotoDuiReversa: $("#dui-reverso-input"),
    comentarios: $("#comentarios"),
    idCliente: $("#id-cliente"),
    
};

function getData() {
    let formData = new FormData();

    formData.append('codigo', inputs.codigo.val().trim());
    formData.append('nombre', inputs.nombre.val().trim());
    formData.append('sexo', inputs.sexo.val());
    formData.append('ocupacion', inputs.ocupacion.val().trim());
    formData.append('fechaDeNacimiento', inputs.fechaDeNacimiento.val());
    formData.append('telefonos', inputs.telefonos.val().trim());
    formData.append('correo', inputs.correo.val().trim());
    formData.append('dui', inputs.dui.val().trim());
    formData.append('nit', inputs.nit.val().trim());
    formData.append('nrc', inputs.nrc.val().trim());
    formData.append('actividadEconomica', inputs.actividadEconomica.val());
    formData.append('tipoCliente', inputs.tipoCliente.val());
    formData.append('contactoNombre', inputs.contactoNombre.val().trim());
    formData.append('contactoDui', inputs.contactoDui.val().trim());
    formData.append('contactoTelefonos', inputs.contactoTelefonos.val().trim());
    formData.append('departamentos', inputs.departamentos.val());
    formData.append('municipios', inputs.municipios.val());
    formData.append('distritos', inputs.distritos.val());
    formData.append('direccion', inputs.direccion.val().trim());
    formData.append('complementoDireccion', inputs.complementoDireccion.val().trim());
    formData.append('fechaDeVencimientoDui', inputs.fechaDeVencimientoDui.val());

    // archivos
    if (inputs.fotoDuiFrontal[0].files[0]) {
        formData.append('fotoDuiFrontal', inputs.fotoDuiFrontal[0].files[0]);
    }

    if (inputs.fotoDuiReversa[0].files[0]) {
        formData.append('fotoDuiReversa', inputs.fotoDuiReversa[0].files[0]);
    }

    formData.append('comentarios', inputs.comentarios.val().trim());
    formData.append('id-cliente', inputs.idCliente.val().trim());

    return formData;
}
function validarCampoDui() {
    $(inputs.dui).mask('00000000-0');
    $(inputs.contactoDui).mask('00000000-0');
}


function limpiarFormulario() {
    inputs.codigo.val('');
    inputs.nombre.val('');
    inputs.sexo.val('');
    inputs.ocupacion.val('');
    inputs.fechaDeNacimiento.val('');
    inputs.telefonos.val('');
    inputs.correo.val('');
    inputs.dui.val('');
    inputs.nit.val('');
    inputs.nrc.val('');
    inputs.actividadEconomica.val(null).trigger('change');
    inputs.tipoCliente.val('');
    inputs.contactoNombre.val('');
    inputs.contactoDui.val('');
    inputs.contactoTelefonos.val('');
    inputs.departamentos.val('-1');
    inputs.municipios.val('-1');
    inputs.distritos.val('-1');
    inputs.direccion.val('-1');
    inputs.complementoDireccion.val('');
    inputs.fechaDeVencimientoDui.val('');
    inputs.fotoDuiFrontal.val('');
    $('#vista-previa-frontal').attr('src', '');
    inputs.fotoDuiFrontal.val('');
    $('#vista-previa-reversa').attr('src', '');
    inputs.comentarios.val('');
    inputs.idCliente.val('');
}

function cargarActividadesEconomicas() {
    $('#actividad-economica').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getActividades', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(actividad => ({
                        id: actividad.id_actividad_economica,
                        text: actividad.nombre
                    }))
                };
            },
            cache: true
        }
    })
}

function cargarDepartamentos() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDepartamentos',
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                var selectDepartamento = $('#departamentos');
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
                var selectMunicipios = $('#municipios');
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
                var selectDistrito = $('#distritos');
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

                var selectDirecciones = $('#direccion');
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

function abrirModalNuevoCliente() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    $('#modal-clientes').modal('show');
}

function previewImage(input, imgId) {
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const img = document.getElementById(imgId);
            img.src = e.target.result;
            img.style.display = 'block'; // mostrar imagen
        }

        reader.readAsDataURL(file);
    }
}

function guardarOeditarCliente(tipoProceso) {
    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevoCliente' : 'editarCliente';

    const emailRegex = /^\S+@\S+\.\S+$/;

    if (!validarCampo(data.get('codigo'), 'El codigo es requerido', inputs.codigo)) return;
    if (data.get('correo') && !emailRegex.test(data.get('correo'))) {
        alertaError('Por favor ingrese un correo válido');
        colorEnInputConFocus(inputs.correo[0]);
        return false;
    } else {
        eliminarColorYfocus(inputs.correo[0]);
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: data,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                // tablaTarifas.ajax.reload();
                Swal.close();
                $('#modal-clientes').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    });
}

function eventosUsuarios() {

    $('#departamentos').on('change', function () {
        var departamentoSeleccionado = $(this).val();
        cargarMunicipios(departamentoSeleccionado);
    });

    $('#municipios').on('change', function () {
        var municipioSeleccionado = $(this).val();
        cargarDistritos(municipioSeleccionado);
    });

    $('#distritos').on('change', function () {
        var distritoSeleccionado = $(this).val();
        cargarDirecciones(distritoSeleccionado);
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarCliente('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarCliente('2');
    });

    $("#btn-agregar").on("click", function () {
        abrirModalNuevoCliente();
    });

    // $("#tbl-periodos tbody").on("click", '.btn-ver-opciones', function () {
    //     abrirModalEditarPeriodo(this);
    // });

    $("#dui-frontal-input").on("change", function () {
        previewImage(this, "vista-previa-frontal");
    });

    $("#dui-reverso-input").on("change", function () {
        previewImage(this, "vista-previa-reversa");
    });
}

function iniciarTodo() {
    eventosUsuarios();
    validarCampoDui();
    cargarActividadesEconomicas();
    cargarDepartamentos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);