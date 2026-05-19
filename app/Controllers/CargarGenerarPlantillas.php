<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\ContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaModel;
use App\Models\PagoFacturaModel;
use App\Models\PeriodoModel;
use App\Models\ServicioModel;
use App\Models\SolicitudModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargarGenerarPlantillas extends BaseController
{
    private FacturaModel $facturasModel;
    private PeriodoModel $periodosModel;
    private PagoFacturaModel $pagosFacturaModel;
    private FacturaDetalleModel $facturaDetalleModel;
    private CobroContratoModel $cobrosContratoModel;
    private SolicitudModel $solicitudesModel;
    private ContratoModel $contratosModel;
    private $servicioModel;

    public function __construct()
    {
        $this->facturasModel = new FacturaModel();
        $this->periodosModel = new PeriodoModel();
        $this->pagosFacturaModel = new PagoFacturaModel();
        $this->facturaDetalleModel = new FacturaDetalleModel();
        $this->cobrosContratoModel = new CobroContratoModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->contratosModel = new ContratoModel();
        $this->servicioModel = new ServicioModel();
    }

    public function index()
    {
        return view('cargar_generar_plantillas/index');
    }

    private function pagosFacturaTieneCampo(string $campo): bool
    {
        return \Config\Database::connect()->fieldExists($campo, 'pagos_factura');
    }

    private function registrarImportacionFactura(
        int $facturaId,
        array $periodo,
        string $tiraje,
        string $correlativo,
        string $referencia,
        string $estadoExcel,
        ?float $montoPagado,
        ?string $fechaPago,
        string $archivoOrigen
    ): void {
        $payload = [
            'id_factura' => $facturaId,
            'tiraje' => $tiraje,
            'correlativo' => $correlativo,
            'referencia' => $referencia,
            'monto_pagado' => $montoPagado,
            'fecha_pago' => $fechaPago,
            'fecha_carga' => date('Y-m-d'),
            'id_usuario' => session()->get('id_usuario'),
            'archivo_origen' => $archivoOrigen
        ];

        if ($this->pagosFacturaTieneCampo('id_periodo')) {
            $payload['id_periodo'] = $periodo['id_periodo'];
        }

        if ($this->pagosFacturaTieneCampo('estado_excel')) {
            $payload['estado_excel'] = $estadoExcel;
        }

        $registroExistente = $this->pagosFacturaModel
            ->where('id_factura', $facturaId)
            ->first();

        if ($registroExistente) {
            $this->pagosFacturaModel->update($registroExistente['id_pago_factura'], $payload);
            return;
        }

        $this->pagosFacturaModel->insert($payload);
    }

    private function aplicarEstiloEncabezado($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function aplicarBordesTabla($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'inside' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    private function autoSizeColumnas($sheet, $inicio, $fin)
    {
        foreach (range($inicio, $fin) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function configurarHoja($sheet, $range)
    {
        $sheet->setAutoFilter($range);
        $sheet->freezePane('A2');
        $sheet->setSelectedCell('A1');
    }

    public function exportarExcel()
    {
        $periodo = $this->periodosModel->getPeriodoActivo();
        if (!$periodo) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('No hay periodo activo');
        }
        log_message('info', 'Periodo activo ID: ' . $periodo['id_periodo']);


        $facturas = $this->facturasModel->getFacturasExcel($periodo['id_periodo']);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // =========================
        // HOJA 1: BASE
        // =========================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('BASE');

        $sheet1->fromArray([
            'Tiraje',
            'Correlativo',
            'fcliente',
            'nombre',
            'total',
            'ESTATUTOS',
            'ACTIVO'
        ], NULL, 'A1');

        $row = 2;
        foreach ($facturas as $f) {
            $tiraje = $f['tiraje'];
            $correlativo = $f['correlativo'];
            $sheet1->fromArray([
                $tiraje,
                $correlativo,
                $f['codigo'],
                $f['cliente'],
                $f['total'],
                'No pagó',
                'ACTIVO'
            ], NULL, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $range1 = "A1:G{$lastRow}";

        // 🔥 USANDO HELPERS
        $this->aplicarEstiloEncabezado($sheet1, 'A1:G1');
        $this->aplicarBordesTabla($sheet1, $range1);
        $this->autoSizeColumnas($sheet1, 'A', 'G');
        $this->configurarHoja($sheet1, $range1);


        // =========================
        // HOJA 2: COBROS
        // =========================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('COBROS');

        $sheet2->fromArray([
            'Tiraje',
            'Correlativo',
            'Fecha de pago',
            'CANTIDAD RECIBO',
            'fcliente',
            'Nombre o Razon Social',
            'Total Pagado',
            'Fecha de emision',
            'Fecha de Vencimiento',
        ], NULL, 'A1');

        $row = 2;
        foreach ($facturas as $d) {
            $tiraje2 = $d['tiraje'];
            $correlativo2 = $d['correlativo'];
            $sheet2->fromArray([
                $tiraje2,
                $correlativo2,
                $d['fecha_de_pago'],
                '',
                $d['codigo'],
                $d['cliente'],
                '',
                $d['fechaEmision'],
                $d['fechaVencimiento'],
            ], NULL, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $range2 = "A1:I{$lastRow}";

        // 🔥 USANDO HELPERS
        $this->aplicarEstiloEncabezado($sheet2, 'A1:I1');
        $this->aplicarBordesTabla($sheet2, $range2);
        $this->autoSizeColumnas($sheet2, 'A', 'I');
        $this->configurarHoja($sheet2, $range2);

        // Alineaciones específicas
        $sheet2->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet2->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet2->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal('right');


        // =========================
        // ABRIR EN HOJA BASE
        // =========================
        $spreadsheet->setActiveSheetIndexByName('BASE');


        // =========================
        // NOMBRE ARCHIVO
        // =========================
        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE'
        ];

        $mes = $meses[(int)date('n')];
        $anio = date('Y');

        $filename = "BASE DE ACAYCCOMAC {$periodo['nombre']} {$anio}.xlsx";


        // =========================
        // DESCARGA
        // =========================
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportarExcelAlcaldia()
    {
        $periodo = $this->periodosModel->getPeriodoActivo();

        if (!$periodo) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('No hay periodo activo');
        }

        $facturas = $this->facturasModel->getFacturasConsumoAlcaldiaPorPeriodo($periodo['id_periodo']);

        if (empty($facturas)) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('No hay facturas de consumo en el periodo activo');
        }

        $pagadas = [];
        $noPagadas = [];
        $resumenPorDia = [];

        foreach ($facturas as $factura) {
            $aseo = (float)($factura['aseo'] ?? 0);
            $alumbrado = (float)($factura['alumbrado'] ?? 0);
            $total = $aseo + $alumbrado;

            $factura['aseo'] = $aseo;
            $factura['alumbrado'] = $alumbrado;
            $factura['total_alcaldia'] = $total;
            $factura['estado_alcaldia'] = strtoupper((string)($factura['estado'] ?? '')) === 'PAGADA' ? 'Pagó' : 'No pagó';
            $factura['activo_alcaldia'] = strtoupper((string)($factura['estado_contrato'] ?? '')) === 'APROBADO' ? 'ACTIVO' : 'INACTIVO';

            if (strtoupper((string)($factura['estado'] ?? '')) === 'PAGADA') {
                $pagadas[] = $factura;

                $fechaPagoKey = $factura['fecha_de_pago'] ?: $factura['fecha_emision'];

                if (!isset($resumenPorDia[$fechaPagoKey])) {
                    $resumenPorDia[$fechaPagoKey] = [
                        'fecha_pago' => $fechaPagoKey,
                        'aseo' => 0,
                        'alumbrado' => 0,
                        'fecha_emision' => $factura['fecha_emision'],
                        'fecha_vencimiento' => $factura['fecha_vencimiento']
                    ];
                }

                $resumenPorDia[$fechaPagoKey]['aseo'] += $aseo;
                $resumenPorDia[$fechaPagoKey]['alumbrado'] += $alumbrado;
            } else {
                $noPagadas[] = $factura;
            }
        }

        ksort($resumenPorDia);

        $spreadsheet = new Spreadsheet();

        // =========================
        // HOJA 1: PAGO A DETALLE
        // =========================
        $sheetDetalle = $spreadsheet->getActiveSheet();
        $sheetDetalle->setTitle('PAGO A DETALLE');
        $sheetDetalle->setCellValue('A2', 'control de Pagos ' . strtoupper($periodo['nombre']));
        $sheetDetalle->mergeCells('A2:I2');
        $sheetDetalle->fromArray([
            'FECHA',
            'FICHA ALCALDIA',
            'NUMERO Cliente',
            'Nombre o Razon Social',
            'ASEO PUBLICO',
            'alumbrado',
            'Total Pagado',
            'Fecha de emision',
            'Fecha de Vencimiento'
        ], null, 'A3');

        $row = 4;
        foreach ($pagadas as $factura) {
            $sheetDetalle->fromArray([
                $factura['fecha_de_pago'] ?: null,
                $factura['ficha_alcaldia'],
                $factura['numero_cliente'],
                $factura['cliente'],
                $factura['aseo'],
                $factura['alumbrado'],
                $factura['total_alcaldia'],
                $factura['fecha_emision'] ?: null,
                $factura['fecha_vencimiento'] ?: null
            ], null, 'A' . $row);
            $row++;
        }

        $lastDetalle = max($row - 1, 3);
        $this->aplicarEstiloEncabezado($sheetDetalle, 'A3:I3');
        $this->aplicarBordesTabla($sheetDetalle, 'A3:I' . $lastDetalle);
        $this->autoSizeColumnas($sheetDetalle, 'A', 'I');
        $sheetDetalle->getStyle('A2:I2')->getFont()->setBold(true)->setSize(14);
        $sheetDetalle->getStyle('A2:I2')->getAlignment()->setHorizontal('center');
        if ($lastDetalle >= 4) {
            $sheetDetalle->getStyle('A4:A' . $lastDetalle)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheetDetalle->getStyle('H4:I' . $lastDetalle)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheetDetalle->getStyle('E4:G' . $lastDetalle)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // =========================
        // HOJA 2: PAGO POR DIA
        // =========================
        $sheetDia = $spreadsheet->createSheet();
        $sheetDia->setTitle('PAGO POR DIA');
        $sheetDia->setCellValue('B2', 'control de Pagos ' . strtoupper($periodo['nombre']));
        $sheetDia->mergeCells('B2:G2');
        $sheetDia->fromArray([
            '',
            'FECHA',
            'ASEO PUBLICO',
            'alumbrado',
            'Total Pagado',
            'Fecha de emision',
            'Fecha de Vencimiento'
        ], null, 'A3');

        $row = 4;
        foreach ($resumenPorDia as $dia) {
            $totalDia = (float)$dia['aseo'] + (float)$dia['alumbrado'];

            $sheetDia->fromArray([
                '',
                $dia['fecha_pago'] ?: null,
                $dia['aseo'],
                $dia['alumbrado'],
                $totalDia,
                $dia['fecha_emision'] ?: null,
                $dia['fecha_vencimiento'] ?: null
            ], null, 'A' . $row);
            $row++;
        }

        $sheetDia->setCellValue('B' . $row, 'TOTALES:');
        $sheetDia->setCellValue('C' . $row, '=SUM(C4:C' . ($row - 1) . ')');
        $sheetDia->setCellValue('D' . $row, '=SUM(D4:D' . ($row - 1) . ')');
        $sheetDia->setCellValue('E' . $row, '=SUM(E4:E' . ($row - 1) . ')');

        $lastDia = max($row, 3);
        $this->aplicarEstiloEncabezado($sheetDia, 'A3:G3');
        $this->aplicarBordesTabla($sheetDia, 'A3:G' . $lastDia);
        $this->autoSizeColumnas($sheetDia, 'A', 'G');
        $sheetDia->getStyle('B2:G2')->getFont()->setBold(true)->setSize(14);
        $sheetDia->getStyle('B2:G2')->getAlignment()->setHorizontal('center');
        if ($lastDia >= 4) {
            $sheetDia->getStyle('B4:B' . $lastDia)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheetDia->getStyle('F4:G' . $lastDia)->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheetDia->getStyle('C4:E' . $lastDia)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // =========================
        // HOJA 3: NO PAGARON
        // =========================
        $sheetNoPagaron = $spreadsheet->createSheet();
        $sheetNoPagaron->setTitle('NO PAGARON');
        $sheetNoPagaron->fromArray([
            '# CLIENTE',
            'No DE FICHA ALCALDIA',
            'NOMBRE DEL USUARIO',
            'Aseo',
            'ALUMBRADO ',
            'TOTAL CARGO ALCALDIA',
            'ESTATUTOS',
            'ACTIVO'
        ], null, 'A1');

        $row = 2;
        foreach ($noPagadas as $factura) {
            $sheetNoPagaron->fromArray([
                $factura['numero_cliente'],
                $factura['ficha_alcaldia'],
                $factura['cliente'],
                $factura['aseo'],
                $factura['alumbrado'],
                $factura['total_alcaldia'],
                'No pagó',
                $factura['activo_alcaldia']
            ], null, 'A' . $row);
            $row++;
        }

        $lastNoPagaron = max($row - 1, 1);
        $this->aplicarEstiloEncabezado($sheetNoPagaron, 'A1:H1');
        $this->aplicarBordesTabla($sheetNoPagaron, 'A1:H' . $lastNoPagaron);
        $this->autoSizeColumnas($sheetNoPagaron, 'A', 'H');
        if ($lastNoPagaron >= 2) {
            $sheetNoPagaron->getStyle('D2:F' . $lastNoPagaron)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $spreadsheet->setActiveSheetIndexByName('PAGO A DETALLE');

        $nombrePeriodo = strtoupper(str_replace(['/', '\\'], '-', trim($periodo['nombre'] ?? 'PERIODO')));
        $filename = "01-REPORTE DE PAGO DE ACEO Y ALUMBRADO-FACTURACION ALCALDIA-{$nombrePeriodo}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importarExcel()
    {
        $file = $this->request->getFile('excel');

        $periodo = $this->periodosModel->getPeriodoActivo();

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo no recibido o inválido'
            ]);
        }

        if (!$periodo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay periodo activo para importar'
            ]);
        }

        $db = \Config\Database::connect();
        $errores = [];
        $procesados = 0;

        try {

            $spreadsheet = IOFactory::load($file->getTempName());

            $sheetBase = $spreadsheet->getSheetByName('BASE');
            $sheetCobros = $spreadsheet->getSheetByName('COBROS');

            if (!$sheetBase) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontró la hoja BASE'
                ]);
            }

            if (!$sheetCobros) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontró la hoja COBROS'
                ]);
            }

            $data = $sheetBase->toArray();
            $cobrosData = $sheetCobros->toArray();

            $cobrosIndex = [];

            /**
             * ==========================================
             * INDEXAR COBROS POR REFERENCIA (IMPORTANTE)
             * ==========================================
             */
            $fechaActual = null;
            $cobrosIndex = [];

            foreach ($cobrosData as $i => $c) {

                if ($i === 0) continue; // encabezado

                // intentar formato nuevo (tiraje / correlativo separados)
                $tirajeCobro = trim($c[0] ?? null);
                $correlativoCobro = trim($c[1] ?? null);

                if ($tirajeCobro && $correlativoCobro) {
                    $referencia = $tirajeCobro . '-' . $correlativoCobro;
                } else {
                    // fallback formato viejo "1-6"
                    $referencia = trim($c[0] ?? null);
                }

                if (!$referencia) continue;

                log_message('info', 'Referencia COBRO detectada: ' . $referencia);

                // 2. FECHA (columna 1)
                $fechaExcel = trim($c[2] ?? '');

                // 🔥 SI VIENE FECHA, LA ACTUALIZAMOS
                if (!empty($fechaExcel)) {
                    $fechaActual = $fechaExcel;
                }

                // 🔥 SI NO VIENE FECHA, USA LA ÚLTIMA
                $cobrosIndex[$referencia] = [
                    'fecha_pago'   => $fechaActual, // 👈 clave
                    'monto_pagado' => $c[6] ?? null
                ];
            }

            log_message('info', 'COBROS INDEX: ' . print_r($cobrosIndex, true));

            $db->transStart();

            foreach ($data as $index => $row) {

                if ($index === 0) continue;

                $tiraje = trim($row[0] ?? null);
                $correlativo = trim($row[1] ?? null);
                $estadoExcel = trim($row[5] ?? null);

                // reconstruir referencia para uso interno
                $referencia = $tiraje . '-' . $correlativo;

                if (!$tiraje || !$correlativo) {
                    $errores[] = "Fila $index sin tiraje o correlativo";
                    continue;
                }

                log_message('info', "Referencia reconstruida: $referencia");

                $factura = $this->facturasModel
                    ->where('tiraje', $tiraje)
                    ->where('correlativo', $correlativo)
                    ->first();

                if (!$factura) {
                    $errores[] = "Factura no encontrada: $referencia";
                    continue;
                }

                $facturaId = $factura['id_factura'];

                log_message('info', 'factura encontrada ID ' . $facturaId);

                /**
                 * ==========================================
                 * SI PAGÓ
                 * ==========================================
                 */
                if (strtolower($estadoExcel) === 'pagó' || strtolower($estadoExcel) === 'pago') {

                    $cobro = $cobrosIndex[$referencia] ?? null;

                    $montoPagado = $cobro['monto_pagado'] ?? $factura['total'];
                    $fechaPago = $cobro['fecha_pago'] ?? null;

                    if (is_numeric($fechaPago)) {
                        $fechaPago = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaPago);
                        $fechaPago = $fechaPago->format('Y-m-d');
                    } elseif ($fechaPago) {
                        $fechaPago = date('Y-m-d', strtotime($fechaPago));
                    } else {
                        $fechaPago = date('Y-m-d');
                    }

                    // evitar duplicado
                    $existePago = $this->pagosFacturaModel
                        ->where('id_factura', $facturaId)
                        ->first();

                    if (!$existePago) {
                        $this->registrarImportacionFactura(
                            $facturaId,
                            $periodo,
                            $tiraje,
                            $correlativo,
                            $referencia,
                            'PAGADA',
                            (float)$montoPagado,
                            $fechaPago,
                            $file->getName()
                        );
                    }

                    // actualizar factura actual
                    $this->facturasModel->update($facturaId, [
                        'estado' => 'PAGADA',
                        'fecha_de_pago' => $fechaPago,
                    ]);

                    // inicializar
                    $contrato = $this->contratosModel
                        ->where('id_contrato', $factura['id_contrato'])
                        ->first();

                    log_message('info', ' data de contrato obtenido ' . print_r($contrato, true));

                    $solicitudesAfectadas = [];
                    $montoCapital = 0;

                    /**
                     * ==========================================
                     * 1. FACTURAS ANTERIORES
                     * ==========================================
                     */
                    $facturasPendientes = $this->facturasModel
                        ->where('id_contrato', $factura['id_contrato'])
                        ->whereIn('estado', ['PENDIENTE'])
                        ->where('id_factura <', $facturaId)
                        ->orderBy('id_factura', 'ASC')
                        ->findAll();

                    foreach ($facturasPendientes as $fp) {

                        $detallesPendientes = $this->facturaDetalleModel
                            ->where('id_factura', $fp['id_factura'])
                            ->findAll();

                        foreach ($detallesPendientes as $dp) {

                            if (
                                (!empty($dp['id_cobro_instalacion']) ||
                                    stripos($dp['concepto'], 'cuota') !== false)
                                &&
                                stripos($dp['concepto'], 'mora') === false
                            ) {

                                // sumar capital SIEMPRE
                                $montoCapital += (float)($dp['monto'] ?? 0);

                                // actualizar SOLO si tiene ID
                                if (!empty($dp['id_cobro_instalacion'])) {
                                    $this->cobrosContratoModel->update(
                                        $dp['id_cobro_instalacion'],
                                        [
                                            'estado' => 'CANCELADO',
                                            'fecha_pago' => $fechaPago
                                        ]
                                    );
                                }

                                if ($contrato && !empty($contrato['id_solicitud'])) {
                                    $solicitudesAfectadas[$contrato['id_solicitud']] = true;
                                }
                            }
                        }

                        $this->facturasModel->update($fp['id_factura'], [
                            'estado' => 'CANCELADA',
                            'saldo_pendiente' => 0,
                            'fecha_de_pago' => $fechaPago
                        ]);
                    }

                    /**
                     * ==========================================
                     * 2. FACTURA ACTUAL
                     * ==========================================
                     */
                    $detalles = $this->facturaDetalleModel
                        ->where('id_factura', $facturaId)
                        ->findAll();

                    foreach ($detalles as $d) {

                        if (
                            (!empty($d['id_cobro_instalacion']) ||
                                stripos($d['concepto'], 'cuota') !== false)
                            &&
                            stripos($d['concepto'], 'mora') === false
                        ) {

                            // sumar capital
                            $montoCapital += (float)($d['monto'] ?? 0);

                            // actualizar SOLO si tiene ID
                            if (!empty($d['id_cobro_instalacion'])) {
                                $this->cobrosContratoModel->update(
                                    $d['id_cobro_instalacion'],
                                    [
                                        'estado' => 'CANCELADO',
                                        'fecha_pago' => $fechaPago
                                    ]
                                );
                            }

                            log_message('info', 'monto acumulado ' . $montoCapital);

                            if ($contrato && !empty($contrato['id_solicitud'])) {
                                $solicitudesAfectadas[$contrato['id_solicitud']] = true;
                            }
                        }
                    }

                    /**
                     * ==========================================
                     * 3. ACTUALIZAR SOLICITUD
                     * ==========================================
                     */
                    log_message('info', 'valorde variable monto capital ' . $montoCapital);
                    foreach (array_keys($solicitudesAfectadas) as $idSolicitud) {

                        $solicitud = $this->solicitudesModel->find($idSolicitud);

                        if (!$solicitud) continue;

                        $nuevoSaldo = max(
                            0,
                            (float)$solicitud['saldo_pendiente'] - $montoCapital
                        );

                        log_message('info', 'Nuevo saldo solicitud ' . $idSolicitud . ': ' . $nuevoSaldo);

                        $this->solicitudesModel->update(
                            $idSolicitud,
                            [
                                'saldo_pendiente' => $nuevoSaldo
                            ]
                        );
                    }
                } else {

                    /**
                     * ==========================================
                     * NO PAGÓ → SOLO NO PAGADA
                     * ==========================================
                     */

                    // 1. Marcar factura como no pagada
                    $this->facturasModel->update($facturaId, [
                        'estado' => 'NO PAGADA',
                        'fecha_de_pago' => null,
                    ]);

                    $this->registrarImportacionFactura(
                        $facturaId,
                        $periodo,
                        $tiraje,
                        $correlativo,
                        $referencia,
                        'NO PAGADA',
                        0,
                        null,
                        $file->getName()
                    );

                    log_message('info', 'Factura marcada como NO PAHADA ID: ' . $facturaId);

                    // 2. Actualizar cobros asociados
                    $detalles = $this->facturaDetalleModel
                        ->where('id_factura', $facturaId)
                        ->findAll();

                    foreach ($detalles as $d) {

                        if (!empty($d['id_cobro_instalacion'])) {

                            $this->cobrosContratoModel->update(
                                $d['id_cobro_instalacion'],
                                [
                                    'estado' => 'NO PAGADA',
                                    'fecha_pago' => null
                                ]
                            );
                        }
                    }

                    log_message('info', 'Factura no pagada SIN aplicar mora aún');
                }

                $procesados++;
            }
            // exit;

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error en la transacción de base de datos'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Archivo procesado correctamente',
                'procesados' => $procesados,
                'errores' => $errores
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar archivo',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancelarImportacionExcelPeriodoActivo()
    {
        $periodo = $this->periodosModel->getPeriodoActivo();

        if (!$periodo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay periodo activo para revertir'
            ]);
        }

        $db = \Config\Database::connect();

        try {
            $pagosBuilder = $db->table('pagos_factura pf')
                ->select('pf.id_pago_factura, pf.id_factura, pf.fecha_pago, pf.monto_pagado, f.id_contrato');

            if ($this->pagosFacturaTieneCampo('estado_excel')) {
                $pagosBuilder->select('pf.estado_excel');
            }

            $pagosBuilder->join('facturas f', 'f.id_factura = pf.id_factura', 'inner');

            if ($this->pagosFacturaTieneCampo('id_periodo')) {
                $pagosBuilder->where('pf.id_periodo', $periodo['id_periodo']);
            } else {
                $pagosBuilder->where('f.id_periodo', $periodo['id_periodo']);
            }

            $pagosPeriodo = $pagosBuilder->get()->getResultArray();

            $facturasPeriodo = [];
            if (!empty($pagosPeriodo)) {
                $idsFacturasPeriodo = array_values(array_unique(array_column($pagosPeriodo, 'id_factura')));

                if (!empty($idsFacturasPeriodo)) {
                    $facturasPeriodo = $this->facturasModel
                        ->whereIn('id_factura', $idsFacturasPeriodo)
                        ->findAll();
                }
            }

            if (empty($facturasPeriodo)) {
                $facturasPeriodo = $this->facturasModel
                    ->where('id_periodo', $periodo['id_periodo'])
                    ->groupStart()
                    ->whereIn('estado', ['PAGADA', 'NO PAGADA'])
                    ->orWhere('fecha_de_pago IS NOT NULL', null, false)
                    ->groupEnd()
                    ->findAll();
            }

            if (empty($facturasPeriodo) && empty($pagosPeriodo)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No hay cambios de importacion para revertir en el periodo activo'
                ]);
            }

            $pagosPorFactura = [];
            foreach ($pagosPeriodo as $pago) {
                $pagosPorFactura[$pago['id_factura']] = $pago;
            }

            $facturasRevertir = [];
            $facturasQueRestauranSolicitud = [];

            foreach ($facturasPeriodo as $factura) {
                $facturasRevertir[$factura['id_factura']] = $factura;

                $estadoFactura = strtoupper((string)($factura['estado'] ?? ''));
                $fechaPagoFactura = $factura['fecha_de_pago'] ?? null;

                if (!$fechaPagoFactura && isset($pagosPorFactura[$factura['id_factura']])) {
                    $fechaPagoFactura = $pagosPorFactura[$factura['id_factura']]['fecha_pago'] ?? null;
                }

                $registroImportacion = $pagosPorFactura[$factura['id_factura']] ?? null;
                $estadoImportado = strtoupper(trim((string)($registroImportacion['estado_excel'] ?? '')));
                $esFacturaPagadaEnImportacion = in_array($estadoImportado, ['PAGADA', 'PAGO', 'PAGÓ'], true)
                    || (
                        empty($estadoImportado)
                        && in_array($estadoFactura, ['PAGADA'], true)
                        && !empty($fechaPagoFactura)
                    );

                if ($esFacturaPagadaEnImportacion && !empty($fechaPagoFactura)) {
                    $facturasQueRestauranSolicitud[$factura['id_factura']] = true;

                    $facturasAnteriores = $this->facturasModel
                        ->where('id_contrato', $factura['id_contrato'])
                        ->where('id_factura <', $factura['id_factura'])
                        ->where('estado', 'CANCELADA')
                        ->where('fecha_de_pago', $fechaPagoFactura)
                        ->findAll();

                    foreach ($facturasAnteriores as $facturaAnterior) {
                        $tienePagoRegistrado = $this->pagosFacturaModel
                            ->where('id_factura', $facturaAnterior['id_factura'])
                            ->first();

                        if (!$tienePagoRegistrado) {
                            $facturasRevertir[$facturaAnterior['id_factura']] = $facturaAnterior;
                            $facturasQueRestauranSolicitud[$facturaAnterior['id_factura']] = true;
                        }
                    }
                }
            }

            $solicitudesAjuste = [];
            $facturasRevertidas = 0;

            $db->transStart();

            foreach ($facturasRevertir as $factura) {
                $detalles = $this->facturaDetalleModel
                    ->where('id_factura', $factura['id_factura'])
                    ->findAll();

                $contrato = $this->contratosModel
                    ->where('id_contrato', $factura['id_contrato'])
                    ->first();

                $idSolicitud = $contrato['id_solicitud'] ?? null;
                $montoCapital = 0;

                foreach ($detalles as $detalle) {
                    $concepto = strtolower((string)($detalle['concepto'] ?? ''));
                    $esCapitalInstalacion = (
                        !empty($detalle['id_cobro_instalacion'])
                        || str_contains($concepto, 'cuota')
                    ) && !str_contains($concepto, 'mora');

                    if (!empty($detalle['id_cobro_instalacion'])) {
                        $this->cobrosContratoModel->update(
                            $detalle['id_cobro_instalacion'],
                            [
                                'estado' => 'PENDIENTE',
                                'fecha_pago' => null
                            ]
                        );
                    }

                    if (
                        $esCapitalInstalacion
                        && isset($facturasQueRestauranSolicitud[$factura['id_factura']])
                    ) {
                        $montoCapital += (float)($detalle['monto'] ?? 0);
                    }
                }

                if ($idSolicitud && $montoCapital > 0) {
                    if (!isset($solicitudesAjuste[$idSolicitud])) {
                        $solicitudesAjuste[$idSolicitud] = 0;
                    }

                    $solicitudesAjuste[$idSolicitud] += $montoCapital;
                }

                $this->facturasModel->update(
                    $factura['id_factura'],
                    [
                        'estado' => 'PENDIENTE',
                        'fecha_de_pago' => null
                    ]
                );

                $facturasRevertidas++;
            }

            foreach ($solicitudesAjuste as $idSolicitud => $montoAjuste) {
                $solicitud = $this->solicitudesModel->find($idSolicitud);

                if (!$solicitud) {
                    continue;
                }

                $this->solicitudesModel->update(
                    $idSolicitud,
                    [
                        'saldo_pendiente' => (float)($solicitud['saldo_pendiente'] ?? 0) + $montoAjuste
                    ]
                );
            }

            if (!empty($pagosPeriodo)) {
                $idsPagos = array_column($pagosPeriodo, 'id_pago_factura');
                $db->table('pagos_factura')
                    ->whereIn('id_pago_factura', $idsPagos)
                    ->delete();
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudieron revertir los cambios de la importacion'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Se revirtieron los cambios de la importacion del periodo activo',
                'facturas_revertidas' => $facturasRevertidas,
                'pagos_eliminados' => count($pagosPeriodo)
            ]);
        } catch (\Throwable $th) {
            log_message('error', 'Error al cancelar importacion Excel: ' . $th->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al revertir la importacion del Excel',
                'error' => $th->getMessage()
            ]);
        }
    }
}
