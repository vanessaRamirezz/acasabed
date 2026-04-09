import { alertaError, alertaInfo, alertaOk, alertEnSweet, colorEnInputConFocus, colorEnInputConFocusSelect, eliminarColorYfocus, eliminarColorYfocusSelect, validarCampo } from "../metodos/metodos.js";

const inputs = {
    //datos del cliente
    selectCliente: $("#buscar-cliente"),
    fechaCreacion: $("#fecha-creacion"),
    idCliente: $("#id-cliente"),
    nombre: $("#nombre"),
    edad: $("#edad"),
    dui: $("#dui"),
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
    si: $("#si"),
    no: $("#no"),
    tiempo: $("#tiempo"),
    monto: $("#monto"),
    contado: $("#contado"),
    otro: $("#otro"),
    cantidadDePagos: $("#cantidad-pagos"),
    totalCuota: $("#total-cuota"),

    // datos de comision municipal
    acuerdo: $("#acuerdo"),
    fechaSession: $("#fecha-session"),
    numeroActa: $("#numero-acta"),

    // datos del contrato
    fechaInicio: $("#fecha-inicio"),
    fechaVencimiento: $("#fecha-vencimiento"),
    estado: $("#estado"),
    ruta: $("#rutas"),
    medidor: $("#medidores"),
    direccionMedidor: $("#direccion-medidor"),
    tarifa: $("#tarifas"),

}

function getData() {
    let formData = new FormData();

    // datos del cliente
    formData.append('fechaCreacion', inputs.fechaCreacion.val().trim());
    formData.append('idCliente', inputs.idCliente.val().trim());
    formData.append('nombre', inputs.nombre.val().trim());
    formData.append('dui', inputs.dui.val().trim());
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

    formData.append('abonera', inputs.abonera.val().trim());
    formData.append('hoyoSeco', inputs.hoyoSeco.val().trim());
    formData.append('lavable', inputs.lavable.val().trim());
    formData.append('otroBaño', inputs.otroBaño.val().trim());

    // datos de la entrevista dirigida
    formData.append('si', inputs.si.is(":checked"));
    formData.append('no', inputs.no.is(":checked"));
    formData.append('tiempo', inputs.tiempo.val().trim());
    formData.append('monto', inputs.monto.val().trim());
    formData.append('contado', inputs.contado.is(":checked"));
    formData.append('otro', inputs.otro.val().trim());
    formData.append('cantidadDePagos', inputs.cantidadDePagos.val().trim());
    formData.append('totalCuota', inputs.totalCuota.val().trim());

    //datos de comision municipa
    formData.append('acuerdo', inputs.acuerdo.val().trim());
    formData.append('fechaSession', inputs.fechaSession.val().trim());
    formData.append('numeroActa', inputs.numeroActa.val().trim());

    // datos del contrato
    formData.append('fechaInicio', inputs.fechaInicio.val().trim());
    formData.append('fechaVencimiento', inputs.fechaVencimiento.val().trim());
    formData.append('estado', inputs.estado.val().trim());
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

function eventoSeleccionCliente() {
    $('#buscar-cliente').on('select2:select', function (e) {
        const cliente = e.params.data.data;

        llenarInputs(cliente);
    });
}

function llenarInputs(cliente) {
    inputs.idCliente.val(cliente.id_cliente);
    inputs.nombre.val(cliente.nombre_completo).prop('disabled', true);
    inputs.edad.val(cliente.edad).prop('disabled', true);
    inputs.dui.val(cliente.dui).prop('disabled', true);
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

    const fecha = new Date(fechaInput);

    const dia = fecha.getDate();
    const anio = fecha.getFullYear();

    const meses = [
        "enero", "febrero", "marzo", "abril", "mayo", "junio",
        "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
    ];

    const mes = meses[fecha.getMonth()];

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
    fieldLine("DUI NUMERO:", dui, margin, yContenido);

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

    // =========================
    // Abonera + Hoyo Seco (especial)
    // =========================
    const abonera = data.get('abonera') || '';
    const hoyoSeco = data.get('hoyoSeco') || '';
    doc.text("Abonera:", margin, yContenido);
    const xInicio2 = margin + 50;
    const anchoLugar2 = 100;
    const anchoFecha2 = 100;
    doc.text(abonera, xInicio2, yContenido);
    doc.line(xInicio2, yContenido + 2, xInicio2 + anchoLugar2, yContenido + 2);
    const xComa2 = xInicio2 + anchoLugar2 + 15;
    doc.text("Hoyo Seco:", xComa2, yContenido);
    doc.text(hoyoSeco, xComa2 + 65, yContenido);
    doc.line(xComa2 + 65, yContenido + 2, xComa2 + 65 + anchoFecha2, yContenido + 2);

    yContenido += 25;

    // =========================
    // Lavable + Otro (especial)
    // =========================
    const lavable = data.get('lavable') || '';
    const otroBaño = data.get('otroBaño') || '';
    doc.text("Lavable:", margin, yContenido);
    doc.text(lavable, xInicio2, yContenido);
    doc.line(xInicio2, yContenido + 2, xInicio2 + anchoLugar2, yContenido + 2);
    doc.text("Otro:", xComa2, yContenido);
    doc.text(otroBaño, xComa2 + 65, yContenido);
    doc.line(xComa2 + 65, yContenido + 2, xComa2 + 65 + anchoFecha2, yContenido + 2);

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
    setFont("normal", 9);
    doc.text(
        "Si se atrasa en el pago el interés que usted cancelara será de del 05% anual ",
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
    // TITULOS
    // =========================
    setFont("normal", 8);

    // Posiciones base
    let yLine = y;        // posición de la línea
    let yText = y + 15;    // texto debajo de la línea (ajusta 5–8 según tamaño de fuente)

    // Firma 1
    let x1 = margin;
    doc.line(x1, yLine, x1 + 150, yLine);
    doc.text(50, yText, "Firma de la Persona Solicitante");

    // Firma 2
    let x2 = pageWidth / 2 + 20;
    doc.line(x2, yLine, x2 + 150, yLine);
    doc.text(350, yText, "Firma de Administrador");


    // =========================
    // OUTPUT
    // =========================
    window.open(doc.output('bloburl'), '_blank');
}

function enviarDatosContrato() {
    let data = getData();

    // crear form dinámico
    let form = document.createElement("form");
    form.method = "POST";
    form.action = baseURL + "contratos/pdf";
    form.target = "_blank"; // abre en nueva pestaña

    // convertir FormData a inputs
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


function guardarOeditarSolicitudContrato(tipoProceso) {
    const data = getData();
    let tipo_proceso = tipoProceso === '1' ? 'nuevaSolicitudContrato' : 'editarSolicitudContrato';

    // const emailRegex = /^\S+@\S+\.\S+$/;

    if (!data.get('idCliente')) {
        alertaError('Debe seleccionar un cliente');
        colorEnInputConFocusSelect(inputs.selectCliente[0]);
        return false;
    } else {
        eliminarColorYfocusSelect(inputs.selectCliente[0]);
    }
    // if (data.get('correo') && !emailRegex.test(data.get('correo'))) {
    //     alertaError('Por favor ingrese un correo válido');
    //     colorEnInputConFocus(inputs.correo[0]);
    //     return false;
    // } else {
    //     eliminarColorYfocus(inputs.correo[0]);
    // }

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
                alertaOk(response.mensaje);
                // tablaClientes.ajax.reload();
                Swal.close();
                // $('#modal-clientes').modal('hide');
            } else {
                alertEnSweet('error', 'Uups..', response.mensaje);
            }
        },
        error: function () {
            alertEnSweet('error', 'Ups..', 'Ocurrió un error en la operacion');
        }
    });
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

    $('.btn-vista-previa-pdf').on('click', function () {
        enviarDatosContrato();
    });

    $("#guardar-registro").on("click", function () {
        guardarOeditarSolicitudContrato('1');
    });

    $("#actualizar-registro").on("click", function () {
        guardarOeditarSolicitudContrato('2');
    });
}

function iniciarTodo() {
    eventosUsuarios();
    validarCampoDui();
    cargarClientes();
    cargarRutas();
    cargarMedidores();
    cargarTarifas();
    eventoSeleccionCliente();
}

document.addEventListener('DOMContentLoaded', iniciarTodo);