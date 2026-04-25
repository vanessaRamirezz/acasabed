<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaModel;
use App\Models\HistorialCobroInstalacionModel;
use App\Models\PagosInstalacionModel;
use App\Models\PeriodoModel;
use App\Models\RangoFacturaModel;
use App\Models\SolicitudModel;
use PHPUnit\Event\Telemetry\Info;

class CobrosInstalacion extends BaseController
{
    private $facturasModel;

    private $cobrosContratoModel;

    private $solicitudesModel;
    private $periodosModel;
    private $rangoFacturasModel;
    private $facturasDetalleModel;


    public function __construct()
    {
        $this->facturasModel = new FacturaModel();

        $this->cobrosContratoModel = new CobroContratoModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->periodosModel = new PeriodoModel();
        $this->rangoFacturasModel = new RangoFacturaModel();

        $this->facturasDetalleModel = new FacturaDetalleModel();
    }

    public function index()
    {
        return view('cobrosInstalacion/index');
    }

    public function getCobrosRealizados()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->facturasModel->getHistorial($start, $length, $searchValue);

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


    public function getDetalleCobroCliente()
    {
        try {
            $idCliente = $this->request->getVar('idCliente'); // corregido

            if (!$idCliente) {
                return $this->respondError('Debe seleccionar un cliente');
            }

            $detalle = $this->cobrosContratoModel->getDetalleCobroPorCliente($idCliente);

            if (empty($detalle)) {
                return $this->respondError('No se encontró información del cliente');
            }

            return $this->respondSuccess($detalle);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al obtener detalle del cobro');
        }
    }

    // private function calcularAplicacionPago($idContrato, $montoPago, $moras)
    // {
    //     if ($montoPago <= 0) {
    //         throw new \Exception('El monto debe ser mayor a 0');
    //     }

    //     // 🔹 Calcular mora total
    //     $totalMora = 0;
    //     foreach ($moras as $m) {
    //         if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
    //             throw new \Exception('Formato de mora inválido');
    //         }

    //         if ((float)$m['mora'] < 0) {
    //             throw new \Exception('La mora no puede ser negativa');
    //         }

    //         $totalMora += (float)$m['mora'];
    //     }

    //     // 🔹 Obtener detalle
    //     $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);
    //     if (!$detalle) {
    //         throw new \Exception('No se encontró información');
    //     }

    //     $cuotasPendientes = array_values(array_filter($detalle['cuotas'], function ($c) {
    //         return ($c['monto_cuota'] - $c['cantidad_abonada']) > 0;
    //     }));

    //     if (empty($cuotasPendientes)) {
    //         throw new \Exception('No hay cuotas pendientes');
    //     }

    //     // 🔹 Validar mínimo (primera cuota + mora)
    //     $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
    //     $montoMinimo = $primeraCuota + $totalMora;

    //     // ❌ caso 1: ni siquiera alcanza la primera cuota
    //     if ($montoPago < $primeraCuota) {
    //         throw new \Exception(
    //             'El monto no alcanza para cubrir la primera cuota.'
    //         );
    //     }

    //     // ❌ caso 2: mora existe pero no está incluida correctamente
    //     if ($totalMora > 0 && $montoPago < ($primeraCuota + $totalMora)) {
    //         throw new \Exception(
    //             'El monto debe incluir la mora.'
    //         );
    //     }
    //     // $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
    //     // $montoMinimo = $primeraCuota + $totalMora;

    //     // if (round($montoPago, 2) < round($montoMinimo, 2)) {
    //     //     throw new \Exception(
    //     //         'El monto debe incluir la mora.'
    //     //     );
    //     // }

    //     // 🔹 Separar monto
    //     $montoParaCuotas = $montoPago - $totalMora;

    //     // 🔹 Aplicar SOLO cuotas completas
    //     $cuotasAplicadas = [];
    //     $totalAplicable = 0;

    //     foreach ($cuotasPendientes as $cuota) {
    //         $saldoCuota = (float)$cuota['saldo_cuota'];

    //         if ($montoParaCuotas >= ($totalAplicable + $saldoCuota)) {
    //             $totalAplicable += $saldoCuota;
    //             $cuotasAplicadas[] = $cuota;
    //         } else {
    //             break;
    //         }
    //     }

    //     $totalEsperado = $totalAplicable + $totalMora;

    //     if (round($montoPago, 2) != round($totalEsperado, 2)) {
    //         throw new \Exception(
    //             'El monto debe ser exacto (cuotas completas + recargo si aplica).'
    //         );
    //     }

    //     return [
    //         'detalle' => $detalle,
    //         'cuotasAplicadas' => $cuotasAplicadas,
    //         'montoCuotas' => $totalAplicable,
    //         'moraTotal' => $totalMora,
    //         'totalEsperado' => $totalEsperado
    //     ];
    // }


    // public function validarCobroInstalacion()
    // {
    //     try {
    //         $idContrato = $this->request->getPost('idContrato');
    //         $montoPago = (float)$this->request->getPost('montoPago');
    //         $moras = json_decode($this->request->getPost('moras'), true) ?? [];

    //         log_message('debug', 'datos recibidos para validae ' . print_r($this->request->getPost(), true));
    //         // exit;
    //         if (!$idContrato) {
    //             throw new \Exception('Debe seleccionar una cuenta');
    //         }

    //         $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

    //         return $this->respondSuccess([
    //             'montoPago' => $montoPago,
    //             'montoCuotas' => $resultado['montoCuotas'],
    //             'moraTotal' => $resultado['moraTotal'],
    //             'totalEsperado' => $resultado['totalEsperado'],
    //             'cuotasAplicadas' => array_column($resultado['cuotasAplicadas'], 'id_cobro_instalacion')
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->respondError($th->getMessage());
    //     }
    // }

    // public function registrarPagoInstalacion()
    // {
    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     try {
    //         log_message('info', '===== INICIO registrarPagoInstalacion =====');

    //         $idContrato = $this->request->getPost('idContrato');
    //         $montoPago  = (float)$this->request->getPost('montoPago');
    //         $moras      = json_decode($this->request->getPost('moras'), true) ?? [];

    //         log_message('info', 'POST recibido: ' . print_r([
    //             'idContrato' => $idContrato,
    //             'montoPago'  => $montoPago,
    //             'moras'      => $moras
    //         ], true));

    //         if (!$idContrato) {
    //             throw new \Exception('Debe seleccionar una cuenta');
    //         }

    //         // 🔒 Revalidación completa
    //         $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

    //         log_message('info', 'Resultado cálculo: ' . print_r($resultado, true));

    //         $cuotasAplicadas = $resultado['cuotasAplicadas'];
    //         $montoAplicado   = $resultado['montoCuotas'];
    //         $detalle         = $resultado['detalle'];

    //         $fechaPago = date('Y-m-d');

    //         log_message('info', 'Cuotas a procesar: ' . print_r($cuotasAplicadas, true));

    //         // 🔥 MAPA DE MORAS
    //         $morasMap = [];

    //         foreach ($moras as $m) {
    //             if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
    //                 log_message('warning', 'Mora inválida ignorada: ' . print_r($m, true));
    //                 continue;
    //             }

    //             $morasMap[(int)$m['id_cobro_instalacion']] = (float)$m['mora'];
    //         }

    //         log_message('info', 'Mapa de moras construido: ' . print_r($morasMap, true));

    //         // 🔥 CORRELATIVO DE PAGO
    //         // $pagosModel = new \App\Models\pagosInstalacionModel();
    //         $correlativo = $this->pagosInstalacionModel->correlativoPago($db);

    //         log_message('info', 'Correlativo generado: ' . $correlativo);

    //         // 🔥 INSERT CABECERA PAGO
    //         $idPago = $this->pagosInstalacionModel->insert([
    //             'correlativo'   => $correlativo,
    //             'id_contrato'   => $idContrato,
    //             'id_solicitud'  => $detalle['resumen']['id_solicitud'],
    //             'fecha_creacion' => date('Y-m-d H:i:s')
    //         ]);

    //         log_message('info', 'ID pago creado: ' . $idPago);

    //         if (!$idPago) {
    //             throw new \Exception('No se pudo crear el pago');
    //         }

    //         // 🔥 PROCESAR CUOTAS
    //         foreach ($cuotasAplicadas as $cuota) {

    //             log_message('info', 'Procesando cuota: ' . print_r($cuota, true));

    //             $id = $cuota['id_cobro_instalacion'];

    //             $moraCuota = $morasMap[$id] ?? 0;

    //             log_message('info', "Cuota ID {$id} con mora: {$moraCuota}");

    //             $dataUpdate = [
    //                 'estado'            => 'CANCELADO',
    //                 'cantidad_abonada'  => $cuota['saldo_cuota'],
    //                 'recargo'           => $moraCuota,
    //                 'fecha_pago'        => $fechaPago
    //             ];

    //             log_message('info', 'Update cuota: ' . print_r($dataUpdate, true));

    //             $updated = $this->cobrosContratoModel->update($id, $dataUpdate);

    //             log_message('info', 'Resultado update: ' . ($updated ? 'OK' : 'FAIL'));

    //             if (!$updated) {
    //                 throw new \Exception("No se actualizó la cuota ID {$id}");
    //             }

    //             // 🔥 HISTORIAL POR CUOTA (correcto dentro del loop)

    //             $historialData = [
    //                 'id_pago'              => $idPago,
    //                 'id_contrato'         => $idContrato,
    //                 'id_solicitud'        => $detalle['resumen']['id_solicitud'],
    //                 'id_cobro_instalacion' => $id,
    //                 'recargo_aplicado'    => $moraCuota,
    //                 'monto_cuota'         => $cuota['saldo_cuota'],
    //                 'total'               => $cuota['saldo_cuota'] + $moraCuota,
    //                 'fecha_creacion'      => date('Y-m-d H:i:s')
    //             ];

    //             log_message('info', 'Insert historial: ' . print_r($historialData, true));

    //             $hist = $this->historialCobroInstalacionModel->insert($historialData);

    //             log_message('info', 'Historial insert ID: ' . $hist);

    //             if (!$hist) {
    //                 throw new \Exception('No se pudo guardar historial');
    //             }
    //         }

    //         // ACTUALIZAR SALDO
    //         $nuevoSaldo = max(0, (float)$detalle['resumen']['saldo_pendiente'] - $montoAplicado);

    //         log_message('info', 'Nuevo saldo a actualizar: ' . $nuevoSaldo);

    //         $this->solicitudesModel->update(
    //             $detalle['resumen']['id_solicitud'],
    //             ['saldo_pendiente' => $nuevoSaldo]
    //         );

    //         // exit;
    //         $db->transCommit();

    //         log_message('info', '===== FIN OK registrarPagoInstalacion =====');

    //         return $this->respondOk('Pago aplicado correctamente');
    //     } catch (\Throwable $th) {

    //         $db->transRollback();

    //         log_message('error', 'ERROR registrarPagoInstalacion: ' . $th->getMessage());

    //         return $this->respondError($th->getMessage());
    //     }
    // }

    function dibujarComprobante($pdf, $x, $y, $titulo, $factura, $detalle)
    {
        $w = 95; // aca se mide lo ancho del cuadro principal
        $h = 140; // aca se mide lo largo del cuadro principal

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
        $pdf->Cell($colW, 5, '', 1, 0);
        $pdf->Cell($colW, 5, '', 1, 0);
        $pdf->Cell($colW, 5, '', 1, 0);
        $pdf->Cell($colW, 5, '', 1, 0);
        $pdf->Cell($colW, 5, '', 1, 1);


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
            $pdf->SetFont('helvetica', '', 7);

            $concepto = $item['concepto'] ?? 'Cobro de instalación';
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

        $pdf->SetTextColor(0, 51, 153);
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

    private function agregarPaginaFacturaCobro($pdf, $imprimir, $periodo, array $factura, array $detalle, $posY)
    {
        if ($pdf->getNumPages() === 0 || $posY == 10) {
            $pdf->AddPage();
        }

        if ($imprimir == 'SI') {
            $pdf->SetTitle('Facturas_cobro_periodo_' . $periodo['nombre']);
        } else {
            $pdf->SetTitle('Factura ' . ($factura['cliente'] ?? 'Cobro'));
        }

        // Arriba o abajo según Y
        $this->dibujarComprobante($pdf, 10, $posY, 'COMPROBANTE DEL CLIENTE', $factura, $detalle);
        $this->dibujarComprobante($pdf, 110, $posY, 'COMPROBANTE DEL BANCO', $factura, $detalle);
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

    public function facturaCobroInstalacion($id)
    {
        $data = $this->facturasModel->obtenerFacturaPorId($id);

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

        $this->agregarPaginaFacturaCobro($pdf, $imprimir, $periodo, $factura, $detalle, 5);

        // 🔑 Nombre dinámico con cliente
        $nombrePDF = 'Factura_' . preg_replace('/[^A-Za-z0-9]/', '_', $factura['cliente']) . '.pdf';

        // 🔥 IMPORTANTE: salida directa
        $pdf->Output($nombrePDF, 'I');

        exit; // corta ejecución (clave)
    }

    public function imprimirFacturasCobroPeriodoActivo()
    {
        try {
            $periodo = $this->periodosModel->getPeriodoActivo();

            if (!$periodo) {
                return $this->responderVentanaImpresionConMensaje('No hay periodo activo para imprimir facturas.', 404);
            }

            $facturas = $this->facturasModel
                ->join('facturas_detalle fd', 'fd.id_factura = facturas.id_factura')
                ->where('facturas.id_periodo', $periodo['id_periodo'])
                ->where('fd.tipo', 'Instalacion')
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


            $posicionesY = [5, 150]; // arriba y abajo
            $index = 0;

            foreach ($facturas as $factura) {
                $dataFactura = $this->facturasModel->obtenerFacturaPorId($factura['id_factura']);

                if (empty($dataFactura['factura'])) {
                    continue;
                }

                $posY = $posicionesY[$index % 2];

                $this->agregarPaginaFacturaCobro(
                    $pdf,
                    $imprimir,
                    $periodo,
                    $dataFactura['factura'],
                    $dataFactura['detalle'] ?? [],
                    $posY
                );

                // Cada 2 facturas → nueva página
                if ($index % 2 == 1) {
                    // opcional: podrías forzar salto aquí si quieres control estricto
                }

                $index++;
            }

            if ($pdf->getNumPages() === 0) {
                return $this->responderVentanaImpresionConMensaje('No se encontraron facturas válidas para imprimir en el periodo activo.', 404);
            }

            if ($this->request->getGet('autoPrint') === '1') {
                $pdf->IncludeJS('print(true);');
            }

            $nombrePDF = 'Facturas_cobro_periodo_' . $periodo['nombre'] . '.pdf';

            $pdf->Output($nombrePDF, 'I');
            exit;
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->responderVentanaImpresionConMensaje('Ocurrió un error al preparar la impresión de facturas.', 500);
        }
    }

    // funciones para manejar la creacion de facturas
    public function generarFacturasCobros()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $periodo = $this->periodosModel->getPeriodoActivo();
            if (!$periodo) {
                throw new \Exception('No hay periodo activo');
            }
            log_message('info', 'Periodo activo ID: ' . $periodo['id_periodo']);

            $contratos = $this->cobrosContratoModel->getContratosParaFacturar();
            log_message('info', 'Contratos obtenidos: ' . count($contratos));

            $facturasGeneradas = 0;
            foreach ($contratos as $contrato) {

                log_message('info', '--- Procesando contrato: ' . $contrato->id_contrato);


                // Validar si ya existe factura en el periodo actual activo
                $existeFactura = $this->facturasModel
                    ->where('id_contrato', $contrato->id_contrato)
                    ->where('id_periodo', $periodo['id_periodo'])
                    ->first();
                if ($existeFactura) {
                    log_message('info', 'Contrato omitido: ya tiene factura en este periodo');
                    continue;
                }
                log_message('info', 'Contrato Valido: no  tiene factura en este periodo');

                // Obtener las cuotas del contrato
                $cuotas = $this->cobrosContratoModel->getCuotasPendientesPorContrato($contrato->id_contrato);
                if (empty($cuotas)) {
                    log_message('info', 'Sin cuotas para contrato');
                    continue;
                }
                log_message('info', 'Cuotas encontradas: ' . count($cuotas));


                $cuotasPendientes = [];
                $cuotaNueva = null;

                // =========================
                // PASO 1: OBTENER HASTA 3 PENDIENTES
                // =========================
                foreach ($cuotas as $cuota) {

                    if (count($cuotasPendientes) >= 3) {
                        break;
                    }

                    $facturaPendiente = $db->table('facturas_detalle fd')
                        ->select('f.id_factura, f.estado')
                        ->join('facturas f', 'f.id_factura = fd.id_factura')
                        ->where('fd.id_cobro_instalacion', $cuota->id_cobro_instalacion)
                        ->orderBy('f.id_factura', 'DESC') // última factura donde apareció
                        ->get(1)
                        ->getFirstRow('array');

                    if ($facturaPendiente && $facturaPendiente['estado'] === 'VENCIDA') {
                        log_message('info', 'Pendiente detectada: cuota ' . $cuota->numero_cuota);

                        $cuotasPendientes[] = $cuota;
                    }
                }
                log_message('info', 'Total de cuotas pendientes no pagadas encontradas: ' . count($cuotasPendientes));


                if (count($cuotasPendientes) >= 3) {
                    log_message('info', 'Contrato omitido: ya tiene 3 cuotas pendientes facturadas');
                    continue;
                }

                // =========================
                // PASO 2: BUSCAR 1 NUEVA (solo si hay menos de 3 pendientes)
                // =========================
                foreach ($cuotas as $cuota) {

                    $existeEnFacturas = $db->table('facturas_detalle')
                        ->select('id_factura_detalle')
                        ->where('id_cobro_instalacion', $cuota->id_cobro_instalacion)
                        ->get(1)
                        ->getFirstRow('array');

                    if (!$existeEnFacturas) {
                        log_message('info', 'Cuota nueva detectada: ' . $cuota->numero_cuota);

                        $cuotaNueva = $cuota;
                        break;
                    }
                }

                // =========================
                // CONSTRUIR DETALLE
                // =========================
                $detalle = [];
                $total = 0;

                // 🔴 Agregar pendientes
                foreach ($cuotasPendientes as $cuota) {

                    $detalle[] = [
                        'tipo' => 'Instalacion',
                        'concepto' => 'Servicio de instalación cuota numero ' . $cuota->numero_cuota . ' de ' . count($cuotas) . ' (Factura Retrasada)',
                        'mora' => 2.00,
                        'monto' => $cuota->monto_cuota,
                        'id_cobro_instalacion' => $cuota->id_cobro_instalacion,
                    ];

                    $total += ($cuota->monto_cuota + 2.00);
                }

                // Agregar nueva
                if ($cuotaNueva) {

                    $detalle[] = [
                        'tipo' => 'Instalacion',
                        'concepto' => ($cuotaNueva->numero_cuota == 0)
                            ? 'Servicio de instalación'
                            : 'Servicio de instalación cuota numero ' . $cuotaNueva->numero_cuota . ' de ' . count($cuotas),
                        'mora' => 0,
                        'monto' => $cuotaNueva->monto_cuota,
                        'id_cobro_instalacion' => $cuotaNueva->id_cobro_instalacion,
                    ];

                    $total += $cuotaNueva->monto_cuota;
                }

                log_message('info', 'Detalle final: ' . print_r($detalle, true));
                log_message('info', 'Total calculado: ' . $total);

                if (empty($detalle)) {
                    log_message('info', 'No hay nada que facturar');
                    continue;
                }

                // =========================
                // GENERAR FACTURA
                // =========================
                $correlativoData = $this->rangoFacturasModel->obtenerCorrelativoFactura($db);

                $fechaEmision = date('Y-m-d');

                // calcular día 3 del siguiente mes
                $fechaVencimiento = date('Y-m-03', strtotime('first day of next month'));

                $idFactura = $this->facturasModel->insert([
                    'id_rango_factura' => $correlativoData['id_rango_factura'],
                    'correlativo'      => $correlativoData['correlativo'],
                    'tiraje'           => $correlativoData['tiraje'],
                    'id_contrato'      => $contrato->id_contrato,
                    'id_periodo'       => $periodo['id_periodo'],
                    'fecha_emision'    => $fechaEmision,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado'           => 'PENDIENTE',
                    'total'            => $total,
                    'saldo_pendiente'  => $total,
                    'id_usuario'       => session('id_usuario')
                ]);

                $batch = [];

                foreach ($detalle as $item) {
                    $batch[] = [
                        'id_factura'           => $idFactura,
                        'tipo'                 => $item['tipo'],
                        'concepto'             => $item['concepto'],
                        'monto'                => $item['monto'],
                        'mora'                 => $item['mora'],
                        'id_cobro_instalacion' => $item['id_cobro_instalacion'],
                    ];
                }

                $this->facturasDetalleModel->insertBatch($batch);

                // actualizar fechas de vencimiento en cobros
                $idsCobros = array_column($detalle, 'id_cobro_instalacion');

                $this->cobrosContratoModel
                    ->whereIn('id_cobro_instalacion', $idsCobros)
                    ->set(['fecha_vencimiento' => $fechaVencimiento])
                    ->update();

                $facturasGeneradas++;

                log_message('info', 'Factura creada correctamente para contrato ' . $contrato->id_contrato);
            }
            // exit;
            $db->transCommit();

            return $this->respondSuccess("Se generaron {$facturasGeneradas} facturas correctamente");
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->respondError($e->getMessage());
        }
    }
}
