<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use App\Models\PeriodoModel;

class ReporteFacturasPDF extends \TCPDF
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

class ReporteFacturas extends BaseController
{
    private $facturaModel;
    private $periodosModel;

    public function __construct()
    {
        $this->facturaModel = new FacturaModel();
        $this->periodosModel = new PeriodoModel();
    }

    public function index()
    {
        return view('reportes/reporte_facturas');
    }

    public function getPeriodosReporteSelect()
    {
        try {
            $search = $this->request->getGet('q') ?? '';
            $periodos = $this->periodosModel->buscarPeriodosSelect($search);

            return $this->respondSuccess($periodos);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('No se pudieron cargar los períodos');
        }
    }

    public function generarPDFAntiguo()
    {
        try {
            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            $idPeriodo = $this->request->getGet('periodo');
            $idPeriodo = !empty($idPeriodo) ? $idPeriodo : null;
            $tipo = $this->request->getGet('tipo') ?? 'Todos';
            $search = trim($this->request->getGet('search') ?? '');
            $periodo = $idPeriodo ? $this->periodosModel->find($idPeriodo) : null;

            $facturas = $this->facturaModel->getReporteFacturasPorPeriodo($idPeriodo, $tipo, $search);

            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf = new ReporteFacturasPDF();
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // Logo
            $logo = FCPATH . 'dist/img/agua.png';
            $pdf->Image($logo, 10, 10, 25);

            $tipoTexto = $tipo === 'Todos' ? 'Todos los tipos' : $tipo;
            $nombrePeriodo = 'Todos los periodos';

            if (!empty($idPeriodo) && is_array($periodo) && !empty($periodo['nombre'])) {
                $nombrePeriodo = esc($periodo['nombre']);
            }

            $searchTexto = $search !== '' ? ' | Búsqueda: ' . esc($search) : '';
            $totalGeneral = 0;

            $html = '
                <style>
                    .titulo {
                        text-align: center;
                        font-size: 16px;
                        font-weight: bold;
                    }
                    .subtitulo {
                        text-align: center;
                        font-size: 10px;
                        margin-bottom: 2px;
                    }
                    .filtro {
                        text-align: center;
                        font-size: 9px;
                        margin-bottom: 10px;
                    }
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 8px;
                    }
                    th {
                        background-color: #003366;
                        color: #ffffff;
                        padding: 5px;
                        text-align: left;
                        border-top: 1px solid #000;
                        border-bottom: 1px solid #000;
                    }
                    td {
                        padding: 4px;
                        text-align: left;
                        border-bottom: 1px solid #000;
                        word-wrap: break-word;
                    }
                    .total-row td {
                        font-weight: bold;
                        background-color: #f1f5f9;
                    }
                    .center {
                        text-align: center;
                    }
                    .right {
                        text-align: right;
                    }
                </style>

                <div class="titulo">REPORTE DE FACTURAS GENERADAS</div>
                <div class="subtitulo">Periodo: ' . $nombrePeriodo . '</div>
                <div class="filtro">Tipo: ' . esc($tipoTexto) . $searchTexto . '</div>
                <br>
                <table>
                    <thead>
                        <tr>
                            <th width="10%" align="left">No.</th>
                            <th width="10%" align="left">Correlativo</th>
                            <th width="10%" align="left">Tipo</th>
                            <th width="10%" align="left">Contrato</th>
                            <th width="30%" align="left">Cliente</th>
                            <th width="10%" align="left">Total</th>
                            <th width="10%" align="left">F. pago</th>
                            <th width="10%" align="left">Estado</th>
                        </tr>
                    </thead>
                    <tbody>';

            if (empty($facturas)) {
                $html .= '
                        <tr>
                            <td colspan="9" class="center">No hay facturas para los filtros seleccionados.</td>
                        </tr>';
            } else {
                $numero = 1;

                foreach ($facturas as $factura) {
                    $correlativo = trim(($factura['tiraje'] ?? '') . '-' . ($factura['correlativo'] ?? ''), '-');
                    $totalGeneral += (float)($factura['total'] ?? 0);

                    $html .= '
                            <tr>
                                <td width="10%">' . $numero++ . '</td>
                                <td width="10%">' . esc($correlativo) . '</td>
                                <td width="10%">' . esc($factura['tipo_factura'] ?? '-') . '</td>
                                <td width="10%">' . esc($factura['numero_contrato'] ?? '-') . '</td>
                                <td width="30%">' . esc($factura['cliente'] ?? '-') . '</td>
                                <td width="10%">$ ' . number_format((float)($factura['total'] ?? 0), 2) . '</td>
                                <td width="10%">' . esc($factura['fecha_pago'] ?? '-') . '</td>
                                <td width="10%">' . esc($factura['estado'] ?? '-') . '</td>
                            </tr>';
                }

                $html .= '
                        <tr class="total-row">
                            <td colspan="5" class="right">TOTAL GENERAL</td>
                            <td class="right">$ ' . number_format($totalGeneral, 2) . '</td>
                            <td colspan="3"></td>
                        </tr>';
            }

            $html .= '
                    </tbody>
                </table>';

            $pdf->Ln(12);
            $pdf->writeHTML($html, true, false, true, false, '');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="reporte_facturas_generadas.pdf"')
                ->setBody($pdf->Output('reporte_facturas_generadas.pdf', 'S'));
        } catch (\Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error al generar el reporte'
                ]);
        }
    }

    public function generarPDF()
    {
        try {

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            $idPeriodo = $this->request->getGet('periodo');
            $idPeriodo = !empty($idPeriodo) ? $idPeriodo : null;
            $tipo = $this->request->getGet('tipo') ?? 'Todos';
            $search = trim($this->request->getGet('search') ?? '');
            $periodo = $idPeriodo ? $this->periodosModel->find($idPeriodo) : null;

            $facturas = $this->facturaModel->getReporteFacturasPorPeriodo($idPeriodo, $tipo, $search);


            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf = new ReporteFacturasPDF();

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
            // $tipoTexto = $tipo === 'Todos' ? 'Todos los tipos' : $tipo;
            $nombrePeriodo = (is_array($periodo) && !empty($periodo['nombre']))
                ? esc($periodo['nombre'])
                : 'Todos los periodos';

            // $searchTexto = $search !== '' ? ' | Búsqueda: ' . esc($search) : '';

            $pdf->Ln(12);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'REPORTE DE FACTURAS GENERADAS', 0, 1, 'C');

            $pdf->SetFont('helvetica', '', 7);
            $pdf->Cell(0, 5, "Periodo: {$nombrePeriodo}", 0, 1, 'C');
            // $pdf->Cell(0, 5, "Tipo: {$tipoTexto}{$searchTexto}", 0, 1, 'C');

            $mostrarPeriodo = empty($idPeriodo) ? true : false;

            $pdf->Ln(4);

            // =========================
            // HEADER TABLA
            // =========================
            $pdf->SetFillColor(0, 51, 102);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 6);

            if (!$mostrarPeriodo) {
                $pdf->Cell(10, 6, 'No.',          1, 0, 'L', true);
            } else {
                $pdf->Cell(10, 6, 'Periodo',          1, 0, 'L', true);
            }
            $pdf->Cell(25, 6, 'Correlativo',  1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Tipo',         1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Contrato',     1, 0, 'L', true);
            $pdf->Cell(60, 6, 'Cliente',      1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Total',        1, 0, 'L', true);
            $pdf->Cell(15, 6, 'F. Pago',      1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Estado',       1, 1, 'L', true); // Solo esta lleva 1


            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 6);

            // =========================
            // DATA
            // =========================
            $totalGeneral = 0;
            $numero = 1;
            $pdf->setCellPaddings(0, 0, 0, 0);
            $pdf->setCellMargins(0, 0, 0, 0);
            $pdf->setCellHeightRatio(1);

            $lineHeight = 3; // prueba entre 2.5 y 3
            foreach ($facturas as $factura) {

                $totalGeneral += (float)$factura['total'];

                $correlativo = trim(
                    ($factura['tiraje'] ?? '') . '-' . ($factura['correlativo'] ?? ''),
                    '-'
                );

                //==========================================
                // DATOS
                //==========================================
                if (!$mostrarPeriodo) {

                    $fila = [
                        $numero,
                        $correlativo,
                        $factura['tipo_factura'],
                        $factura['numero_contrato'],
                        $factura['cliente'],
                        number_format((float)$factura['total'], 2),
                        $factura['fecha_pago'],
                        $factura['estado']
                    ];
                } else {

                    $fila = [
                        $factura['periodo'], // o el campo que corresponda
                        $correlativo,
                        $factura['tipo_factura'],
                        $factura['numero_contrato'],
                        $factura['cliente'],
                        number_format((float)$factura['total'], 2),
                        $factura['fecha_pago'],
                        $factura['estado']
                    ];
                }

                $anchos = [10, 25, 20, 20, 60, 20, 15, 20];

                //==========================================
                // CALCULAR ALTURA DE LA FILA
                //==========================================
                $altura = $lineHeight;

                foreach ($fila as $i => $texto) {

                    $lineas = max(1, $pdf->getNumLines($texto, $anchos[$i]));

                    $altoCelda = $lineas * $lineHeight;

                    $altura = max($altura, $altoCelda);
                }

                //==========================================
                // SALTO DE PAGINA
                //==========================================
                if ($pdf->GetY() + $altura > 280) {

                    $pdf->AddPage();

                    // volver a imprimir encabezado
                    $pdf->SetFillColor(0, 51, 102);
                    $pdf->SetTextColor(255);
                    $pdf->SetFont('helvetica', 'B', 6);

                    if (!$mostrarPeriodo) {
                        $pdf->Cell(10, 6, 'No.', 1, 0, 'L', true);
                    } else {
                        $pdf->Cell(10, 6, 'Periodo',          1, 0, 'L', true);
                    }
                    $pdf->Cell(25, 6, 'Correlativo', 1, 0, 'L', true);
                    $pdf->Cell(20, 6, 'Tipo', 1, 0, 'L', true);
                    $pdf->Cell(20, 6, 'Contrato', 1, 0, 'L', true);
                    $pdf->Cell(60, 6, 'Cliente', 1, 0, 'L', true);
                    $pdf->Cell(20, 6, 'Total', 1, 0, 'L', true);
                    $pdf->Cell(15, 6, 'F. Pago', 1, 0, 'L', true);
                    $pdf->Cell(20, 6, 'Estado', 1, 1, 'L', true);

                    $pdf->SetTextColor(0);
                    $pdf->SetFont('helvetica', '', 6);
                }

                //==========================================
                // DIBUJAR FILA
                //==========================================
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                foreach ($fila as $i => $texto) {

                    $pdf->MultiCell(
                        $anchos[$i],
                        $altura,
                        $texto,
                        0,
                        'L',
                        false,
                        0,
                        '',
                        '',
                        true,
                        0,
                        false,
                        false,
                        $altura,
                        'T'
                    );

                    $x += $anchos[$i];
                    $pdf->SetXY($x, $y);
                }

                $pdf->SetY($y + $altura);

                // Línea separadora
                $pdf->Cell(190, 0, '', 'T', 1);

                $numero++;
            }


            // =========================
            // TOTAL GENERAL
            // =========================
            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->Ln(2);
            $pdf->Cell(190, 0, '', 'T');
            $pdf->Ln(2);

            $pdf->Cell(150, 6, 'TOTAL', 0, 0, 'R');
            $pdf->Cell(30, 6, '$ ' . number_format($totalGeneral, 2), 0, 1, 'R');

            // =========================
            // OUTPUT
            // =========================
            $pdfContent = $pdf->Output('reporte_facturas_generadas.pdf', 'S');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="reporte_facturas_generadas.pdf"')
                ->setBody($pdfContent);
        } catch (\Throwable $e) {

            log_message('error', $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error al generar el reporte'
                ]);
        }
    }
}
