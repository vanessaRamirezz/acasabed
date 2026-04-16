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
    // inputs.periodo.val(null).trigger('change');
    inputs.contrato.val(null).trigger('change');
    inputs.fecha.val('');
    inputs.valor.val('');
    inputs.instalador.val(null).trigger('change');
    inputs.idLectura.val('');
}

function cargarPeriodos() {

    $.ajax({
        url: baseURL + 'getPeriodosSelect',
        type: 'GET',
        dataType: 'json',
        success: function (response) {

            const periodo = response.data;

            if (!periodo) return;

            const $select = $('#periodo');

            $select.empty();

            $select.append(
                $('<option>', {
                    value: periodo.id,
                    text: periodo.periodo,
                    selected: true
                })
            );

            $('#periodo').prop('disabled', true);
        }
    });
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
                data: 'fecha_toma_lectura_texto'
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

    // 👉 SETEAR FECHA ACTUAL
    const hoy = new Date();
    const fecha = hoy.toISOString().split('T')[0];
    $('#fecha').val(fecha);

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
                if (tipoProceso != '1') {
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


// seccion para nuevo modal carga por lotes
let lecturasLote = [];

function cargarPeriodosLote() {
    $.ajax({
        url: baseURL + 'getPeriodosSelect',
        type: 'GET',
        dataType: 'json',
        success: function (response) {

            const periodo = response.data;

            if (!periodo) return;

            const $select = $('#periodo-lote');

            $select.empty();

            $select.append(
                $('<option>', {
                    value: periodo.id,
                    text: periodo.periodo,
                    selected: true
                })
            );

            $('#periodo-lote').prop('disabled', true);
        }
    });
}

let cacheInstaladores = null;
let instaladorActualLote = null;

function inicializarInstaladoresLote() {

    $('.instalador-lote').each(function () {

        const $select = $(this);

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2('destroy');
        }

        $select.select2({
            placeholder: "Buscar instalador",
            allowClear: true,
            theme: 'bootstrap4',
            ajax: {
                transport: function (params, success, failure) {

                    if (cacheInstaladores) {
                        success(cacheInstaladores);
                        return;
                    }

                    $.ajax({
                        url: baseURL + 'getSelectInstaladores',
                        dataType: 'json',
                        success: function (r) {

                            cacheInstaladores = {
                                results: r.data.map(i => ({
                                    id: i.id,
                                    text: i.nombre_de_instalador
                                }))
                            };

                            success(cacheInstaladores);
                        },
                        error: failure
                    });
                }
            }
        });

    });
}

function limpiarLoteLecturas() {
    lecturasLote = [];
    $('#tbl-lecturas-lote tbody').empty();
}

function cargarContratosLectura() {
    instaladorActualLote = null;
    const periodo = $('#periodo-lote').val();

    if (!periodo) {
        alertaError('Debe seleccionar un periodo');
        return;
    }

    $.ajax({
        url: baseURL + 'getContratosPeriodos',
        type: 'GET',
        dataType: 'json',
        data: {
            periodo: periodo
        },
        success: function (response) {

            const tbody = $('#tbl-lecturas-lote tbody');
            tbody.empty();

            if (!response.data || response.data.length === 0) {
                tbody.html(`
                <tr>
                    <td colspan="4" class="text-center">
                        No hay contratos pendientes para este periodo
                    </td>
                </tr>
            `);
                return;
            }

            response.data.forEach((c, index) => {

                tbody.append(`
                <tr data-index="${index}">
                    
                    <td>${c.numero_contrato}</td>
                    <td>${c.nombre_completo}</td>

                   <td>
                        <select class="form-control instalador-lote w-100"
                                data-index="${index}"
                                data-id="${c.id_contrato}">
                        </select>
                    </td>

                    <td>
                        <input type="number"
                                class="form-control lectura-input-lote"
                                data-index="${index}"
                                data-id="${c.id_contrato}">
                    </td>

                </tr>
            `);
            });

            // 🔥 IMPORTANTE: inicializar Select2 DESPUÉS del render
            inicializarInstaladoresLote();
        }
    });
}

function guardarLecturasLote() {

    const lecturas = obtenerLecturasLote();

    if (lecturas.length === 0) {
        alertaError('No hay lecturas para guardar');
        return;
    }

    $.ajax({
        url: baseURL + 'guardarLecturasMasivas',
        type: 'POST',
        dataType: 'json',
        data: {
            lecturas: JSON.stringify(lecturas)
        },
        success: function (response) {

            if (response.status === 'success') {
                alertaOk('Lecturas guardadas correctamente');
                $('#modal-lecturas').modal('hide');
                // 
                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                alertaError(response.mensaje);
            }
        },
        error: function () {
            alertaError('Error al guardar lecturas');
        }
    });
}

function obtenerLecturasLote() {

    let lecturas = [];

    $('.lectura-input-lote').each(function () {

        const valor = parseFloat($(this).val());
        const idContrato = $(this).data('id');

        if (!valor || valor <= 0) return;

        // 🔥 obtener fila actual
        const row = $(this).closest('tr');

        // 🔥 obtener select de esa fila
        const instalador = row.find('.instalador-lote').val();

        lecturas.push({
            idContrato: idContrato,
            valor: valor,
            periodo: $('#periodo-lote').val(),
            instalador: instalador, // ✅ correcto
            fecha: $('#fecha-lote').val()
        });
    });

    return lecturas;
}

function abrirModalNuevaLecturaLote() {

    $('.modal-guardar').show();
    $('.modal-editar').hide();

    limpiarFormulario();
    limpiarLoteLecturas();

    $('#periodo-lote').val(null).trigger('change');
    $('#instalador-lote').val(null).trigger('change');
    $('#fecha-lote').val('');

    cargarPeriodosLote();

    // 👉 SETEAR FECHA ACTUAL
    const hoy = new Date();
    const fecha = hoy.toISOString().split('T')[0];
    $('#fecha-lote').val(fecha);

    $('#modal-lecturas-lote').modal('show');
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


    // seccionde eventos del nuevo ingreso masivo de lecturas
    $("#btn-agregar-lote").on("click", function () {
        abrirModalNuevaLecturaLote();
    });

    // evento para caturar cambio de instalador 
    $(document).on('change', '.instalador-lote', function () {

        const data = $(this).select2('data')[0];

        if (!data) return;

        instaladorActualLote = {
            id: data.id,
            text: data.text
        };
    });

    $(document).on('keydown', '.lectura-input-lote', function (e) {

        if (e.key !== 'Enter') return;

        e.preventDefault();

        const inputs = $('.lectura-input-lote');
        const index = inputs.index(this);

        const next = inputs.get(index + 1);

        if (!next) return;

        const nextRow = $(next).closest('tr');
        const nextSelect = nextRow.find('.instalador-lote');

        if (instaladorActualLote) {

            // ❌ NO loops
            // ❌ NO cascadas
            // ❌ NO propagate all rows

            const option = new Option(
                instaladorActualLote.text,
                instaladorActualLote.id,
                true,
                true
            );

            nextSelect
                .empty() // 🔥 evita duplicados visuales
                .append(option)
                .val(instaladorActualLote.id)
                .trigger('change');
        }

        next.focus();
        next.select();
    });

    $('#btn-cargar-contratos').on('click', function () {
        cargarContratosLectura();
    });

    $('#btn-guardar-lecturas').on('click', function () {
        guardarLecturasLote();
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