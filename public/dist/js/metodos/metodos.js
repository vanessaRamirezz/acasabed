// funcion para mostrar una alerta tipo notificacion simple 
export function alertaError(msg) {
    toastr.error(msg, 'ERROR');
}

export function alertaOk(msg) {
    toastr.success(msg, 'CORRECTO');
}

export function alertaAdvertencia(msg) {
    toastr.warning(msg, 'ATENCIÓN');
}

export function alertaInfo(msg) {
    toastr.info(msg, 'INFO');
}

export function colorEnInputConFocus(input) {
    input.style.borderColor = 'red';
    input.focus();
}

export function eliminarColorYfocus(input) {
    input.style.borderColor = '';
}

export function alertEnSweet(tipo, title, text) {
    Swal.fire({
        icon: tipo,
        title: title,
        text: text,
        heightAuto: false
    });
}

export function validarCampo(valor, mensaje, input) {
    if (!valor) {
        alertaError(mensaje);
        colorEnInputConFocus(input[0]);
        return false;
    }
    eliminarColorYfocus(input[0]);
    return true;
}

// para selects

export function colorEnInputConFocusSelect(input) {
    input.classList.add('is-invalid');
    input.focus();
}

export function eliminarColorYfocusSelect(input) {
    input.classList.remove('is-invalid');
}