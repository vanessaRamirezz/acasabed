import { alertEnSweet } from "../metodos/metodos.js";

async function iniciarTodo() {

    const visor = document.getElementById('visorPDF');
    const loading = document.getElementById('loadingRutas');

    loading.style.display = 'flex';

    try {

        const response = await fetch(baseURL + 'reporte-rutas/pdf');

        if (!response.ok) {
            throw new Error('No fue posible generar el reporte');
        }

        const blob = await response.blob();

        if (blob.type.indexOf('pdf') === -1) {
            throw new Error('El servidor no devolvió un PDF válido');
        }

        const pdfUrl = URL.createObjectURL(blob);

        visor.onload = () => {
            loading.style.display = 'none';
        };

        visor.src = pdfUrl;

        // respaldo por si el onload no dispara
        setTimeout(() => {
            loading.style.display = 'none';
        }, 1000);

    } catch (error) {

        loading.style.display = 'none';

        alertEnSweet(
            'error',
            'Error',
            error.message || 'Ocurrió un error al generar el reporte'
        );

        console.error(error);
    }
}

document.addEventListener('DOMContentLoaded', iniciarTodo);