import { alertaError, colorEnInputConFocus, eliminarColorYfocus, alertEnSweet } from '../metodos/metodos.js';


const dui = document.getElementById('dui');
const clave = document.getElementById('clave');
const botonLogin = document.getElementById('btn-login')


// valida que solamente pueda escribir un numero de dui sin letras
function validarDUI() {
    $(dui).mask('00000000-0', {
        placeholder: 'DUI'
    });
}

// valida que si esta vacios los inputs los colorea de rojo y muestra el mensaje de noificación de error
function ErrorEnInput(input, mensaje) {
    colorEnInputConFocus(input)
    alertaError(mensaje);
}

function login() {

    const usuario = dui.value.trim();
    const password = clave.value.trim();

    if (!usuario) {
        ErrorEnInput(dui, 'DUI vacío');
        return;
    } else {
        eliminarColorYfocus(dui);
    }

    if (!password) {
        ErrorEnInput(clave, 'Clave vacío');
        return;
    } else {
        eliminarColorYfocus(clave);
    }

    Swal.fire({
        title: 'Espere...',
        text: 'Verificando credenciales...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        heightAuto: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'POST',
        url: baseURL + 'validar',
        data: {
            usuario: usuario,
            password: password
        },
        dataType: 'json',

        success: function (response) {
            if (response.status === 'success') {
                window.location.href = response.redirect;
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Uups..', 'Error en el servidor');
        }
    });

}

function iniciarLogin() {
    validarDUI();
    botonLogin.addEventListener('click', login);
}

window.addEventListener('load', iniciarLogin);