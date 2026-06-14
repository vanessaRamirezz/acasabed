import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";

function eventosUsuarios() {

    const btn = document.getElementById('btnGenerar');
    const iframe = document.getElementById('visorPDF');
    const message = document.getElementById('pdfMessage');
    const loading = document.getElementById('loadingMedidores');

    btn.addEventListener('click', async () => {

        const estado = document.getElementById('estado').value;

        let url = baseURL + 'reporte-medidores/pdf';

        if (estado) {
            url += '?estado=' + encodeURIComponent(estado);
        }

        message.style.display = 'none';
        iframe.style.display = 'none';
        loading.style.display = 'flex';

        try {

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('No fue posible generar el reporte');
            }

            const blob = await response.blob();

            if (!blob.type.includes('pdf')) {
                throw new Error('El servidor no devolvió un PDF válido');
            }

            const pdfUrl = URL.createObjectURL(blob);

            iframe.onload = () => {
                loading.style.display = 'none';
                iframe.style.display = 'block';
            };

            iframe.src = pdfUrl;

        } catch (error) {

            loading.style.display = 'none';

            alertEnSweet(
                'error',
                'Error',
                error.message || 'Ocurrió un error al generar el reporte'
            );

            message.style.display = 'flex';

            console.error(error);
        }

    });
}

function iniciarTodo() {
    eventosUsuarios();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);