import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

function eventosUsuarios() {
    const btn = document.getElementById('btnGenerar');
    const iframe = document.getElementById('visorPDF');
    const message = document.getElementById('pdfMessage');

    btn.addEventListener('click', () => {

        const estado = document.getElementById('estado').value;

        let url = baseURL + 'reporte-medidores/pdf';
        if (estado) {
            url += '?estado=' + estado;
        }

        // ocultar mensaje
        message.style.display = 'none';

        // mostrar iframe
        iframe.style.display = 'block';

        // cargar PDF
        iframe.src = url;
    });

    // 👇 cuando carga el PDF (opcional UX)
    iframe.addEventListener('load', () => {
        // aquí ya puedes quitar loaders si quisieras
    });
}

function iniciarTodo() {
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);