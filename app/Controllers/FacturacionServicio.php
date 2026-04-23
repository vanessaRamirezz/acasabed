<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaServicioModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;
use App\Models\RangoFacturaModel;

class FacturacionServicio extends BaseController
{
    private $contratosModel;
    private $facturaServicioModel;
    private $periodosModel;
    private $rangoFacturasModel;
    private $lecturasModel;
    private $facturaDetalleModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
        $this->facturaServicioModel = new FacturaServicioModel();
        $this->periodosModel = new PeriodoModel();
        $this->rangoFacturasModel = new RangoFacturaModel();
        $this->lecturasModel = new LecturaModel();
        $this->facturaDetalleModel = new FacturaDetalleModel();
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

            $result = $this->facturaServicioModel->getHistorialFacturas($start, $length, $searchValue);

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

        $pdf->SetX($x);
        $pdf->Cell($colW, 5, $factura['lecturaActual'], 1, 0);
        $pdf->Cell($colW, 5, $factura['lecturaAnterior'], 1, 0);
        $pdf->Cell($colW, 5, $factura['consumo_mes'], 1, 0);
        $pdf->Cell($colW, 5, $factura['codigoTarifa'], 1, 0);
        $pdf->Cell($colW, 5, $factura['fechaLectura'], 1, 1);


        // DETALLE
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
            $pdf->SetXY($x, $yDetalle);

            $pdf->Cell($colW, 7, '', 'L', 0);
            $pdf->Cell($colW * 3, 7, $item['concepto'] ?? '', 'L', 0);
            $pdf->Cell($colW, 7, number_format($item['monto'], 2), 'LR', 1);

            $yDetalle += 7;
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

        $pdf->Cell($colW, 5, $factura['fechaVencimiento'], 0, 0, 'R');


        $currentY = $pdf->GetY();

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

    private function agregarPaginaFacturaCobro($pdf, array $factura, array $detalle)
    {
        $pdf->AddPage();
        $pdf->SetTitle('Factura ' . ($factura['cliente'] ?? 'Cobro'));

        $this->dibujarComprobante($pdf, 10, 10, 'COMPROBANTE DEL CLIENTE', $factura, $detalle);
        $this->dibujarComprobante($pdf, 110, 10, 'COMPROBANTE DEL BANCO', $factura, $detalle);
    }

    public function facturaCobroServicio($id)
    {
        $data = $this->facturaServicioModel->getFacturaResumenPorId($id);

        log_message('info', ' datos recibidos para enviar a ver la factura' . print_r($data, true));
        // exit;
        if (!$data['factura']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No existe la factura');
        }

        $factura = $data['factura'];
        $detalle = $data['detalle'];

        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $this->agregarPaginaFacturaCobro($pdf, $factura, $detalle);


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
            log_message('info', 'Periodo activo ID ' . print_r($periodoActivo, true));

            $contratos = $this->contratosModel->getContratosActivosFacturacionServicio();
            log_message('info', 'Contratos obtenidos: ' . count($contratos));

            $facturasGeneradas = 0;

            foreach ($contratos as $contrato) {

                log_message('info', '--- Procesando contrato: ' . $contrato->id_contrato);
                log_message('info', 'dato del contrato ' . print_r($contrato, true));

                // validar si ya existe factura en ese periodo
                $existeFactura = $this->facturaServicioModel
                    ->existeFacturaPeriodoContrato(
                        $contrato->id_contrato,
                        $periodoActivo['id_periodo']
                    );

                if ($existeFactura) {
                    log_message('info', 'Contrato omitido: ya tiene factura en este periodo');
                    continue;
                }

                //obtener las facturas
                $facturasPendientes = $this->facturaServicioModel
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
                $this->facturaServicioModel->insert($dataFactura);
                $idFactura = $this->facturaServicioModel->insertID();
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


    // private function obtenerCadenaFacturasPendientes(array $facturaInicial)
    // {
    //     $cadena = [];
    //     $actual = $facturaInicial;
    //     $visitadas = [];

    //     while ($actual && !in_array($actual['id_factura'], $visitadas, true)) {
    //         $visitadas[] = $actual['id_factura'];

    //         $cadena[] = [
    //             'id_factura' => $actual['id_factura'],
    //             'correlativo' => $actual['correlativo'],
    //             'periodo' => $actual['periodo'],
    //             'fecha_emision' => $actual['fecha_emision'],
    //             'fecha_vencimiento' => $actual['fecha_vencimiento'],
    //             'consumo_mes' => (float)($actual['consumo_mes'] ?? 0),
    //             'monto_factura' => (float)($actual['monto_factura'] ?? 0),
    //             'saldo_anterior' => (float)($actual['saldo_anterior'] ?? 0),
    //             'mora' => (float)($actual['mora'] ?? 0),
    //             'saldo_pendiente' => (float)($actual['saldo_pendiente'] ?? 0),
    //             'estado' => $actual['estado']
    //         ];

    //         if (empty($actual['id_factura_anterior'])) {
    //             break;
    //         }

    //         $anterior = $this->facturaServicioModel->getFacturaResumenPorId($actual['id_factura_anterior']);

    //         if (
    //             !$anterior ||
    //             !in_array($anterior['estado'], ['SALDO TRASLADADO', 'PENDIENTE', 'VENCIDA'], true)
    //         ) {
    //             break;
    //         }

    //         $actual = $anterior;
    //     }

    //     return $cadena;
    // }

    // private function construirDetalleFacturacion(array $factura)
    // {
    //     $cadena = $this->obtenerCadenaFacturasPendientes($factura);

    //     return [
    //         'resumen' => [
    //             'id_factura' => $factura['id_factura'],
    //             'id_contrato' => $factura['id_contrato'],
    //             'numero_contrato' => $factura['numero_contrato'],
    //             'codigo_solicitud' => $factura['codigo_solicitud'],
    //             'cliente' => $factura['cliente'],
    //             'numero_serie' => $factura['numero_serie'],
    //             'periodo' => $factura['periodo'],
    //             'fecha_emision' => $factura['fecha_emision'],
    //             'fecha_vencimiento' => $factura['fecha_vencimiento'],
    //             'saldo_pendiente' => (float)($factura['saldo_pendiente'] ?? 0),
    //             'estado' => $factura['estado'],
    //             'facturas_pendientes' => count($cadena)
    //         ],
    //         'facturas' => $cadena
    //     ];
    // }

    // private function validarPagoFactura($idFactura, $montoPago)
    // {
    //     $this->actualizarFacturasVencidas();

    //     if (!$idFactura) {
    //         throw new \Exception('Debe seleccionar una factura');
    //     }

    //     $factura = $this->facturaServicioModel->getFacturaResumenPorId($idFactura);

    //     if (!$factura) {
    //         throw new \Exception('No se encontró la factura');
    //     }

    //     if (!in_array($factura['estado'], ['PENDIENTE', 'VENCIDA'], true)) {
    //         throw new \Exception('La factura seleccionada ya no está disponible para pago');
    //     }

    //     $saldoPendiente = (float)$factura['saldo_pendiente'];

    //     if ($saldoPendiente <= 0) {
    //         throw new \Exception('La factura no tiene saldo pendiente');
    //     }

    //     if ($montoPago <= 0) {
    //         throw new \Exception('El monto debe ser mayor a 0');
    //     }

    //     if (round($montoPago, 2) !== round($saldoPendiente, 2)) {
    //         throw new \Exception('El monto debe ser exacto al saldo pendiente de la factura vigente');
    //     }

    //     return [
    //         'factura' => $factura,
    //         'montoPago' => $montoPago,
    //         'estadoPagado' => $factura['estado'] === 'VENCIDA' ? 'PAGADA VENCIDA' : 'PAGADA'
    //     ];
    // }

    // public function getDetalleFacturaCliente()
    // {
    //     try {
    //         $this->actualizarFacturasVencidas();

    //         $idCliente = $this->request->getVar('idCliente');

    //         if (!$idCliente) {
    //             return $this->respondError('Debe seleccionar un cliente');
    //         }

    //         $facturas = $this->facturaServicioModel->getFacturasPendientesCliente($idCliente);

    //         if (empty($facturas)) {
    //             return $this->respondError('No se encontró facturación pendiente para el cliente seleccionado');
    //         }

    //         $resultado = [];

    //         foreach ($facturas as $factura) {
    //             $facturaCompleta = $this->facturaServicioModel->getFacturaResumenPorId($factura['id_factura']);

    //             if (!$facturaCompleta) {
    //                 continue;
    //             }

    //             $resultado[] = $this->construirDetalleFacturacion($facturaCompleta);
    //         }

    //         return $this->respondSuccess($resultado);
    //     } catch (\Throwable $th) {
    //         log_message('error', $th->getMessage());
    //         return $this->respondError('Error al obtener detalle de facturación');
    //     }
    // }

    // public function generarFacturasServicio()
    // {
    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     try {
    //         $this->actualizarFacturasVencidas();

    //         $periodoActivo = $this->periodosModel->getPeriodoActivo();

    //         if (!$periodoActivo) {
    //             throw new \Exception('No hay periodo activo');
    //         }

    //         $periodo = $this->periodosModel->find($periodoActivo['id_periodo']);

    //         if (!$periodo) {
    //             throw new \Exception('No se encontró la información del periodo activo');
    //         }

    //         $contratos = $this->contratosModel->getContratosActivosFacturacionServicio($periodo['id_periodo']);
    //         $facturasGeneradas = 0;

    //         foreach ($contratos as $contrato) {
    //             $existeFactura = $this->facturaServicioModel
    //                 ->existeFacturaPeriodoContrato($contrato['id_contrato'], $periodo['id_periodo']);

    //             if ($existeFactura) {
    //                 continue;
    //             }

    //             if (empty($contrato['id_lectura'])) {
    //                 continue;
    //             }

    //             $lecturaAnterior = $this->lecturasModel
    //                 ->getUltimaLecturaAnterior($contrato['id_contrato'], $periodo['id_periodo']);

    //             $valorLecturaAnterior = (float)($lecturaAnterior['valor'] ?? 0);
    //             $valorLecturaActual = (float)($contrato['lectura_actual'] ?? 0);

    //             $consumo = $this->calcularConsumo($valorLecturaActual, $valorLecturaAnterior);
    //             $montoServicio = $this->calcularMontoServicio($consumo, $contrato);

    //             $alumbrado = 0.00;
    //             $trenDeAseo = 0.00;
    //             $mora = 0.00;

    //             $facturaAnterior = $this->facturaServicioModel
    //                 ->getUltimaFacturaPendienteParaTraslado($contrato['id_contrato'], $periodo['id_periodo']);

    //             $saldoAnterior = (float)($facturaAnterior['saldo_pendiente'] ?? 0);
    //             $idFacturaAnterior = $facturaAnterior['id_factura'] ?? null;

    //             $montoFactura = round($montoServicio + $alumbrado + $trenDeAseo, 2);
    //             $saldoPendiente = round($montoFactura + $saldoAnterior + $mora, 2);

    //             if ($saldoPendiente <= 0) {
    //                 continue;
    //             }

    //             if ($idFacturaAnterior) {
    //                 $this->facturaServicioModel->update($idFacturaAnterior, [
    //                     'estado' => 'SALDO TRASLADADO'
    //                 ]);
    //             }

    //             $correlativo = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

    //             $insert = $this->facturaServicioModel->insert([
    //                 'correlativo' => $correlativo,
    //                 'id_contrato' => $contrato['id_contrato'],
    //                 'id_periodo' => $periodo['id_periodo'],
    //                 'fecha_emision' => date('Y-m-d'),
    //                 'fecha_vencimiento' => $periodo['fecha_hasta'],
    //                 'alumbrado' => $alumbrado,
    //                 'tren_de_aseo' => $trenDeAseo,
    //                 'consumo_mes' => $consumo,
    //                 'monto_factura' => $montoFactura,
    //                 'saldo_anterior' => $saldoAnterior,
    //                 'saldo_pendiente' => $saldoPendiente,
    //                 'estado' => 'PENDIENTE',
    //                 'mora' => $mora,
    //                 'id_lectura' => $contrato['id_lectura'],
    //                 'id_factura_anterior' => $idFacturaAnterior
    //             ]);

    //             if (!$insert) {
    //                 throw new \Exception('No se pudo generar una de las facturas del servicio');
    //             }

    //             $facturasGeneradas++;
    //         }

    //         $db->transCommit();

    //         return $this->respondSuccess("Se generaron {$facturasGeneradas} facturas de servicio correctamente");
    //     } catch (\Throwable $th) {
    //         $db->transRollback();
    //         log_message('error', $th->getMessage());

    //         return $this->respondError($th->getMessage());
    //     }
    // }

    // public function validarPagoFacturaServicio()
    // {
    //     try {
    //         $idFactura = $this->request->getPost('idFactura');
    //         $montoPago = (float)$this->request->getPost('montoPago');

    //         $resultado = $this->validarPagoFactura($idFactura, $montoPago);

    //         return $this->respondSuccess([
    //             'idFactura' => $resultado['factura']['id_factura'],
    //             'montoPago' => $resultado['montoPago'],
    //             'saldoPendiente' => (float)$resultado['factura']['saldo_pendiente'],
    //             'estadoActual' => $resultado['factura']['estado']
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->respondError($th->getMessage());
    //     }
    // }

    // public function registrarPagoFacturaServicio()
    // {
    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     try {
    //         $idFactura = $this->request->getPost('idFactura');
    //         $montoPago = (float)$this->request->getPost('montoPago');

    //         $resultado = $this->validarPagoFactura($idFactura, $montoPago);
    //         $factura = $resultado['factura'];

    //         $insertPago = $this->pagoServicioModel->insert([
    //             'id_factura' => $factura['id_factura'],
    //             'fecha_pago' => date('Y-m-d'),
    //             'monto' => $montoPago,
    //             'id_contrato' => $factura['id_contrato']
    //         ]);

    //         if (!$insertPago) {
    //             throw new \Exception('No se pudo registrar el pago');
    //         }

    //         $this->facturaServicioModel->update($factura['id_factura'], [
    //             'saldo_pendiente' => 0,
    //             'estado' => $resultado['estadoPagado']
    //         ]);

    //         $facturaAnteriorId = $factura['id_factura_anterior'];
    //         $visitadas = [];

    //         while ($facturaAnteriorId && !in_array($facturaAnteriorId, $visitadas, true)) {
    //             $visitadas[] = $facturaAnteriorId;

    //             $facturaAnterior = $this->facturaServicioModel->find($facturaAnteriorId);

    //             if (!$facturaAnterior) {
    //                 break;
    //             }

    //             if (in_array($facturaAnterior['estado'], ['SALDO TRASLADADO', 'PENDIENTE', 'VENCIDA'], true)) {
    //                 $estadoAnteriorPagado = $facturaAnterior['estado'] === 'VENCIDA'
    //                     ? 'PAGADA VENCIDA'
    //                     : 'PAGADA';

    //                 $this->facturaServicioModel->update($facturaAnteriorId, [
    //                     'saldo_pendiente' => 0,
    //                     'estado' => $estadoAnteriorPagado
    //                 ]);
    //             }

    //             $facturaAnteriorId = $facturaAnterior['id_factura_anterior'];
    //         }

    //         $db->transCommit();

    //         return $this->respondOk('Pago de factura registrado correctamente');
    //     } catch (\Throwable $th) {
    //         $db->transRollback();
    //         log_message('error', $th->getMessage());

    //         return $this->respondError($th->getMessage());
    //     }
    // }
}
