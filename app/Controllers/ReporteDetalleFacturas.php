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

class ReporteDetalleFacturas extends BaseController
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
        return view('reportes/reporte_detalle_facturas');
    }

    public function generarPDF()
    {
        try {

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            $idPeriodo = $this->request->getGet('periodo') ?: null;
            // $tipo = $this->request->getGet('tipo') ?? 'Todos';
            $fecha = $this->request->getGet('fecha') ?? '';
            $search = trim($this->request->getGet('search') ?? '');

            log_message('info', 'fecha recibida ' . $fecha);

            $periodo = $idPeriodo
                ? $this->periodosModel->find($idPeriodo)
                : null;

            $facturas = $this->facturaModel
                ->getReporteFacturasDetallePorPeriodo($idPeriodo, $search, $fecha);

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
            $pdf->Cell(0, 6, 'REPORTE DE FACTURAS DETALLES', 0, 1, 'C');

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
            $pdf->SetFont('helvetica', 'B', 7);

            $pdf->Cell(25, 6, 'Correlativo', 1, 0, 'L', true);

            if ($mostrarPeriodo) {
                $pdf->Cell(20, 6, 'Periodo', 1, 0, 'L', true);
            }

            $pdf->Cell(30, 6, 'Contrato', 1, 0, 'L', true);
            $pdf->Cell(65, 6, 'Cliente', 1, 0, 'L', true);
            $pdf->Cell(20, 6, 'Estado', 1, 0, 'L', true);

            if (!$mostrarPeriodo) {
                $pdf->Cell(50, 6, 'Fecha Pago', 1, 1, 'L', true);
            } else {
                $pdf->Cell(30, 6, 'Fecha Pago', 1, 1, 'L', true);
            }

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 7);

            // =========================
            // DATA
            // =========================
            $totalGeneral = 0;
            $first = true;

            foreach ($facturas as $factura) {

                if (!$first) {
                    // separador visual
                    $pdf->Ln(0.5);
                }
                $first = false;

                $correlativo = trim(
                    ($factura['tiraje'] ?? '') . '-' . ($factura['correlativo'] ?? ''),
                    '-'
                );

                $totalGeneral += (float)$factura['total'];

                // fila principal
                $pdf->Cell(25, 3, $correlativo, 0);

                if ($mostrarPeriodo) {
                    $pdf->Cell(20, 5, $factura['periodo'] ?? '', 0, 0, 'L');
                }

                $pdf->Cell(30, 3, $factura['numero_contrato'], 0);

                $pdf->Cell(65, 3, $factura['cliente'], 0);

                $pdf->Cell(20, 3, $factura['estado'], 0);

                if (!$mostrarPeriodo) {
                    $pdf->Cell(50, 3, $factura['fecha_pago'], 0, 1);
                } else {
                    $pdf->Cell(30, 3, $factura['fecha_pago'], 0, 1);
                }

                // encabezado detalles (sin HTML)
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(150, 2, 'Concepto', 0);
                $pdf->Cell(30, 2, 'Monto', 0, 1, 'R');

                $pdf->SetFont('helvetica', '', 6);

                if (!empty($factura['detalles'])) {

                    $detalles = explode('##', $factura['detalles']);

                    foreach ($detalles as $detalle) {

                        $partes = explode('||', $detalle);

                        $concepto = $partes[0] ?? '';
                        $mora = (float)($partes[1] ?? 0);
                        $monto = (float)($partes[2] ?? 0);

                        $valor = $mora > 0 ? $mora : $monto;

                        $pdf->Cell(150, 2, $concepto, 0);
                        $pdf->Cell(30, 2, '$ ' . number_format($valor, 2), 0, 1, 'R');
                    }
                }

                // total factura
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->Cell(150, 2, 'Total', 0, 0, 'R');
                $pdf->Cell(30, 2, '$ ' . number_format($factura['total'], 2), 0, 1, 'R');

                $pdf->SetFont('helvetica', '', 7);

                // =========================
                // SEPARADOR VISUAL FACTURA
                // =========================
                $pdf->Ln(1);
                $pdf->Cell(190, 0, '', 'T'); // línea horizontal
                // $pdf->Ln(2);
            }

            $currentY = $pdf->GetY();

            if ($currentY > 260) {
                $pdf->AddPage();
            }
            // =========================
            // TOTAL GENERAL
            // =========================
            $pdf->SetFont('helvetica', 'B', 8);

            $pdf->Ln(2);
            $pdf->Cell(190, 0, '', 'T');
            $pdf->Ln(2);

            $pdf->Cell(150, 6, 'TOTAL GENERAL', 0, 0, 'R');
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
