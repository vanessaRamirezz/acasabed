import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, colorEnInputConFocusSelect, eliminarColorYfocus, eliminarColorYfocusSelect, validarCampo } from "../metodos/metodos.js";

let tablaContratos;

const inputs = {
    numeroContrato: $('#numero-contrato'),
    motivo: $('#motivo'),
    idContrato: $('#id-contrato'),
}

function getData() {
    let formData = new FormData();

    formData.append('numeroContrato', inputs.numeroContrato.val());
    formData.append('motivo', inputs.motivo.val());
    formData.append('idContrato', inputs.idContrato.val());

    return formData;
}

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
                data: 'estadoContrato',
                render: function (data, type, row) {
                    if (data == 'APROBADO') {
                        return '<span style="color: green;">' + data + '</span>';
                    } else {
                        return '<span style="color: red;">' + data + '</span>';
                    }
                }
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
                                    <i class="fas fa-file-contract text-danger"></i> Contrato pdf
                                </a> 

                                <a class="dropdown-item dropdown-item-custom btn-suspender-contrato" href="#"
                                    data-contrato="${encodeURIComponent(JSON.stringify(row))}">
                                    <i class="fas fa-ban mr-2 text-warning" ></i> Suspender Contrato
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

function abrirModalSuspenderContrato(elemento) {
    $('.modal.editar').show();

    inputs.numeroContrato.val('');
    eliminarColorYfocusSelect(inputs.motivo[0]);
    inputs.idContrato.val('');
    var dataContrato = JSON.parse(
        decodeURIComponent($(elemento).attr('data-contrato'))
    );

    if (dataContrato.estadoContrato == 'SUSPENDIDO') {
        inputs.numeroContrato.val(dataContrato.cod_contrato).prop('disabled', true);
        inputs.motivo.val(dataContrato.motivoSuspencion).prop('disabled', true);
        $("#actualizar-registro").prop('disabled', true);
    } else {
        inputs.numeroContrato.val(dataContrato.cod_contrato).prop('disabled', true);
        inputs.motivo.val(dataContrato.motivoSuspencion).prop('disabled', false);
        inputs.idContrato.val(dataContrato.id_contrato);
        $("#actualizar-registro").prop('disabled', false);
    }


    $('#numero-contrato').val()
    $('#modal-suspender-contrato').modal('show');
}

function suspenderContrato() {
    const data = getData();

    if (!data.get('motivo')) {
        alertaError('El motivo es requerido');
        colorEnInputConFocusSelect(inputs.motivo[0]);
        return false;
    } else {
        eliminarColorYfocusSelect(inputs.motivo[0]);
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
        url: baseURL + 'suspenderContratoUnoaUno',
        data: data,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                tablaContratos.ajax.reload();
                Swal.close();
                $('#modal-suspender-contrato').modal('hide');
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


    // evento para enviar a suspender el contrato desde la tabla 
    $("#tbl-contratos tbody").on("click", '.btn-suspender-contrato', function () {
        abrirModalSuspenderContrato(this);
    });

    $("#actualizar-registro").on("click", function () {
        suspenderContrato();
    });
}

function iniciarTodo() {
    cargarContratos();
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);