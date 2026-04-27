<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaModel;
use App\Models\FacturaServicioModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;
use App\Models\RangoFacturaModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class FacturacionServicio extends BaseController
{
    private $contratosModel;

    private $periodosModel;
    private $rangoFacturasModel;
    private $lecturasModel;
    private $facturaDetalleModel;
    private $facturaModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
        $this->periodosModel = new PeriodoModel();
        $this->rangoFacturasModel = new RangoFacturaModel();
        $this->lecturasModel = new LecturaModel();
        $this->facturaDetalleModel = new FacturaDetalleModel();
        $this->facturaModel = new FacturaModel();
    }

    public function index()
    {
        return view('facturacionServicio/index');
    }

    public function getFacturasServicio()
    {
        try {

            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->facturaModel->getHistorialFacturas($start, $length, $searchValue);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => $result['data']
            ]);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }

    function dibujarComprobante($pdf, $x, $y, $titulo, $factura, $detalle)
    {
        $w = 95; // aca se mide lo ancho del cuadro principal
        $h = 150; // aca se mide lo largo del cuadro principal

        // Marco
        $pdf->SetDrawColor(0, 51, 153);
        $pdf->Rect($x, $y, $w, $h);

        //LOGO 
        $pdf->Image(
            FCPATH . 'dist/img/agua.png', // ruta
            $x + 2,   // posición X (ligeramente dentro)
            $y + 2,   // posición Y
            14,       // ancho (pequeño)
            14        // alto
        );

        // ENCABEZADO
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY($x + 15, $y + 7); // 👈 control exacto (NO usar solo SetX)

        $pdf->MultiCell(
            $w - 15,
            4,
            "Asociación Comunal Administradora del Sistema de Agua
            Potable del Cantón El Coyolito, Tejutla, Bendición de Dios.
            NIT: 0433-140613-101-8
            acasabed2013@hotmail.com",
            0,
            'C'
        );

        // NUMERO ROJO
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetXY($x + 2, $y + 18);
        $pdf->Cell(40, 5, 'N° ' . $factura['correlativo'], 0, 0);
        $pdf->SetTextColor(0, 0, 0);

        // TITULO
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($x, $y + 24);
        $pdf->Cell($w, 5, $titulo, 0, 1, 'C');

        // BLOQUE CLIENTE
        // MARCO GENERAL
        $pdf->Rect($x, $y + 30, 76, 22);

        // punto inicial
        $currentY = $y + 32;

        // === CLIENTE ===
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->SetXY($x + 2, $currentY);

        $pdf->MultiCell(60, 4, $factura['cliente'], 0, 'L');

        // actualizar Y dinámicamente
        $currentY = $pdf->GetY();

        // === DIRECCIÓN ===
        $pdf->SetXY($x + 2, $currentY);

        $pdf->MultiCell(60, 4, $factura['direccion'], 0, 'L');

        $currentY = $pdf->GetY();

        // === MEDIDOR LABEL ===
        $currentY += 6; // espacio extra
        $pdf->SetXY($x + 1, $currentY);

        // etiqueta (azul)
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(20, 4, 'MEDIDOR No.:', 0, 0, 'L'); // 👈 0 = no salto

        // valor (negro)
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(40, 4, $factura['numero_serie'], 0, 1, 'L'); // 👈 1 = salto

        $leftTop = $y + 30;          // inicio del bloque cliente
        $leftBottom = $pdf->GetY();  // donde terminó realmente
        $leftHeight = $leftBottom - $leftTop;

        // ancho del bloque izquierdo
        $leftWidth = 76;

        // bloque derecho adaptado
        $rightX = $x + $leftWidth;
        $rightY = $y + 30;
        $rightW = $w - $leftWidth; // 👈 esto lo ajusta automáticamente

        // alturas definidas (clave)
        $totalRight = $leftHeight;

        // dividir en 4 bloques (doc label, doc value, cuenta label, cuenta value)
        $hLabel = $totalRight * 0.2;
        $hValue = $totalRight * 0.2;


        // === No. DOCUMENTO ===
        $pdf->SetXY($rightX, $rightY);

        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 5);
        $pdf->Cell($rightW, $hLabel, 'No. DOCUMENTO', 1, 0, 'C');

        $pdf->SetXY($rightX, $rightY + $hLabel);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 5);
        $pdf->Cell($rightW, $hValue, $factura['numero_contrato'], 1, 0, 'C');

        // === No. CUENTA ===
        $pdf->SetXY($rightX, $rightY + $hLabel + $hValue);

        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 5);
        $pdf->Cell($rightW, $hLabel, 'No. CUENTA', 1, 0, 'C');

        $pdf->SetXY($rightX, $rightY + ($hLabel * 2) + $hValue);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 5);
        $pdf->Cell($rightW, $hValue, $factura['codigo_solicitud'], 1, 0, 'C');


        // LECTURAS
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 5);

        // posición inicial

        $pdf->SetXY($x, $y + 52);

        // ancho dinámico (mejor distribuido)
        $colW = $w / 5;

        // fila completa
        $pdf->Cell($colW, 5, 'LEC. ACTUAL M³', 1, 0, 'C');
        $pdf->Cell($colW, 5, 'LEC. ANTERIOR M³', 1, 0, 'C');
        $pdf->Cell($colW, 5, 'CONSUMO M³', 1, 0, 'C');
        $pdf->Cell($colW, 5, 'COD. TARIFA', 1, 0, 'C');
        $pdf->Cell($colW, 5, 'FECHA LECTURA', 1, 1, 'C'); // 👈 solo aquí salto

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX($x);
        $pdf->Cell($colW, 5, $factura['lecturaActual'], 1, 0);
        $pdf->Cell($colW, 5, $factura['lecturaAnterior'], 1, 0);
        $pdf->Cell($colW, 5, $factura['consumo'], 1, 0);
        $pdf->Cell($colW, 5, $factura['codigoTarifa'], 1, 0);
        $pdf->Cell($colW, 5, $factura['fechaLectura'], 1, 1);


        // DETALLE
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetXY($x, $y + 62);

        // CÓDIGO ocupa columna 1
        $pdf->Cell($colW, 5, 'CÓDIGO', 1, 0, 'C');

        // CONCEPTO ocupa columnas 2,3,4 (3 columnas unidas)
        $pdf->Cell($colW * 3, 5, 'CONCEPTOS FACTURADOS', 1, 0, 'C');

        // VALOR ocupa columna 5
        $pdf->Cell($colW, 5, 'VALOR', 1, 1, 'C');

        // filas
        $yDetalle = $y + 67;

        // 1. Mostrar los registros reales
        $total = 0;
        foreach ($detalle as $item) {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 7);

            $concepto = $item['concepto'] ?? '';
            $monto = number_format($item['monto'], 2);

            // Guardar posición inicial
            $xInicio = $x;
            $yInicio = $yDetalle;

            // Calcular altura dinámica del concepto
            $alturaConcepto = $pdf->getStringHeight($colW * 3, $concepto);

            // Usar la mayor altura (mínimo 7 para mantener consistencia)
            $alturaFila = max(7, $alturaConcepto);

            // Columna vacía
            $pdf->SetXY($xInicio, $yInicio);
            $pdf->Cell($colW, $alturaFila, '', 'L', 0);

            // Concepto con salto automático
            $pdf->SetXY($xInicio + $colW, $yInicio);
            $pdf->MultiCell($colW * 3, 7, $concepto, 'L', 'L', false);

            // Monto (alineado con altura dinámica)
            $pdf->SetXY($xInicio + ($colW * 4), $yInicio);
            $pdf->Cell($colW, $alturaFila, $monto, 'LR', 0, 'R');

            // Mover cursor hacia abajo
            $yDetalle += $alturaFila;

            $total += $item['monto'];
        }

        // 2. Rellenar filas vacías hasta 10
        $filas = count($detalle);

        for ($i = $filas; $i < 10; $i++) {

            $pdf->SetXY($x, $yDetalle);

            $pdf->Cell($colW, 7, '', 'L', 0);
            $pdf->Cell($colW * 3, 7, '', 'L', 0);
            $pdf->Cell($colW, 7, '', 'LR', 1);

            $yDetalle += 5;
        }

        // TOTALES
        $pdf->SetXY($x, $yDetalle); // 👈 sin +19

        // izquierda
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell($colW * 2.5, 6, 'SI UD. NO PAGA A TIEMPO PAGARÁ', 'TB', 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colW * 0.5, 6, '$' . $total + 2, 'TB', 0, 'R');

        // derecha
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell($colW, 6, 'TOTAL:', 'TB', 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colW, 6, '$ ' . $total, 'TB', 1, 'R');

        // FOOTER
        $currentY = $pdf->GetY();

        // === IZQUIERDA (texto en 2 líneas controladas) ===
        $pdf->SetXY($x, $currentY);
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 6);

        $pdf->MultiCell(
            $colW * 4,
            3,
            "USTED PAGA SIN MORA ENTRE EL 24 AL 3.\nPAGA CON MORA DEL 04 AL 07 DE CADA MES",
            0,
            'L'
        );

        $pdf->SetXY($x + ($colW * 4), $currentY);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colW, 5, $factura['fechaVencimiento'], 0, 0, 'R');


        $currentY = $pdf->GetY();
        $pdf->SetTextColor(0, 51, 153);
        // $currentY = $pdf->GetY();

        // === TELÉFONO (solo lado izquierdo enmarcado) ===
        $pdf->SetXY($x, $currentY + 7);

        // ancho izquierdo (igual que totales)
        $leftW = $colW * 3;
        $rightW = $colW * 2;

        // rectángulo SOLO del lado izquierdo
        $pdf->RoundedRect(
            $x,                // posición X
            $currentY + 6,    // posición Y
            $leftW,            // ancho
            6,                 // alto
            1.5,               // radio de la esquina (ajusta aquí)
            '1111',            // esquinas (todas redondeadas)
            ''                 // estilo (solo borde)
        );

        // texto dentro del cuadro
        $pdf->SetFont('helvetica', '', 5);
        $pdf->SetXY($x + 1, $currentY + 7);
        $pdf->Cell($leftW - 2, 4, 'TELÉFONO DE EMERGENCIA: 2332-0282', 0, 0, 'L');

        // === TEXTO DERECHO (sin cuadro) ===
        $pdf->SetXY($x + $leftW, $currentY + 6);

        $pdf->MultiCell(
            $rightW,
            3,
            "PRESENTAR RECLAMO 3 DÍAS\nDESPUÉS DE RECIBIDO",
            0,
            'C'
        );
    }

    private function agregarPaginaFacturaCobro($pdf, $imprimir, $periodo, array $factura, array $detalle)
    {
        $pdf->AddPage();

        if ($imprimir == 'SI') {
            $pdf->SetTitle('Facturas_consumo_periodo_' . $periodo['nombre']);
        } else {
            $pdf->SetTitle('Factura ' . ($factura['cliente'] ?? 'Cobro'));
        }

        $this->dibujarComprobante($pdf, 10, 10, 'COMPROBANTE DEL CLIENTE', $factura, $detalle);
        $this->dibujarComprobante($pdf, 110, 10, 'COMPROBANTE DEL BANCO', $factura, $detalle);
    }


    // private function agregarPaginaFacturaCobro($pdf, $imprimir, $periodo, array $factura, array $detalle, $posY)
    // {
    //     if ($pdf->getNumPages() === 0 || $posY == 10) {
    //         $pdf->AddPage();
    //     }

    //     if ($imprimir == 'SI') {
    //         $pdf->SetTitle('Facturas_consumo_periodo_' . $periodo['nombre']);
    //     } else {
    //         $pdf->SetTitle('Factura ' . ($factura['cliente'] ?? 'Cobro'));
    //     }

    //     $this->dibujarComprobante($pdf, 10, $posY, 'COMPROBANTE DEL CLIENTE', $factura, $detalle);
    //     $this->dibujarComprobante($pdf, 110, $posY, 'COMPROBANTE DEL BANCO', $factura, $detalle);
    // }

    public function facturaCobroServicio($id)
    {
        $data = $this->facturaModel->getFacturaResumenPorId($id);

        log_message('info', ' datos recibidos para enviar a ver la factura' . print_r($data, true));
        // exit;
        if (!$data['factura']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No existe la factura');
        }

        $factura = $data['factura'];
        $detalle = $data['detalle'];
        $imprimir = 'NO';
        $periodo = null;

        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $this->agregarPaginaFacturaCobro($pdf, $imprimir, $periodo, $factura, $detalle);


        // 🔑 Nombre dinámico con cliente
        $nombrePDF = 'Factura_' . preg_replace('/[^A-Za-z0-9]/', '_', $factura['correlativo']) . '.pdf';

        // 🔥 IMPORTANTE: salida directa
        $pdf->Output($nombrePDF, 'I');

        exit; // corta ejecución (clave)
    }

    private function calcularConsumo($lecturaActual, $lecturaAnterior)
    {
        return max(0, (float)$lecturaActual - (float)$lecturaAnterior);
    }

    private function buscarColumna($header, $palabra)
    {
        foreach ($header as $i => $col) {
            if (strpos($col, $palabra) !== false) {
                return $i;
            }
        }
        return false;
    }

    private function procesarExcel($ruta)
    {
        try {

            log_message('info', '2- Leyendo Excel: ' . $ruta);

            $reader = new Xlsx();
            $reader->setReadDataOnly(true);

            //SOLO LEER NOMBRES DE HOJAS (no carga datos aún)
            $worksheetNames = $reader->listWorksheetNames($ruta);

            if (empty($worksheetNames)) {
                throw new \Exception('El archivo no contiene hojas');
            }

            $primeraHoja = $worksheetNames[0];

            log_message('info', '3- Primera hoja detectada: ' . $primeraHoja);

            // SOLO cargar esa hoja
            $reader->setLoadSheetsOnly($primeraHoja);

            $spreadsheet = $reader->load($ruta);
            $sheet = $spreadsheet->getActiveSheet();

            $data = [];
            $header = [];

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];

                foreach ($cellIterator as $cell) {
                    $rowData[] = strtolower(trim((string)$cell->getValue()));
                }

                // encabezado
                if ($rowIndex === 1) {

                    $header = $rowData;

                    log_message('info', '4- Encabezados: ' . print_r($header, true));

                    $colFicha = $this->buscarColumna($header, 'ficha');
                    $colAlumbrado = $this->buscarColumna($header, 'alumbrado');
                    $colAseo = $this->buscarColumna($header, 'aseo');

                    if ($colFicha === false || $colAlumbrado === false || $colAseo === false) {
                        throw new \Exception('Columnas requeridas no encontradas');
                    }

                    continue;
                }

                $ficha = trim($rowData[$colFicha] ?? '');
                if (empty($ficha)) continue;

                $data[] = [
                    'ficha' => $ficha,
                    'alumbrado' => (float)($rowData[$colAlumbrado] ?? 0),
                    'aseo' => (float)($rowData[$colAseo] ?? 0),
                ];
            }

            // iberar memoria
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            log_message('info', '5- Filas procesadas: ' . count($data));

            return $data;
        } catch (\Throwable $e) {
            log_message('error', 'Error Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cargarExcelAlcaldia()
    {
        try {

            // evitar problemas de salida previa
            if (ob_get_length()) ob_clean();

            $db = \Config\Database::connect();

            $file = $this->request->getFile('excel');

            if (!$file || !$file->isValid()) {
                return $this->respondError('Archivo inválido');
            }

            log_message('info', '1- Archivo recibido: ' . $file->getName());

            // guardar archivo
            $nombre = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $nombre);

            $ruta = WRITEPATH . 'uploads/' . $nombre;

            // procesar excel
            $dataExcel = $this->procesarExcel($ruta);

            if (empty($dataExcel)) {
                return $this->respondError('El Excel no contiene datos válidos');
            }

            // limpiar tabla temporal
            $db->table('tmp_alcaldia')->truncate();

            // insertar datos
            $db->table('tmp_alcaldia')->insertBatch($dataExcel);
            // foreach ($dataExcel as $row) {
            //     $db->table('tmp_alcaldia')->insert($row);
            // }

            log_message('info', '6- Datos insertados en tmp_alcaldia');

            return $this->respondSuccess(
                "Excel cargado correctamente (" . count($dataExcel) . " registros)"
            );
        } catch (\Throwable $e) {

            log_message('error', 'Error en carga Excel: ' . $e->getMessage());

            return $this->respondError(
                'Error al procesar el archivo: ' . $e->getMessage()
            );
        }
    }

    public function generarFacturasServicioANTERIOR()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            log_message('info', '--------------------------------------------------------------');
            $periodoActivo = $this->periodosModel->getPeriodoActivo();
            if (!$periodoActivo) {
                throw new \Exception('No hay periodo activo');
            }
            log_message('info', 'Periodo activo ID ' . print_r($periodoActivo, true));

            $contratos = $this->contratosModel->getContratosActivosFacturacionServicio();
            log_message('info', 'Contratos obtenidos: ' . count($contratos));

            $facturasGeneradas = 0;
            foreach ($contratos as $contrato) {

                log_message('info', '--- Procesando contrato: ' . $contrato->id_contrato);
                log_message('info', 'dato del contrato ' . print_r($contrato, true));
                exit;

                // validar si ya existe factura en ese periodo
                $existeFactura = $this->facturaModel
                    ->existeFacturaPeriodoContrato(
                        $contrato->id_contrato,
                        $periodoActivo['id_periodo']
                    );

                if ($existeFactura) {
                    log_message('info', 'Contrato omitido: ya tiene factura en este periodo');
                    continue;
                }

                //obtener las facturas
                $facturasPendientes = $this->facturaModel
                    ->getFacturasPendientesContrato($contrato->id_contrato);

                log_message('info', 'facturas vencidas o pendientes obtenidas de este contrato ' . print_r($facturasPendientes, true));


                if (count($facturasPendientes) >= 3) {
                    log_message('info', 'Contrato con 3 facturas pendientes - NO se factura');
                    continue;
                }

                $saldoAnterior = 0;
                foreach ($facturasPendientes as $factura) {
                    $saldoAnterior += $factura->saldo_pendiente;
                    log_message('info', 'saldo anterior de facturas vencidas ' . $saldoAnterior);
                }


                $correlativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

                // 🔹 1. Obtener datos necesarios (ajusta según tu lógica real)

                // ejemplo: lectura
                $lecturaActual = $this->lecturasModel->getLecturaActual($contrato->id_contrato, $periodoActivo['id_periodo']);
                if (!$lecturaActual) {
                    log_message('error', 'Contrato sin lectura, se omite');
                    continue;
                }
                log_message('info', 'lectura actual con id numero ' . print_r($lecturaActual, true));

                $lecturaAnterior = $this->lecturasModel
                    ->getUltimaLecturaAnterior($contrato->id_contrato, $periodoActivo['id_periodo']);
                log_message('info', 'lectura anterior con id numero ' . print_r($lecturaActual, true));

                $valorLecturaAnterior = (float)($lecturaAnterior['valor'] ?? 0);
                $valorLecturaActual = (float)($lecturaActual['valor'] ?? 0);

                // consumo (ajusta a tu lógica)
                $consumo = $this->calcularConsumo($valorLecturaActual, $valorLecturaAnterior);
                log_message('info', 'consumo de este periodo ' . $consumo);

                // cargos fijos (ejemplo)
                $alumbrado = $contrato->alumbrado ?? 0;
                $trenAseo = $contrato->tren_aseo ?? 0;

                // mora (por ahora 0, luego puedes calcularla)
                $mora = 0;

                // 🔹 2. Calcular monto del mes
                $montoServicio = 0;

                // total de esta factura
                $totalFactura = $montoServicio + $mora;

                // 🔹 3. Obtener correlativo
                $correlativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

                // 🔹 4. Fechas
                $fechaEmision = date('Y-m-d');
                $fechaVencimiento = date('Y-m-d', strtotime('+15 days')); // ajusta regla

                // 🔹 5. Armar data
                $dataFactura = [
                    'correlativo'        => $correlativo,
                    'id_contrato'        => $contrato->id_contrato,
                    'id_periodo'         => $periodoActivo['id_periodo'],
                    'id_lectura'         => $lecturaActual['id_lectura'],
                    'fecha_emision'      => $fechaEmision,
                    'fecha_vencimiento'  => $fechaVencimiento,
                    'alumbrado'          => $alumbrado,
                    'tren_de_aseo'       => $trenAseo,
                    'consumo_mes'        => $consumo,
                    'monto_servicio'     => $montoServicio,
                    'mora'               => $mora,
                    'total'              => $totalFactura,
                    'saldo_pendiente'    => $totalFactura,
                    'estado'             => 'PENDIENTE',
                    'id_usuario'         => session()->get('id_usuario')
                ];

                // 🔹 6. Insertar factura
                $this->facturaModel->insert($dataFactura);
                $idFactura = $this->facturaModel->insertID();
                log_message('info', 'Factura creada correctamente: ' . json_encode($dataFactura));

                $detalles = [
                    ['concepto' => 'SERVICIO DOMICILIAR', 'monto' => $montoServicio, 'orden' => 1],
                    ['concepto' => 'TREN DE ASEO', 'monto' => $trenAseo, 'orden' => 2],
                    ['concepto' => 'ALUMBRADO PU', 'monto' => $alumbrado, 'orden' => 3],
                ];

                foreach ($detalles as $detalle) {
                    $this->facturaDetalleModel->insert([
                        'id_factura' => $idFactura,
                        'concepto'   => $detalle['concepto'],
                        'monto'      => $detalle['monto'],
                        'orden'      => $detalle['orden']
                    ]);
                }

                $facturasGeneradas++;
            }
            // exit;
            $db->transCommit();

            return $this->respondSuccess("Se generaron {$facturasGeneradas} facturas de servicio correctamente");
        } catch (\Throwable $th) {
            $db->transRollback();
            log_message('error', $th->getMessage());

            return $this->respondError($th->getMessage());
        }
    }

    private function calcularMontoServicio($idTarifa, $consumo)
    {
        $db = \Config\Database::connect();

        $rangos = $db->table('tarifas_detalle')
            ->where('id_tarifa', $idTarifa)
            ->orderBy('desde_n_metros', 'ASC')
            ->get()
            ->getResult();

        if (empty($rangos)) {
            throw new \Exception("Tarifa sin configuración");
        }

        $total = 0;
        $consumoRestante = $consumo;

        foreach ($rangos as $index => $rango) {

            $desde = (float)$rango->desde_n_metros;
            $hasta = (float)$rango->hasta_n_metros;
            $precio = (float)$rango->valor_metro_cubico;
            $minimo = (float)$rango->pago_minimo;

            // calcular metros dentro de este rango
            if ($consumo > $desde) {

                $limiteSuperior = min($consumo, $hasta);
                $metrosEnRango = $limiteSuperior - $desde;

                if ($metrosEnRango < 0) {
                    $metrosEnRango = 0;
                }

                $subtotal = $metrosEnRango * $precio;

                // 🔥 aplicar pago mínimo SOLO en el primer bloque
                if ($index === 0 && $minimo > 0) {
                    $subtotal = max($subtotal, $minimo);
                }

                $total += $subtotal;
            }
        }

        return round($total, 2);
    }

    public function generarFacturasServicio()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            log_message('info', '--------------------------------------------------------------');

            $periodoActivo = $this->periodosModel->getPeriodoActivo();
            if (!$periodoActivo) {
                throw new \Exception('No hay periodo activo');
            }

            log_message('info', '1- Periodo activo ID ' . print_r($periodoActivo, true));

            $contratos = $this->contratosModel->getContratosActivosFacturacionServicio();
            log_message('info', '2- Contratos obtenidos: ' . count($contratos));

            $facturasGeneradas = 0;

            foreach ($contratos as $contrato) {

                log_message('info', '3- --- Procesando contrato: ' . $contrato->id_contrato);

                // 1. VALIDAR SI YA EXISTE FACTURA DE CONSUMO
                $existeFactura = $this->facturaModel
                    ->existeFacturaConsumoPeriodoContrato(
                        $contrato->id_contrato,
                        $periodoActivo['id_periodo']
                    );

                if ($existeFactura) {
                    log_message('info', '5- Contrato omitido: ya tiene factura en este periodo');
                    continue;
                }

                // 2. FACTURAS PENDIENTES
                $facturasPendientes = $this->facturaModel
                    ->getFacturasPendientesContrato($contrato->id_contrato);

                if (count($facturasPendientes) >= 3) {
                    log_message('info', '6- Tiene 3 o más pendientes, se omite');
                    continue;
                }

                $detalle = [];
                $totalFactura = 0;

                // 🔴 AGREGAR SALDOS PENDIENTES
                foreach ($facturasPendientes as $facturaPendiente) {

                    $montoPendiente = (float)$facturaPendiente->saldo_pendiente + (float)$facturaPendiente->mora;

                    if ($montoPendiente <= 0) continue;

                    $detalle[] = [
                        'tipo' => 'Consumo',
                        'concepto' => 'Saldo pendiente de factura anterior',
                        'monto' => $montoPendiente,
                        'mora' => 0
                    ];

                    log_message('info', 'detalle de factura anterior ' . print_r($detalle, true));

                    $totalFactura += $montoPendiente;
                }

                // 3. DATOS DE ALCALDÍA
                $tmp = $db->table('tmp_alcaldia')
                    ->where('ficha', $contrato->ficha_alcaldia)
                    ->get()
                    ->getRow();

                if (!$tmp) {
                    log_message('error', "7- Sin datos de alcaldía");
                    continue;
                }

                $alumbrado = (float)$tmp->alumbrado;
                $trenAseo  = (float)$tmp->aseo;

                // 4. CORRELATIVO
                $dataCorrelativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

                // 5. LECTURAS
                $lecturaActual = $this->lecturasModel
                    ->getLecturaActual($contrato->id_contrato, $periodoActivo['id_periodo']);

                if (!$lecturaActual) {
                    log_message('error', 'Sin lectura');
                    continue;
                }

                $lecturaAnterior = $this->lecturasModel
                    ->getUltimaLecturaAnterior($contrato->id_contrato, $periodoActivo['id_periodo']);

                $consumo = $this->calcularConsumo(
                    (float)($lecturaActual['valor'] ?? 0),
                    (float)($lecturaAnterior['valor'] ?? 0)
                );

                // 6. CARGOS ACTUALES
                $montoServicio = $this->calcularMontoServicio(
                    $contrato->id_tarifa,
                    $consumo
                );

                $mora = 0;

                // 🔴 AGREGAR CONCEPTOS ACTUALES
                $detalle[] = [
                    'tipo' => 'Consumo',
                    'concepto' => 'SERVICIO DOMICILIAR',
                    'monto' => $montoServicio,
                    'mora' => 0
                ];

                $detalle[] = [
                    'tipo' => 'Consumo',
                    'concepto' => 'TREN DE ASEO',
                    'monto' => $trenAseo,
                    'mora' => 0
                ];

                $detalle[] = [
                    'tipo' => 'Consumo',
                    'concepto' => 'ALUMBRADO PU',
                    'monto' => $alumbrado,
                    'mora' => 0
                ];

                // 🔥 SUMAR TODO (NO SOBREESCRIBIR)
                $totalFactura += ($montoServicio + $trenAseo + $alumbrado + $mora);

                // 7. INSERT FACTURA
                $fechaEmision = date('Y-m-d');
                $fechaVencimiento = date('Y-m-03', strtotime('first day of next month'));

                $this->facturaModel->insert([
                    'id_rango_factura'   => $dataCorrelativo['id_rango_factura'],
                    'correlativo'        => $dataCorrelativo['correlativo'],
                    'tiraje'             => $dataCorrelativo['tiraje'],
                    'id_contrato'        => $contrato->id_contrato,
                    'id_periodo'         => $periodoActivo['id_periodo'],
                    'id_lectura'         => $lecturaActual['id_lectura'],
                    'fecha_emision'      => $fechaEmision,
                    'fecha_vencimiento'  => $fechaVencimiento,
                    'saldo_pendiente'    => $totalFactura,
                    'estado'             => 'PENDIENTE',
                    'total'              => $totalFactura,
                    'id_usuario'         => session()->get('id_usuario'),
                    'consumo'            => $consumo,
                ]);

                $idFactura = $this->facturaModel->insertID();

                // 8. INSERT DETALLE COMPLETO
                foreach ($detalle as $d) {
                    $this->facturaDetalleModel->insert([
                        'id_factura' => $idFactura,
                        'tipo'       => $d['tipo'],
                        'concepto'   => $d['concepto'],
                        'monto'      => $d['monto'],
                        'mora'       => $d['mora']
                    ]);
                }

                $facturasGeneradas++;
            }
            // exit;
            // limpiar tabla temporal después de usarla
            $db->table('tmp_alcaldia')->truncate();

            $db->transCommit();

            return $this->respondSuccess(
                "Se generaron {$facturasGeneradas} facturas correctamente"
            );
        } catch (\Throwable $e) {

            $db->transRollback();
            log_message('error', $e->getMessage());

            return $this->respondError($e->getMessage());
        }
    }

    private function responderVentanaImpresionConMensaje($mensaje, $statusCode = 400)
    {
        $html = '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Facturas de cobro</title>
        </head>
        <body>
            <script>
                alert(' . json_encode($mensaje) . ');
                window.close();
            </script>
        </body>
        </html>';

        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('text/html; charset=UTF-8')
            ->setBody($html);
    }

    public function imprimirFacturasConsumoPeriodoActivo()
    {
        try {
            $periodo = $this->periodosModel->getPeriodoActivo();

            if (!$periodo) {
                return $this->responderVentanaImpresionConMensaje('No hay periodo activo para imprimir facturas.', 404);
            }
            $facturas = $this->facturaModel
                ->join('facturas_detalle fd', 'fd.id_factura = facturas.id_factura')
                ->where('facturas.id_periodo', $periodo['id_periodo'])
                ->where('fd.tipo', 'Consumo')
                ->groupBy('facturas.id_factura')
                ->orderBy('facturas.id_factura', 'ASC')
                ->findAll();

            if (empty($facturas)) {
                return $this->responderVentanaImpresionConMensaje('No hay facturas generadas en el periodo activo.', 404);
            }

            $pdf = new \TCPDF('P', 'mm', 'A4');
            $pdf->SetMargins(5, 5, 5);
            $pdf->SetAutoPageBreak(false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $imprimir = 'SI';

            // $posicionesY = [5, 150]; // arriba y abajo
            // $index = 0;

            foreach ($facturas as $factura) {
                $dataFactura = $this->facturaModel->getFacturaResumenPorId($factura['id_factura']);

                if (empty($dataFactura['factura'])) {
                    continue;
                }

                // $posY = $posicionesY[$index % 2];

                $this->agregarPaginaFacturaCobro(
                    $pdf,
                    $imprimir,
                    $periodo,
                    $dataFactura['factura'],
                    $dataFactura['detalle'] ?? [],
                    // $posY
                );

                // Cada 2 facturas → nueva página
                // if ($index % 2 == 1) {
                //     // opcional: podrías forzar salto aquí si quieres control estricto
                // }

                // $index++;
            }

            if ($pdf->getNumPages() === 0) {
                return $this->responderVentanaImpresionConMensaje('No se encontraron facturas válidas para imprimir en el periodo activo.', 404);
            }

            if ($this->request->getGet('autoPrint') === '1') {
                $pdf->IncludeJS('print(true);');
            }

            $nombrePDF = 'Facturas_consumo_periodo_' . $periodo['id_periodo'] . '.pdf';

            $pdf->Output($nombrePDF, 'I');
            exit;
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->responderVentanaImpresionConMensaje('Ocurrió un error al preparar la impresión de facturas.', 500);
        }
    }
}
