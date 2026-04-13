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
                data: 'nombre'
            },
            {
                data: 'fechaTexto'
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

                                <a class="dropdown-item dropdown-item-custom btn-ver-contrato-pdf" href="#"
                                    data-id="${row.id}">
                                    <i class="fas fa-file-contract"></i> Contrato pdf
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


function eventosUsuarios() {
    $(document).on("click", ".btn-ver-contrato-pdf", function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        // console.log("ID:", id); // 👈 clave

        if (!id) {
            alert("ID no válido");
            return;
        }

        const encoded = btoa(id);
        window.open(baseURL + 'contratos/contrato?solicitud=' + encoded, '_blank');
    });


}

function iniciarTodo() {
    cargarContratos();
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);