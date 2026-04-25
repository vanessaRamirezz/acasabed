import { alertaOk, alertEnSweet, eliminarColorYfocus, validarCampo } from "../metodos/metodos.js";


function iniciarTodo() {
    document.getElementById('visorPDF').src = baseURL + 'reporteRutas';
}

document.addEventListener('DOMContentLoaded', iniciarTodo);