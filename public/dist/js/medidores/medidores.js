import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaMedidores;

const inputs = {
    numero: $('#numero'),
    fecha: $('#fecha'),
    contrato: $('#contrato'),
    instalador: $('#instalador'),
    idMedidor: $("#id-medidor"),
};

function getData() {
    let formData = new FormData();

    formData.append('numero', inputs.numero.val().trim());
    formData.append('fecha', inputs.fecha.val());
    formData.append('contrato', inputs.contrato.val());
    formData.append('instalador', inputs.instalador.val());
    formData.append('idMedidor', inputs.idMedidor.val());

    return formData;
}

function limpiarFormulario() {
    inputs.numero.val('');
    inputs.fecha.val('');
    inputs.contrato.val(null).trigger('change');
    inputs.instalador.val(null).trigger('change');
    inputs.idMedidor.val('');
}

function cargarContratos() {
    $('#contrato').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getContratos', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(contrato => ({
                        id: contrato.id_contrato,
                        text: contrato.codigo
                    }))
                };
            },
            cache: true
        }
    })
}

function cargarInstaladores() {
    $('#instalador').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getSelectInstaladores', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(instalador => ({
                        id: instalador.id,
                        text: instalador.nombre_de_instalador
                    }))
                };
            },
            cache: true
        }
    })
}

function abrirModalNuevoMedidor() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    eliminarColorYfocus(inputs.numero[0]);

    $('#modal-medidores').modal('show');
}

function setSelect2Value(selector, id, text) {
    const option = new Option(text, id, true, true);
    $(selector).append(option).trigger('change');
}

function abrirModalEditarMedidor(elemento) {
    limpiarFormulario();

    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataMedidor = JSON.parse(
        decodeURIComponent($(elemento).attr('data-medidor'))
    );

    $('#numero').val(dataMedidor.numeros_de_serie);
    $('#fecha').val(dataMedidor.fecha_de_instalacion);
    setSelect2Value('#contrato',
        dataMedidor.id_de_contrato,
        dataMedidor.codigo_de_contrato
    );
    setSelect2Value('#instalador',
        dataMedidor.id_de_instalador,
        dataMedidor.nombre_instalador
    );

    $('#id-medidor').val(dataMedidor.id);

    eliminarColorYfocus(inputs.numero[0]);

    $('#modal-medidores').modal('show');
}

function cargarMedidores() {
    tablaMedidores = $('#tbl-medidores').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getMedidores',
            data: function (d) {
                d.searchValue = $('#customSearchMedidores').val();
            }
        },
        columns: [
            {
                data: 'numeros_de_serie'
            },
            {
                data: 'fecha_de_instalacion'
            },
            {
                data: 'codigo_de_contrato'
            },
            {
                data: 'nombre_instalador'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-medidor='${encodeURIComponent(JSON.stringify(row))}'>
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
    $('#customSearchMedidores').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaMedidores.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnMedidores').off('click').on('click', function () {
        tablaMedidores.draw();
    });

    $('#clearSearchBtnMedidores').on('click', function (e) {
        $('#customSearchMedidores').val('');
        tablaMedidores.draw();
    });
}

function guardarOeditarMedidor(tipoProceso) {
    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevoMedidor' : 'editarMedidor';

    if (!validarCampo(data.get('numero'), 'El numero de serie es requerido', inputs.numero)) return;

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
                tablaMedidores.ajax.reload();
                Swal.close();
                $('#modal-medidores').modal('hide');
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

    $("#guardar-registro").on("click", function () {
        guardarOeditarMedidor('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarMedidor('2');
    });

    $("#btn-agregar").on("click", function () {
        abrirModalNuevoMedidor();
    });

    $("#tbl-medidores tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarMedidor(this);
    });
}

function iniciarTodo() {
    eventosUsuarios();
    cargarContratos();
    cargarInstaladores();
    cargarMedidores();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);