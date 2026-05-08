<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use App\Models\PeriodoModel;

class ReporteResumenFacturasPDF extends \TCPDF
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

class ReporteResumenFacturas extends BaseController
{
    private FacturaModel $facturaModel;
    private PeriodoModel $periodosModel;

    public function __construct()
    {
        $this->facturaModel = new FacturaModel();
        $this->periodosModel = new PeriodoModel();
    }

    public function index()
    {
        return view('reportes/reporte_resumen_facturas', [
            'periodoActivo' => $this->periodosModel->getPeriodoActivo()
        ]);
    }

    public function getPeriodosSelect()
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

    private function ordenarEstados(array $estados): array
    {
        $orden = [
            'PAGADA' => 1,
            'PAGADA VENCIDA' => 2,
            'PENDIENTE' => 3,
            'NO PAGADA' => 4,
            'VENCIDA' => 5,
            'SALDO TRASLADADO' => 6,
            'CANCELADA' => 7
        ];

        usort($estados, static function ($a, $b) use ($orden) {
            $estadoA = strtoupper(trim((string)($a['estado'] ?? '')));
            $estadoB = strtoupper(trim((string)($b['estado'] ?? '')));

            return ($orden[$estadoA] ?? 99) <=> ($orden[$estadoB] ?? 99);
        });

        return $estados;
    }

    public function generarPDF()
    {
        $idPeriodo = $this->request->getGet('periodo');
        $idPeriodo = !empty($idPeriodo) ? $idPeriodo : null;
        $tipo = $this->request->getGet('tipo') ?? 'Todos';
        $periodo = $idPeriodo ? $this->periodosModel->find($idPeriodo) : null;

        $resumen = $this->facturaModel->getResumenContableFacturas($idPeriodo, $tipo);
        $general = $resumen['general'] ?? [];
        $estados = $this->ordenarEstados($resumen['estados'] ?? []);
        $tipos = $resumen['tipos'] ?? [];
        $servicios = $resumen['servicios'] ?? [];
        $totalMora = (float)($resumen['mora']['total_mora'] ?? 0);

        if ($totalMora > 0 || empty($servicios)) {
            $servicios[] = [
                'servicio' => 'MORA',
                'subtotal_servicio' => $totalMora,
                'cantidad_facturas' => 0
            ];
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = new ReporteResumenFacturasPDF();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $logo = FCPATH . 'dist/img/agua.png';
        $pdf->Image($logo, 10, 10, 22);

        $nombrePeriodo = 'Todos los periodos';
        if (!empty($idPeriodo) && is_array($periodo) && !empty($periodo['nombre'])) {
            $nombrePeriodo = esc($periodo['nombre']);
        }

        $tipoTexto = $tipo === 'Todos' ? 'Todos los tipos' : esc($tipo);

        $html = '
        <style>
            .titulo {
                text-align: center;
                font-size: 15px;
                font-weight: bold;
            }
            .subtitulo {
                text-align: center;
                font-size: 10px;
            }
            .section-title {
                background-color: #0f4c81;
                color: #ffffff;
                font-size: 10px;
                font-weight: bold;
                padding: 6px 8px;
                margin-top: 10px;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                font-size: 8px;
            }
            th {
                background-color: #d9e8f5;
                color: #102a43;
                padding: 5px;
                border: 1px solid #9fb3c8;
                text-align: left;
            }
            td {
                padding: 4px;
                border: 1px solid #c5d3e0;
            }
            .box {
                border: 1px solid #c5d3e0;
                background-color: #f8fbff;
                padding: 8px;
                font-size: 9px;
            }
            .right {
                text-align: right;
            }
            .center {
                text-align: center;
            }
        </style>

        <div class="titulo">REPORTE RESUMEN DE FACTURACIÓN</div>
        <div class="subtitulo">Periodo: ' . $nombrePeriodo . '</div>
        <div class="subtitulo">Tipo: ' . $tipoTexto . '</div>
        <br>

        <div class="section-title">RESUMEN GENERAL</div>
        <table cellpadding="5">
            <tr>
                <td class="box"><b>Total de facturas</b><br>' . number_format((float)($general['total_facturas'] ?? 0), 0) . '</td>
                <td class="box"><b>Total facturado</b><br>$ ' . number_format((float)($general['total_facturado'] ?? 0), 2) . '</td>
                <td class="box"><b>Saldo pendiente</b><br>$ ' . number_format((float)($general['saldo_pendiente_total'] ?? 0), 2) . '</td>
                <td class="box"><b>Total mora</b><br>$ ' . number_format($totalMora, 2) . '</td>
            </tr>
            <tr>
                <td class="box"><b>Facturas pagadas</b><br>' . number_format((float)($general['facturas_pagadas'] ?? 0), 0) . '</td>
                <td class="box"><b>Facturas no pagadas</b><br>' . number_format((float)($general['facturas_no_pagadas'] ?? 0), 0) . '</td>
                <td class="box"><b>Monto pagado</b><br>$ ' . number_format((float)($general['monto_pagado'] ?? 0), 2) . '</td>
                <td class="box"><b>Monto no pagado</b><br>$ ' . number_format((float)($general['monto_no_pagado'] ?? 0), 2) . '</td>
            </tr>
        </table>

        <div class="section-title">FACTURAS POR ESTADO</div>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th class="center">Cantidad</th>
                    <th class="right">Total facturado</th>
                    <th class="right">Saldo pendiente</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($estados)) {
            $html .= '<tr><td colspan="4" class="center">No hay datos para los filtros seleccionados.</td></tr>';
        } else {
            foreach ($estados as $estado) {
                $html .= '
                <tr>
                    <td>' . esc($estado['estado'] ?? '-') . '</td>
                    <td class="center">' . number_format((float)($estado['cantidad_facturas'] ?? 0), 0) . '</td>
                    <td class="right">$ ' . number_format((float)($estado['total_facturado'] ?? 0), 2) . '</td>
                    <td class="right">$ ' . number_format((float)($estado['saldo_pendiente'] ?? 0), 2) . '</td>
                </tr>';
            }
        }

        $html .= '
            </tbody>
        </table>

        <div class="section-title">FACTURACIÓN POR TIPO</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th class="center">Cantidad</th>
                    <th class="right">Total facturado</th>
                    <th class="right">Saldo pendiente</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($tipos)) {
            $html .= '<tr><td colspan="4" class="center">No hay tipos registrados.</td></tr>';
        } else {
            foreach ($tipos as $filaTipo) {
                $html .= '
                <tr>
                    <td>' . esc($filaTipo['tipo'] ?? '-') . '</td>
                    <td class="center">' . number_format((float)($filaTipo['cantidad_facturas'] ?? 0), 0) . '</td>
                    <td class="right">$ ' . number_format((float)($filaTipo['total_facturado'] ?? 0), 2) . '</td>
                    <td class="right">$ ' . number_format((float)($filaTipo['saldo_pendiente'] ?? 0), 2) . '</td>
                </tr>';
            }
        }

        $html .= '
            </tbody>
        </table>

        <div class="section-title">COMPOSICIÓN POR SERVICIO</div>
        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th class="center">Facturas asociadas</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($servicios)) {
            $html .= '<tr><td colspan="3" class="center">No hay conceptos de servicio para este filtro.</td></tr>';
        } else {
            foreach ($servicios as $servicio) {
                $html .= '
                <tr>
                    <td>' . esc($servicio['servicio'] ?? '-') . '</td>
                    <td class="center">' . number_format((float)($servicio['cantidad_facturas'] ?? 0), 0) . '</td>
                    <td class="right">$ ' . number_format((float)($servicio['subtotal_servicio'] ?? 0), 2) . '</td>
                </tr>';
            }
        }

        $html .= '
            </tbody>
        </table>';

        $pdf->Ln(10);
        $pdf->writeHTML($html, true, false, true, false, '');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="reporte_resumen_facturacion.pdf"')
            ->setBody($pdf->Output('reporte_resumen_facturacion.pdf', 'S'));
    }
}
