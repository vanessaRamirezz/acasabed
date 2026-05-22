import { alertaError, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus } from "../metodos/metodos.js";

let tablaServicios;
let operacionesCargadas = false;
let tiposServiciosCargadas = false;

const inputCodigo = document.getElementById('codigo-servicio');
const inputNombre = document.getElementById('nombre-servicio');
const inputValor = document.getElementById('valor-servicio');
const selectOperacion = document.getElementById('operacion-servicio');
const selectTipo = document.getElementById('tipo-servicio');
const selectEstado = document.getElementById('estado-servicio');
const inputIdServicio = document.getElementById('id-servicio');

function iniciarTabla() {
    tablaServicios = $('#tbl-servicios').DataTable({
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        ordering: false
    });

    traerServicios();
}

function cargarOperaciones(callback = null) {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getOperacionesServicio',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje);
                return;
            }

            selectOperacion.innerHTML = '<option value="">Seleccione</option>';

            response.data.forEach(function (operacion) {
                selectOperacion.innerHTML += `<option value="${operacion.id_operacion}">${operacion.nombre}</option>`;
            });

            operacionesCargadas = true;

            if (typeof callback === 'function') {
                callback();
            }
        },
        error: function () {
            alertaError('No se pudieron cargar las operaciones');
        }
    });
}

function cargarTipos(callback = null) {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getTipoServicio',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje);
                return;
            }

            selectTipo.innerHTML = '<option value="">Seleccione</option>';

            response.data.forEach(function (tipoServicio) {
                selectTipo.innerHTML += `<option value="${tipoServicio.id_tipos_servicios}">${tipoServicio.nombre}</option>`;
            });

            tiposServiciosCargadas = true;

            if (typeof callback === 'function') {
                callback();
            }
        },
        error: function () {
            alertaError('No se pudieron cargar los tipos de servicio');
        }
    });
}

function traerServicios() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getServicios',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje);
                return;
            }

            tablaServicios.clear();

            response.data.forEach(function (row) {
                let estadoHtml = '-';

                if (row.estado === 'ACTIVO' || row.estado === 'Activo') {
                    estadoHtml = `<span class="badge badge-success">${row.estado}</span>`;
                } else if (row.estado === 'INACTIVO' || row.estado === 'Inactivo') {
                    estadoHtml = `<span class="badge badge-danger">${row.estado}</span>`;
                }

                let rowData = [
                    row.codigo ?? '',
                    row.nombre ?? '',
                    Number(row.valor ?? 0).toFixed(2),
                    estadoHtml,
                    row.operacion ?? '-',
                    row.nombre_tipo ?? '-'
                ];

                rowData.push(`
                    <button class="btn btn-info btn-sm btn-editar-servicio"
                        data-servicio='${encodeURIComponent(JSON.stringify(row))}'>
                        <i class="fa fa-edit"></i>
                    </button>
                `);

                tablaServicios.row.add(rowData);
            });

            tablaServicios.draw();

            $('#tbl-servicios tbody').off('click', '.btn-editar-servicio').on('click', '.btn-editar-servicio', function () {
                const servicio = JSON.parse(decodeURIComponent($(this).attr('data-servicio')));
                abrirModalEditar(servicio);
            });
        },
        error: function () {
            alertaError('Error al cargar los servicios');
        }
    });
}

function limpiarCampos() {
    $('#codigo-servicio').val('');
    $('#nombre-servicio').val('');
    $('#valor-servicio').val('');
    $('#operacion-servicio').val('');
    $('#tipo-servicio').val('');
    $('#estado-servicio').val('1');
    $('#id-servicio').val('');

    eliminarColorYfocus(inputCodigo);
    eliminarColorYfocus(inputNombre);
    eliminarColorYfocus(inputValor);
    eliminarColorYfocus(selectOperacion);
    eliminarColorYfocus(selectTipo);
}

function abrirModalNuevo() {
    limpiarCampos();
    $('.modal-guardar').show();
    $('.modal-editar').hide();
    $('.contenedor-estado-servicio').addClass('d-none');
    $('#modal-servicio-label').text('Nuevo servicio');
    $('#modal-servicio').modal('show');
}

function abrirModalEditar(servicio) {
    limpiarCampos();
    $('.modal-guardar').hide();
    $('.modal-editar').show();
    $('.contenedor-estado-servicio').removeClass('d-none');

    $('#modal-servicio-label').text('Editar servicio');
    $('#id-servicio').val(servicio.id_servicio ?? '');
    $('#codigo-servicio').val(servicio.codigo ?? '');
    $('#nombre-servicio').val(servicio.nombre ?? '');
    $('#valor-servicio').val(servicio.monto ?? '');
    $('#operacion-servicio').val(servicio.id_operacion ?? '');
    $('#tipo-servicio').val(servicio.tipo ?? '');
    $('#estado-servicio').val((servicio.estado ?? '').toUpperCase() === 'ACTIVO' || servicio.estado === 'Activo' ? '1' : '0');

    $('#modal-servicio').modal('show');
}

function validarFormulario() {
    const codigo = $('#codigo-servicio').val().trim();
    const nombre = $('#nombre-servicio').val().trim();
    const valorTexto = $('#valor-servicio').val().trim();
    const operacion = $('#operacion-servicio').val().trim();
    const tipo = $('#tipo-servicio').val().trim();

    if (codigo === '') {
        alertaError('El código es requerido');
        colorEnInputConFocus(inputCodigo);
        return false;
    }
    eliminarColorYfocus(inputCodigo);

    if (nombre === '') {
        alertaError('El nombre es requerido');
        colorEnInputConFocus(inputNombre);
        return false;
    }
    eliminarColorYfocus(inputNombre);

    if (operacion === '') {
        alertaError('Debes seleccionar una operación');
        colorEnInputConFocus(selectOperacion);
        return false;
    }
    eliminarColorYfocus(selectOperacion);

    return true;
}

function guardarOEditarServicio(tipoProceso) {
    if (!validarFormulario()) {
        return;
    }

    const url = tipoProceso === 'nuevo' ? 'nuevoServicio' : 'editarServicio';

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
        url: baseURL + url,
        dataType: 'json',
        data: {
            idServicio: $('#id-servicio').val().trim(),
            codigo: $('#codigo-servicio').val().trim(),
            nombre: $('#nombre-servicio').val().trim(),
            valor: $('#valor-servicio').val().trim(),
            operacion: $('#operacion-servicio').val().trim(),
            tipo: $('#tipo-servicio').val().trim(),
            estado: $('#estado-servicio').val().trim()
        },
        success: function (response) {
            if (response.status === 'success') {
                alertaOk(response.mensaje);
                $('#modal-servicio').modal('hide');
                traerServicios();
                Swal.close();
                return;
            }

            alertEnSweet('error', 'Uups..', response.mensaje);
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operación');
        }
    });
}

function eventos() {
    $('#btn-agregar-servicio').on('click', function () {
        if (operacionesCargadas) {
            abrirModalNuevo();
            return;
        }

        cargarOperaciones(abrirModalNuevo);
    });

    $('#guardar-servicio').on('click', function () {
        guardarOEditarServicio('nuevo');
    });

    $('#actualizar-servicio').on('click', function () {
        guardarOEditarServicio('editar');
    });
}

function iniciarTodo() {
    iniciarTabla();
    cargarOperaciones();
    cargarTipos();
    eventos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
