import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo, colorEnInputConFocusSelect, eliminarColorYfocusSelect } from "../metodos/metodos.js";

let tablaLecturas;

const inputs = {
    periodo: $('#periodo'),
    contrato: $('#contrato'),
    fecha: $('#fecha'),
    valor: $('#valor'),
    instalador: $('#instalador'),
    idLectura: $("#id-lectura"),
};

function getData() {
    let formData = new FormData();

    formData.append('periodo', inputs.periodo.val());
    formData.append('contrato', inputs.contrato.val());
    formData.append('fecha', inputs.fecha.val());
    formData.append('valor', inputs.valor.val().trim());
    formData.append('instalador', inputs.instalador.val());
    formData.append('idLectura', inputs.idLectura.val());

    return formData;
}

function limpiarFormulario() {
    inputs.periodo.val(null).trigger('change');
    inputs.contrato.val(null).trigger('change');
    inputs.fecha.val('');
    inputs.valor.val('');
    inputs.instalador.val(null).trigger('change');
    inputs.idLectura.val('');
}

function cargarPeriodos() {
    $('#periodo').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getPeriodosSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(periodo => ({
                        id: periodo.id,
                        text: periodo.periodo
                    }))
                };
            },
            cache: true
        }
    })
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

function cargarLecturas() {
    tablaLecturas = $('#tbl-lecturas').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getLecturas',
            data: function (d) {
                d.searchValue = $('#customSearchLecturas').val();
            }
        },
        columns: [
            {
                data: 'nombre_de_periodo'
            },
            {
                data: 'codigo_de_contrato'
            },
            {
                data: 'fecha_toma_lectura'
            },
            {
                data: 'valor_obtenido'
            },
            {
                data: 'nombre_instalador'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-lectura='${encodeURIComponent(JSON.stringify(row))}'>
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
    $('#customSearchLecturas').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaLecturas.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnLecturas').off('click').on('click', function () {
        tablaLecturas.draw();
    });

    $('#clearSearchBtnLecturas').on('click', function (e) {
        $('#customSearchLecturas').val('');
        tablaLecturas.draw();
    });
}

function abrirModalNuevaLectura() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    eliminarColorYfocusSelect(inputs.periodo[0]);

    $('#modal-lecturas').modal('show');
}

function setSelect2Value(selector, id, text) {
    const option = new Option(text, id, true, true);
    $(selector).append(option).trigger('change');
}

function abrirModalEditarLectura(elemento) {
    limpiarFormulario();

    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataLectura = JSON.parse(
        decodeURIComponent($(elemento).attr('data-lectura'))
    );

    setSelect2Value('#periodo',
        dataLectura.id_de_periodo,
        dataLectura.nombre_de_periodo
    );

    setSelect2Value('#contrato',
        dataLectura.id_de_contrato,
        dataLectura.codigo_de_contrato
    );

    $('#fecha').val(dataLectura.fecha_toma_lectura);
    $('#valor').val(dataLectura.valor_obtenido);

    setSelect2Value('#instalador',
        dataLectura.id_de_instalador,
        dataLectura.nombre_instalador
    );

    $('#id-lectura').val(dataLectura.id);

    eliminarColorYfocusSelect(inputs.periodo[0]);


    $('#modal-lecturas').modal('show');
}

function guardarOeditarLectura(tipoProceso) {
    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevaLectura' : 'editarLectura';

    if (!data.get('periodo')) {
        alertaError('El periodo es requerido');
        colorEnInputConFocusSelect(inputs.periodo[0]);
        return false;
    } else {
        eliminarColorYfocusSelect(inputs.periodo[0]);
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
                tablaLecturas.ajax.reload();
                Swal.close();
                if(tipoProceso != '1'){
                    $('#modal-lecturas').modal('hide');
                } else {
                    inputs.contrato.val(null).trigger('change');
                    inputs.valor.val('');
                }
                // $('#modal-lecturas').modal('hide');
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
        guardarOeditarLectura('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarLectura('2');
    });

    $("#btn-agregar").on("click", function () {
        abrirModalNuevaLectura();
    });

    $("#tbl-lecturas tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarLectura(this);
    });
}

function iniciarTodo() {
    eventosUsuarios();
    cargarPeriodos();
    cargarContratos();
    cargarInstaladores();
    cargarLecturas();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);