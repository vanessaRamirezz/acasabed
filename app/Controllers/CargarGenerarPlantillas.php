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
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class CargarGenerarPlantillas extends BaseController
{
    protected bool $tieneIdPeriodoPagoFactura = false;

    protected bool $tieneEstadoExcelPagoFactura = false;

    protected array $pagosFacturaIndex = [];

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
        ?string $tiraje,
        ?string $correlativo,
        string $referencia,
        string $estadoExcel,
        ?float $montoPagado,
        ?string $fechaPago,
        string $archivoOrigen
    ): void {

        $payload = [
            'id_factura'     => $facturaId,
            'tiraje'         => $tiraje,
            'correlativo'    => $correlativo,
            'referencia'     => $referencia,
            'monto_pagado'   => $montoPagado,
            'fecha_pago'     => $fechaPago,
            'fecha_carga'    => date('Y-m-d'),
            'id_usuario'     => session()->get('id_usuario'),
            'archivo_origen' => $archivoOrigen
        ];

        if ($this->tieneIdPeriodoPagoFactura) {
            $payload['id_periodo'] = $periodo['id_periodo'];
        }

        if ($this->tieneEstadoExcelPagoFactura) {
            $payload['estado_excel'] = $estadoExcel;
        }

        $idPagoFactura =
            $this->pagosFacturaIndex[$facturaId]
            ?? null;

        if ($idPagoFactura) {

            $this->pagosFacturaModel
                ->update($idPagoFactura, $payload);

            return;
        }

        $nuevoId = $this->pagosFacturaModel
            ->insert($payload, true);

        $this->pagosFacturaIndex[$facturaId] = $nuevoId;
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

    // public function exportarExcel()
    // {
    //     $periodo = $this->periodosModel->getPeriodoActivo();
    //     if (!$periodo) {
    //         return $this->response
    //             ->setStatusCode(400)
    //             ->setBody('No hay periodo activo');
    //     }
    //     log_message('info', 'Periodo activo ID: ' . $periodo['id_periodo']);


    //     $facturas = $this->facturasModel->getFacturasExcel($periodo['id_periodo']);

    //     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    //     // =========================
    //     // HOJA 1: BASE
    //     // =========================
    //     $sheet1 = $spreadsheet->getActiveSheet();
    //     $sheet1->setTitle('BASE');

    //     $sheet1->fromArray([
    //         'Tiraje',
    //         'Correlativo',
    //         'fcliente',
    //         'nombre',
    //         'total',
    //         'ESTATUTOS',
    //         'ACTIVO'
    //     ], NULL, 'A1');

    //     $row = 2;
    //     foreach ($facturas as $f) {
    //         $tiraje = $f['tiraje'];
    //         $correlativo = $f['correlativo'];
    //         $sheet1->fromArray([
    //             $tiraje,
    //             $correlativo,
    //             $f['codigo'],
    //             $f['cliente'],
    //             $f['total'],
    //             'No pagó',
    //             'ACTIVO'
    //         ], NULL, 'A' . $row);
    //         $row++;
    //     }

    //     $lastRow = $row - 1;
    //     $range1 = "A1:G{$lastRow}";

    //     // 🔥 USANDO HELPERS
    //     $this->aplicarEstiloEncabezado($sheet1, 'A1:G1');
    //     $this->aplicarBordesTabla($sheet1, $range1);
    //     $this->autoSizeColumnas($sheet1, 'A', 'G');
    //     $this->configurarHoja($sheet1, $range1);


    //     // =========================
    //     // HOJA 2: COBROS
    //     // =========================
    //     $sheet2 = $spreadsheet->createSheet();
    //     $sheet2->setTitle('COBROS');

    //     $sheet2->fromArray([
    //         'Tiraje',
    //         'Correlativo',
    //         'Fecha de pago',
    //         'CANTIDAD RECIBO',
    //         'fcliente',
    //         'Nombre o Razon Social',
    //         'Total Pagado',
    //         'Fecha de emision',
    //         'Fecha de Vencimiento',
    //     ], NULL, 'A1');

    //     $row = 2;
    //     foreach ($facturas as $d) {
    //         $tiraje2 = $d['tiraje'];
    //         $correlativo2 = $d['correlativo'];
    //         $sheet2->fromArray([
    //             '',
    //             '',
    //             $d['fecha_de_pago'],
    //             '',
    //             '',
    //             '',
    //             '',
    //             $d['fechaEmision'],
    //             $d['fechaVencimiento'],
    //         ], NULL, 'A' . $row);
    //         $row++;
    //     }

    //     $lastRow = $row - 1;
    //     $range2 = "A1:I{$lastRow}";

    //     // 🔥 USANDO HELPERS
    //     $this->aplicarEstiloEncabezado($sheet2, 'A1:I1');
    //     $this->aplicarBordesTabla($sheet2, $range2);
    //     $this->autoSizeColumnas($sheet2, 'A', 'I');
    //     $this->configurarHoja($sheet2, $range2);

    //     // Alineaciones específicas
    //     $sheet2->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal('center');
    //     $sheet2->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal('center');
    //     $sheet2->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal('right');


    //     // =========================
    //     // ABRIR EN HOJA BASE
    //     // =========================
    //     $spreadsheet->setActiveSheetIndexByName('BASE');


    //     // =========================
    //     // NOMBRE ARCHIVO
    //     // =========================
    //     $meses = [
    //         1 => 'ENERO',
    //         2 => 'FEBRERO',
    //         3 => 'MARZO',
    //         4 => 'ABRIL',
    //         5 => 'MAYO',
    //         6 => 'JUNIO',
    //         7 => 'JULIO',
    //         8 => 'AGOSTO',
    //         9 => 'SEPTIEMBRE',
    //         10 => 'OCTUBRE',
    //         11 => 'NOVIEMBRE',
    //         12 => 'DICIEMBRE'
    //     ];

    //     $mes = $meses[(int)date('n')];
    //     $anio = date('Y');

    //     $filename = "BASE DE ACAYCCOMAC {$periodo['nombre']} {$anio}.xlsx";


    //     // =========================
    //     // DESCARGA
    //     // =========================
    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header("Content-Disposition: attachment; filename=\"$filename\"");
    //     header('Cache-Control: max-age=0');

    //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    //     $writer->save('php://output');
    //     exit;
    // }

    public function exportarExcel()
    {
        $periodo = $this->periodosModel->getPeriodoActivo();

        if (!$periodo) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('No hay periodo activo');
        }

        $facturas = $this->facturasModel->getFacturasExcel($periodo['id_periodo']);

        // ============================================
        // CONFIGURACIÓN DE LA PLANTILLA
        // ============================================
        $capacidadPlantilla = 2000; // Última fila con fórmulas
        $filaInicial = 2;

        $maxFacturas = $capacidadPlantilla - ($filaInicial - 1);

        if (count($facturas) > $maxFacturas) {
            throw new \RuntimeException(
                "La cantidad de facturas (" . count($facturas) .
                    ") supera la capacidad de la plantilla ({$maxFacturas})."
            );
        }

        // ============================================
        // CARGAR PLANTILLA
        // ============================================
        $spreadsheet = IOFactory::load(APPPATH . 'Templates/plantilla_cobros.xlsx');

        // ============================================
        // HOJA BASE
        // ============================================
        $sheet1 = $spreadsheet->getSheetByName('BASE');

        $row = $filaInicial;

        foreach ($facturas as $f) {

            $sheet1->fromArray([
                $f['tiraje'],
                $f['correlativo'],
                (int)$f['codigo'],
                $f['cliente'],
                $f['total']
            ], null, "A{$row}");

            $row++;
        }

        $ultimaFilaDatos = $row - 1;

        // Eliminar filas sobrantes de la plantilla
        if ($ultimaFilaDatos < $capacidadPlantilla) {

            $sheet1->removeRow(
                $ultimaFilaDatos + 1,
                $capacidadPlantilla - $ultimaFilaDatos
            );
        }

        // ============================================
        // HOJA COBROS
        // ============================================
        $sheet2 = $spreadsheet->getSheetByName('COBROS');

        $row = $filaInicial;

        foreach ($facturas as $d) {

            $sheet2->fromArray([
                '',
                '',
                $d['fecha_de_pago'],
                '',
                '',
                '',
                '',
                $d['fechaEmision'],
                $d['fechaVencimiento']
            ], null, "A{$row}");

            $row++;
        }

        $ultimaFilaDatos = $row - 1;

        if ($ultimaFilaDatos < $capacidadPlantilla) {

            $sheet2->removeRow(
                $ultimaFilaDatos + 1,
                $capacidadPlantilla - $ultimaFilaDatos
            );
        }

        // ============================================
        // ABRIR EN HOJA BASE
        // ============================================
        $spreadsheet->setActiveSheetIndexByName('BASE');

        // ============================================
        // NOMBRE DEL ARCHIVO
        // ============================================
        $anio = date('Y');

        $filename = "BASE DE ACAYCCOMAC {$periodo['nombre']} {$anio}.xlsx";

        // ============================================
        // DESCARGAR
        // ============================================
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        // NO calcular las fórmulas de Excel
        $writer->setPreCalculateFormulas(false);

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

    private function normalizarFechaPago($fechaPago)
    {
        if (empty($fechaPago)) {
            return date('Y-m-d');
        }

        // Fecha serial de Excel
        if (is_numeric($fechaPago)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaPago)
                ->format('Y-m-d');
        }

        // Formato dd/mm/yyyy
        $fecha = \DateTime::createFromFormat('d/m/Y', trim($fechaPago));

        if ($fecha !== false) {
            return $fecha->format('Y-m-d');
        }

        // Otros formatos válidos
        $timestamp = strtotime($fechaPago);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        log_message(
            'warning',
            'No se pudo convertir fecha: ' . $fechaPago
        );

        return date('Y-m-d');
    }

    private function procesarDetallesFactura(
        array $detalles,
        string $fechaPago,
        array &$solicitudesAfectadas,
        int &$montoCapital,
        $idSolicitud
    ) {
        foreach ($detalles as $d) {

            $esValido =
                (!empty($d['id_cobro_instalacion']) ||
                    stripos($d['concepto'], 'cuota') !== false)
                &&
                stripos($d['concepto'], 'mora') === false;

            if (!$esValido) continue;

            $montoCapital += (float)($d['monto'] ?? 0);

            if (!empty($d['id_cobro_instalacion'])) {
                $this->cobrosContratoModel->update(
                    $d['id_cobro_instalacion'],
                    [
                        'estado' => 'CANCELADO',
                        'fecha_pago' => $fechaPago
                    ]
                );
            }

            if ($idSolicitud) {
                $solicitudesAfectadas[$idSolicitud] = true;
            }
        }
    }

    // public function validarDatosDeExcelBanco()
    // {
    //     $inicio = microtime(true);
    //     $db = \Config\Database::connect();
    //     $errores = [];
    //     try {
    //         $procesados = 0;
    //         $db->transBegin();

    //         $primeraFilaBase = $data[1] ?? [];
    //         $formatoBaseCliente = false;

    //         if (
    //             isset($primeraFilaBase[0]) &&
    //             isset($primeraFilaBase[1]) &&
    //             isset($primeraFilaBase[2]) &&
    //             isset($primeraFilaBase[3])
    //         ) {
    //             $formatoBaseCliente = !is_numeric(trim((string)($primeraFilaBase[1] ?? '')));
    //         }

    //         $this->tieneIdPeriodoPagoFactura =
    //             $this->pagosFacturaTieneCampo('id_periodo');
    //         $this->tieneEstadoExcelPagoFactura =
    //             $this->pagosFacturaTieneCampo('estado_excel');

    //         $pagosExistentes = $this->pagosFacturaModel
    //             ->select('id_pago_factura,id_factura')
    //             ->findAll();
    //         $this->pagosFacturaIndex = [];
    //         foreach ($pagosExistentes as $p) {

    //             $this->pagosFacturaIndex[$p['id_factura']]
    //                 = $p['id_pago_factura'];
    //         }

    //         $detallesFacturas = $this->facturaDetalleModel
    //             ->select('id_factura,id_cobro_instalacion,monto,concepto')
    //             ->findAll();
    //         $detallesPorFactura = [];
    //         foreach ($detallesFacturas as $detalle) {

    //             $detallesPorFactura[$detalle['id_factura']][] = $detalle;
    //         }

    //         foreach ($data as $index => $row) {

    //             if ($index === 0) continue;

    //             $factura = null;


    //             /**
    //              * ==========================================
    //              * FORMATO 1
    //              * TIRAJE / CORRELATIVO
    //              * ==========================================
    //              */
    //             if (!$formatoBaseCliente) {
    //                 $tiraje = trim((string)($row[0] ?? ''));
    //                 $correlativo = trim((string)($row[1] ?? ''));
    //                 $estadoExcel = trim((string)($row[5] ?? ''));

    //                 // reconstruir referencia para uso interno
    //                 $referencia = $tiraje . '-' . $correlativo;

    //                 if (!$tiraje || !$correlativo) {
    //                     $errores[] = "Fila $index sin tiraje o correlativo";
    //                     continue;
    //                 }

    //                 // log_message('info', "Referencia reconstruida: $referencia");
    //                 $factura = $facturasPorReferencia[$referencia] ?? null;
    //             } else {
    //                 /**
    //                  * ==========================================
    //                  * FORMATO 2
    //                  * CODIGO CLIENTE
    //                  * ==========================================
    //                  */

    //                 $codigoCliente = trim((string)($row[0] ?? ''));
    //                 $totalFactura = (float)($row[2] ?? 0);
    //                 $estadoExcel = trim((string)($row[3] ?? ''));

    //                 if (!$codigoCliente) {
    //                     $errores[] = "Fila $index sin código cliente";
    //                     break;
    //                 }

    //                 $facturasCliente = $facturasPorCodigo[$codigoCliente] ?? [];

    //                 foreach ($facturasCliente as $f) {

    //                     if (
    //                         abs(
    //                             (float)$f['total'] - $totalFactura
    //                         ) < 0.01
    //                     ) {
    //                         $factura = $f;
    //                         break;
    //                     }
    //                 }

    //                 if ($factura) {

    //                     $tiraje = (string)($factura['tiraje'] ?? '');
    //                     $correlativo = (string)($factura['correlativo'] ?? '');

    //                     $referencia = trim($tiraje . '-' . $correlativo, '-');
    //                 }
    //             }

    //             if (!$factura) {

    //                 $errores[] = "Error en fila $index";

    //                 break;
    //             }


    //             $facturaId = $factura['id_factura'];
    //             /**
    //              * ==========================================
    //              * SI PAGÓ
    //              * ==========================================
    //              */
    //             if (strtolower($estadoExcel) === 'pagó' || strtolower($estadoExcel) === 'pago') {


    //                 $cobro = $cobrosIndex[$referencia] ?? null;


    //                 $montoPagado = $cobro['monto_pagado'] ?? $factura['total'];
    //                 // $fechaPago = $cobro['fecha_pago'] ?? null;


    //                 $fechaPago = $this->normalizarFechaPago($cobro['fecha_pago'] ?? null);


    //                 $this->registrarImportacionFactura(
    //                     $facturaId,
    //                     $periodo,
    //                     $tiraje,
    //                     $correlativo,
    //                     $referencia,
    //                     'PAGADA',
    //                     (float)$montoPagado,
    //                     $fechaPago,
    //                     $file->getName()
    //                 );


    //                 // actualizar factura actual
    //                 $this->facturasModel->update($facturaId, [
    //                     'estado' => 'PAGADA',
    //                     'fecha_de_pago' => $fechaPago,
    //                 ]);

    //                 // inicializar
    //                 $contrato = $this->contratosModel
    //                     ->where('id_contrato', $factura['id_contrato'])
    //                     ->first();

    //                 // log_message('info', ' data de contrato obtenido ' . print_r($contrato, true));

    //                 $solicitudesAfectadas = [];
    //                 $montoCapital = 0;

    //                 /**
    //                  * ==========================================
    //                  * 1. FACTURAS ANTERIORES
    //                  * ==========================================
    //                  */
    //                 $facturasPendientes = $this->facturasModel
    //                     ->where('id_contrato', $factura['id_contrato'])
    //                     ->whereIn('estado', ['PENDIENTE'])
    //                     ->where('id_factura <', $facturaId)
    //                     ->orderBy('id_factura', 'ASC')
    //                     ->findAll();

    //                 foreach ($facturasPendientes as $fp) {

    //                     $this->facturasModel->update($fp['id_factura'], [
    //                         'estado' => 'CANCELADA',
    //                         'saldo_pendiente' => 0,
    //                         'fecha_de_pago' => $fechaPago
    //                     ]);

    //                     $detallesPendientes = $detallesPorFactura[$fp['id_factura']] ?? [];

    //                     $this->procesarDetallesFactura(
    //                         $detallesPendientes,
    //                         $fechaPago,
    //                         $solicitudesAfectadas,
    //                         $montoCapital,
    //                         $contrato['id_solicitud'] ?? null
    //                     );
    //                 }

    //                 /**
    //                  * ==========================================
    //                  * 2. FACTURA ACTUAL
    //                  * ==========================================
    //                  */
    //                 $detalles = $detallesPorFactura[$facturaId] ?? [];

    //                 $this->procesarDetallesFactura(
    //                     $detalles,
    //                     $fechaPago,
    //                     $solicitudesAfectadas,
    //                     $montoCapital,
    //                     $contrato['id_solicitud'] ?? null
    //                 );

    //                 /**
    //                  * ==========================================
    //                  * 3. ACTUALIZAR SOLICITUD
    //                  * ==========================================
    //                  */
    //                 log_message('info', 'valorde variable monto capital ' . $montoCapital);
    //                 foreach (array_keys($solicitudesAfectadas) as $idSolicitud) {

    //                     $solicitud = $this->solicitudesModel->find($idSolicitud);

    //                     if (!$solicitud) continue;

    //                     $nuevoSaldo = max(
    //                         0,
    //                         (float)$solicitud['saldo_pendiente'] - $montoCapital
    //                     );

    //                     log_message('info', 'Nuevo saldo solicitud ' . $idSolicitud . ': ' . $nuevoSaldo);

    //                     $this->solicitudesModel->update(
    //                         $idSolicitud,
    //                         [
    //                             'saldo_pendiente' => $nuevoSaldo
    //                         ]
    //                     );
    //                 }
    //             } else {

    //                 /**
    //                  * ==========================================
    //                  * NO PAGÓ → SOLO NO PAGADA
    //                  * ==========================================
    //                  */

    //                 // 1. Marcar factura como no pagada
    //                 $this->facturasModel->update($facturaId, [
    //                     'estado' => 'NO PAGADA',
    //                     'fecha_de_pago' => null,
    //                 ]);

    //                 if (!isset($this->pagosFacturaIndex[$facturaId])) {

    //                     $this->registrarImportacionFactura(
    //                         $facturaId,
    //                         $periodo,
    //                         $tiraje,
    //                         $correlativo,
    //                         $referencia,
    //                         'NO PAGADA',
    //                         0,
    //                         null,
    //                         $file->getName()
    //                     );
    //                 }
    //                 // log_message('info', 'Factura marcada como NO PAHADA ID: ' . $facturaId);

    //                 // 2. Actualizar cobros asociados
    //                 $detalles = $detallesPorFactura[$facturaId] ?? [];

    //                 foreach ($detalles as $d) {

    //                     if (!empty($d['id_cobro_instalacion'])) {

    //                         $this->cobrosContratoModel->update(
    //                             $d['id_cobro_instalacion'],
    //                             [
    //                                 'estado' => 'NO PAGADA',
    //                                 'fecha_pago' => null
    //                             ]
    //                         );
    //                     }
    //                 }
    //             }

    //             $procesados++;
    //         }

    //         log_message(
    //             'info',
    //             'Tiempo total: ' .
    //                 round(microtime(true) - $inicio, 2)
    //                 . ' segundos'
    //         );
    //         if ($db->transStatus() === false) {
    //             $db->transRollback();

    //             return $this->response->setJSON([
    //                 'success' => false,
    //                 'message' => 'Error en la transacción de base de datos'
    //             ]);
    //         }

    //         $db->transCommit();

    //         return $this->response->setJSON([
    //             'success' => true,
    //             'message' => 'Archivo procesado correctamente',
    //             'procesados' => $procesados,
    //             'errores' => $errores
    //         ]);
    //     } catch (\Throwable $e) {

    //         if ($db->transStatus()) {
    //             $db->transRollback();
    //         }

    //         log_message('error', 'ERROR IMPORTAR EXCEL');
    //         log_message('error', 'Mensaje: ' . $e->getMessage());
    //         log_message('error', 'Archivo: ' . $e->getFile());
    //         log_message('error', 'Linea: ' . $e->getLine());
    //         log_message('error', 'Trace: ' . $e->getTraceAsString());

    //         return $this->response->setJSON([
    //             'success' => false,
    //             'message' => 'Error al procesar archivo',
    //             'error' => $e->getMessage(),
    //             'linea' => $e->getLine(),
    //             'archivo' => $e->getFile()
    //         ]);
    //     }
    // }

    public function cancelarImportacionExcelPeriodoActivo()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $periodo = $this->periodosModel->getPeriodoActivo();

        if (!$periodo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay periodo activo para revertir'
            ]);
        }

        $db = \Config\Database::connect();

        try {
            $fieldsPagosFactura = $db->getFieldNames('pagos_factura');

            $tieneEstadoExcel = in_array('estado_excel', $fieldsPagosFactura);
            $tieneIdPeriodo   = in_array('id_periodo', $fieldsPagosFactura);

            $pagosBuilder = $db->table('pagos_factura pf')
                ->select('pf.id_pago_factura, pf.id_factura, pf.fecha_pago, pf.monto_pagado, f.id_contrato');

            if ($tieneEstadoExcel) {
                $pagosBuilder->select('pf.estado_excel');
            }

            $pagosBuilder->join(
                'facturas f',
                'f.id_factura = pf.id_factura',
                'inner'
            );

            if ($tieneIdPeriodo) {
                $pagosBuilder->where('pf.id_periodo', $periodo['id_periodo']);
            } else {
                $pagosBuilder->where('f.id_periodo', $periodo['id_periodo']);
            }

            $pagosPeriodo = $pagosBuilder->get()->getResultArray();

            $facturasPeriodo = [];

            $idsFacturasPeriodo = array_unique(
                array_column($pagosPeriodo, 'id_factura')
            );

            $facturasQuery = $this->facturasModel;
            if (!empty($idsFacturasPeriodo)) {
                $facturasQuery = $facturasQuery->whereIn('id_factura', $idsFacturasPeriodo);
            } else {
                $facturasQuery = $facturasQuery
                    ->where('id_periodo', $periodo['id_periodo'])
                    ->groupStart()
                    ->whereIn('estado', ['PAGADA', 'NO PAGADA'])
                    ->orWhere('fecha_de_pago IS NOT NULL', null, false)
                    ->groupEnd();
            }

            $facturasPeriodo = $facturasQuery->findAll();

            if (empty($facturasPeriodo) && empty($pagosPeriodo)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No hay cambios de importación para revertir en el periodo activo'
                ]);
            }

            $pagosPorFactura = [];
            foreach ($pagosPeriodo as $pago) {
                $pagosPorFactura[$pago['id_factura']] = $pago;
            }

            $facturasRevertir = [];
            $facturasQueRestauranSolicitud = [];

            //indexar pagos
            $pagosFacturaIndex = [];
            foreach ($pagosPeriodo as $pago) {
                $pagosFacturaIndex[$pago['id_factura']] = true;
            }

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
                        $tienePagoRegistrado =
                            isset($pagosFacturaIndex[$facturaAnterior['id_factura']]);

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

            // indexar contratos
            $contratos = $this->contratosModel
                ->select('id_contrato,id_solicitud')
                ->findAll();
            $contratosIndex = [];
            foreach ($contratos as $contrato) {
                $contratosIndex[$contrato['id_contrato']]
                    = $contrato;
            }

            //indexar detalle
            $detallesFacturas = $this->facturaDetalleModel
                ->findAll();
            $detallesPorFactura = [];
            foreach ($detallesFacturas as $detalle) {
                $detallesPorFactura[$detalle['id_factura']][] = $detalle;
            }

            //indexar soliciutdes
            $solicitudes = $this->solicitudesModel
                ->findAll();
            $solicitudesIndex = [];
            foreach ($solicitudes as $s) {
                $solicitudesIndex[$s['id_solicitud']] = $s;
            }

            $cobrosActualizar = [];

            foreach ($facturasRevertir as $factura) {
                $detalles = $detallesPorFactura[$factura['id_factura']] ?? [];

                $contrato = $contratosIndex[$factura['id_contrato']] ?? null;

                $idSolicitud = $contrato['id_solicitud'] ?? null;
                $montoCapital = 0;

                foreach ($detalles as $detalle) {
                    $concepto = strtolower((string)($detalle['concepto'] ?? ''));
                    $esCapitalInstalacion = (
                        !empty($detalle['id_cobro_instalacion'])
                        || str_contains($concepto, 'cuota')
                    ) && !str_contains($concepto, 'mora');

                    if (!empty($detalle['id_cobro_instalacion'])) {

                        $cobrosActualizar[] = [
                            'id_cobro_instalacion' => $detalle['id_cobro_instalacion'],
                            'estado' => 'PENDIENTE',
                            'fecha_pago' => null
                        ];
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

            if (!empty($cobrosActualizar)) {

                $this->cobrosContratoModel->updateBatch(
                    $cobrosActualizar,
                    'id_cobro_instalacion'
                );
            }

            foreach ($solicitudesAjuste as $idSolicitud => $montoAjuste) {
                $solicitud = $solicitudesIndex[$idSolicitud] ?? null;

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

    // aca se paso el proceso de importacion del documento que recibo de la alcaldia
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
                "Ya se importo un documento con: (" . count($dataExcel) . " registros)"
            );
        } catch (\Throwable $e) {

            log_message('error', 'Error en carga Excel: ' . $e->getMessage());

            return $this->respondError(
                'Error al procesar el archivo: ' . $e->getMessage()
            );
        }
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

            $reader = new XlsxReader();
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

    public function cancelarExcelAlcaldia()
    {
        try {

            $db = \Config\Database::connect();

            $db->query("TRUNCATE TABLE acasabed.tmp_alcaldia");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Los datos temporales fueron eliminados correctamente.'
            ]);
        } catch (\Exception $e) {

            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar los datos temporales.'
            ]);
        }
    }

    public function validarExcelCargado()
    {
        try {

            $db = \Config\Database::connect();

            $cantidad = $db->table('tmp_alcaldia')->countAllResults();

            return $this->response->setJSON([
                'success' => true,
                'hayDatos' => $cantidad > 0,
                'cantidad' => $cantidad
            ]);
        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'success' => false,
                'hayDatos' => false
            ]);
        }
    }

    // nuevo proceso de importacion de excel
    private function cargarDatosExcel($spreadsheet): array
    {
        $sheetBase = $spreadsheet->getSheetByName('BASE');
        $sheetCobros = $spreadsheet->getSheetByName('COBROS');

        if (!$sheetBase) {
            throw new \Exception('No existe la hoja BASE');
        }

        if (!$sheetCobros) {
            throw new \Exception('No existe la hoja COBROS');
        }

        $base = $sheetBase->rangeToArray(
            'A1:' . $sheetBase->getHighestDataColumn() . $sheetBase->getHighestDataRow(),
            null,
            true,
            false,
            false
        );

        $cobros = $sheetCobros->rangeToArray(
            'A1:' . $sheetCobros->getHighestDataColumn() . $sheetCobros->getHighestDataRow(),
            null,
            true,
            false,
            false
        );

        return [
            'base' => $base,
            'cobros' => $cobros
        ];
    }

    private function indexarFacturas($facturasPeriodo): array
    {
        $facturasPorCodigo = [];
        $facturasPorReferencia = [];

        foreach ($facturasPeriodo as $f) {

            $codigoNormalizado = ltrim((string)$f['codigo'], '0');

            if ($codigoNormalizado === '') {
                $codigoNormalizado = '0';
            }

            $facturasPorCodigo[$codigoNormalizado][] = $f;

            $tiraje = trim((string)$f['tiraje']);
            $correlativo = trim((string)$f['correlativo']);

            // Referencia completa
            if ($tiraje !== '' && $correlativo !== '') {
                $facturasPorReferencia[$tiraje . '-' . $correlativo] = $f;
            }

            // Solo correlativo
            if ($correlativo !== '') {
                $facturasPorReferencia[$correlativo] = $f;
            }
        }

        return [
            'facturasPorCodigo' => $facturasPorCodigo,
            'facturasPorReferencia' => $facturasPorReferencia
        ];
    }

    private function esFormatoCobroCliente(array $cobrosData): bool
    {
        /**
         * ==========================================
         * DETECTAR FORMATO DE COBROS
         * ==========================================
         *
         * Formato 1:
         * 0 = tiraje
         * 1 = correlativo
         * 2 = fecha
         * ...
         * 6 = monto
         *
         * Formato 2:
         * 0 = fecha
         * 2 = codigo_cliente
         * 3 = nombre_cliente
         * 4 = monto
         */
        $primeraFilaCobro = $cobrosData[1] ?? [];

        return isset(
            $primeraFilaCobro[2],
            $primeraFilaCobro[3],
            $primeraFilaCobro[4]
        );
    }

    private function procesarCobroTradicional($c, &$fechaActual, &$cobrosIndex): void
    {
        $tirajeCobro = trim((string)($c[0] ?? ''));
        $correlativoCobro = trim((string)($c[1] ?? ''));

        if ($tirajeCobro && $correlativoCobro) {
            $referencia = $tirajeCobro . '-' . $correlativoCobro;
        } else {
            $referencia = trim((string)($c[0] ?? ''));
        }

        if (!$referencia) {
            return;
        }

        log_message('info', 'Referencia COBRO detectada: ' . $referencia);

        $fechaExcel = trim($c[2] ?? '');

        if (!empty($fechaExcel)) {
            $fechaActual = $fechaExcel;
        }

        $cobrosIndex[$referencia] = [
            'fecha_pago'   => $fechaActual,
            'monto_pagado' => $c[6] ?? null
        ];
    }

    private function procesarCobroCliente(
        $c,
        $facturasPorCodigo,
        &$fechaActual,
        &$cobrosIndex
    ): void {

        $codigoCliente = trim((string)($c[2] ?? ''));
        $codigoCliente = ltrim($codigoCliente, '0');

        $montoPagado = (float)($c[4] ?? 0);

        if (!$codigoCliente) {
            return;
        }

        $facturasCliente = $facturasPorCodigo[$codigoCliente] ?? [];

        if (empty($facturasCliente)) {

            log_message(
                'warning',
                'No se encontró factura para cliente: ' . $codigoCliente
            );

            throw new \Exception(
                "No se encontró factura para cliente: {$codigoCliente}."
            );
        }

        $facturaEncontrada = null;

        foreach ($facturasCliente as $factura) {

            if (abs((float)$factura['total'] - $montoPagado) < 0.01) {

                $facturaEncontrada = $factura;
                break;
            }
        }

        if (!$facturaEncontrada) {

            log_message(
                'warning',
                'No se encontró coincidencia en el total pagado para cliente '
                    . $codigoCliente
                    . ' monto ' . $montoPagado
                    . ' y en el sistema ' . (float)$factura['total']
            );

            throw new \Exception(
                "No se encontró coincidencia en el total pagado para el cliente
                {$codigoCliente}
                monto {$montoPagado}
                y en el sistema {$factura['total']}."
            );
        }

        $fechaExcel = trim((string)($c[0] ?? ''));

        $tiraje = trim((string)($facturaEncontrada['tiraje'] ?? ''));
        $correlativo = trim((string)($facturaEncontrada['correlativo'] ?? ''));

        $referencia = $tiraje !== ''
            ? $tiraje . '-' . $correlativo
            : $correlativo;

        if (!empty($fechaExcel)) {
            $fechaActual = $fechaExcel;
        }


        $cobrosIndex[$referencia] = [
            'fecha_pago'   => $fechaActual,
            'monto_pagado' => $montoPagado
        ];
    }

    private function prepararDatosCobros($cobrosData, $facturasPeriodo)
    {
        $fechaActual = null;
        $cobrosIndex = [];
        $errores = [];

        $indices = $this->indexarFacturas($facturasPeriodo);

        $facturasPorCodigo = $indices['facturasPorCodigo'];
        $facturasPorReferencia = $indices['facturasPorReferencia'];

        $formatoCobroCliente = $this->esFormatoCobroCliente($cobrosData);

        foreach ($cobrosData as $i => $c) {

            if ($i === 0) {
                continue;
            }

            if ($formatoCobroCliente) {

                $this->procesarCobroCliente(
                    $c,
                    $facturasPorCodigo,
                    $fechaActual,
                    $cobrosIndex
                );
            } else {
                $this->procesarCobroTradicional(
                    $c,
                    $fechaActual,
                    $cobrosIndex
                );
            }
        }

        return [
            'cobrosIndex' => $cobrosIndex,
            'errores' => $errores,
            'facturasPorReferencia' => $facturasPorReferencia,
            'facturasPorCodigo' => $facturasPorCodigo,
        ];
    }

    public function procesarImportacion(
        $data,
        $periodo,
        $file,
        $facturasPorReferencia,
        $facturasPorCodigo,
        $cobrosIndex
    ) {

        $errores = [];
        $procesados = 0;

        $pagadas = [];

        $this->tieneIdPeriodoPagoFactura =
            $this->pagosFacturaTieneCampo('id_periodo');
        $this->tieneEstadoExcelPagoFactura =
            $this->pagosFacturaTieneCampo('estado_excel');

        $pagosExistentes = $this->pagosFacturaModel
            ->select('id_pago_factura,id_factura')
            ->findAll();

        $this->pagosFacturaIndex = [];
        foreach ($pagosExistentes as $p) {
            $this->pagosFacturaIndex[$p['id_factura']] = $p['id_pago_factura'];
        }

        $detallesFacturas = $this->facturaDetalleModel
            ->select('id_factura,id_cobro_instalacion,monto,concepto')
            ->findAll();

        $detallesPorFactura = [];
        foreach ($detallesFacturas as $detalle) {
            $detallesPorFactura[$detalle['id_factura']][] = $detalle;
        }

        /**
         * =========================================================
         * 1. PROCESO PRINCIPAL: COBROS = FACTURAS PAGADAS
         * =========================================================
         */
        foreach ($cobrosIndex as $referencia => $cobro) {

            $factura = $facturasPorReferencia[$referencia] ?? null;

            if (!$factura) {
                $errores[] = "No existe factura para cobro: $referencia";
                continue;
            }

            $facturaId = $factura['id_factura'];

            $pagadas[$facturaId] = true;

            $tiraje = $factura['tiraje'] ?? '';
            $correlativo = $factura['correlativo'] ?? '';

            $montoPagado = $cobro['monto_pagado'] ?? $factura['total'];
            $fechaPago = $this->normalizarFechaPago($cobro['fecha_pago'] ?? null);

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

            $this->facturasModel->update($facturaId, [
                'estado' => 'PAGADA',
                'fecha_de_pago' => $fechaPago,
            ]);

            $contrato = $this->contratosModel
                ->where('id_contrato', $factura['id_contrato'])
                ->first();

            $solicitudesAfectadas = [];
            $montoCapital = 0;

            $facturasPendientes = $this->facturasModel
                ->where('id_contrato', $factura['id_contrato'])
                ->where('estado', 'PENDIENTE')
                ->where('id_factura <', $facturaId)
                ->orderBy('id_factura', 'ASC')
                ->findAll();

            foreach ($facturasPendientes as $fp) {

                $this->facturasModel->update($fp['id_factura'], [
                    'estado' => 'CANCELADA',
                    'saldo_pendiente' => 0,
                    'fecha_de_pago' => $fechaPago
                ]);

                $detallesPendientes = $detallesPorFactura[$fp['id_factura']] ?? [];

                $this->procesarDetallesFactura(
                    $detallesPendientes,
                    $fechaPago,
                    $solicitudesAfectadas,
                    $montoCapital,
                    $contrato['id_solicitud'] ?? null
                );
            }

            $detalles = $detallesPorFactura[$facturaId] ?? [];

            $this->procesarDetallesFactura(
                $detalles,
                $fechaPago,
                $solicitudesAfectadas,
                $montoCapital,
                $contrato['id_solicitud'] ?? null
            );

            foreach (array_keys($solicitudesAfectadas) as $idSolicitud) {

                $solicitud = $this->solicitudesModel->find($idSolicitud);

                if (!$solicitud) continue;

                $nuevoSaldo = max(
                    0,
                    (float)$solicitud['saldo_pendiente'] - $montoCapital
                );

                $this->solicitudesModel->update($idSolicitud, [
                    'saldo_pendiente' => $nuevoSaldo
                ]);
            }

            $procesados++;
        }

        /**
         * =========================================================
         * 2. FACTURAS NO EN COBROS = NO PAGADAS
         * =========================================================
         */
        foreach ($facturasPorReferencia as $referencia => $factura) {

            $facturaId = $factura['id_factura'];

            if (isset($pagadas[$facturaId])) {
                continue;
            }

            $this->facturasModel->update($facturaId, [
                'estado' => 'NO PAGADA',
                'fecha_de_pago' => null,
            ]);

            if (!isset($this->pagosFacturaIndex[$facturaId])) {

                $this->registrarImportacionFactura(
                    $facturaId,
                    $periodo,
                    $factura['tiraje'] ?? '',
                    $factura['correlativo'] ?? '',
                    $referencia,
                    'NO PAGADA',
                    0,
                    null,
                    $file->getName()
                );
            }

            $detalles = $detallesPorFactura[$facturaId] ?? [];

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
        }

        return [
            'procesados' => $procesados,
            'errores' => $errores
        ];
    }

    public function importarExcel()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $db = \Config\Database::connect();

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
                'message' => 'No hay periodo activo'
            ]);
        }

        $facturasPeriodo = $this->facturasModel
            ->select('
            facturas.*,
            clientes.codigo
        ')
            ->join('contratos', 'contratos.id_contrato=facturas.id_contrato')
            ->join('clientes', 'clientes.id_cliente=contratos.id_cliente')
            ->where('facturas.id_periodo', $periodo['id_periodo'])
            ->findAll();

        try {

            $reader = IOFactory::createReaderForFile($file->getTempName());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getTempName());

            $excel = $this->cargarDatosExcel($spreadsheet);

            $resultado = $this->prepararDatosCobros(
                $excel['cobros'],
                $facturasPeriodo
            );

            $db->transBegin();

            $resultadoImportacion  = $this->procesarImportacion(
                $excel['base'],
                $periodo,
                $file,
                $resultado['facturasPorReferencia'],
                $resultado['facturasPorCodigo'],
                $resultado['cobrosIndex']
            );

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar la información.');
            }

            $db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Archivo procesado correctamente',
                'procesados' => $resultadoImportacion['procesados'],
                'errores' => $resultadoImportacion['errores']
            ]);
        } catch (\Throwable $e) {

            if ($db->transStatus()) {
                $db->transRollback();
            }

            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
