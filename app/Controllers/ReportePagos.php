<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use App\Models\PeriodoModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportePagosPDF extends \TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(
            0,
            10,
            'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(),
            0,
            0,
            'C'
        );
    }
}

class ReportePagos extends BaseController
{
    private PeriodoModel $periodosModel;
    private FacturaModel $facturaModel;

    function __construct()
    {
        $this->periodosModel = new PeriodoModel();
        $this->facturaModel = new FacturaModel();
    }

    public function index()
    {
        return view('reportes/reporte_pagos');
    }

    private function imprimirEncabezadoTabla($pdf)
    {
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 6);

        $pdf->Cell(17, 6, '# Cliente', 1, 0, 'C', true);
        $pdf->Cell(11, 6, 'Ficha', 1, 0, 'C', true);
        $pdf->Cell(62, 6, 'Nombre Usuario', 1, 0, 'C', true);
        $pdf->Cell(11, 6, 'Agua', 1, 0, 'C', true);
        $pdf->Cell(11, 6, 'Aseo', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'Alumbrado', 1, 0, 'C', true);
        $pdf->Cell(21, 6, 'T. Alcaldía', 1, 0, 'C', true);
        $pdf->Cell(21, 6, 'Saldo Ant.', 1, 0, 'C', true);
        $pdf->Cell(10, 6, 'Total', 1, 0, 'C', true);
        $pdf->Cell(11, 6, 'Estado', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 6);
    }

    public function generarPDF()
    {
        try {
            $idPeriodo = $this->request->getGet('periodo');
            $idPeriodo = !empty($idPeriodo) ? $idPeriodo : null;
            $estado = $this->request->getGet('estado') ?? 'TODAS';
            $periodo = $idPeriodo ? $this->periodosModel->find($idPeriodo) : null;

            $facturas = $this->facturaModel->getReportePagos($idPeriodo, $estado);

            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf = new ReportePagosPDF();

            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(true);

            $logo = FCPATH . 'dist/img/agua.png';
            if (file_exists($logo)) {
                $pdf->Image($logo, 10, 10, 25);
            }

            // =========================
            // ENCABEZADOS
            // =========================
            $nombrePeriodo = (is_array($periodo) && !empty($periodo['nombre']))
                ? esc($periodo['nombre'])
                : 'Todos los periodos';

            $pdf->Ln(12);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'REPORTE DE PAGOS', 0, 1, 'C');

            $pdf->SetFont('helvetica', '', 7);
            $pdf->Cell(0, 5, "Periodo: {$nombrePeriodo}", 0, 1, 'C');

            $pdf->Ln(4);

            // =========================
            // HEADER TABLA
            // =========================
            $this->imprimirEncabezadoTabla($pdf);


            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 6);


            // =========================
            // DATA
            // =========================
            $pdf->setCellPaddings(0, 0, 0, 0);
            $pdf->setCellMargins(0, 0, 0, 0);
            $pdf->setCellHeightRatio(1);

            $lineHeight = 3; // prueba entre 2.5 y 3

            foreach ($facturas as $factura) {

                $total = $factura['agua']
                    + $factura['aseo']
                    + $factura['alumbrado']
                    + $factura['saldoAnterior'];


                $aseo = $factura['aseo'];
                $alumbrado = $factura['alumbrado'];

                $totalCargoAlcaldia = $aseo + $alumbrado;
                //==========================================
                // DATOS
                //==========================================

                if ($factura['estado'] === 'PAGADA') {
                    $estado = "Pagó";
                } else if ($factura['estado'] === 'NO PAGADA') {
                    $estado = "No pagó";
                } else {
                    $estado = "--";
                }

                $fila = [
                    $factura['numero_cliente'], // o el campo que corresponda
                    $factura['ficha_alcaldia'],
                    $factura['cliente'],
                    $factura['agua'],
                    $aseo,
                    $alumbrado,
                    $totalCargoAlcaldia,
                    $factura['saldoAnterior'],
                    $total,
                    $estado
                ];


                $anchos = [17, 11, 62, 11, 11, 15, 21, 21, 10, 11];

                $fila = array_map(function ($valor) {
                    return (string)($valor ?? '');
                }, $fila);

                //==========================================
                // CALCULAR ALTURA DE LA FILA
                //==========================================
                $altura = $lineHeight;

                foreach ($fila as $i => $texto) {
                    $lineas = max(1, $pdf->getNumLines($texto, $anchos[$i]));

                    $altoCelda = $lineas * $lineHeight;

                    $altura = max($altura, $altoCelda);
                }

                // //==========================================
                // // SALTO DE PAGINA
                // //==========================================
                if ($pdf->GetY() + $altura > 280) {
                    $pdf->AddPage();
                    $this->imprimirEncabezadoTabla($pdf);
                }

                //==========================================
                // DIBUJAR FILA
                //==========================================
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                foreach ($fila as $i => $texto) {
                    $align = ($i >= 3) ? 'R' : 'L';
                    $pdf->MultiCell(
                        $anchos[$i],
                        $altura,
                        $texto,
                        0,
                        $align,
                        false,
                        0,
                        '',
                        '',
                        true,
                        0,
                        false,
                        false,
                        $altura,
                        'M'
                    );

                    $x += $anchos[$i];
                    $pdf->SetXY($x, $y);
                }

                $pdf->SetY($y + $altura);

                // Línea separadora
                $pdf->Cell(190, 0, '', 'T', 1);
            }


            // =========================
            // OUTPUT
            // =========================
            $pdfContent = $pdf->Output('reporte_pagos.pdf', 'S');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="reporte_pagos.pdf"')
                ->setBody($pdfContent);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error al generar el reporte'
                ]);
        }
    }

    private function limpiarMonto($valor)
    {
        return ($valor == 0 || $valor === null || $valor === '')
            ? ''
            : $valor;
    }

    private function llenarHoja($hoja, $datos)
    {
        $fila = 2;

        $estado = "";
        foreach ($datos as $factura) {

            $totalCargoAlcaldia = $factura['aseo'] + $factura['alumbrado'];

            $total = $factura['agua']
                + $factura['aseo']
                + $factura['alumbrado']
                + $factura['saldoAnterior'];

            $hoja->setCellValue('A' . $fila, (int)$factura['numero_cliente']);
            $hoja->setCellValue('B' . $fila, $factura['ficha_alcaldia']);
            $hoja->setCellValue('C' . $fila, $factura['cliente']);
            $hoja->setCellValue('D' . $fila, $factura['agua']);
            $hoja->setCellValue('E' . $fila, $this->limpiarMonto($factura['aseo']));
            $hoja->setCellValue('F' . $fila, $this->limpiarMonto($factura['alumbrado']));
            $hoja->setCellValue('G' . $fila, $totalCargoAlcaldia);
            $hoja->setCellValue('H' . $fila, $factura['saldoAnterior']);
            $hoja->setCellValue('I' . $fila, $total);
            if ($factura['estado'] === 'PAGADA') {
                $estado = "Pagó";
            } else if ($factura['estado'] === 'NO PAGADA') {
                $estado = "No pagó";
            } else {
                $estado = "--";
            }
            $hoja->setCellValue('J' . $fila, $estado);

            $fila++;
        }

        // Última fila con datos
        $ultimaFila = $fila - 1;

        //==========================================
        // ALINEACIONES
        //==========================================
        // codigo cliente a la derecha
        $hoja->getStyle("A2:A{$ultimaFila}")
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Ficha centrado
        $hoja->getStyle("B2:B{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Agua a la derecha
        $hoja->getStyle("D2:I{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Aseo  centrado
        $hoja->getStyle("E2:E{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Alumbrado  centrado
        $hoja->getStyle("F2:F{$ultimaFila}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //==========================================
        // FORMATO MONEDA
        //==========================================

        //==========================================
        // FORMATOS DINERO
        //==========================================
        // Aseo
        $hoja->getStyle("E2:E{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        // Alumbrado
        $hoja->getStyle("F2:F{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        // Total Cargo Alcaldía
        $hoja->getStyle("G2:G{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

        // Saldo anterior
        $hoja->getStyle("H2:H{$ultimaFila}")
            ->getNumberFormat()
            ->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

        // // Total
        // $hoja->getStyle("I2:I{$ultimaFila}")
        //     ->getNumberFormat()
        //     ->setFormatCode('"$"#,##0.00');

        //==========================================
        // BORDES
        //==========================================

        $hoja->getStyle("A1:J{$ultimaFila}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    public function generarExcelReportePagos()
    {
        try {

            $idPeriodo = $this->request->getGet('periodo');
            $estado = $this->request->getGet('estado') ?? 'TODAS';

            if (empty($idPeriodo)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Debe seleccionar un período.'
                ]);
            }

            if (empty($estado)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Debe seleccionar un estado de facturas.'
                ]);
            }

            //=========================
            // CARGAR PLANTILLA
            //=========================

            $rutaPlantilla = APPPATH . 'Templates/plantilla_pago_no_pago.xlsx';

            if (!file_exists($rutaPlantilla)) {
                throw new \Exception('No existe la plantilla del reporte.');
            }

            $spreadsheet = IOFactory::load($rutaPlantilla);

            //=========================
            // OBTENER HOJAS
            //=========================
            $hojaPagos = $spreadsheet->getSheetByName('PAGO');
            $hojaNoPagos = $spreadsheet->getSheetByName('NO PAGO');

            if (!$hojaPagos || !$hojaNoPagos) {
                throw new \Exception('No se encontraron las hojas PAGO y NO PAGO.');
            }

            //=========================
            // CONSULTAS
            //=========================
            $datosPagos = $this->facturaModel->getReportePagos($idPeriodo, 'PAGADA');
            $datosNoPagos = $this->facturaModel->getReportePagos($idPeriodo, 'NO PAGADA');
            if ($estado === "PAGADA") {
                //========================================================
                // AQUÍ EN EL SIGUIENTE PASO LLENAREMOS LAS DOS HOJAS
                //========================================================
                $this->llenarHoja($hojaPagos, $datosPagos);
            } else if ($estado === "NO PAGADA") {
                //========================================================
                // AQUÍ EN EL SIGUIENTE PASO LLENAREMOS LAS DOS HOJAS
                //========================================================
                $this->llenarHoja($hojaNoPagos, $datosNoPagos);
            } else {
                //========================================================
                // AQUÍ EN EL SIGUIENTE PASO LLENAREMOS LAS DOS HOJAS
                //========================================================
                $this->llenarHoja($hojaPagos, $datosPagos);
                $this->llenarHoja($hojaNoPagos, $datosNoPagos);
            }

            //=========================
            // GENERAR EXCEL
            //=========================
            $writer = new Xlsx($spreadsheet);

            if (ob_get_length()) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Reporte_Pagos.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Throwable $th) {

            log_message('error', $th->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error al generar el Excel.'
                ]);
        }
    }
}
