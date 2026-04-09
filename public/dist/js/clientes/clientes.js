import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaClientes;

const inputs = {
    fotoCliente: $("#foto-cliente-input"),
    codigo: $("#codigo"),
    nombre: $("#nombre"),
    edad: $("#edad"),
    sexo: $("#sexo"),
    ocupacion: $("#ocupacion"),
    estadoFamiliar: $("#estado-familiar"),
    numeroGrupoFamiliar: $("#numero-grupo-familiar"),
    lugarDeNacimiento: $("#lugar-de-nacimiento"),
    fechaDeNacimiento: $("#fecha-de-nacimiento"),
    lugarDeTrabajo: $("#lugar-de-trabajo"),
    telefonos: $("#telefono"),
    correo: $("#correo"),
    dui: $("#dui"),
    extendido: $("#extendido"),
    fecha: $("#fecha"),
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
    colonia: $("#colonia"),
    complementoDireccion: $("#complemento-direccion"),
    fechaDeVencimientoDui: $("#fecha-vencimiento-dui"),
    fotoDuiFrontal: $("#dui-frontal-input"),
    fotoDuiReversa: $("#dui-reverso-input"),
    comentarios: $("#comentarios"),
    idCliente: $("#id-cliente"),

};

function getData() {
    let formData = new FormData();

    if (inputs.fotoCliente[0].files[0]) {
        formData.append('fotoDeCliente', inputs.fotoCliente[0].files[0]);
    }

    formData.append('codigo', inputs.codigo.val().trim());
    formData.append('nombre', inputs.nombre.val().trim());
    formData.append('edad', inputs.edad.val().trim());
    formData.append('sexo', inputs.sexo.val());
    formData.append('ocupacion', inputs.ocupacion.val().trim());
    formData.append('estadoFamiliar', inputs.estadoFamiliar.val().trim());
    formData.append('numeroGrupoFamiliar', inputs.numeroGrupoFamiliar.val().trim());
    formData.append('lugarDeNacimiento', inputs.lugarDeNacimiento.val());
    formData.append('fechaDeNacimiento', inputs.fechaDeNacimiento.val());
    formData.append('lugarDeTrabajo', inputs.lugarDeTrabajo.val());
    formData.append('telefonos', inputs.telefonos.val().trim());
    formData.append('correo', inputs.correo.val().trim());
    formData.append('dui', inputs.dui.val().trim());
    formData.append('extendido', inputs.extendido.val().trim());
    formData.append('fecha', inputs.fecha.val().trim());
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
    formData.append('colonia', inputs.colonia.val().trim());
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
    inputs.fotoCliente.val('');
    $('#vista-previa-foto-cliente').attr('src', '').hide();
    inputs.codigo.val('');
    inputs.codigo.prop('disabled', false);
    inputs.nombre.val('');
    inputs.edad.val('');
    inputs.sexo.val('');
    inputs.ocupacion.val('');
    inputs.estadoFamiliar.val('');
    inputs.numeroGrupoFamiliar.val('');
    inputs.lugarDeNacimiento.val('');
    inputs.fechaDeNacimiento.val('');
    inputs.lugarDeTrabajo.val('');
    inputs.telefonos.val('');
    inputs.correo.val('');
    inputs.dui.val('');
    inputs.extendido.val('');
    inputs.fecha.val('');
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
    inputs.colonia.val('-1');
    inputs.complementoDireccion.val('');
    inputs.fechaDeVencimientoDui.val('');
    inputs.fotoDuiFrontal.val('');
    $('#vista-previa-frontal').attr('src', '').hide();
    inputs.fotoDuiReversa.val('');
    $('#vista-previa-reversa').attr('src', '').hide();
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

function cargarMunicipios(idDepartamento, callback = null) {
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
        data: { idDepartamento },
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

                // AQUI ejecutas el callback
                if (callback) callback();

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

function cargarDistritos(idMunicipio, callback = null) {
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

                if (callback) callback();
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

function cargarColonias(idDistrito, callback = null) {
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
        url: baseURL + 'getColonias',
        data: {
            idDistrito
        },
        dataType: 'json',
        success: function (response) {

            if (response.status === 'success') {

                var selectColonias = $('#colonia');
                selectColonias.empty();

                // Validar si viene vacío
                if (!response.data || response.data.length === 0) {
                    selectColonias.append('<option value="-1">No hay Colonias</option>');
                    Swal.close();
                    alertaInfo('No hay colonias registradas aún');
                    return;
                }

                selectColonias.append('<option value="-1">Seleccione...</option>');

                response.data.forEach(function (colonia) {
                    let option = $('<option></option>')
                        .attr('value', colonia.id_colonia)
                        .text(colonia.nombre);
                    selectColonias.append(option);
                });

                Swal.close();

                // AQUI ejecutas el callback
                if (callback) callback();

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

function setSelect2Value(selector, id, text) {
    const option = new Option(text, id, true, true);
    $(selector).append(option).trigger('change');
}

function cargarClientes() {
    tablaClientes = $('#tbl-clientes').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getClientes',
            data: function (d) {
                d.searchValue = $('#customSearchClientes').val();
            }
        },
        columns: [
            {
                data: 'codigo'
            },
            {
                data: 'nombre'
            },
            {
                data: 'nombre_tipo_cliente'
            },
            {
                data: 'numero_de_dui'
            },
            {
                data: 'numero_de_nit'
            },
            {
                data: 'numero_de_nrc'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-cliente='${encodeURIComponent(JSON.stringify(row))}'>
                                <i class="fa fa-edit"></i>
                            </button>`;
                }
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false,
        initComplete: function () {
            let searchInput = $('.dataTables_filter input');
            searchInput.val('').trigger('input');
        }
    });

    //Buscar al presionar Enter en tu input
    $('#customSearchClientes').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaClientes.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnClientes').off('click').on('click', function () {
        tablaClientes.draw();
    });

    $('#clearSearchBtnClientes').on('click', function (e) {
        $('#customSearchClientes').val('');
        tablaClientes.draw();
    });
}

function abrirModalNuevoCliente() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    eliminarColorYfocus(inputs.codigo[0]);
    eliminarColorYfocus(inputs.correo[0]);

    $('#modal-clientes').modal('show');
}

function abrirModalEditarCliente(elemento) {

    limpiarFormulario();

    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataClientes = JSON.parse(
        decodeURIComponent($(elemento).attr('data-cliente'))
    );

    // Ruta base donde están tus imágenes
    var baseImagenes = baseURL; // o algo como: baseURL + 'uploads/'


    // FOTO DE CLIENTE
    if (dataClientes.foto_de_cliente) {
        $('#vista-previa-foto-cliente')
            .attr('src', baseImagenes + dataClientes.foto_de_cliente + '?t=' + Date.now())
            .show();
    } else {
        inputs.fotoCliente.val('');
        $('#vista-previa-foto-cliente').hide();
    }

    $('#codigo').val(dataClientes.codigo).prop('disabled', true);
    $('#nombre').val(dataClientes.nombre);
    $('#edad').val(dataClientes.edad);
    var sexo = (dataClientes.sexo || '').trim();
    var $selectSexo = $('#sexo');
    $selectSexo.find('option').each(function () {
        $(this).prop('selected', $(this).text().trim() === sexo);
    });
    $('#ocupacion').val(dataClientes.ocupacion);
    $('#estado-familiar').val(dataClientes.estado_familiar);
    $('#numero-grupo-familiar').val(dataClientes.numero_grupo_familiar);
    $('#lugar-de-nacimiento').val(dataClientes.lugar_de_nacimiento);
    $('#fecha-de-nacimiento').val(dataClientes.fecha_de_nacimiento);
    $('#lugar-de-trabajo').val(dataClientes.lugar_trabajo);
    $('#telefono').val(dataClientes.telefonos);
    $('#correo').val(dataClientes.email);
    $('#dui').val(dataClientes.numero_de_dui);
    $('#extendido').val(dataClientes.extendido);
    $('#fecha').val(dataClientes.fecha);
    $('#nit').val(dataClientes.numero_de_nit);
    $('#nrc').val(dataClientes.numero_de_nrc);
    setSelect2Value('#actividad-economica',
        dataClientes.id_actividad_economica,
        dataClientes.nombre_actividad_economica
    );
    $('#tipo-cliente')
        .val(dataClientes.id_tipo_cliente || '')
        .trigger('change');
    $('#contacto-nombre').val(dataClientes.nombre_de_contacto);
    $('#contacto-dui').val(dataClientes.numero_de_dui_contacto);
    $('#contacto-telefono').val(dataClientes.numeros_de_telefonos);

    $('#departamentos')
        .val(dataClientes.id_departamento || '-1');

    cargarMunicipios(dataClientes.id_departamento, function () {

        $('#municipios')
            .val(dataClientes.id_municipio || '-1');

        cargarDistritos(dataClientes.id_municipio, function () {

            $('#distritos')
                .val(dataClientes.id_distrito || '-1');

            cargarColonias(dataClientes.id_distrito, function () {

                $('#colonia')
                    .val(dataClientes.id_colonia || '-1');
            });
        });
    });
    $('#complemento-direccion').val(dataClientes.direccion_complemento);
    $('#fecha-vencimiento-dui').val(dataClientes.fecha_de_vencimiento_dui);

    // DUI FRONTAL
    if (dataClientes.foto_de_dui_frontal) {
        $('#vista-previa-frontal')
            .attr('src', baseImagenes + dataClientes.foto_de_dui_frontal + '?t=' + Date.now())
            .show();
    } else {
        inputs.fotoDuiFrontal.val('');
        $('#vista-previa-frontal').hide();
    }

    // DUI REVERSO
    if (dataClientes.foto_de_dui_reversa) {
        $('#vista-previa-reversa')
            .attr('src', baseImagenes + dataClientes.foto_de_dui_reversa + '?t=' + Date.now())
            .show();
    } else {
        inputs.fotoDuiReversa.val('');
        $('#vista-previa-reversa').hide();
    }

    eliminarColorYfocus(inputs.codigo[0]);
    eliminarColorYfocus(inputs.correo[0]);

    $('#comentarios').val(dataClientes.comentarios);
    $('#id-cliente').val(dataClientes.id_cliente);

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
                tablaClientes.ajax.reload();
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
        cargarColonias(distritoSeleccionado);
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

    $("#tbl-clientes tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarCliente(this);
    });

    $("#dui-frontal-input").on("change", function () {
        previewImage(this, "vista-previa-frontal");
    });

    $("#dui-reverso-input").on("change", function () {
        previewImage(this, "vista-previa-reversa");
    });

    $("#foto-cliente-input").on("change", function () {
        previewImage(this, "vista-previa-foto-cliente");
    });
}

function iniciarTodo() {
    eventosUsuarios();
    validarCampoDui();
    cargarActividadesEconomicas();
    cargarDepartamentos();
    cargarClientes();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);