import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

function eventosUsuarios() {
    const btn = document.getElementById('btnGenerar');

    btn.addEventListener('click', () => {
        const estado = document.getElementById('estado').value;

        let url = baseURL + 'reporte-contratos/pdf';

        if (estado) {
            url += '?estado=' + estado;
        }

        // 👇 Vista previa en iframe
        document.getElementById('visorPDF').src = url;
    });
}

function iniciarTodo() {
    eventosUsuarios();

}

document.addEventListener('DOMContentLoaded', iniciarTodo);