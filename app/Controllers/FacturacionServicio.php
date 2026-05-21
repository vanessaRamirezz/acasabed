<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaModel;
use App\Models\FacturaServicioModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;
use App\Models\RangoFacturaModel;
use App\Models\ServicioModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class FacturacionServicio extends BaseController
{
    private ContratoModel $contratosModel;
    private PeriodoModel $periodosModel;
    private RangoFacturaModel $rangoFacturasModel;
    private LecturaModel $lecturasModel;
    private FacturaDetalleModel $facturaDetalleModel;
    private FacturaModel $facturaModel;
    private ServicioModel $serviciosModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
        $this->periodosModel = new PeriodoModel();
        $this->rangoFacturasModel = new RangoFacturaModel();
        $this->lecturasModel = new LecturaModel();
        $this->facturaDetalleModel = new FacturaDetalleModel();
        $this->facturaModel = new FacturaModel();
        $this->serviciosModel = new ServicioModel();
    }

    public function index()
    {
        return view('facturacionServicio/index');
    }

    private function numeroEnteroALetras(int $numero): string
    {
        $unidades = [
            0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO',
            5 => 'CINCO', 6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE',
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
            15 => 'QUINCE', 16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO',
            19 => 'DIECINUEVE', 20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDOS',
            23 => 'VEINTITRES', 24 => 'VEINTICUATRO', 25 => 'VEINTICINCO',
            26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'
        ];

        $decenas = [
            30 => 'TREINTA', 40 => 'CUARENTA', 50 => 'CINCUENTA',
            60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA', 90 => 'NOVENTA'
        ];

        $centenas = [
            100 => 'CIEN', 200 => 'DOSCIENTOS', 300 => 'TRESCIENTOS', 400 => 'CUATROCIENTOS',
            500 => 'QUINIENTOS', 600 => 'SEISCIENTOS', 700 => 'SETECIENTOS',
            800 => 'OCHOCIENTOS', 900 => 'NOVECIENTOS'
        ];

        if ($numero < 30) {
            return $unidades[$numero];
        }

        if ($numero < 100) {
            $decena = (int)(floor($numero / 10) * 10);
            $resto = $numero % 10;
            return $resto === 0 ? $decenas[$decena] : $decenas[$decena] . ' Y ' . $this->numeroEnteroALetras($resto);
        }

        if ($numero < 1000) {
            if ($numero === 100) {
                return 'CIEN';
            }

            $centena = (int)(floor($numero / 100) * 100);
            $resto = $numero % 100;
            $prefijo = $numero < 200 ? 'CIENTO' : $centenas[$centena];
            return $resto === 0 ? $prefijo : $prefijo . ' ' . $this->numeroEnteroALetras($resto);
        }

        if ($numero < 1000000) {
            $miles = (int)floor($numero / 1000);
            $resto = $numero % 1000;
            $prefijo = $miles === 1 ? 'MIL' : $this->numeroEnteroALetras($miles) . ' MIL';
            return $resto === 0 ? $prefijo : $prefijo . ' ' . $this->numeroEnteroALetras($resto);
        }

        $millones = (int)floor($numero / 1000000);
        $resto = $numero % 1000000;
        $prefijo = $millones === 1 ? 'UN MILLON' : $this->numeroEnteroALetras($millones) . ' MILLONES';
        return $resto === 0 ? $prefijo : $prefijo . ' ' . $this->numeroEnteroALetras($resto);
    }

    private function montoALetras(float $monto): string
    {
        $entero = (int)floor($monto);
        $centavos = (int)round(($monto - $entero) * 100);

        if ($centavos === 100) {
            $entero++;
            $centavos = 0;
        }

        return $this->numeroEnteroALetras($entero) . ' DOLARES CON ' . str_pad((string)$centavos, 2, '0', STR_PAD_LEFT) . '/100';
    }

    public function getContratosFacturacionOtro()
    {
        try {
            $search = trim((string)($this->request->getVar('q') ?? ''));
            return $this->respondSuccess($this->contratosModel->buscarContratosFacturacionOtro($search));
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('No se pudieron cargar los contratos');
        }
    }

    public function getServiciosFacturacionOtro()
    {
        try {
            $search = trim((string)($this->request->getVar('q') ?? ''));
            return $this->respondSuccess($this->serviciosModel->buscarServiciosActivos($search));
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('No se pudieron cargar los servicios');
        }
    }

    public function crearFacturaOtro()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $idContrato = (int)($this->request->getPost('idContrato') ?? 0);
            $itemsJson = $this->request->getPost('items');

            if ($idContrato <= 0) {
                return $this->respondError('Debes seleccionar un cliente o contrato');
            }

            if (empty($itemsJson)) {
                return $this->respondError('Debes agregar al menos un servicio');
            }

            $items = json_decode($itemsJson, true);

            if (!is_array($items) || empty($items)) {
                return $this->respondError('Los servicios enviados no son válidos');
            }

            $periodoActivo = $this->periodosModel->getPeriodoActivo();
            if (!$periodoActivo) {
                return $this->respondError('No hay periodo activo');
            }

            $contrato = $this->db->table('contratos c')
                ->select('c.id_contrato, c.numero_contrato, c.estado, c.id_cliente')
                ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
                ->where('c.id_contrato', $idContrato)
                ->where('c.estado', 'APROBADO')
                ->get()
                ->getRowArray();

            if (!$contrato) {
                return $this->respondError('El contrato seleccionado no está disponible para facturar');
            }

            $serviciosIds = [];
            foreach ($items as $item) {
                $idServicio = (int)($item['id_servicio'] ?? 0);
                if ($idServicio > 0) {
                    $serviciosIds[] = $idServicio;
                }
            }

            $serviciosIds = array_values(array_unique($serviciosIds));

            if (empty($serviciosIds)) {
                return $this->respondError('Debes seleccionar servicios válidos');
            }

            $servicios = $this->serviciosModel
                ->whereIn('id_servicio', $serviciosIds)
                ->findAll();

            $serviciosMap = [];
            foreach ($servicios as $servicio) {
                $serviciosMap[(int)$servicio['id_servicio']] = $servicio;
            }

            $detalle = [];
            $totalFactura = 0;

            foreach ($items as $item) {
                $idServicio = (int)($item['id_servicio'] ?? 0);
                $monto = round((float)($item['monto'] ?? 0), 2);

                if ($idServicio <= 0 || !isset($serviciosMap[$idServicio])) {
                    throw new \Exception('Hay servicios no válidos en la factura');
                }

                if ($monto <= 0) {
                    throw new \Exception('Todos los servicios deben tener un monto mayor a 0');
                }

                $concepto = trim((string)($item['concepto'] ?? ''));
                if ($concepto === '') {
                    $concepto = $serviciosMap[$idServicio]['nombre'];
                }

                $detalle[] = [
                    'id_servicio' => $idServicio,
                    'concepto' => $concepto,
                    'monto' => $monto,
                    'mora' => 0
                ];

                $totalFactura += $monto;
            }

            if (empty($detalle)) {
                return $this->respondError('No hay detalle para facturar');
            }

            $dataCorrelativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);
            if (!$dataCorrelativo) {
                return $this->respondError('No hay correlativos disponibles para facturar');
            }

            $fechaEmision = date('Y-m-d');
            $fechaVencimiento = date('Y-m-03', strtotime('first day of next month'));

            $this->facturaModel->insert([
                'id_rango_factura' => $dataCorrelativo['id_rango_factura'],
                'correlativo' => $dataCorrelativo['correlativo'],
                'tiraje' => $dataCorrelativo['tiraje'],
                'id_contrato' => $idContrato,
                'id_periodo' => $periodoActivo['id_periodo'],
                'id_lectura' => null,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'PENDIENTE',
                'total' => round($totalFactura, 2),
                'id_usuario' => session()->get('id_usuario'),
                'tipo' => 'OTRO'
            ]);

            $idFactura = $this->facturaModel->insertID();

            foreach ($detalle as $d) {
                $this->facturaDetalleModel->insert([
                    'id_factura' => $idFactura,
                    'id_servicio' => $d['id_servicio'],
                    'concepto' => $d['concepto'],
                    'monto' => $d['monto'],
                    'mora' => 0
                ]);
            }

            $db->transCommit();

            return $this->respondSuccess([
                'mensaje' => 'Factura tipo OTRO creada correctamente',
                'id_factura' => $idFactura
            ]);
        } catch (\Throwable $th) {
            $db->transRollback();
            log_message('error', $th->getMessage());
            return $this->respondError($th->getMessage());
        }
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

    function dibujarComprobante(\TCPDF $pdf, int $x, int $y, string $titulo, array $factura, array $detalle)
    {
        $w = 95; // aca se mide lo ancho del cuadro principal
        $h = 165; // aca se mide lo largo del cuadro principal

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
        $hLabel = $totalRight * 0.27;
        $hValue = $totalRight * 0.28;


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
        $pdf->Cell($colW, 5, $factura['lecturaActual'], 1, 0, 'C');
        $pdf->Cell($colW, 5, $factura['lecturaAnterior'], 1, 0, 'C');
        $pdf->Cell($colW, 5, $factura['consumo'], 1, 0, 'C');
        $pdf->Cell($colW, 5, $factura['codigoTarifa'], 1, 0, 'C');
        $pdf->Cell($colW, 5, $factura['fechaLectura'], 1, 1, 'C');


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
        $totalSinMora = 0;
        foreach ($detalle as $item) {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 7);

            $concepto = $item['concepto'] ?? '';
            $monto = number_format($item['monto'] + $item['mora'], 2);

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

            $total += $item['monto'] + $item['mora'];
            $totalSinMora += $item['monto'];
        }

        // 2. Rellenar filas vacías hasta 10
        $filas = count($detalle);

        for ($i = $filas; $i < 10; $i++) {

            $pdf->SetXY($x, $yDetalle);

            $pdf->Cell($colW, 7, '', 'L', 0);
            $pdf->Cell($colW * 3, 7, '', 'L', 0);
            $pdf->Cell($colW, 7, '', 'LR', 1);

            $yDetalle += 7;
        }

        $textoTotalLetras = $this->montoALetras((float)$totalSinMora);

        $pdf->SetXY($x, $yDetalle);
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 5.5);
        $pdf->Cell($colW * 5, 5, 'Total en letras: ' . $textoTotalLetras, 'TB', 1, 'L');

        // TOTALES
        $pdf->SetXY($x, $yDetalle + 5);
        $totalConMora = $total + 2;
        $totalConMoraFormateado = number_format($totalConMora,2);

        // izquierda
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell($colW * 2.5, 6, 'SI UD. NO PAGA A TIEMPO PAGARÁ', 'TB', 0, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colW * 0.5, 6, '$' . $totalConMoraFormateado, 'TB', 0, 'R');

        // derecha
        $pdf->SetTextColor(0, 51, 153);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell($colW, 6, 'TOTAL:', 'TB', 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($colW, 6, '$ ' . number_format($total,2), 'TB', 1, 'R');

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

    private function agregarPaginaFacturaCobro(\TCPDF $pdf, string $imprimir, ?array $periodo, array $factura, array $detalle)
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

    public function facturaCobroServicio(int $id)
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
        $nombrePDF = 'Factura_' . preg_replace('/[^A-Za-z0-9]/', '_', $factura['cliente']) . '.pdf';

        // 🔥 IMPORTANTE: salida directa
        $pdf->Output($nombrePDF, 'I');

        exit; // corta ejecución (clave)
    }

    private function calcularConsumo(?int $lecturaActual, ?int $lecturaAnterior): int
    {
        // sin lectura actual → no facturar
        if ($lecturaActual === null) {
            log_message('error', 'Lectura actual inválida');
            return 0;
        }

        return $lecturaActual - $lecturaAnterior;

        // PRIMERA LECTURA
        // if ($lecturaAnterior === null) {
        //     log_message('info', 'Sin lectura anterior → consumo = 0 (aplicará mínimo en tarifa)');
        //     return 0;
        // }

        // error de lectura preguntar aca?
        // if ($lecturaActual < $lecturaAnterior) {
        //     log_message('error', 'Lectura actual menor a anterior');
        //     return 0;
        // }
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

    private function procesarExcel(string $ruta)
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

    private function calcularMontoServicio(int $idTarifa, float $consumo)
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

        // consumo cero
        if ($consumo <= 0) {

            $primerRango = $rangos[0];

            return round((float)$primerRango->pago_minimo, 2);
        }

        foreach ($rangos as $rango) {

            $desde  = (float)$rango->desde_n_metros;
            $hasta  = (float)$rango->hasta_n_metros;
            $precio = (float)$rango->valor_metro_cubico;
            $minimo = (float)$rango->pago_minimo;

            if ($consumo >= $desde && $consumo <= $hasta) {

                $total = $consumo * $precio;

                // aplicar mínimo si existe
                if ($minimo > 0) {
                    $total = max($total, $minimo);
                }

                return round($total, 2);
            }
        }

        throw new \Exception("No se encontró rango para el consumo");
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

            $servicios = $this->serviciosModel->findAll();
            $mapServicios = [];
            foreach ($servicios as $s) {
                $mapServicios[strtoupper($s['codigo'])] = $s['id_servicio'];
            }
            log_message('info', 'Servicios cargados: ' . print_r($mapServicios, true));


            $contratos = $this->contratosModel->getContratosActivosFacturacionServicio();
            log_message('info', '2- Contratos obtenidos: ' . count($contratos));

            $rangoActivo = $this->rangoFacturasModel
                ->where('estado', 'Activo')
                ->findAll();

            $totalDisponibles = 0;
            foreach ($rangoActivo as $r) {
                $totalDisponibles += ((int)$r['numero_fin'] - (int)$r['numero_actual']);
            }
            $totalContratos = count($contratos);
            if ($totalContratos > $totalDisponibles) {
                return $this->respondError(
                    "El Tiraje de correlativos no alcanza para generar todas las facturas. " .
                        "Disponibles: {$totalDisponibles}, Requeridas: {$totalContratos}. " .
                        "Debe crear un nuevo tiraje antes de continuar."
                );
            }

            $facturasGeneradas = 0;
            foreach ($contratos as $contrato) {

                log_message('info', '--- Procesando contrato: ' . $contrato->id_contrato);

                // =========================
                // 1. VALIDAR FACTURA EXISTENTE
                // =========================
                $existeFactura = $this->facturaModel
                    ->where('id_contrato', $contrato->id_contrato)
                    ->where('id_periodo', $periodoActivo['id_periodo'])
                    ->first();

                if ($existeFactura) {
                    log_message('info', 'Omitido: ya tiene factura en este periodo');
                    continue;
                }

                // =========================
                // 2. FACTURAS NO PAGADAS
                // =========================
                $facturasNoPagadas = $db->table('facturas')
                    ->select('id_factura')
                    ->where('id_contrato', $contrato->id_contrato)
                    ->where('tipo', 'Consumo')
                    ->where('estado', 'NO PAGADA')
                    ->orderBy('id_factura', 'ASC')
                    ->get()
                    ->getResult();

                $cantidadFacturas = count($facturasNoPagadas);

                log_message('info', 'Facturas NO PAGADAS: ' . $cantidadFacturas);

                if ($cantidadFacturas >= 3) {
                    log_message('info', 'Omitido: tiene 3 o más facturas no pagadas');
                    continue;
                }

                // =========================
                // 🧾 DEUDA ANTERIOR (SIN MORA)
                // =========================
                $totalDeudaAnterior = 0;

                if (!empty($facturasNoPagadas)) {

                    // 🔥 tomar solo la última factura
                    $ultimaFactura = end($facturasNoPagadas);

                    $detalles = $db->table('facturas_detalle')
                        ->where('id_factura', $ultimaFactura->id_factura)
                        ->get()
                        ->getResult();

                    foreach ($detalles as $d) {

                        // excluir mora
                        if ((float)$d->mora > 0) {
                            continue;
                        }

                        $totalDeudaAnterior += (float)$d->monto;
                    }
                }

                log_message('info', 'Total deuda anterior: ' . $totalDeudaAnterior);

                // =========================
                // 🔥 MORA (DETERMINÍSTICA)
                // =========================
                $moraPorFactura = 2;
                $totalMora = $cantidadFacturas * $moraPorFactura;

                log_message('info', 'Total mora calculada: ' . $totalMora);

                // =========================
                // 🧾 DETALLE
                // =========================
                $detalle = [];
                $totalFactura = 0;

                // 🔴 DEUDA AGRUPADA
                if ($totalDeudaAnterior > 0) {

                    $detalle[] = [
                        'id_servicio' => $mapServicios['00002'],
                        'concepto' => 'Saldo pendiente de facturas anteriores (' . $cantidadFacturas . ')',
                        'monto' => $totalDeudaAnterior,
                        'mora' => 0
                    ];

                    $totalFactura += $totalDeudaAnterior;
                }

                // 🔴 MORA
                if ($totalMora > 0) {

                    $detalle[] = [
                        'id_servicio' => $mapServicios['00014'],
                        'concepto' => 'Mora acumulada por facturas pendientes',
                        'mora' => $totalMora,
                        'monto' => 0
                    ];

                    $totalFactura += $totalMora;
                }

                // =========================
                // 3. DATOS ALCALDÍA
                // =========================
                $tmp = $db->table('tmp_alcaldia')
                    ->where('ficha', $contrato->ficha_alcaldia)
                    ->get()
                    ->getRow();

                if (!$tmp) {
                    log_message('error', "Sin datos de alcaldía");
                    continue;
                }

                $alumbrado = (float)$tmp->alumbrado;
                $trenAseo  = (float)$tmp->aseo;

                // =========================
                // 4. LECTURAS
                // =========================
                $lecturaActual = $this->lecturasModel
                    ->getLecturaActual($contrato->id_contrato, $periodoActivo['id_periodo']);

                if (!$lecturaActual) {
                    log_message('error', 'Sin lectura');
                    continue;
                }


                $lecturaAnterior = $this->lecturasModel
                    ->getUltimaLecturaAnterior($contrato->id_contrato, $periodoActivo['id_periodo']);

                $lecturaActualValor = isset($lecturaActual['valor'])
                    ? (int)$lecturaActual['valor']
                    : null;

                $lecturaAnteriorValor = isset($lecturaAnterior['valor'])
                    ? (int)$lecturaAnterior['valor']
                    : null;

                $consumo = $this->calcularConsumo(
                    $lecturaActualValor,
                    $lecturaAnteriorValor
                );

                log_message('info', 'valor de consumo calculado ' . $consumo);

                // =========================
                // 5. CARGOS ACTUALES
                // =========================
                $montoServicio = $this->calcularMontoServicio(
                    $contrato->id_tarifa,
                    $consumo
                );
                log_message('info', 'valor del servicio ' . $montoServicio);

                $detalle[] = [
                    'id_servicio' => $mapServicios['00001'],
                    'concepto' => 'SERVICIO DOMICILIAR',
                    'monto' => $montoServicio,
                    'mora' => 0
                ];

                $detalle[] = [
                    'id_servicio' => $mapServicios['00015'],
                    'concepto' => 'TREN DE ASEO',
                    'monto' => $trenAseo,
                    'mora' => 0
                ];

                $detalle[] = [
                    'id_servicio' => $mapServicios['00016'],
                    'concepto' => 'ALUMBRADO PUBLICO',
                    'monto' => $alumbrado,
                    'mora' => 0
                ];

                $totalFactura += ($montoServicio + $trenAseo + $alumbrado);

                log_message('info', 'Detalle final: ' . print_r($detalle, true));
                log_message('info', 'TOTAL FACTURA: ' . $totalFactura);


                if (empty($detalle)) {
                    continue;
                }

                // =========================
                // 6. CORRELATIVO
                // =========================
                $dataCorrelativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

                $fechaEmision = date('Y-m-d');
                $fechaVencimiento = date('Y-m-03', strtotime('first day of next month'));

                // =========================
                // 7. CREAR FACTURA
                // =========================
                $this->facturaModel->insert([
                    'id_rango_factura'   => $dataCorrelativo['id_rango_factura'],
                    'correlativo'        => $dataCorrelativo['correlativo'],
                    'tiraje'             => $dataCorrelativo['tiraje'],
                    'id_contrato'        => $contrato->id_contrato,
                    'id_periodo'         => $periodoActivo['id_periodo'],
                    'id_lectura'         => $lecturaActual['id_lectura'],
                    'fecha_emision'      => $fechaEmision,
                    'fecha_vencimiento'  => $fechaVencimiento,
                    'estado'             => 'PENDIENTE',
                    'total'              => $totalFactura,
                    'id_usuario'         => session()->get('id_usuario'),
                    'tipo'            => 'Consumo',
                    'consumo'        => $consumo
                ]);

                $idFactura = $this->facturaModel->insertID();

                // =========================
                // 8. INSERT DETALLE
                // =========================
                foreach ($detalle as $d) {
                    $this->facturaDetalleModel->insert([
                        'id_factura' => $idFactura,
                        'id_servicio'       => $d['id_servicio'],
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

    private function responderVentanaImpresionConMensaje(string $mensaje, int $statusCode = 400)
    {
        $html = '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Facturas de cobro</title>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>

            <script>
                Swal.fire({
                    icon: "warning",
                    title: "Atención",
                    text: ' . json_encode($mensaje) . ',
                    confirmButtonText: "Cerrar"
                }).then(() => {
                    window.close();
                });
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
            $idDepartamento = $this->request->getGet('departamento');
            $idMunicipio = $this->request->getGet('municipio');
            $idDistrito = $this->request->getGet('distrito');
            $idColonia = $this->request->getGet('colonia');

            if (!$periodo) {
                return $this->responderVentanaImpresionConMensaje('No hay periodo activo para imprimir facturas.', 404);
            }

            $facturas = $this->facturaModel->getFacturasConsumoPorPeriodoYDireccion(
                $periodo['id_periodo'],
                $idDepartamento,
                $idMunicipio,
                $idDistrito,
                $idColonia
            );

            if (empty($facturas)) {
                return $this->responderVentanaImpresionConMensaje('No hay facturas generadas en el periodo activo para los filtros seleccionados.', 404);
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
