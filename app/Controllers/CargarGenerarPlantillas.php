<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\ContratoModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaModel;
use App\Models\PagoFacturaModel;
use App\Models\PeriodoModel;
use App\Models\SolicitudModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargarGenerarPlantillas extends BaseController
{
    private $facturasModel;
    private $periodosModel;
    private $pagosFacturaModel;
    private $facturaDetalleModel;
    private $cobrosContratoModel;
    private $solicitudesModel;
    private $contratosModel;

    public function __construct()
    {
        $this->facturasModel = new FacturaModel();
        $this->periodosModel = new PeriodoModel();
        $this->pagosFacturaModel = new PagoFacturaModel();
        $this->facturaDetalleModel = new FacturaDetalleModel();
        $this->cobrosContratoModel = new CobroContratoModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->contratosModel = new ContratoModel();
    }

    public function index()
    {
        return view('cargar_generar_plantillas/index');
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
            'referencia',
            'fcliente',
            'nombre',
            'total',
            'ESTATUTOS',
            'ACTIVO'
        ], NULL, 'A1');

        $row = 2;
        foreach ($facturas as $f) {
            $referencia = $f['tiraje'] . '-' . $f['correlativo'];
            $sheet1->fromArray([
                $referencia,
                $f['codigo'],
                $f['cliente'],
                $f['total'],
                'No pagó',
                'ACTIVO'
            ], NULL, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;
        $range1 = "A1:F{$lastRow}";

        // 🔥 USANDO HELPERS
        $this->aplicarEstiloEncabezado($sheet1, 'A1:F1');
        $this->aplicarBordesTabla($sheet1, $range1);
        $this->autoSizeColumnas($sheet1, 'A', 'F');
        $this->configurarHoja($sheet1, $range1);


        // =========================
        // HOJA 2: COBROS
        // =========================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('COBROS');

        $sheet2->fromArray([
            'referencia',
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
            $referencia = $f['tiraje'] . '-' . $f['correlativo'];
            $sheet2->fromArray([
                $referencia,
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
        $range2 = "A1:H{$lastRow}";

        // 🔥 USANDO HELPERS
        $this->aplicarEstiloEncabezado($sheet2, 'A1:H1');
        $this->aplicarBordesTabla($sheet2, $range2);
        $this->autoSizeColumnas($sheet2, 'A', 'H');
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

        $filename = "BASE DE ACAYCCOMAC {$mes} {$anio}.xlsx";


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

    public function importarExcel()
    {
        $file = $this->request->getFile('excel');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo no recibido o inválido'
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
            foreach ($cobrosData as $i => $c) {

                if ($i === 0) continue; // encabezado

                // AJUSTA SI TU REFERENCIA NO ESTÁ EN COLUMNA 0
                $referencia = trim($c[0] ?? null);

                if (!$referencia) continue;

                $cobrosIndex[$referencia] = [
                    'fecha_pago' => $c[1],
                    'monto_pagado' => $c[5] ?? null
                ];
            }

            log_message('info', 'COBROS INDEX: ' . print_r($cobrosIndex, true));

            $db->transStart();

            foreach ($data as $index => $row) {

                if ($index === 0) continue;

                $referencia = trim($row[0] ?? null);
                $estadoExcel = trim($row[4] ?? null);

                if (!$referencia) {
                    $errores[] = "Fila $index sin referencia";
                    continue;
                }

                if (!str_contains($referencia, '-')) {
                    $errores[] = "Referencia inválida en fila $index: $referencia";
                    continue;
                }

                [$tiraje, $correlativo] = explode('-', $referencia);

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

                        $this->pagosFacturaModel->insert([
                            'id_factura' => $facturaId,
                            'tiraje' => $tiraje,
                            'correlativo' => $correlativo,
                            'referencia' => $referencia,
                            'monto_pagado' => $montoPagado,
                            'fecha_pago' => $fechaPago,
                            'fecha_carga' => date('Y-m-d'),
                            'id_usuario' => session()->get('id_usuario'),
                            'archivo_origen' => $file->getName()
                        ]);
                    }

                    // actualizar factura
                    $this->facturasModel->update($facturaId, [
                        'saldo_pendiente' => 0,
                        'estado' => 'PAGADA',
                        'fecha_de_pago' => $fechaPago
                    ]);

                    $facturasPendientes = $this->facturasModel
                        ->where('id_contrato', $factura['id_contrato'])
                        ->whereIn('estado', ['VENCIDA', 'PENDIENTE'])
                        ->where('id_factura <', $facturaId) // clave: anteriores
                        ->orderBy('id_factura', 'ASC') // FIFO
                        ->findAll();

                    foreach ($facturasPendientes as $fp) {

                        $this->facturasModel->update($fp['id_factura'], [
                            'estado' => 'CANCELADA',
                            'saldo_pendiente' => 0,
                            'fecha_de_pago' => $fechaPago
                        ]);
                    }

                    /**
                     * INSTALACIONES
                     */
                    $detalles = $this->facturaDetalleModel
                        ->where('id_factura', $facturaId)
                        ->findAll();

                    $contrato = $this->contratosModel
                        ->where('id_contrato', $factura['id_contrato'])
                        ->first();
                    log_message('info', ' data de contrato obtenido ' . print_r($contrato, true));

                    $solicitudesAfectadas = [];
                    $montoCapital = 0;
                    foreach ($detalles as $d) {

                        if ($d['tipo'] === 'Instalacion' && !empty($d['id_cobro_instalacion'])) {

                            $this->cobrosContratoModel->update(
                                $d['id_cobro_instalacion'],
                                [
                                    'estado' => 'CANCELADO',
                                    'fecha_pago' => $fechaPago,
                                    'recargo' => $d['mora'] ?? 0
                                ]
                            );

                            $montoCapital += (float)($d['monto'] ?? 0);
                            if ($contrato && !empty($contrato['id_solicitud'])) {
                                $solicitudesAfectadas[$contrato['id_solicitud']] = true;
                            }
                        }
                    }

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
                     * NO PAGÓ
                     * ==========================================
                     */

                    $this->facturasModel->update($facturaId, [
                        'estado' => 'VENCIDA'
                    ]);

                    $detalles = $this->facturaDetalleModel
                        ->where('id_factura', $facturaId)
                        ->findAll();

                    foreach ($detalles as $d) {

                        if ($d['tipo'] === 'Instalacion' && !empty($d['id_cobro_instalacion'])) {

                            $this->cobrosContratoModel->update(
                                $d['id_cobro_instalacion'],
                                [
                                    'estado' => 'VENCIDA',
                                    'recargo' => 2
                                ]
                            );
                        }
                    }
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
}
