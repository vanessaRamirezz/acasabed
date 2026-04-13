import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaPeriodos;

const inputs = {
    nombre: $("#periodo"),
    desde: $("#fecha-desde"),
    hasta: $("#fecha-hasta"),
    estado: $('input[name="estado"]'),
    idPeriodo: $("#id-periodo")
};

function getData() {
    return {
        nombre: inputs.nombre.val(),
        desde: inputs.desde.val(),
        hasta: inputs.hasta.val(),
        estado: $('input[name="estado"]:checked').val() || null,
        idPeriodo: inputs.idPeriodo.val().trim()
    };
}

function limpiarFormulario() {
    inputs.nombre.val('');
    inputs.desde.val('');
    inputs.hasta.val('');
    inputs.idPeriodo.val('');

    // reset radios correctamente
    $('input[name="estado"]').prop('checked', false);
    $('#estado-activo').prop('checked', true);

    eliminarColorYfocus(inputs.nombre[0]);
    eliminarColorYfocus(inputs.desde[0]);
}

function abrirModalNuevoPeriodo() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();

    eliminarColorYfocus(inputs.nombre[0]);
    eliminarColorYfocus(inputs.desde[0]);

    $('#estado-inactivo').prop('disabled', true);

    $('#modal-periodos').modal('show');
}

function abrirModalEditarPeriodo(elemento) {
    $('.modal-guardar').hide();
    $('.modal-editar').show();

    var dataPeriodo = JSON.parse(
        decodeURIComponent($(elemento).attr('data-periodo'))
    );

    $('#periodo').val(dataPeriodo.nombre_periodo);
    $('#fecha-desde').val(dataPeriodo.fecha1);
    $('#fecha-hasta').val(dataPeriodo.fecha2);
    if (dataPeriodo.status == 'ACTIVO') {
        $('#estado-activo').prop('checked', true);
    } else {
        $('#estado-inactivo').prop('checked', true);
    }
    $('#id-periodo').val(dataPeriodo.id);

    eliminarColorYfocus(inputs.nombre[0]);
    eliminarColorYfocus(inputs.desde[0]);

    $('#estado-inactivo').prop('disabled', false);

    $('#modal-periodos').modal('show');
}

function cargarPeriodos() {
    tablaPeriodos = $('#tbl-periodos').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getPeriodos',
            data: function (d) {
                d.searchValue = $('#customSearchPeriodos').val();
            }
        },
        columns: [
            {
                data: 'nombre_periodo'
            },
            {
                data: 'fecha1_texto'
            },
            {
                data: 'fecha2_texto'
            },
            {
                data: 'status',
                render: function (data) {
                    if (data == 'ACTIVO') {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-secondary">Cerrado</span>';
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button class="btn btn-info btn-sm btn-ver-opciones"
                                data-periodo='${encodeURIComponent(JSON.stringify(row))}'>
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
    $('#customSearchPeriodos').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaPeriodos.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnPeriodos').off('click').on('click', function () {
        tablaPeriodos.draw();
    });

    $('#clearSearchBtnPeriodos').on('click', function (e) {
        $('#customSearchPeriodos').val('');
        tablaPeriodos.draw();
    });
}

function guardarOeditarPeriodo(tipoProceso) {
    const data = getData();

    let tipo_proceso = tipoProceso === '1' ? 'nuevoPeriodo' : 'editarPeriodo';

    if (!validarCampo(data.nombre, 'Nombre de periodo es requerido', inputs.nombre)) return;
    if (!validarCampo(data.desde, 'Fecha desde es requerido', inputs.desde)) return;

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
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                tablaPeriodos.ajax.reload();
                Swal.close();
                $('#modal-periodos').modal('hide');
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
        guardarOeditarPeriodo('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarPeriodo('2');
    });

    $("#btn-agregar").on("click", function () {
        abrirModalNuevoPeriodo();
    });

    $("#tbl-periodos tbody").on("click", '.btn-ver-opciones', function () {
        abrirModalEditarPeriodo(this);
    });

}

function iniciarTodo() {
    eventosUsuarios();
    cargarPeriodos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);