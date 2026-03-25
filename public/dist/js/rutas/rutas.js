import { alertaError, eliminarColorYfocus, colorEnInputConFocus, alertaOk, alertEnSweet } from "../metodos/metodos.js";

let inputCodigo = document.getElementById('codigo');
let inputDesde = document.getElementById('desde');
let inputHasta = document.getElementById('hasta');
let codigo;
let desde;
let hasta;
let idRuta;
let tablaRutas;

function abrirModalNuevaRuta() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    // limpiar campos del formulario
    $('#codigo').val('').prop('disabled', false);
    $('#desde').val('');
    $('#hasta').val('');
    $('#id-ruta').val('');

    $('#model-rutas').modal('show');
}

function abrirModalEditarRuta(elemento) {
    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataRutas = JSON.parse(
        decodeURIComponent($(elemento).attr('data-ruta'))
    );

    $('#codigo').val(dataRutas.codigo).prop('disabled', true);
    $('#desde').val(dataRutas.desde);
    $('#hasta').val(dataRutas.hasta);
    $('#id-ruta').val(dataRutas.id_ruta);

    $('#model-rutas').modal('show');
}

function cargarRutas() {
    tablaRutas = $('#tbl-rutas').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getRutas',
            data: function (d) {
                d.searchValue = $('#customSearchRutas').val();
            }
        },
        columns: [
            {
                data: 'codigo'
            },
            {
                data: 'desde'
            },
            {
                data: 'hasta'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-ruta='${encodeURIComponent(JSON.stringify(row))}'>
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
    $('#customSearchRutas').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaRutas.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnRutas').off('click').on('click', function () {
        tablaRutas.draw();
    });

    $('#clearSearchBtnRutas').on('click', function (e) {
        $('#customSearchRutas').val('');
        tablaRutas.draw();
    });
}


function guardarOeditarRuta(tipoProceso) {
    codigo = $("#codigo").val().trim();
    desde = $('#desde').val().trim();
    hasta = $('#hasta').val().trim();
    idRuta = $('#id-ruta').val().trim();

    var tipo_proceso = tipoProceso === '1' ? 'nuevaRuta' : 'editarRuta';

    if (codigo === "") {
        alertaError('El Código es requerido');
        colorEnInputConFocus(inputCodigo);
        return false;
    } else {
        eliminarColorYfocus(inputCodigo);
    }

    if (desde === "") {
        alertaError('Campo desde es requerido');
        colorEnInputConFocus(inputDesde);
        return false;
    } else {
        eliminarColorYfocus(inputDesde);
    }

    if (hasta === "") {
        alertaError('Campo hasta es requerido');
        colorEnInputConFocus(inputHasta);
        return false;
    } else {
        eliminarColorYfocus(inputHasta);
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: {
            codigo,
            desde,
            hasta,
            idRuta
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                tablaRutas.ajax.reload();
                Swal.close();
                $('#model-rutas').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    })
}

function eventosUsuarios() {
    $("#btn-agregar").on("click", function () {
        abrirModalNuevaRuta();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarRuta('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarRuta('2');
    });

    $("#tbl-rutas tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarRuta(this);
    });
}

function iniciarTodo() {
    cargarRutas();
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);