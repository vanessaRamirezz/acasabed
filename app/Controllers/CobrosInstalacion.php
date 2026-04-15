<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\HistorialCobroInstalacionModel;
use App\Models\PagosInstalacionModel;
use App\Models\SolicitudModel;
use PHPUnit\Event\Telemetry\Info;

class CobrosInstalacion extends BaseController
{
    private $cobrosContratoModel;
    private $historialCobroInstalacionModel;
    private $solicitudesModel;
    private $pagosInstalacionModel;

    public function __construct()
    {
        $this->cobrosContratoModel = new CobroContratoModel();
        $this->historialCobroInstalacionModel = new HistorialCobroInstalacionModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->pagosInstalacionModel = new PagosInstalacionModel();
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

            $result = $this->historialCobroInstalacionModel->getHistorial($start, $length, $searchValue);

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
            $idCliente = $this->request->getVar('idCliente'); // ✅ corregido

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

    private function calcularAplicacionPago($idContrato, $montoPago, $moras)
    {
        if ($montoPago <= 0) {
            throw new \Exception('El monto debe ser mayor a 0');
        }

        // 🔹 Calcular mora total
        $totalMora = 0;
        foreach ($moras as $m) {
            if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
                throw new \Exception('Formato de mora inválido');
            }

            if ((float)$m['mora'] < 0) {
                throw new \Exception('La mora no puede ser negativa');
            }

            $totalMora += (float)$m['mora'];
        }

        // 🔹 Obtener detalle
        $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);
        if (!$detalle) {
            throw new \Exception('No se encontró información');
        }

        $cuotasPendientes = array_values(array_filter($detalle['cuotas'], function ($c) {
            return ($c['monto_cuota'] - $c['cantidad_abonada']) > 0;
        }));

        if (empty($cuotasPendientes)) {
            throw new \Exception('No hay cuotas pendientes');
        }

        // 🔹 Validar mínimo (primera cuota + mora)
        $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
        $montoMinimo = $primeraCuota + $totalMora;

        // ❌ caso 1: ni siquiera alcanza la primera cuota
        if ($montoPago < $primeraCuota) {
            throw new \Exception(
                'El monto no alcanza para cubrir la primera cuota.'
            );
        }

        // ❌ caso 2: mora existe pero no está incluida correctamente
        if ($totalMora > 0 && $montoPago < ($primeraCuota + $totalMora)) {
            throw new \Exception(
                'El monto debe incluir la mora.'
            );
        }
        // $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
        // $montoMinimo = $primeraCuota + $totalMora;

        // if (round($montoPago, 2) < round($montoMinimo, 2)) {
        //     throw new \Exception(
        //         'El monto debe incluir la mora.'
        //     );
        // }

        // 🔹 Separar monto
        $montoParaCuotas = $montoPago - $totalMora;

        // 🔹 Aplicar SOLO cuotas completas
        $cuotasAplicadas = [];
        $totalAplicable = 0;

        foreach ($cuotasPendientes as $cuota) {
            $saldoCuota = (float)$cuota['saldo_cuota'];

            if ($montoParaCuotas >= ($totalAplicable + $saldoCuota)) {
                $totalAplicable += $saldoCuota;
                $cuotasAplicadas[] = $cuota;
            } else {
                break;
            }
        }

        $totalEsperado = $totalAplicable + $totalMora;

        if (round($montoPago, 2) != round($totalEsperado, 2)) {
            throw new \Exception(
                'El monto debe ser exacto (cuotas completas + recargo si aplica).'
            );
        }

        return [
            'detalle' => $detalle,
            'cuotasAplicadas' => $cuotasAplicadas,
            'montoCuotas' => $totalAplicable,
            'moraTotal' => $totalMora,
            'totalEsperado' => $totalEsperado
        ];
    }


    public function validarCobroInstalacion()
    {
        try {
            $idContrato = $this->request->getPost('idContrato');
            $montoPago = (float)$this->request->getPost('montoPago');
            $moras = json_decode($this->request->getPost('moras'), true) ?? [];

            log_message('debug', 'datos recibidos para validae ' . print_r($this->request->getPost(), true));
            // exit;
            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta');
            }

            $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

            return $this->respondSuccess([
                'montoPago' => $montoPago,
                'montoCuotas' => $resultado['montoCuotas'],
                'moraTotal' => $resultado['moraTotal'],
                'totalEsperado' => $resultado['totalEsperado'],
                'cuotasAplicadas' => array_column($resultado['cuotasAplicadas'], 'id_cobro_instalacion')
            ]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());
        }
    }

    public function registrarPagoInstalacion()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            log_message('info', '===== INICIO registrarPagoInstalacion =====');

            $idContrato = $this->request->getPost('idContrato');
            $montoPago  = (float)$this->request->getPost('montoPago');
            $moras      = json_decode($this->request->getPost('moras'), true) ?? [];

            log_message('info', 'POST recibido: ' . print_r([
                'idContrato' => $idContrato,
                'montoPago'  => $montoPago,
                'moras'      => $moras
            ], true));

            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta');
            }

            // 🔒 Revalidación completa
            $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

            log_message('info', 'Resultado cálculo: ' . print_r($resultado, true));

            $cuotasAplicadas = $resultado['cuotasAplicadas'];
            $montoAplicado   = $resultado['montoCuotas'];
            $detalle         = $resultado['detalle'];

            $fechaPago = date('Y-m-d');

            log_message('info', 'Cuotas a procesar: ' . print_r($cuotasAplicadas, true));

            // 🔥 MAPA DE MORAS
            $morasMap = [];

            foreach ($moras as $m) {
                if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
                    log_message('warning', 'Mora inválida ignorada: ' . print_r($m, true));
                    continue;
                }

                $morasMap[(int)$m['id_cobro_instalacion']] = (float)$m['mora'];
            }

            log_message('info', 'Mapa de moras construido: ' . print_r($morasMap, true));

            // 🔥 CORRELATIVO DE PAGO
            // $pagosModel = new \App\Models\pagosInstalacionModel();
            $correlativo = $this->pagosInstalacionModel->correlativoPago($db);

            log_message('info', 'Correlativo generado: ' . $correlativo);

            // 🔥 INSERT CABECERA PAGO
            $idPago = $this->pagosInstalacionModel->insert([
                'correlativo'   => $correlativo,
                'id_contrato'   => $idContrato,
                'id_solicitud'  => $detalle['resumen']['id_solicitud'],
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);

            log_message('info', 'ID pago creado: ' . $idPago);

            if (!$idPago) {
                throw new \Exception('No se pudo crear el pago');
            }

            // 🔥 PROCESAR CUOTAS
            foreach ($cuotasAplicadas as $cuota) {

                log_message('info', 'Procesando cuota: ' . print_r($cuota, true));

                $id = $cuota['id_cobro_instalacion'];

                $moraCuota = $morasMap[$id] ?? 0;

                log_message('info', "Cuota ID {$id} con mora: {$moraCuota}");

                $dataUpdate = [
                    'estado'            => 'CANCELADO',
                    'cantidad_abonada'  => $cuota['saldo_cuota'],
                    'recargo'           => $moraCuota,
                    'fecha_pago'        => $fechaPago
                ];

                log_message('info', 'Update cuota: ' . print_r($dataUpdate, true));

                $updated = $this->cobrosContratoModel->update($id, $dataUpdate);

                log_message('info', 'Resultado update: ' . ($updated ? 'OK' : 'FAIL'));

                if (!$updated) {
                    throw new \Exception("No se actualizó la cuota ID {$id}");
                }

                // 🔥 HISTORIAL POR CUOTA (correcto dentro del loop)

                $historialData = [
                    'id_pago'              => $idPago,
                    'id_contrato'         => $idContrato,
                    'id_solicitud'        => $detalle['resumen']['id_solicitud'],
                    'id_cobro_instalacion' => $id,
                    'recargo_aplicado'    => $moraCuota,
                    'monto_cuota'         => $cuota['saldo_cuota'],
                    'total'               => $cuota['saldo_cuota'] + $moraCuota,
                    'fecha_creacion'      => date('Y-m-d H:i:s')
                ];

                log_message('info', 'Insert historial: ' . print_r($historialData, true));

                $hist = $this->historialCobroInstalacionModel->insert($historialData);

                log_message('info', 'Historial insert ID: ' . $hist);

                if (!$hist) {
                    throw new \Exception('No se pudo guardar historial');
                }
            }

            // 🔥 ACTUALIZAR SALDO
            $nuevoSaldo = max(0, (float)$detalle['resumen']['saldo_pendiente'] - $montoAplicado);

            log_message('info', 'Nuevo saldo a actualizar: ' . $nuevoSaldo);

            $this->solicitudesModel->update(
                $detalle['resumen']['id_solicitud'],
                ['saldo_pendiente' => $nuevoSaldo]
            );

            // exit;
            $db->transCommit();

            log_message('info', '===== FIN OK registrarPagoInstalacion =====');

            return $this->respondOk('Pago aplicado correctamente');
        } catch (\Throwable $th) {

            $db->transRollback();

            log_message('error', 'ERROR registrarPagoInstalacion: ' . $th->getMessage());

            return $this->respondError($th->getMessage());
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
        $pdf->SetFont('helvetica', '', 7);
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

        $filas = count($detalle);

        for ($i = $filas; $i < 10; $i++) {
            $pdf->SetXY($x, $yDetalle);

            $pdf->Cell($colW, 7, '', 'L', 0);
            $pdf->Cell($colW * 3, 7, '', 'L', 0);
            $pdf->Cell($colW, 7, '', 'LR', 1);

            $yDetalle += 7;
        }

        // TOTALES
        $pdf->SetXY($x, $yDetalle); // 👈 sin +19

        // izquierda
        $pdf->SetFont('helvetica', '', 6);
        $pdf->Cell($colW * 2.5, 6, 'SI UD. NO PAGA A TIEMPO PAGARÁ', 'TB', 0, 'L');
        $pdf->Cell($colW * 0.5, 6, '$0.00', 'TB', 0, 'R');

        // derecha
        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->Cell($colW, 6, 'TOTAL:', 'TB', 0, 'R');
        $pdf->Cell($colW, 6, '$0.00', 'TB', 1, 'R');

        // FOOTER
        $currentY = $pdf->GetY();

        // === IZQUIERDA (texto en 2 líneas controladas) ===
        $pdf->SetXY($x, $currentY);

        $pdf->SetFont('helvetica', '', 7);

        $pdf->MultiCell(
            $colW * 4,
            3,
            "USTED PAGA SIN MORA ENTRE EL 24 AL 3.\nPAGA CON MORA DEL 04 AL 07 DE CADA MES",
            0,
            'L'
        );

        $pdf->SetXY($x + ($colW * 4), $currentY);

        $pdf->Cell($colW, 5, '03/02/2026', 0, 0, 'R');


        $currentY = $pdf->GetY();

        $currentY = $pdf->GetY();

        // === TELÉFONO (solo lado izquierdo enmarcado) ===
        $pdf->SetXY($x, $currentY + 10);

        // ancho izquierdo (igual que totales)
        $leftW = $colW * 3;
        $rightW = $colW * 2;

        // rectángulo SOLO del lado izquierdo
        $pdf->RoundedRect(
            $x,                // posición X
            $currentY + 10,    // posición Y
            $leftW,            // ancho
            6,                 // alto
            1.5,               // radio de la esquina (ajusta aquí)
            '1111',            // esquinas (todas redondeadas)
            ''                 // estilo (solo borde)
        );

        // texto dentro del cuadro
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY($x + 1, $currentY + 11);
        $pdf->Cell($leftW - 2, 4, 'TELÉFONO DE EMERGENCIA: 2332-0282', 0, 0, 'L');

        // === TEXTO DERECHO (sin cuadro) ===
        $pdf->SetXY($x + $leftW, $currentY + 10);

        $pdf->MultiCell(
            $rightW,
            3,
            "PRESENTAR RECLAMO 3 DÍAS\nDESPUÉS DE RECIBIDO",
            0,
            'C'
        );
    }

    public function facturaCobroInstalacion($id)
    {

        // 1. Traer datos del pago
        $data = $this->historialCobroInstalacionModel->obtenerFacturaPorId($id);
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
        $pdf->AddPage();

        // IZQUIERDA (CLIENTE)
        $this->dibujarComprobante($pdf, 10, 10, 'COMPROBANTE DEL CLIENTE', $factura, $detalle);

        // DERECHA (BANCO)
        $this->dibujarComprobante($pdf, 110, 10, 'COMPROBANTE DEL BANCO', $factura, $detalle);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($pdf->Output('factura.pdf', 'S'));
    }
}
