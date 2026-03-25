import { alertEnSweet, alertaError, alertaOk } from "../metodos/metodos.js";

function traerAccesos() {

    Swal.fire({
        title: 'Espere...',
        html: 'Cargando datos...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        heightAuto: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: "get",
        url: baseURL + "getPerfiles",
        dataType: "json",
        success: function (response) {

            if (response.status === 'success') {

                let perfiles = response.data;

                $("#permisos tbody").empty();

                perfiles.forEach(function (perfil) {

                    let totalPermisos = (perfil.accesos && perfil.accesos.length)
                        ? perfil.accesos.length
                        : 0;

                    let fila = `<tr>
                        <td>${perfil.nombre}</td>
                        <td>${totalPermisos}</td>
                        <td>
                            <button class="btn btn-info btn-ver"
                                    data-perfil='${JSON.stringify(perfil)}'>
                                <i class="fa fa-eye"></i>
                            </button>

                            <button class="btn btn-primary btn-editar" 
                                    data-perfil='${JSON.stringify(perfil)}'>
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                    </tr>`;

                    $("#permisos tbody").append(fila);

                });

                Swal.close();

            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }

        },
        error: function () {
            alertEnSweet('error', 'Uups..', 'Ocurrió un error al cargar los datos');
        }
    });

}

function cargarTabla() {
    $('#permisos').DataTable({
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        searching: false,
        ordering: false
    });

    traerAccesos();
}

function getDatAccesos(accesos) {
    $.ajax({
        type: "get",
        url: baseURL + "getAccesos",
        dataType: "json",
        success: function (response) {
            if (response.status == 'success') {
                accesos(response.data);
            } else {
                alertaError(response.mensaje)
            }
        },
        error: function () {
            alertaError('Error en carga de datos')
        }
    });
}

function abrirModalPerfil(perfilJson) {
    try {
        const perfil = JSON.parse(decodeURIComponent(perfilJson));
        document.getElementById('modalPerfilNombre').textContent = 'Perfil seleccionado: ' + perfil.nombre;
        $('#id_perfil').val(perfil.id_perfil);

        getDatAccesos(function (accesos) {
            $('#permisosContainer').empty();
            const perfilAccesos = Array.isArray(perfil.accesos) ? perfil.accesos : [];
            accesos.forEach(function (acceso) {
                let isChecked = perfilAccesos.some(pAcceso => pAcceso.id_acceso === acceso.id_acceso);

                let checkbox = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${acceso.id_acceso}" id="permiso_${acceso.id_acceso}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="permiso_${acceso.id_acceso}">
                            ${acceso.acceso}
                        </label>
                    </div>
                `;
                $('#permisosContainer').append(checkbox);
            });
            $('#editarAccesosPerfil').modal('show');
        });
    } catch (error) {
        alertaError('No se pudo abrir el modal')
    }
}

function verPermisos(perfilJson) {

    const perfil = JSON.parse(decodeURIComponent(perfilJson));

    let listaPermisos = '';

    if (perfil.accesos && perfil.accesos.length > 0) {

        perfil.accesos.forEach(function (acceso) {
            listaPermisos += `<li>${acceso.acceso}</li>`;
        });

    } else {
        listaPermisos = '<li>No tiene permisos</li>';
    }

    Swal.fire({
        title: 'Permisos de ' + perfil.nombre,
        html: `<ul style="text-align:left">${listaPermisos}</ul>`,
    });

}

function editPerfilAcceso() {
    Swal.fire({
        title: 'Espere...',
        html: 'Actualizando datos...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const idPerfil = $('#id_perfil').val();
    let accesosSeleccionados = [];

    $('#permisosContainer input[type="checkbox"]').each(function () {
        if ($(this).is(':checked')) {

            accesosSeleccionados.push($(this).val());
        }
    });

    $.ajax({
        type: "POST",
        url: baseURL + "editarPermisos",
        data: {
            idPerfil: idPerfil,
            accesos: accesosSeleccionados
        },
        dataType: "json",
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje);
                $('#editarAccesosPerfil').modal('hide');
                traerAccesos();
                Swal.close();
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertaError('Ocurrió un error en la solicitud. Por favor, inténtalo de nuevo.')
            Swal.close();
        }
    });
}

function iniciarTodo() {
    cargarTabla();

    // boton ver permisos
    $(document).on('click', '.btn-ver', function () {
        let perfil = $(this).data('perfil');
        verPermisos(encodeURIComponent(JSON.stringify(perfil)));
    });

    //boton editar permisos
    $(document).on('click', '.btn-editar', function () {
        let perfil = $(this).data('perfil');
        abrirModalPerfil(encodeURIComponent(JSON.stringify(perfil)));
    });

    // boton editar
    $('#editar-acceso').on('click', function () {
        editPerfilAcceso();
    });
}

document.addEventListener('DOMContentLoaded', iniciarTodo);