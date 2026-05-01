import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";


function iniciarTodo() {
    document.getElementById('visorPDF').src = baseURL + 'reporte-rutas/pdf';
}

document.addEventListener('DOMContentLoaded', iniciarTodo);