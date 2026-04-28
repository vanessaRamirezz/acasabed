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

    public function generarPDF()
    {
        $idPeriodo = $this->request->getGet('periodo');
        $tipo = $this->request->getGet('tipo') ?? 'Todos';
        $search = trim($this->request->getGet('search') ?? '');

        if (!$idPeriodo) {
            return $this->response->setStatusCode(400)->setBody('Debe seleccionar un período');
        }

        $periodo = $this->periodosModel->find($idPeriodo);

        if (!$periodo) {
            return $this->response->setStatusCode(404)->setBody('El período seleccionado no existe');
        }

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
        <div class="subtitulo">Periodo: ' . esc($periodo['nombre']) . '</div>
        <div class="filtro">Tipo: ' . esc($tipoTexto) . $searchTexto . '</div>
        <br>
        <table>
            <thead>
                <tr>
                    <th align="left">No.</th>
                    <th align="left">Correlativo</th>
                    <th align="left">Tipo</th>
                    <th align="left">Contrato</th>
                    <th align="left">Cliente</th>
                    <th align="left">Monto</th>
                    <th align="left">Total</th>
                    <th align="left">F. pago</th>
                    <th align="left">Estado</th>
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
                        <td>' . $numero++ . '</td>
                        <td>' . esc($correlativo) . '</td>
                        <td>' . esc($factura['tipo_factura'] ?? '-') . '</td>
                        <td>' . esc($factura['numero_contrato'] ?? '-') . '</td>
                        <td>' . esc($factura['cliente'] ?? '-') . '</td>
                        <td>$ ' . number_format((float)($factura['monto'] ?? 0), 2) . '</td>
                        <td>$ ' . number_format((float)($factura['total'] ?? 0), 2) . '</td>
                        <td>' . esc($factura['fecha_pago'] ?? '-') . '</td>
                        <td>' . esc($factura['estado'] ?? '-') . '</td>
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
    }
}
