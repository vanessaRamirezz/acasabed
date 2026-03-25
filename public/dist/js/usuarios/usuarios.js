import { alertaError, eliminarColorYfocus, colorEnInputConFocus, alertaOk, alertEnSweet } from "../metodos/metodos.js";

const divGenerador = document.querySelector('.generador-pwd');

let inputDui = document.getElementById('dui');
let inputNombres = document.getElementById('nombres');
let inputApellidos = document.getElementById('apellidos');
let inputCorreo = document.getElementById('correo');
let inputPerfil = document.getElementById('perfil');
let inputEstado = document.getElementById('estado');
let inputClave = document.getElementById('clave');
var dui;
var nombres;
var apellidos;
var correo;
var perfil;
var telefono;
var clave;
var idUsuario;
let tablaUsuarios;

function validarCampoDui() {
    $(inputDui).mask('00000000-0');
}

function cargarTabla() {
    tablaUsuarios = $('#usuarios').DataTable({
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        searching: false,
        ordering: false
    });
    traerUsuarios();
}

function traerUsuarios() {
    $.ajax({
        type: 'GET',
        url: baseURL + 'getUsuarios',
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                tablaUsuarios.clear();

                response.data.forEach(function (row) {
                    var keysToShow = ['dui', 'nombres', 'apellidos', 'correo', 'telefono', 'nombre', 'estado'];

                    var rowData = keysToShow.map(function (key) {
                        return row[key];
                    });

                    rowData.push(`<button class="btn btn-info btn-sm btn-ver-opciones"
                        data-usuario='${encodeURIComponent(JSON.stringify(row))}'>
                        <i class="fa fa-edit"></i>
                    </button>`);

                    tablaUsuarios.row.add(rowData); // ❗ sin draw
                });

                tablaUsuarios.draw(); // ✅ SOLO UNA VEZ

                // Configurar evento de clic para mostrar modal con opciones
                $('#usuarios tbody').off('click', '.btn-ver-opciones').on('click', '.btn-ver-opciones', function () {
                    $('.modal-guardar').hide();
                    $('.modal-editar').show();
                    $('#clave').val('');

                    // Obtener todo el objeto del usuario desde el data-attribute
                    var usuario = JSON.parse(
                        decodeURIComponent($(this).attr('data-usuario'))
                    );

                    // Llenar los campos del formulario
                    $('#dui').val(usuario.dui).prop('disabled', true);
                    $('#nombres').val(usuario.nombres);
                    $('#apellidos').val(usuario.apellidos);
                    $('#correo').val(usuario.correo);
                    $('#telefono').val(usuario.telefono);
                    $('#estado').val(usuario.estado);

                    $('#id-usuario').val(usuario.id_usuario);

                    // Tipo de perfil
                    var $selectPerfiles = $('#perfil');
                    $selectPerfiles.find('option').each(function () {
                        $(this).prop('selected', $(this).text().trim() === usuario.nombre.trim());
                    });

                    divGenerador.style.display = 'block';


                    // Botón estado
                    var $btnEstado = $('#actualizar-estado');
                    if (usuario.estado.trim().toUpperCase() === 'SI') {
                        $btnEstado.removeClass('btn-success').addClass('btn-danger').text('Desactivar usuario');
                    } else {
                        $btnEstado.removeClass('btn-danger').addClass('btn-success').text('Activar usuario');
                    }

                    $('#mantenimiento-usuarios').modal('show');
                });
            } else {
                alertaError(response.mensaje);
            }
        }, error: function () {
            alertaError('Error al cargar los datos de la tabla')
        }
    });
}

function generarClave(longitud) {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let contrasena = '';
    for (let i = 0; i < longitud; i++) {
        const randomIndex = Math.floor(Math.random() * caracteres.length);
        contrasena += caracteres[randomIndex];
    }
    return contrasena;
}

function verClaveGenerada() {
    document.getElementById('toggle-password').addEventListener('click', function () {
        const input = document.getElementById('clave');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        this.textContent = isPassword ? '🙈' : '👁️';
    });
}

function btnGenerarClave() {
    document.getElementById('generate-password').addEventListener('click', function () {
        const password = generarClave(8); // Podés cambiar la longitud
        document.getElementById('clave').value = password;
    });
}

function funcionesDeClave() {
    generarClave();
    verClaveGenerada();
    btnGenerarClave();
}

function abrirModalNuevoUsuario() {
    $('.modal-guardar').show();
    $('.modal-editar').hide();

    // limpiar campos del formulario
    $('#dui').val('').prop('disabled', false);
    $('#nombres').val('');
    $('#apellidos').val('');
    $('#correo').val('');
    $('#perfil').val('').trigger('change');
    $('#telefono').val('');
    $('#clave').val('');
    $('#id-usuario').val('');
    inputEstado.value = 'SI';
    divGenerador.style.display = 'block';


    $('#mantenimiento-usuarios').modal('show');
}

function actualizarEstado() {
    idUsuario = $("#id-usuario").val().trim();
    var estadoActual = $('#estado').val().trim().toUpperCase();
    var nuevoEstado = (estadoActual === "SI") ? "NO" : "SI";

    var accionTexto = (estadoActual === "SI")
        ? "desactivar al usuario"
        : "activar al usuario";

    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas ${accionTexto}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Espere...',
                html: 'Procesando actualización...',
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            // Aquí mandas la petición AJAX
            $.ajax({
                url: baseURL + 'actualizarEstadoUsuario',
                type: 'POST',
                data: {
                    idUsuario,
                    nuevoEstado
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.close();
                        alertEnSweet('success','Éxito', response.mensaje)
                        traerUsuarios();
                        $('#mantenimiento-usuarios').modal('hide');
                    } else {
                        Swal.close();
                        alertaError(response.mensaje);
                    }
                },
                error: function () {
                    Swal.close();
                    alertEnSweet('error','Error','No se logro actualizar el estado del usuarios');
                }
            });
        }
    });
}

function guardarOeditarUsuario(tipoProceso) {
    dui = $("#dui").val().trim();
    nombres = $('#nombres').val().trim();
    apellidos = $('#apellidos').val().trim();
    correo = $('#correo').val().trim();
    perfil = $("#perfil").val();
    telefono = $('#telefono').val().trim();
    clave = $("#clave").val().trim();
    idUsuario = $("#id-usuario").val().trim();

    const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
    const emailRegex = /^\S+@\S+\.\S+$/;

    var tipo_proceso = tipoProceso === '1' ? 'nuevoUsuario' : 'editarUsuario';

    if (dui === "") {
        alertaError('El DUI es requerido');
        colorEnInputConFocus(inputDui);
        return false;
    } else {
        eliminarColorYfocus(inputDui);
    }

    if (nombres === "") {
        alertaError('El nombre es requerido');
        colorEnInputConFocus(inputNombres);
        return false;
    } if (!nameRegex.test(nombres)) {
        alertaError('El nombre solo debe contener letras');
        colorEnInputConFocus(inputNombres);
        return false;
    } else {
        eliminarColorYfocus(inputNombres);
    }

    if (apellidos === "") {
        alertaError('El apellido es requerido');
        colorEnInputConFocus(inputApellidos);
        return false;
    } if (!nameRegex.test(apellidos)) {
        alertaError('El apellido solo debe contener letras');
        colorEnInputConFocus(inputApellidos);
        return false;
    } else {
        eliminarColorYfocus(inputApellidos);
    }

    if (correo && !emailRegex.test(correo)) {
        alertaError('Por favor ingrese un correo válido');
        colorEnInputConFocus(inputCorreo);
        return false;
    } else {
        eliminarColorYfocus(inputCorreo);
    }

    if (!perfil) {
        alertaError('Seleccione un perfil para el usuario');
        colorEnInputConFocus(inputPerfil);
        return false;
    } else {
        eliminarColorYfocus(inputPerfil);
    }

    if (tipoProceso === '1' && clave === '') {
        alertaError("Debe ingresar una contraseña para el usuario");
        colorEnInputConFocus(inputClave);
        return false;
    } else {
        eliminarColorYfocus(inputClave);
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
            dui,
            nombres,
            apellidos,
            correo,
            perfil,
            telefono,
            clave,
            idUsuario
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                alertaOk(response.mensaje)
                traerUsuarios();
                Swal.close();
                $('#mantenimiento-usuarios').modal('hide');
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

    $("#guardar-registro").on("click", function () {
        guardarOeditarUsuario('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarUsuario('2');
    });

    $("#btn-agregar").on("click", function () {
        abrirModalNuevoUsuario();
    });

    $("#actualizar-estado").on("click", function () {
        actualizarEstado();
    });

}

function iniciarTodo() {
    cargarTabla();
    funcionesDeClave();
    eventosUsuarios();
    validarCampoDui();
}



document.addEventListener('DOMContentLoaded', iniciarTodo);