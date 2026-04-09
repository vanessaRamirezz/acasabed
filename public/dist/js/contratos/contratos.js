import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

let tablaContratos;

function cargarContratos() {
    tablaContratos = $('#tbl-contratos').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getContratosTabla',
            data: function (d) {
                d.searchValue = $('#customSearchContratos').val();
            }
        },
        columns: [
            {
                data: 'cod_contrato'
            },
            {
                data: 'cod_solicitud'
            },
            {
                data: 'nombre'
            },
            {
                data: 'estado'
            },
            {
                data: null,
                render: function (data, type, row) {

                    return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                Acciones
                            </button>
                            <div class="dropdown-menu">

                                <a class="dropdown-item btn-ver-contrato" href="#"
                                    data-id="${row.cod_contrato}"
                                    data-nombre="${row.nombre}">
                                    <i class="fas fa-copy"></i> Ver contrato
                                </a>

                                <a class="dropdown-item btn-ver-solicitud" href="#"
                                    data-id="${row.cod_solicitud}">
                                    <i class="fas fa-file-alt"></i> Ver solicitud
                                </a>

                            </div>
                        </div>
                        `;
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
    $('#customSearchContratos').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaContratos.draw(); // ahora sí funciona
        }
    });

    $('#searchBtnClientes').off('click').on('click', function () {
        tablaContratos.draw();
    });

    $('#clearSearchBtnContratos').on('click', function (e) {
        $('#customSearchContratos').val('');
        tablaContratos.draw();
    });
}

function iniciarTodo() {
    cargarContratos();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);