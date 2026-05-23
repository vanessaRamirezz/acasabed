import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo, colorEnInputConFocusSelect, eliminarColorYfocusSelect } from "../metodos/metodos.js";

let tablaLecturas;

const inputs = {
    periodo: $('#periodo'),
    contrato: $('#contrato'),
    fecha: $('#fecha'),
    valor: $('#valor'),
    instalador: $('#instalador'),
    idLectura: $("#id-lectura"),
    filtroRutaReporteLectura: $('#filtro-ruta-reporte-lectura'),
    filtroDepartamentoReporteLectura: $('#filtro-departamento-reporte-lectura'),
    filtroMunicipioReporteLectura: $('#filtro-municipio-reporte-lectura'),
    filtroDistritoReporteLectura: $('#filtro-distrito-reporte-lectura'),
    filtroColoniaReporteLectura: $('#filtro-colonia-reporte-lectura'),
    filtroRutaCargarContratos: $('#filtro-ruta-cargar-contratos'),
    filtroDepartamentoCargarContratos: $('#filtro-departamento-cargar-contratos'),
    filtroMunicipioCargarContratos: $('#filtro-municipio-cargar-contratos'),
    filtroDistritoCargarContratos: $('#filtro-distrito-cargar-contratos'),
    filtroColoniaCargarContratos: $('#filtro-colonia-cargar-contratos'),
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

function resetSelect(selector, placeholder = "Todos") {
    $(selector).empty().append(`<option value="-1">${placeholder}</option>`);
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

function cargarRutasFiltroReporteLectura() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'selectRuta',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar las rutas');
                return;
            }

            inputs.filtroRutaReporteLectura.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (rutas) {
                inputs.filtroRutaReporteLectura.append(
                    $('<option></option>')
                        .attr('value', rutas.id_ruta)
                        .text(rutas.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar las rutas');
        }
    });
}

function cargarDepartamentosReporteLectura() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDepartamentos',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los departamentos');
                return;
            }

            inputs.filtroDepartamentoReporteLectura.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (departamento) {
                inputs.filtroDepartamentoReporteLectura.append(
                    $('<option></option>')
                        .attr('value', departamento.id_departamento)
                        .text(departamento.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los departamentos');
        }
    });
}

function cargarMunicipiosReporteLectura(idDepartamento) {
    resetSelect(inputs.filtroMunicipioReporteLectura, 'Todos');
    resetSelect(inputs.filtroDistritoReporteLectura, 'Todos');
    resetSelect(inputs.filtroColoniaReporteLectura, 'Todos');

    if (!idDepartamento || idDepartamento === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getMunicipios',
        data: { idDepartamento },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los municipios');
                return;
            }

            response.data.forEach(function (municipio) {
                inputs.filtroMunicipioReporteLectura.append(
                    $('<option></option>')
                        .attr('value', municipio.id_municipio)
                        .text(municipio.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los municipios');
        }
    });
}

function cargarDistritosReporteLectura(idMunicipio) {
    resetSelect(inputs.filtroDistritoReporteLectura, 'Todos');
    resetSelect(inputs.filtroColoniaReporteLectura, 'Todos');

    if (!idMunicipio || idMunicipio === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getDistritos',
        data: { idMunicipio },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los distritos');
                return;
            }

            response.data.forEach(function (distrito) {
                inputs.filtroDistritoReporteLectura.append(
                    $('<option></option>')
                        .attr('value', distrito.id_distrito)
                        .text(distrito.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los distritos');
        }
    });
}

function cargarColoniasReporteLectura(idDistrito) {
    resetSelect(inputs.filtroColoniaReporteLectura, 'Todos');

    if (!idDistrito || idDistrito === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getColonias',
        data: { idDistrito },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar las colonias');
                return;
            }

            response.data.forEach(function (colonia) {
                inputs.filtroColoniaReporteLectura.append(
                    $('<option></option>')
                        .attr('value', colonia.id_colonia)
                        .text(colonia.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar las colonias');
        }
    });
}

function reiniciarFiltrosReporteLectura() {
    inputs.filtroRutaReporteLectura.val('-1');
    inputs.filtroDepartamentoReporteLectura.val('-1');
    resetSelect(inputs.filtroMunicipioReporteLectura, 'Todos');
    resetSelect(inputs.filtroDistritoReporteLectura, 'Todos');
    resetSelect(inputs.filtroColoniaReporteLectura, 'Todos');
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

// function cargarContratosLectura(filtros = {}) {
//     instaladorActualLote = null;
//     const periodo = $('#periodo-lote').val();

//     if (!periodo) {
//         alertaError('Debe seleccionar un periodo');
//         return;
//     }

//     $.ajax({
//         url: baseURL + 'getContratosPeriodos',
//         type: 'GET',
//         dataType: 'json',
//         data: {
//             periodo: periodo,
//             departamento: filtros.departamento || '-1',
//             municipio: filtros.municipio || '-1',
//             distrito: filtros.distrito || '-1',
//             colonia: filtros.colonia || '-1'
//         },
//         success: function (response) {

//             const tbody = $('#tbl-lecturas-lote tbody');
//             tbody.empty();

//             if (!response.data || response.data.length === 0) {
//                 tbody.html(`
//                 <tr>
//                     <td colspan="4" class="text-center">
//                         No hay contratos pendientes para este periodo
//                     </td>
//                 </tr>
//             `);
//                 return;
//             }

//             response.data.forEach((c, index) => {

//                 tbody.append(`
//                 <tr data-index="${index}">
                    
//                     <td>${c.numero_contrato}</td>
//                     <td>${c.nombre_completo}</td>

//                    <td>
//                         <select class="form-control instalador-lote w-100"
//                                 data-index="${index}"
//                                 data-id="${c.id_contrato}">
//                         </select>
//                     </td>

//                     <td>
//                         <input type="number"
//                                 class="form-control lectura-input-lote"
//                                 data-index="${index}"
//                                 data-id="${c.id_contrato}">
//                     </td>

//                 </tr>
//             `);
//             });

//             // 🔥 IMPORTANTE: inicializar Select2 DESPUÉS del render
//             inicializarInstaladoresLote();
//         }
//     });
// }

function cargarContratosLectura(filtros = {}) {

    instaladorActualLote = null;

    const periodo = $('#periodo-lote').val();

    if (!periodo) {
        alertaError('Debe seleccionar un periodo');
        return;
    }

    const tbody = $('#tbl-lecturas-lote tbody');

    // 🔥 MOSTRAR CARGA
    tbody.html(`
        <tr>
            <td colspan="4" class="text-center py-4">
                
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="sr-only">Loading...</span>
                </div>

                <div>
                    Cargando contratos...
                </div>

            </td>
        </tr>
    `);

    $.ajax({
        url: baseURL + 'getContratosPeriodos',
        type: 'GET',
        dataType: 'json',
        data: {
            periodo: periodo,
            ruta: filtros.ruta || '-1',
            departamento: filtros.departamento || '-1',
            municipio: filtros.municipio || '-1',
            distrito: filtros.distrito || '-1',
            colonia: filtros.colonia || '-1'
        },

        success: function (response) {

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

            inicializarInstaladoresLote();
        },

        error: function () {

            tbody.html(`
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        Error al cargar contratos
                    </td>
                </tr>
            `);

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

function cargarRutaFiltroContratos() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'selectRuta',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar las rutas');
                return;
            }

            inputs.filtroRutaCargarContratos.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (rutas) {
                inputs.filtroRutaCargarContratos.append(
                    $('<option></option>')
                        .attr('value', rutas.id_ruta)
                        .text(rutas.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar las rutas');
        }
    });
}

function cargarDepartamentosFiltroContratos() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getDepartamentos',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los departamentos');
                return;
            }

            inputs.filtroDepartamentoCargarContratos.empty().append('<option value="-1">Todos</option>');

            response.data.forEach(function (departamento) {
                inputs.filtroDepartamentoCargarContratos.append(
                    $('<option></option>')
                        .attr('value', departamento.id_departamento)
                        .text(departamento.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los departamentos');
        }
    });
}

function cargarMunicipiosFiltroContratos(idDepartamento) {
    resetSelect(inputs.filtroMunicipioCargarContratos, 'Todos');
    resetSelect(inputs.filtroDistritoCargarContratos, 'Todos');
    resetSelect(inputs.filtroColoniaCargarContratos, 'Todos');

    if (!idDepartamento || idDepartamento === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getMunicipios',
        data: { idDepartamento },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los municipios');
                return;
            }

            response.data.forEach(function (municipio) {
                inputs.filtroMunicipioCargarContratos.append(
                    $('<option></option>')
                        .attr('value', municipio.id_municipio)
                        .text(municipio.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los municipios');
        }
    });
}

function cargarDistritosFiltroContratos(idMunicipio) {
    resetSelect(inputs.filtroDistritoCargarContratos, 'Todos');
    resetSelect(inputs.filtroColoniaCargarContratos, 'Todos');

    if (!idMunicipio || idMunicipio === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getDistritos',
        data: { idMunicipio },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar los distritos');
                return;
            }

            response.data.forEach(function (distrito) {
                inputs.filtroDistritoCargarContratos.append(
                    $('<option></option>')
                        .attr('value', distrito.id_distrito)
                        .text(distrito.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar los distritos');
        }
    });
}

function cargarColoniasFiltroContratos(idDistrito) {
    resetSelect(inputs.filtroColoniaCargarContratos, 'Todos');

    if (!idDistrito || idDistrito === '-1') {
        return;
    }

    $.ajax({
        type: 'POST',
        url: baseURL + 'getColonias',
        data: { idDistrito },
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success') {
                alertaError(response.mensaje || 'No se pudieron cargar las colonias');
                return;
            }

            response.data.forEach(function (colonia) {
                inputs.filtroColoniaCargarContratos.append(
                    $('<option></option>')
                        .attr('value', colonia.id_colonia)
                        .text(colonia.nombre)
                );
            });
        },
        error: function () {
            alertaError('Error al cargar las colonias');
        }
    });
}

function reiniciarFiltrosCargaContratos() {
    inputs.filtroRutaCargarContratos.val('-1');
    inputs.filtroDepartamentoCargarContratos.val('-1');
    resetSelect(inputs.filtroMunicipioCargarContratos, 'Todos');
    resetSelect(inputs.filtroDistritoCargarContratos, 'Todos');
    resetSelect(inputs.filtroColoniaCargarContratos, 'Todos');
}

function abrirModalFiltroContratos() {
    const periodo = $('#periodo-lote').val();

    if (!periodo) {
        alertaError('Debe seleccionar un período');
        return;
    }

    reiniciarFiltrosCargaContratos();
    $('#modal-filtro-contratos-direccion').modal('show');
}

function confirmarCargaContratosConFiltro() {
    const filtros = {
        ruta: inputs.filtroRutaCargarContratos.val(),
        departamento: inputs.filtroDepartamentoCargarContratos.val(),
        municipio: inputs.filtroMunicipioCargarContratos.val(),
        distrito: inputs.filtroDistritoCargarContratos.val(),
        colonia: inputs.filtroColoniaCargarContratos.val()
    };

    $('#modal-filtro-contratos-direccion').modal('hide');
    cargarContratosLectura(filtros);
}

function abrirModalReporteLecturas() {
    const periodo = $('#periodo-lote').val();

    if (!periodo) {
        alertaError('Debe seleccionar un período');
        return;
    }

    reiniciarFiltrosReporteLectura();
    $('#modal-reporte-lecturas-direccion').modal('show');
}

function generarPDFReporteLecturas() {
    const periodo = $('#periodo-lote').val();

    if (!periodo) {
        alertaError('Debe seleccionar un período');
        return;
    }

    const params = new URLSearchParams({
        periodo
    });

    const ruta = inputs.filtroRutaReporteLectura.val();
    const departamento = inputs.filtroDepartamentoReporteLectura.val();
    const municipio = inputs.filtroMunicipioReporteLectura.val();
    const distrito = inputs.filtroDistritoReporteLectura.val();
    const colonia = inputs.filtroColoniaReporteLectura.val();

    if (ruta && ruta !== '-1') {
        params.append('ruta', ruta);
    }

    if (departamento && departamento !== '-1') {
        params.append('departamento', departamento);
    }

    if (municipio && municipio !== '-1') {
        params.append('municipio', municipio);
    }

    if (distrito && distrito !== '-1') {
        params.append('distrito', distrito);
    }

    if (colonia && colonia !== '-1') {
        params.append('colonia', colonia);
    }

    const ventana = window.open(
        `${baseURL}reporte-toma-lecturas/pdf?${params.toString()}`,
        '_blank'
    );

    if (!ventana) {
        alertaError('El navegador bloqueó la ventana del PDF. Permite ventanas emergentes e inténtalo nuevamente.');
        return;
    }

    $('#modal-reporte-lecturas-direccion').modal('hide');
    ventana.focus();
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
        abrirModalFiltroContratos();
    });

    $('#btn-guardar-lecturas').on('click', function () {
        guardarLecturasLote();
    });

    $('#btn-hacer-pdf-lecturas').on('click', function () {
        abrirModalReporteLecturas();
    });

    $('#btn-generar-pdf-lecturas-direccion').on('click', function () {
        generarPDFReporteLecturas();
    });

    inputs.filtroDepartamentoReporteLectura.on('change', function () {
        cargarMunicipiosReporteLectura($(this).val());
    });

    inputs.filtroMunicipioReporteLectura.on('change', function () {
        cargarDistritosReporteLectura($(this).val());
    });

    inputs.filtroDistritoReporteLectura.on('change', function () {
        cargarColoniasReporteLectura($(this).val());
    });

    $('#btn-confirmar-cargar-contratos').on('click', function () {
        confirmarCargaContratosConFiltro();
    });

    inputs.filtroDepartamentoCargarContratos.on('change', function () {
        cargarMunicipiosFiltroContratos($(this).val());
    });

    // inputs.filtroRutaCargarContratos.on('change', function () {
    //     cargarRutaFiltroContratos($(this).val());
    // });

    inputs.filtroMunicipioCargarContratos.on('change', function () {
        cargarDistritosFiltroContratos($(this).val());
    });

    inputs.filtroDistritoCargarContratos.on('change', function () {
        cargarColoniasFiltroContratos($(this).val());
    });
}

function iniciarTodo() {
    eventosUsuarios();
    cargarPeriodos();
    cargarPeriodosLote();
    cargarContratos();
    cargarInstaladores();
    cargarLecturas();
    cargarRutasFiltroReporteLectura();
    cargarDepartamentosReporteLectura();
    cargarRutaFiltroContratos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
