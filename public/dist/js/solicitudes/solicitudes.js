import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, colorEnInputConFocusSelect, eliminarColorYfocus, eliminarColorYfocusSelect, validarCampo } from "../metodos/metodos.js";

let tablaSolicitudes;
let tablaSolicitudesAceptadas;

let modo = 'crear';
let idSolicitud = null;

const inputs = {
    idSolicitud: $("#id-solicitud"),

    //datos del cliente
    selectCliente: $("#buscar-cliente"),
    fechaCreacion: $("#fecha-creacion"),
    idCliente: $("#id-cliente"),
    nombre: $("#nombre"),
    edad: $("#edad"),
    dui: $("#dui"),
    nit: $("#nit"),
    extendido: $("#extendido"),
    fecha: $("#fecha"),
    lugarNacimiento: $("#lugar-nacimiento"),
    fechaNacimiento: $("#fecha-nacimiento"),
    estadoFamiliar: $("#estado-familiar"),
    numeroGrupoFamiliar: $("#numero-grupo-familiar"),
    direccion: $("#direccion"),
    lugarDeTrabajo: $("#lugar-de-trabajo"),
    ocupacion: $("#ocupacion"),
    telefonos: $("#telefonos"),

    //datos del beneficiario
    idBeneficiario: $("#id-beneficiario"),
    nombreBeneficiario: $("#nombre-beneficiario"),
    edadBeneficiario: $("#edad-beneficiario"),
    parentescoBeneficiario: $("#parentesco-beneficiario"),
    direccionBeneficiario: $("#direccion-beneficiario"),

    // datos direccion del inmueble
    direccionInmueble: $("#direccion-inmueble"),
    propietario: $("#propietario"),
    inquilino: $("#inquilino"),
    representante: $("#representante"),
    otroCheck: $("#otro-check"),

    abonera: $("#abonera"),
    hoyoSeco: $("#hoyo-seco"),
    lavable: $("#lavable"),
    otroBaño: $("#otro-baño"),

    // datos de entrevista dirigida
    idPlanPago: $("#id-plan-pago"),
    si: $("#si"),
    no: $("#no"),
    tiempo: $("#tiempo"),
    monto: $("#monto"),
    contado: $("#contado"),
    otro: $("#otro"),
    cantidadDePagos: $("#cantidad-pagos"),
    totalCuota: $("#total-cuota"),
    interes: $("#interes"),

    // datos de comision municipal
    acuerdo: $("#acuerdo"),
    fechaSession: $("#fecha-session"),
    numeroActa: $("#numero-acta"),

    // datos de los firmantes
    idFirmanteAdministrador: $("#id-firmante-administrador"),
    nombreAdministrador: $("#nombre-administrador"),
    idFirmanteComision1: $("#id-firmante-comision-1"),
    nombreComision1: $("#nombre-comision-1"),
    idFirmanteComision2: $("#id-firmante-comision-2"),
    nombreComision2: $("#nombre-comision-2"),


    // datos del contrato
    fichaAlcaldia: $("#ficha-alcaldia"),
    fechaInicio: $("#fecha-inicio"),
    fechaVencimiento: $("#fecha-vencimiento"),
    // estado: $("#estado"),
    ruta: $("#rutas"),
    medidor: $("#medidores"),
    direccionMedidor: $("#direccion-medidor"),
    tarifa: $("#tarifas"),

}

function getData() {
    let formData = new FormData();

    formData.append('idSolicitud', inputs.idSolicitud.val().trim());

    // datos del cliente
    formData.append('fechaCreacion', inputs.fechaCreacion.val().trim());
    formData.append('idCliente', inputs.idCliente.val().trim());
    formData.append('nombre', inputs.nombre.val().trim());
    formData.append('dui', inputs.dui.val().trim());
    formData.append('nit', inputs.nit.val().trim());
    formData.append('extendido', inputs.extendido.val().trim());
    formData.append('fecha', inputs.fecha.val().trim());
    formData.append('edad', inputs.edad.val().trim());
    formData.append('lugarNacimiento', inputs.lugarNacimiento.val().trim());
    formData.append('fechaNacimiento', inputs.fechaNacimiento.val().trim());
    formData.append('estadoFamiliar', inputs.estadoFamiliar.val().trim());
    formData.append('numeroGrupoFamiliar', inputs.numeroGrupoFamiliar.val().trim());
    formData.append('direccion', inputs.direccion.val().trim());
    formData.append('lugarDeTrabajo', inputs.lugarDeTrabajo.val().trim());
    formData.append('ocupacion', inputs.ocupacion.val().trim());
    formData.append('telefonos', inputs.telefonos.val().trim());

    //datos del beneficiario
    formData.append('idBeneficiario', inputs.idBeneficiario.val().trim());
    formData.append('nombreBeneficiario', inputs.nombreBeneficiario.val().trim());
    formData.append('edadBeneficiario', inputs.edadBeneficiario.val().trim());
    formData.append('parentescoBeneficiario', inputs.parentescoBeneficiario.val().trim());
    formData.append('direccionBeneficiario', inputs.direccionBeneficiario.val().trim());

    //datos  direccion del inmueble
    formData.append('direccionInmueble', inputs.direccionInmueble.val().trim());
    formData.append('propietario', inputs.propietario.is(":checked"));
    formData.append('inquilino', inputs.inquilino.is(":checked"));
    formData.append('representante', inputs.representante.is(":checked"));
    formData.append('otroCheck', inputs.otroCheck.val().trim());

    formData.append('abonera', inputs.abonera.is(":checked"));
    formData.append('hoyoSeco', inputs.hoyoSeco.is(":checked"));
    formData.append('lavable', inputs.lavable.is(":checked"));
    formData.append('otroBaño', inputs.otroBaño.val().trim());

    // datos de la entrevista dirigida
    formData.append('idPlanPago', inputs.idPlanPago.val().trim());
    formData.append('si', inputs.si.is(":checked"));
    formData.append('no', inputs.no.is(":checked"));
    formData.append('tiempo', inputs.tiempo.val().trim());
    formData.append('monto', inputs.monto.val().trim());
    formData.append('contado', inputs.contado.is(":checked"));
    formData.append('otro', inputs.otro.val().trim());
    formData.append('cantidadDePagos', inputs.cantidadDePagos.val().trim());
    formData.append('totalCuota', inputs.totalCuota.val().trim());
    formData.append('interes', inputs.interes.val().trim());

    //datos de comision municipa
    formData.append('acuerdo', inputs.acuerdo.val().trim());
    formData.append('fechaSession', inputs.fechaSession.val().trim());
    formData.append('numeroActa', inputs.numeroActa.val().trim());

    // datos del firmante
    formData.append('idAdministrador', inputs.idFirmanteAdministrador.val().trim());
    formData.append('nombreAdministrador', inputs.nombreAdministrador.val().trim());
    formData.append('idFirmanteComision1', inputs.idFirmanteComision1.val().trim());
    formData.append('nombreComision1', inputs.nombreComision1.val().trim());
    formData.append('idFirmanteComision2', inputs.idFirmanteComision2.val().trim());
    formData.append('nombreComision2', inputs.nombreComision2.val().trim());

    // datos del contrato
    formData.append('fichaAlcaldia', inputs.fichaAlcaldia.val().trim());
    formData.append('fechaInicio', inputs.fechaInicio.val().trim());
    formData.append('fechaVencimiento', inputs.fechaVencimiento.val().trim());
    // formData.append('estado', inputs.estado.val().trim());
    formData.append('ruta', inputs.ruta.val().trim());
    formData.append('medidor', inputs.medidor.val().trim());
    formData.append('direccionMedidor', inputs.direccionMedidor.val().trim());
    formData.append('tarifa', inputs.tarifa.val().trim());

    return formData;
}

function validarCampoDui() {
    $(inputs.dui).mask('00000000-0');
}

function limpiarInputs() {
    Object.values(inputs).forEach(input => input.val('').prop('disabled', false));
}

function cargarBeneficiarios(idCliente, callback = null) {
    $.ajax({
        url: 'getBeneficiariosId',
        type: 'POST',
        data: { idCliente: idCliente },
        dataType: 'json',
        success: function (response) {
            const select = $('#beneficiarios-registratos');

            select.empty();
            select.append('<option value="">Seleccione...</option>');

            if (response.status === 'success' && response.data.length > 0) {
                response.data.forEach(function (b) {
                    let option = new Option(
                        `${b.nombre} - ${b.parentesco} (${b.edad} años)`,
                        b.id_beneficiario
                    );

                    $(option).data('data', b);
                    select.append(option);
                });
            }

            select.trigger('change');

            // 🔥 aquí ejecutas lo que sigue
            if (callback) callback(response.data);
        }
    });
}

function llenarInputs(cliente) {
    inputs.idCliente.val(cliente.id_cliente);
    inputs.nombre.val(cliente.nombre_completo).prop('disabled', true);
    inputs.edad.val(cliente.edad).prop('disabled', true);
    inputs.dui.val(cliente.dui).prop('disabled', true);
    inputs.nit.val(cliente.nit).prop('disabled', true);
    inputs.extendido.val(cliente.extendido).prop('disabled', true);
    inputs.fecha.val(cliente.fecha).prop('disabled', true);
    inputs.lugarNacimiento.val(cliente.lugar_nacimiento).prop('disabled', true);
    inputs.fechaNacimiento.val(cliente.fecha_nacimiento).prop('disabled', true);
    inputs.estadoFamiliar.val(cliente.estado_familiar).prop('disabled', true);
    inputs.numeroGrupoFamiliar.val(cliente.numero_grupo_familiar).prop('disabled', true);
    inputs.direccion.val(cliente.direccion_completa).prop('disabled', true);
    inputs.lugarDeTrabajo.val(cliente.lugar_trabajo).prop('disabled', true);
    inputs.ocupacion.val(cliente.ocupacion).prop('disabled', true);
    inputs.telefonos.val(cliente.telefono).prop('disabled', true);
}

function eventoSeleccionCliente() {
    $('#buscar-cliente').on('select2:select', function (e) {
        const cliente = e.params.data.data;

        llenarInputs(cliente);

        // NUEVO: cargar beneficiarios
        cargarBeneficiarios(cliente.id_cliente);
    });
}

function habilitarInputs() {
    inputs.nombreBeneficiario.prop('disabled', false);
    inputs.edadBeneficiario.prop('disabled', false);
    inputs.parentescoBeneficiario.prop('disabled', false);
    inputs.direccionBeneficiario.prop('disabled', false);
}

function limpiarInputsBeneficiario() {
    inputs.idBeneficiario.val('');
    inputs.nombreBeneficiario.val('').prop('disabled', false);
    inputs.edadBeneficiario.val('').prop('disabled', false);
    inputs.parentescoBeneficiario.val('').prop('disabled', false);
    inputs.direccionBeneficiario.val('').prop('disabled', false);

    $('#beneficiarios-registratos').val('');

    // ocultar botones nuevamente
    $('#btn-editar').hide();
    $('#btn-limpiar').hide();
}

function llenarInputsBeneficiarios(beneficiario) {
    inputs.idBeneficiario.val(beneficiario.id_beneficiario);
    inputs.nombreBeneficiario.val(beneficiario.nombre).prop('disabled', true);
    inputs.edadBeneficiario.val(beneficiario.edad).prop('disabled', true);
    inputs.parentescoBeneficiario.val(beneficiario.parentesco).prop('disabled', true);
    inputs.direccionBeneficiario.val(beneficiario.direccion).prop('disabled', true);
}

function eventoSeleccionBeneficiario() {
    $('#beneficiarios-registratos').on('change', function () {
        const selected = $(this).find(':selected').data('data');

        if (selected) {
            llenarInputsBeneficiarios(selected);

            // mostrar botones
            $('#btn-editar').show();
            $('#btn-limpiar').show();
        } else {
            limpiarInputsBeneficiario();
        }
    });
}

function eventoSeccionBeneficarioEditar() {
    // mostrar botones
    $('#btn-editar').show();
    $('#btn-limpiar').show();
}

function cargarClientes() {
    $('#buscar-cliente').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getClientesSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(cliente => ({
                        id: cliente.id_cliente,
                        text: cliente.nombre_completo,
                        data: cliente
                    }))
                };
            },
            cache: true
        }
    })
}

function irASiguiente(target) {
    // $('#accordion .collapse').collapse('hide');
    $(target).collapse('show');

    $('html, body').animate({
        scrollTop: $(target).offset().top - 100
    }, 300);
}

function irAnterior(target) {
    $(target).collapse('show');
    $('#accordion .collapse').collapse('hide');

    $('html, body').animate({
        scrollTop: $(target).offset().top - 100
    }, 300);
}

function formatearFecha(fechaInput) {
    if (!fechaInput) return "El Coyolito, ______ de __________ 20____";

    const partes = fechaInput.split('-');
    const anio = parseInt(partes[0]);
    const mesIndex = parseInt(partes[1]) - 1;
    const dia = parseInt(partes[2]);

    const meses = [
        "enero", "febrero", "marzo", "abril", "mayo", "junio",
        "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
    ];

    const mes = meses[mesIndex];

    return `El Coyolito, ${dia} de ${mes} ${anio}`;
}

function drawTableAuto(doc, startX, startY, columns, rows, options = {}) {
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = options.margin || 30;
    const cellPadding = 2;

    let y = startY;

    // Calcular ancho disponible
    const tableWidth = pageWidth - (margin * 2);

    // Calcular ancho proporcional de columnas
    const totalWeight = columns.reduce((sum, col) => sum + col.weight, 0);

    const colWidths = columns.map(col => (col.weight / totalWeight) * tableWidth);

    // =========================
    // HEADER
    // =========================
    doc.setFont("helvetica", "bold");
    doc.setFontSize(7);

    let x = startX;

    columns.forEach((col, i) => {
        doc.rect(x, y, colWidths[i], 15);
        doc.text(col.title, x + cellPadding, y + 10);
        x += colWidths[i];
    });

    y += 15;

    // =========================
    // FILAS
    // =========================
    doc.setFont("helvetica", "normal");
    doc.setFontSize(7);

    rows.forEach(row => {

        let x = startX;

        // Calcular altura dinámica (la más alta de la fila)
        let maxHeight = 0;

        const cellLines = row.map((cell, i) => {
            const text = String(cell || '');
            const lines = doc.splitTextToSize(text, colWidths[i] - (cellPadding * 2));
            const height = (lines.length * 20);
            if (height > maxHeight) maxHeight = height;
            return lines;
        });

        // Dibujar celdas
        cellLines.forEach((lines, i) => {
            doc.rect(x, y, colWidths[i], maxHeight);
            doc.text(lines, x + cellPadding, y + 8);
            x += colWidths[i];
        });

        y += maxHeight;
    });

    return y; // devuelve la nueva posición Y
}

function vistaPrevia(e) {
    e.preventDefault();

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'letter');

    const tituloLineas = [
        "ASOCIACION COMUNAL ADMINISTRADORA DEL SISTEMA DE AGUA POTABLE DEL CANTÓN EL COYOLITO",
        "DEL MUNICIPIO DE TEJUTLA DEPARTAMENTO DE CHALATENANGO. BENDICION DE DIOS. (ACASABED)"
    ];

    const pageWidth = doc.internal.pageSize.getWidth();
    let margin = 30;
    const lineHeight = 20;

    let y = 25;

    // =========================
    // HELPERS
    // =========================
    function setFont(style = "normal", size = 7) {
        doc.setFont("helvetica", style);
        doc.setFontSize(size);
    }

    function textCenter(text, y) {
        const w = doc.getTextWidth(text);
        const x = (pageWidth - w) / 2;
        doc.text(text, x, y);
    }

    function textRight(text, y) {
        const w = doc.getTextWidth(text);
        const x = pageWidth - w - margin;
        doc.text(text, x, y);
    }

    function fieldLine(label, value, x, y, offset = 120, lineWidth = 150) {
        doc.text(label, x, y);
        doc.text(value, x + offset, y);
        doc.line(x + offset, y + 2, x + offset + lineWidth, y + 2);
    }

    function fieldDouble(label1, value1, x1, label2, value2, x2, y, offset1 = 120, offset2 = 40, width1 = 150, width2 = 110) {
        doc.text(label1, x1, y);
        doc.text(value1, x1 + offset1, y);
        doc.line(x1 + offset1, y + 2, x1 + offset1 + width1, y + 2);

        doc.text(label2, x2, y);
        doc.text(value2, x2 + offset2, y);
        doc.line(x2 + offset2, y + 2, x2 + offset2 + width2, y + 2);
    }

    function drawCheckbox(doc, x, y, label, checked = false) {
        const size = 8;

        // Cuadro
        doc.rect(x, y - 6, size, size);

        // Marca (X)
        if (checked) {
            doc.line(x, y - 6, x + size, y + 2);
            doc.line(x + size, y - 6, x, y + 2);
        }

        // Texto
        doc.text(label, x + size + 5, y);
    }

    // =========================
    // TÍTULO
    // =========================
    setFont("bold", 10);

    tituloLineas.forEach(line => {
        textCenter(line, y);
        y += lineHeight;
    });

    // =========================
    // DATA
    // =========================
    const data = getData();
    let fechaTexto = formatearFecha(data.get('fechaCreacion'));

    // =========================
    // FECHA
    // =========================
    setFont("normal", 9);
    textRight(fechaTexto, y + 15);

    // =========================
    // SUBTÍTULO
    // =========================
    const subtitulo = 'SOLICITUD DE CONEXION DOMICILIAR.';
    let yContenido = y + 30;

    setFont("bold", 9);
    textCenter(subtitulo, yContenido);

    // =========================
    // TEXTO
    // =========================
    const parrafo = 'Por medio de la presente, solicito a Ustedes, la instalación de 01 acometida domiciliar de prestación de servicio de agua potable.';

    yContenido += 25;

    setFont("normal", 9);

    doc.text(
        "Señores administradores del Sistema de Agua Potable El Coyolito Tejutla.",
        margin,
        yContenido
    );

    yContenido += 10;

    const lineasParrafo = doc.splitTextToSize(parrafo, pageWidth - (margin * 2));
    doc.text(lineasParrafo, margin, yContenido);

    // =========================
    // SECCIÓN DATOS
    // =========================
    yContenido += 30;

    setFont("bold", 9);
    doc.text("Mis datos personales son:", margin, yContenido);

    yContenido += 25;

    setFont("normal", 7);

    // =========================
    // CAMPOS
    // =========================

    const nombre = data.get('nombre') || '';
    fieldLine("NOMBRE COMPLETO:", nombre, margin, yContenido, 120, pageWidth - margin - (margin + 120));

    yContenido += 20;

    const dui = data.get('dui') || '';
    const nit = data.get('nit') || '';
    if (!nit == '') {
        fieldLine("NIT NUMERO:", nit, margin, yContenido);
    } else {
        fieldLine("DUI NUMERO:", dui, margin, yContenido);
    }


    yContenido += 20;

    const extendido = data.get('extendido') || '';
    const fecha = data.get('fecha') || '';

    fieldDouble(
        "EXTENDIDO EN:", extendido, margin,
        "FECHA:", fecha, margin + 400,
        yContenido,
        120, 40, 275, 110
    );

    yContenido += 20;

    const edad = data.get('edad') || '';
    fieldLine("EDAD:", edad, margin, yContenido);

    yContenido += 20;

    // =========================
    // NACIMIENTO (caso especial)
    // =========================
    const lugarNacimiento = data.get('lugarNacimiento') || '';
    const fechaNacimiento = data.get('fechaNacimiento') || '';

    doc.text("LUGAR Y FECHA DE NACIMIENTO:", margin, yContenido);

    const xInicio = margin + 120;
    const anchoLugar = 315;
    const anchoFecha = 100;

    doc.text(lugarNacimiento, xInicio, yContenido);
    doc.line(xInicio, yContenido + 2, xInicio + anchoLugar, yContenido + 2);

    const xComa = xInicio + anchoLugar + 5;

    doc.text(",", xComa, yContenido);
    doc.text(fechaNacimiento, xComa + 10, yContenido);

    doc.line(xComa + 10, yContenido + 2, xComa + 10 + anchoFecha, yContenido + 2);

    yContenido += 20;

    const estadoFamiliar = data.get('estadoFamiliar') || '';
    const numeroGrupoFamiliar = data.get('numeroGrupoFamiliar') || '';

    fieldDouble(
        "ESTADO FAMILIAR:", estadoFamiliar, margin,
        "N° DE PERSONAS QUE INTEGRAN EL GRUPO FAMILIAR:", numeroGrupoFamiliar, margin + 250,
        yContenido,
        120, 200, 100, 100
    );

    yContenido += 20;

    const direccion = data.get('direccion') || '';
    fieldLine("DIRECCIÓN SEGUN DUI:", direccion, margin, yContenido, 120, pageWidth - margin - (margin + 120));

    yContenido += 20;

    const lugarDeTrabajo = data.get('lugarDeTrabajo') || '';
    fieldLine("LUGAR DE TRABAJO:", lugarDeTrabajo, margin, yContenido);

    yContenido += 20;

    // =========================
    // PROFESIÓN + TELÉFONO (especial)
    // =========================
    const ocupacion = data.get('ocupacion') || '';
    const telefono = data.get('telefonos') || '';

    doc.text("PROFESION U OFICIO:", margin, yContenido);

    const xInicio1 = margin + 120;
    const anchoLugar1 = 150;
    const anchoFecha1 = 200;

    doc.text(ocupacion, xInicio1, yContenido);
    doc.line(xInicio1, yContenido + 2, xInicio1 + anchoLugar1, yContenido + 2);

    const xComa1 = xInicio1 + anchoLugar1 + 15;

    doc.text("TELEFONO:", xComa1, yContenido);
    doc.text(telefono, xComa1 + 65, yContenido);

    doc.line(xComa1 + 65, yContenido + 2, xComa1 + 65 + anchoFecha1, yContenido + 2);

    yContenido += 30;

    // =========================
    // SECCION DE BENEFICIARIOS
    // =========================
    setFont("bold", 9);
    doc.text("En caso de muerte designo como beneficiaria/o de mi acometida, a la persona siguiente", margin, yContenido);

    yContenido += 25;

    const columns = [
        { title: "NOMBRE", weight: 3 },
        { title: "EDAD", weight: 1 },
        { title: "PARENTESCO", weight: 2 },
        { title: "DIRECCION", weight: 4 }
    ];

    const rows = [
        [
            data.get('nombreBeneficiario') || '',
            data.get('edadBeneficiario') || '',
            data.get('parentescoBeneficiario') || '',
            data.get('direccionBeneficiario') || ''
        ]
    ];

    yContenido = drawTableAuto(doc, margin, yContenido, columns, rows);

    yContenido += 35;

    // =========================
    // DATOS DEL INMUEBLE
    // =========================
    const titulo2 = ["DATOS DEL INMUEBLE DONDE SE INSTALARA LA ACOMETIDA."];
    setFont("bold", 9);
    titulo2.forEach(line => {
        textCenter(line, yContenido);
        yContenido += lineHeight;
    });

    yContenido += 25;

    setFont("normal", 8);
    const direccionDelInmueble = data.get('direccionInmueble') || '';
    fieldLine("Dirección del Inmueble: ", direccionDelInmueble, margin, yContenido, 120, pageWidth - margin - (margin + 120));

    yContenido += 20;

    setFont("bold", 9);
    doc.text("Calidad del Inmueble", margin, yContenido);

    yContenido += 20;

    setFont("normal", 8);
    const propietario = data.get('propietario') === 'true';
    const inquilino = data.get('inquilino') === 'true';
    const representante = data.get('representante') === 'true';
    const otroCheck = data.get('otroCheck') || '';
    let xStart = margin;
    const yCheck = yContenido;
    // Checkbox 1
    drawCheckbox(doc, xStart, yCheck, "Propietario", propietario);
    // Checkbox 2
    drawCheckbox(doc, xStart + 120, yCheck, "Inquilino", inquilino);
    // Checkbox 3
    drawCheckbox(doc, xStart + 240, yCheck, "Representante", representante);
    // Línea para "otro"
    const xTexto = xStart + 390;
    doc.text("Otro:", xTexto, yCheck);
    // Valor ingresado
    doc.text(otroCheck, xTexto + 35, yCheck);
    // Línea
    doc.line(xTexto + 35, yCheck + 2, xTexto + 160, yCheck + 2);

    yContenido += 30;

    setFont("normal", 9);
    doc.text("Tiene Letrina", margin, yContenido);
    yContenido += 25;



    setFont("normal", 8);
    const abonera = data.get('abonera') === 'true';
    const hoyoSeco = data.get('hoyoSeco') === 'true';
    const lavable = data.get('lavable') === 'true';
    const otroBaño = data.get('otroBaño') || '';
    let xStar = margin;
    const yChec = yContenido;
    // Checkbox 1
    drawCheckbox(doc, xStar, yChec, "Abonera", abonera);
    // Checkbox 2
    drawCheckbox(doc, xStar + 120, yChec, "Hoyo Seco", hoyoSeco);
    // Checkbox 3
    drawCheckbox(doc, xStar + 240, yChec, "Lavable", lavable);
    // Línea para "otro"
    const xText = xStar + 390;
    doc.text("Otro:", xText, yChec);
    // Valor ingresado
    doc.text(otroBaño, xText + 35, yChec);
    // Línea
    doc.line(xText + 35, yChec + 2, xText + 160, yChec + 2);

    yContenido += 30;

    // ===========================================================
    // NUEVA PAGINA
    // ===========================================================
    doc.addPage();

    // Reiniciar coordenada vertical
    yContenido = 60;

    // (Opcional) mismo margen
    margin = 30;

    const titulo3 = ["ENTREVISTA DIRIGIDA"];
    setFont("bold", 9);
    titulo3.forEach(line => {
        textCenter(line, yContenido);
        yContenido += lineHeight;
    });

    yContenido += 25;

    // =========================
    // TEXTO
    // =========================
    const parrafo1 = 'el compromiso de construir la que la Junta Directiva le recomiende, según las normas del Ministerio de Salud?';
    setFont("normal", 9);
    doc.text(
        "Si no tiene Letrina, acepta Usted,",
        margin,
        yContenido
    );
    yContenido += 10;
    const lineasParrafo1 = doc.splitTextToSize(parrafo1, pageWidth - (margin * 2));
    doc.text(lineasParrafo1, margin, yContenido);

    yContenido += 25;

    setFont("normal", 8);
    const si = data.get('si') === 'true';
    const no = data.get('no') === 'true';
    let xStart1 = margin;
    const yCheck1 = yContenido;
    // Checkbox 1
    drawCheckbox(doc, xStart1, yCheck1, "SI", si);
    // Checkbox 2
    drawCheckbox(doc, xStart1 + 200, yCheck1, "NO", no);

    yContenido += 25;

    // cuanto tiempo
    const tiempo = data.get('tiempo') || '';
    doc.text("En cuanto tiempo construiría su letrina:", margin, yContenido);
    // posición del valor
    const xValor = margin + 145;
    // valor
    doc.text(tiempo, xValor, yContenido);
    // línea corta debajo del valor
    doc.line(xValor, yContenido + 2, xValor + 90, yContenido + 2);

    yContenido += 25;

    // Monto
    const monto = data.get('monto') || '';
    doc.text("El monto a cancelar por el D° de conexión, según Estrato será de: $", margin, yContenido);
    // posición del valor
    const xValor1 = margin + 250;
    // valor
    doc.text(monto, xValor1, yContenido);
    // línea corta debajo del valor
    doc.line(xValor1, yContenido + 2, xValor1 + 90, yContenido + 2);
    yContenido += 25;
    setFont("normal", 9);
    doc.text("Pagadero a: ", margin, yContenido);
    yContenido += 25;
    setFont("normal", 8);
    const contado = data.get('contado') === 'true';
    const otro = data.get('otro') || '';
    const cantidadDePagos = data.get('cantidadDePagos') || '';
    const totalCuota = data.get('totalCuota') || '';

    let x = margin;
    y = yContenido;

    // =========================
    // Contado (checkbox)
    // =========================
    drawCheckbox(doc, x, y, "Contado", contado);
    x += 100; // espacio después del checkbox
    // =========================
    // Otro: ________
    // =========================
    doc.text("Otro:", x, y);
    x += 25;
    const xOtroInicio = x;
    doc.text(otro, xOtroInicio, y);
    doc.line(xOtroInicio, y + 2, xOtroInicio + 60, y + 2);
    x += 100;
    // =========================
    // En __ pagos
    // =========================
    doc.text("En", x, y);
    x += 12;
    doc.text(cantidadDePagos, x, y);
    doc.line(x, y + 2, x + 30, y + 2);
    x += 40;
    doc.text("pagos", x, y);
    x += 40;
    // =========================
    // por $ ____
    // =========================
    doc.text("de $", x, y);
    x += 25;
    doc.text(totalCuota, x, y);
    doc.line(x, y + 2, x + 50, y + 2);


    yContenido += 25;

    // =========================
    // TEXTO
    // =========================
    const parrafo2 = 'Asi mismo, dejo constancia que se me ha explicado, sobre el reglamento y estatutos, mis derechos y obligaciones, los cuales estoy dispuesto a someterme y cumplir fielmente.';
    const interes = data.get('interes') || '';
    setFont("normal", 9);
    doc.text(
        "Si se atrasa en el pago el interés que usted cancelara será de " + interes + "anual ",
        margin,
        yContenido
    );
    yContenido += 10;
    const lineasParrafo2 = doc.splitTextToSize(parrafo2, pageWidth - (margin * 2));
    doc.text(lineasParrafo2, margin, yContenido);

    yContenido += 35;

    const titulo4 = ["ESPACIO A COMISIÓN MUNICIPAL ADMINISTRADORA "];
    setFont("bold", 9);
    titulo4.forEach(line => {
        textCenter(line, yContenido);
        yContenido += lineHeight;
    });

    yContenido += 25;


    const acuerdo = data.get('acuerdo') || '';
    const fechaSession = data.get('fechaSession') || '';
    const numeroActa = data.get('numeroActa') || '';

    x = margin;
    y = yContenido;

    setFont("normal", 8);

    // =========================
    // ACUERDO (FORMATO FIJO)
    // =========================

    doc.text("Se tomó el acuerdo de:", x, y);

    let xAcuerdo = x + 130;
    let ancho = 520 - xAcuerdo;

    // 🔥 ALTURA REAL DEL CAMPO
    let linea1Y = y - 2;
    let linea2Y = y + 14;

    // dibujar líneas
    doc.line(xAcuerdo, linea1Y, x + 520, linea1Y);
    doc.line(xAcuerdo, linea2Y, x + 520, linea2Y);

    // dividir texto
    let lineas = doc.splitTextToSize(acuerdo, ancho);

    // 🔥 IMPORTANTE: bajar el texto dentro del campo
    let textoY = y + -3;

    // escribir texto correctamente alineado
    doc.text(lineas.slice(0, 2), xAcuerdo, textoY);

    y += 25;

    // =========================
    // FECHA DE SESIÓN
    // =========================
    doc.text("En sesión realizada el día:", x, y);

    let xFecha = x + 150;
    doc.text(fechaSession, xFecha, y);
    doc.line(xFecha, y + 2, xFecha + 150, y + 2);

    y += 16;

    // =========================
    // ACTA NÚMERO
    // =========================
    doc.text("Acta Número:", x, y);

    let xActa = x + 90;
    doc.text(numeroActa, xActa, y);
    doc.line(xActa, y + 2, xActa + 120, y + 2);

    yContenido = y + 100;

    y = yContenido;

    // =========================
    // FIRMAS
    // =========================
    setFont("normal", 8);
    const firmantes = [
        { nombre: data.get('nombre') || '', puesto: 'Persona Solicitante' || '' },
        { nombre: data.get('nombreAdministrador') || '', puesto: 'Administrador' || '' },
        // { nombre: data.get('nombreFirmante2') || '', puesto: data.get('puestoFirmante2') || '' },
        // { nombre: data.get('nombreFirmante3') || '', puesto: data.get('puestoFirmante3') || '' }
    ];

    setFont("normal", 8);

    const lineWidth = 150;
    const espacioY = 40; // espacio vertical entre filas
    const inicioY = y;

    const mitad = pageWidth / 2;

    firmantes.forEach((f, index) => {
        const fila = Math.floor(index / 2); // 0 o 1
        const col = index % 2; // 0 izquierda, 1 derecha

        let xBase = col === 0 ? margin : mitad + 20;
        let yBase = inicioY + (fila * espacioY);

        // Línea
        doc.line(xBase, yBase, xBase + lineWidth, yBase);

        // Centro de la línea
        let centro = xBase + (lineWidth / 2);

        // Nombre
        doc.text(f.nombre, centro, yBase + 10, { align: "center" });

        // Puesto
        doc.text('Firma ' + f.puesto, centro, yBase + 18, { align: "center" });
    });

    // =========================
    // OUTPUT
    // =========================
    window.open(doc.output('bloburl'), '_blank');
}


function cargarRutas() {
    $('#rutas').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getRutasSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(ruta => ({
                        id: ruta.id_ruta,
                        text: ruta.nombre
                    }))
                };
            },
            cache: true
        }
    })
}

function cargarMedidores() {
    $('#medidores').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getMedidoresSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(medidor => ({
                        id: medidor.id_medidor,
                        text: medidor.numero_serie
                    }))
                };
            },
            cache: true
        }
    })
}

function cargarTarifas() {
    $('#tarifas').select2({
        placeholder: "Busque y seleccione",
        allowClear: true,
        theme: 'bootstrap4',
        ajax: {
            url: baseURL + 'getTarifasSelect', // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(tarifa => ({
                        id: tarifa.id_tarifa,
                        text: tarifa.codigo + ' desde ' + tarifa.desde_n_metros + ' hasta ' + tarifa.hasta_n_metros
                    }))
                };
            },
            cache: true
        }
    })
}

function validarTipoPago() {
    $('input[name="pago"]').on('change', function () {
        if ($('#contado').is(':checked')) {
            inputs.otro.val('').prop('disabled', true);
            inputs.cantidadDePagos.val('').prop('disabled', true);
            inputs.totalCuota.val('').prop('disabled', true);
        } else {
            inputs.otro.prop('disabled', false);
            inputs.cantidadDePagos.prop('disabled', false);
            inputs.totalCuota.prop('disabled', false);
        }
    });
}


function guardarOeditarSolicitudContrato(tipo_proceso) {
    const data = getData();

    if (!data.get('idCliente')) {
        alertaError('Debe seleccionar un cliente');
        colorEnInputConFocusSelect(inputs.selectCliente[0]);
        return false;
    } else {
        eliminarColorYfocusSelect(inputs.selectCliente[0]);
    }

    const esContado = data.get('contado') === 'true';

    if (!esContado) {
        let monto = parseFloat(data.get('monto')) || 0;
        let cantidadDePagos = parseInt(data.get('cantidadDePagos')) || 0;
        let totalCuota = parseFloat(data.get('totalCuota')) || 0;

        let total = parseFloat((cantidadDePagos * totalCuota).toFixed(2));
        monto = parseFloat(monto.toFixed(2));

        if (total !== monto) {
            alertaError(`Al sumar el total de las cuotas (${total}) no coincide con el monto (${monto})`);
            return false;
        }
    }

    Swal.fire({
        title: 'Espere...',
        html: 'Procesando...',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'POST',
        url: baseURL + tipo_proceso,
        data: data,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.mensaje
                }).then(() => {
                    window.location.href = baseURL + 'contratos';
                });
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    });
}

function cargarSolicitudes() {
    tablaSolicitudes = $('#tbl-solicitudes').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getSolicitudesTabla',
            data: function (d) {
                d.searchValue = $('#customSearchSolicitudes').val();
            }
        },
        columns: [
            {
                data: 'cod_solicitud'
            },
            {
                data: 'nombre'
            },
            {
                data: 'estado'
            },
            {
                data: 'fechaGeneracion'
            },
            {
                data: null,
                render: function (data, type, row) {

                    return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                Acciones
                            </button>
                            <div class="dropdown-menu">

                                <a class="dropdown-item dropdown-item-custom btn-ver-solicitud" href="#"
                                    data-id="${row.id}">
                                    <i class="fas fa-eye"></i> Ver Solicitud
                                </a>

                            </div>
                        </div>
                        `;
                }
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false,
        initComplete: function () {
            let searchInput = $('.dataTables_filter input');
            searchInput.val('').trigger('input');
        }
    });

    //Buscar al presionar Enter en tu input
    $('#customSearchSolicitudes').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaSolicitudes.draw(); // ahora sí funciona
        }
    });

    $('#searchBtSolicitudes').off('click').on('click', function () {
        tablaSolicitudes.draw();
    });

    $('#clearSearchBtnSolicitudes').on('click', function (e) {
        $('#customSearchSolicitudes').val('');
        tablaSolicitudes.draw();
    });
}

function cargarSolicitudesAceptadas() {
    tablaSolicitudesAceptadas = $('#tbl-solicitudes-aceptadas').DataTable({
        serverSide: true,
        processing: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            type: 'GET',
            url: baseURL + 'getSolicitudesTablaAceptadas',
            data: function (d) {
                d.searchValue = $('#customSearchSolicitudesAceptadas').val();
            }
        },
        columns: [
            {
                data: 'cod_solicitud'
            },
            {
                data: 'nombre'
            },
            {
                data: 'estado'
            },
            {
                data: 'fechaGeneracion'
            },
            {
                data: null,
                render: function (data, type, row) {

                    return `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                Acciones
                            </button>
                            <div class="dropdown-menu">

                                <a class="dropdown-item dropdown-item-custom btn-ver-solicitud-solo-ver" href="#"
                                    data-id="${row.id}">
                                    <i class="fas fa-eye"></i> Ver Solicitud
                                </a>

                                <a class="dropdown-item dropdown-item-custom btn-ver-contrato-pdf"  href="#"
                                    data-id="${row.id}">
                                    <i class="fas fa-file-contract"></i> Contrato pdf
                                </a>

                            </div>
                        </div>
                        `;
                }
            }
        ],
        language: {
            url: baseURL + "plugins/datatables/es-ES.json"
        },
        stateSave: false,
        responsive: true,
        autoWidth: false,
        initComplete: function () {
            let searchInput = $('.dataTables_filter input');
            searchInput.val('').trigger('input');
        }
    });

    //Buscar al presionar Enter en tu input
    $('#customSearchSolicitudesAceptadas').on('keypress', function (e) {
        if (e.which == 13) { // Enter
            tablaSolicitudesAceptadas.draw(); // ahora sí funciona
        }
    });

    $('#searchBtSolicitudesAceptadas').off('click').on('click', function () {
        tablaSolicitudesAceptadas.draw();
    });

    $('#clearSearchBtnSolicitudesAceptadas').on('click', function (e) {
        $('#customSearchSolicitudesAceptadas').val('');
        tablaSolicitudesAceptadas.draw();
    });
}

function toggleBotones() {
    if (modo === 'crear') {
        $("#guardar-registro").show();
        $("#actualizar-registro").hide();

        // ocultar botón siguiente final
        $("#btn-siguiente-final").hide();
        $(".btn-vista-previa-contrato").hide();
        $(".vista-editar").hide();

    } else if (modo == 'ver') {
        $("#guardar-registro").hide();
        $("#actualizar-registro").hide();
        $(".btn-vista-previa").show();
        $(".vista-editar").show();
        $("#btn-editar").hide().prop("disabled", true);
        $("#btn-limpiar").hide().prop("disabled", true);
        $("#btn-siguiente-final").show();
        $(".btn-vista-previa-contrato").show();


        // DESHABILITAR TODOS LOS CAMPOS
        $("input, select, textarea").prop("disabled", true);
    } else if (modo == 'editar') {

        $(".btn-vista-contrato").toggle();
        $("#guardar-registro").hide();
        $("#actualizar-registro").show();

        // 1. habilitar todo primero
        $("input, select, textarea")
            .prop("disabled", false)
            .prop("readonly", false);

        // 2. luego deshabilitar los específicos
        inputs.selectCliente.prop('disabled', true);
        inputs.fechaCreacion.prop('disabled', true);

        $("#btn-editar").hide().prop("disabled", false);
        $("#btn-limpiar").hide().prop("disabled", false);

        // 🔥 select2 fix
        $('select').trigger('change');

        $("#btn-siguiente-final").show();
        $(".btn-vista-previa-contrato").show();
        $(".vista-editar").show();
    }
}

function cargarSolicitudDesdeURL() {
    const params = new URLSearchParams(window.location.search);
    const encoded = params.get('solicitud');

    if (encoded) {
        const id = atob(encoded); // decodifica

        modo = 'editar'; // AQUÍ defines el modo
        idSolicitud = id;
        // console.log(modo);
        $.ajax({
            url: baseURL + 'getSolicitudById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                // mostrar botón correcto
                toggleBotones();
                // console.log(modo);

                const d = response.data;

                inputs.idSolicitud.val(d.id_solicitud);
                inputs.fechaCreacion.val(d.fechaCreacion);

                // SECCION DE CLIENTE
                const cliente = {
                    id_cliente: d.id_cliente,
                    nombre_completo: d.nombre,
                    edad: d.edad,
                    dui: d.dui,
                    nit: d.nit,
                    extendido: d.extendido,
                    fecha: d.fecha,
                    lugar_nacimiento: d.lugarNacimiento,
                    fecha_nacimiento: d.fechaNacimiento,
                    estado_familiar: d.estadoFamiliar,
                    numero_grupo_familiar: d.numeroGrupoFamiliar,
                    direccion_completa: d.direccion,
                    lugar_trabajo: d.lugarDeTrabajo,
                    ocupacion: d.ocupacion,
                    telefono: d.telefonos
                };
                llenarInputs(cliente);

                // SECCION DE BENEFICIARIO
                const beneficiario = {
                    id_beneficiario: d.idBeneficiario,
                    nombre: d.nombreBeneficiario,
                    edad: d.edadBeneficiario,
                    parentesco: d.parentescoBeneficiario,
                    direccion: d.direccionBeneficiario
                };
                cargarBeneficiarios(cliente.id_cliente, function (lista) {
                    if (beneficiario.id_beneficiario) {
                        // seleccionar en el select
                        $('#beneficiarios-registratos')
                            .val(beneficiario.id_beneficiario)
                            .trigger('change');
                        // llenar inputs
                        llenarInputsBeneficiarios(beneficiario);
                        eventoSeccionBeneficarioEditar();
                    }
                });

                // SECCION DATOS DEL INMUEBLE
                inputs.direccionInmueble.val(d.direccionInmueble);
                inputs.propietario.prop('checked', d.propietario === "1");
                inputs.inquilino.prop('checked', d.inquilino === "1");
                inputs.representante.prop('checked', d.representante === "1");
                inputs.otroCheck.val(d.otroCheck);
                inputs.abonera.prop('checked', d.abonera === "1");
                inputs.hoyoSeco.prop('checked', d.hoyoSeco === "1");
                inputs.lavable.prop('checked', d.lavable === "1");
                inputs.otroBaño.val(d.otroBaño);

                // SECCION DE ENTREVISTA DIRIGIDA
                let tieneLetrina = d.aceptaConstruccionLetrina;
                if (tieneLetrina == 1) {
                    inputs.si.prop('checked', true);
                } else {
                    inputs.no.prop('checked', false);
                }
                inputs.tiempo.val(d.tiempo);
                inputs.monto.val(d.monto);
                inputs.contado.prop('checked', d.contado === "1");
                if ($('#contado').is(':checked')) {
                    inputs.otro.prop('disabled', true);
                    inputs.cantidadDePagos.prop('disabled', true);
                    inputs.totalCuota.prop('disabled', true);
                } else {
                    inputs.otro.prop('disabled', false);
                    inputs.cantidadDePagos.prop('disabled', false);
                    inputs.totalCuota.prop('disabled', false);
                };
                inputs.otro.val(d.otroTipoPago);
                inputs.idPlanPago.val(d.idPlanDePago);
                inputs.cantidadDePagos.val(d.cantidadDePagos);
                inputs.totalCuota.val(d.totalCuota);
                inputs.interes.val(d.interesACobrar);

                // SECCION DE COMISION MUNICIPAL
                inputs.acuerdo.val(d.acuerdo);
                inputs.fechaSession.val(d.fechaSession);
                inputs.numeroActa.val(d.numeroActa);

                // SECCION DE LOS QUE FIRMAN
                inputs.idFirmanteAdministrador.val(d.idAdministrador);
                inputs.nombreAdministrador.val(d.nombreAdministrador);
                inputs.idFirmanteComision1.val(d.idComision1);
                inputs.nombreComision1.val(d.nombreComision1);
                inputs.idFirmanteComision2.val(d.idComision2);
                inputs.nombreComision2.val(d.nombreComision2);
            }
        });
    }
}

function setRutaSeleccionada(id, texto) {
    let select = $('#rutas');

    // crear opción si no existe
    let option = new Option(texto, id, true, true);
    select.append(option).trigger('change');
}

function setMedidorSeleccionada(id, texto) {
    let select = $('#medidores');

    // crear opción si no existe
    let option = new Option(texto, id, true, true);
    select.append(option).trigger('change');
}

function setTarifasSeleccionada(id, texto) {
    let select = $('#tarifas');

    // crear opción si no existe
    let option = new Option(texto, id, true, true);
    select.append(option).trigger('change');
}

function cargarSolicitudDesdeURLSoloVer() {
    const params = new URLSearchParams(window.location.search);
    const encoded = params.get('solicitud');

    if (encoded) {
        const id = atob(encoded); // decodifica

        modo = 'ver'; // AQUÍ defines el modo
        idSolicitud = id;

        $.ajax({
            url: baseURL + 'getSolicitudById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function (response) {
                // mostrar botón correcto
                toggleBotones();

                const d = response.data;

                inputs.idSolicitud.val(d.id_solicitud);
                inputs.fechaCreacion.val(d.fechaCreacion);

                // SECCION DE CLIENTE
                const cliente = {
                    id_cliente: d.id_cliente,
                    nombre_completo: d.nombre,
                    edad: d.edad,
                    dui: d.dui,
                    nit: d.nit,
                    extendido: d.extendido,
                    fecha: d.fecha,
                    lugar_nacimiento: d.lugarNacimiento,
                    fecha_nacimiento: d.fechaNacimiento,
                    estado_familiar: d.estadoFamiliar,
                    numero_grupo_familiar: d.numeroGrupoFamiliar,
                    direccion_completa: d.direccion,
                    lugar_trabajo: d.lugarDeTrabajo,
                    ocupacion: d.ocupacion,
                    telefono: d.telefonos
                };
                llenarInputs(cliente);

                // SECCION DE BENEFICIARIO
                const beneficiario = {
                    id_beneficiario: d.idBeneficiario,
                    nombre: d.nombreBeneficiario,
                    edad: d.edadBeneficiario,
                    parentesco: d.parentescoBeneficiario,
                    direccion: d.direccionBeneficiario
                };
                cargarBeneficiarios(cliente.id_cliente, function (lista) {
                    if (beneficiario.id_beneficiario) {
                        // seleccionar en el select
                        $('#beneficiarios-registratos')
                            .val(beneficiario.id_beneficiario)
                            .trigger('change');
                        // llenar inputs
                        llenarInputsBeneficiarios(beneficiario);
                        eventoSeccionBeneficarioEditar();
                    }
                });

                // SECCION DATOS DEL INMUEBLE
                inputs.direccionInmueble.val(d.direccionInmueble);
                inputs.propietario.prop('checked', d.propietario === "1");
                inputs.inquilino.prop('checked', d.inquilino === "1");
                inputs.representante.prop('checked', d.representante === "1");
                inputs.otroCheck.val(d.otroCheck);
                inputs.abonera.prop('checked', d.abonera === "1");
                inputs.hoyoSeco.prop('checked', d.hoyoSeco === "1");
                inputs.lavable.prop('checked', d.lavable === "1");
                inputs.otroBaño.val(d.otroBaño);

                // SECCION DE ENTREVISTA DIRIGIDA
                let tieneLetrina = d.aceptaConstruccionLetrina;
                if (tieneLetrina == 1) {
                    inputs.si.prop('checked', true);
                } else {
                    inputs.no.prop('checked', false);
                }
                inputs.tiempo.val(d.tiempo);
                inputs.monto.val(d.monto);
                inputs.contado.prop('checked', d.contado === "1");
                if ($('#contado').is(':checked')) {
                    inputs.otro.prop('disabled', true);
                    inputs.cantidadDePagos.prop('disabled', true);
                    inputs.totalCuota.prop('disabled', true);
                } else {
                    inputs.otro.prop('disabled', false);
                    inputs.cantidadDePagos.prop('disabled', false);
                    inputs.totalCuota.prop('disabled', false);
                };
                inputs.otro.val(d.otroTipoPago);
                inputs.idPlanPago.val(d.idPlanDePago);
                inputs.cantidadDePagos.val(d.cantidadDePagos);
                inputs.totalCuota.val(d.totalCuota);
                inputs.interes.val(d.interesACobrar);

                // SECCION DE COMISION MUNICIPAL
                inputs.acuerdo.val(d.acuerdo);
                inputs.fechaSession.val(d.fechaSession);
                inputs.numeroActa.val(d.numeroActa);

                // SECCION DE LOS QUE FIRMAN
                inputs.idFirmanteAdministrador.val(d.idAdministrador);
                inputs.nombreAdministrador.val(d.nombreAdministrador);
                inputs.idFirmanteComision1.val(d.idComision1);
                inputs.nombreComision1.val(d.nombreComision1);
                inputs.idFirmanteComision2.val(d.idComision2);
                inputs.nombreComision2.val(d.nombreComision2);

                // SECCION SOLO DE CONTRATO
                inputs.fichaAlcaldia.val(d.fichaAlcaldia)
                inputs.fechaInicio.val(d.fechaInicio)
                inputs.fechaVencimiento.val(d.fechaVencimiento)
                setRutaSeleccionada(d.idRuta, d.nombreRuta);
                setMedidorSeleccionada(d.idMedidor, d.numeroSerie);
                inputs.direccionMedidor.val(d.direccionMedidor);

                setTarifasSeleccionada(d.idTarifa, d.codigoTarifa + ' desde ' + d.desde + ' hasta ' + d.hasta)
            }
        });
    }
}

// function enviarDatosContrato() {
//     let data = getData();

//     // crear form dinámico
//     let form = document.createElement("form");
//     form.method = "POST";
//     form.action = baseURL + "contratos/pdf";
//     form.target = "_blank"; // abre en nueva pestaña

//     // convertir FormData a inputs
//     for (let pair of data.entries()) {
//         let input = document.createElement("input");
//         input.type = "hidden";
//         input.name = pair[0];
//         input.value = pair[1];
//         form.appendChild(input);
//     }

//     document.body.appendChild(form);
//     form.submit();
// }

function enviarDatosContrato(extraData = {}, usarFormData = true) {

    let data;

    // decidir de dónde salen los datos
    if (usarFormData) {
        data = getData(); // cuando sí existe formulario
    } else {
        data = new FormData(); // cuando vienes desde tabla
    }

    // agregar datos extra (como id)
    for (let key in extraData) {
        data.append(key, extraData[key]);
    }

    let form = document.createElement("form");
    form.method = "POST";
    form.action = baseURL + "contratos/pdf";
    form.target = "_blank";

    for (let pair of data.entries()) {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = pair[0];
        input.value = pair[1];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

function eventosUsuarios() {
    $('#buscar-cliente').on('select2:clear', function () {
        limpiarInputs();
    })

    $(".btn-siguiente").on("click", function () {
        let target = $(this).data("target");
        irASiguiente(target);
    });

    $(".btn-anterior").on("click", function () {
        let target = $(this).data("target");
        irAnterior(target);
    });

    $(".btn-vista-previa").on("click", function (e) {
        vistaPrevia(e);
    });

    $('.btn-vista-previa-contrato').on('click', function () {
        enviarDatosContrato();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarSolicitudContrato('nuevaSolicitud');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarSolicitudContrato('aceptarSolicitud');
    });

    $('#btn-editar').on('click', function () {
        habilitarInputs();
    });

    $('#btn-limpiar').on('click', function () {
        limpiarInputsBeneficiario();
    });


    // evento para ver solicitud editar
    $(document).on("click", ".btn-ver-solicitud", function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const encoded = btoa(id);

        window.location.href = baseURL + 'nueva_solicitud?solicitud=' + encoded + '&modo=editar';
    });

    // EVENTO CORRECTO (delegado) de ver solicitud desde la tabla
    $(document).on("click", ".btn-ver-contrato", function (e) {
        e.preventDefault();

        const id = $(this).data('id');

        enviarDatosContrato(
            { solicitud: id }, // datos mínimos
            false              // no usar getData()
        );
    });

    $(document).on("click", ".btn-siguiente-final", function () {
        const target = $(this).data("target");

        // abrir sección destino
        $(target).collapse('show');
    });

    // evento solo ver la solicitud ya creada
    $(document).on("click", ".btn-ver-solicitud-solo-ver", function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const encoded = btoa(id);

        window.location.href = baseURL + 'nueva_solicitud?solicitud=' + encoded + '&modo=ver';
    });

    $(document).on("click", ".btn-ver-contrato-pdf", function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const encoded = btoa(id);

        window.open(baseURL + 'contratos/contrato?solicitud=' + encoded, '_blank');
    });
}

function detectarModo() {
    const params = new URLSearchParams(window.location.search);

    const encoded = params.get('solicitud');
    const tipoModo = params.get('modo');

    if (!encoded) {
        modo = 'crear';
    } else if (tipoModo === 'ver') {
        modo = 'ver';
    } else {
        modo = 'editar';
    }

    console.log("Modo detectado:", modo);
}

function iniciarTodo() {
    eventosUsuarios();

    detectarModo(); // primero definir modo

    if (modo === 'editar') {
        cargarSolicitudDesdeURL();
    } else if (modo === 'ver') {
        cargarSolicitudDesdeURLSoloVer();
    }

    toggleBotones(); //después aplicar UI


    // cargarSolicitudDesdeURL();
    // cargarSolicitudDesdeURLSoloVer();
    // toggleBotones();
    validarCampoDui();
    cargarClientes();
    cargarRutas();
    cargarMedidores();
    cargarTarifas();
    eventoSeleccionCliente();
    eventoSeleccionBeneficiario();
    validarTipoPago();
    cargarSolicitudes();
    cargarSolicitudesAceptadas();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);
