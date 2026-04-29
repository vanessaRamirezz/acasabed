<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\InstaladorModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;

class ReporteLecturasPDF extends \TCPDF
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

class ReporteLecturas extends BaseController
{
    private $lecturasModel;
    private $periodosModel;
    private $contratosModel;
    private $instaladoresModel;

    public function __construct()
    {
        $this->lecturasModel = new LecturaModel();
        $this->periodosModel = new PeriodoModel();
        $this->contratosModel = new ContratoModel();
        $this->instaladoresModel = new InstaladorModel();
    }

    public function index()
    {
        return view('reportes/reporte_lecturas');
    }

    public function generarPDF()
    {
        $idPeriodo = $this->request->getGet('periodo');
        $idContrato = $this->request->getGet('contrato');
        $idInstalador = $this->request->getGet('instalador');
        $idDepartamento = $this->request->getGet('departamento');
        $idMunicipio = $this->request->getGet('municipio');
        $idDistrito = $this->request->getGet('distrito');
        $idColonia = $this->request->getGet('colonia');

        $lecturas = $this->lecturasModel->getReporteLecturasTomadas(
            $idPeriodo,
            $idContrato,
            $idInstalador,
            $idDepartamento,
            $idMunicipio,
            $idDistrito,
            $idColonia
        );

        $periodo = (!empty($idPeriodo) && $idPeriodo !== '-1') ? $this->periodosModel->find($idPeriodo) : null;
        $contrato = (!empty($idContrato) && $idContrato !== '-1') ? $this->contratosModel->find($idContrato) : null;
        $instalador = (!empty($idInstalador) && $idInstalador !== '-1') ? $this->instaladoresModel->find($idInstalador) : null;

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = new ReporteLecturasPDF();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $logo = FCPATH . 'dist/img/agua.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, 10, 10, 22);
        }

        $filtrosAplicados = [];

        if (!empty($periodo['nombre'])) {
            $filtrosAplicados[] = 'Período: ' . $periodo['nombre'];
        }

        if (!empty($contrato['numero_contrato'])) {
            $filtrosAplicados[] = 'Contrato: ' . $contrato['numero_contrato'];
        }

        if (!empty($instalador['nombre_completo'])) {
            $filtrosAplicados[] = 'Instalador: ' . $instalador['nombre_completo'];
        }

        if (!empty($lecturas)) {
            $primero = $lecturas[0];

            if (!empty($idDepartamento) && $idDepartamento !== '-1' && !empty($primero['departamento'])) {
                $filtrosAplicados[] = 'Departamento: ' . $primero['departamento'];
            }

            if (!empty($idMunicipio) && $idMunicipio !== '-1' && !empty($primero['municipio'])) {
                $filtrosAplicados[] = 'Municipio: ' . $primero['municipio'];
            }

            if (!empty($idDistrito) && $idDistrito !== '-1' && !empty($primero['distrito'])) {
                $filtrosAplicados[] = 'Distrito: ' . $primero['distrito'];
            }

            if (!empty($idColonia) && $idColonia !== '-1' && !empty($primero['colonia'])) {
                $filtrosAplicados[] = 'Colonia: ' . $primero['colonia'];
            }
        }

        if (empty($filtrosAplicados)) {
            $filtrosAplicados[] = 'Todas las lecturas tomadas';
        }

        $html = '
        <style>
            .titulo {
                text-align: center;
                font-size: 16px;
                font-weight: bold;
            }
            .filtro {
                text-align: center;
                font-size: 10px;
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
            .center {
                text-align: center;
            }
            .right {
                text-align: right;
            }
        </style>

        <div class="titulo">REPORTE DE LECTURAS TOMADAS</div>
        <div class="filtro">' . esc(implode(' | ', $filtrosAplicados)) . '</div>
        <br>
        <table>
            <thead>
                <tr>
                    <th align="left">No.</th>
                    <th align="left">Período</th>
                    <th align="left">Contrato</th>
                    <th align="left">Cliente</th>
                    <th align="left">Instalador</th>
                    <th align="left">Fecha</th>
                    <th align="left">Lectura</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($lecturas)) {
            $html .= '
                <tr>
                    <td colspan="8" class="center">No hay lecturas para los filtros seleccionados.</td>
                </tr>';
        } else {
            $numero = 1;

            foreach ($lecturas as $lectura) {
                $html .= '
                    <tr>
                        <td>' . $numero++ . '</td>
                        <td>' . esc($lectura['periodo'] ?? '-') . '</td>
                        <td>' . esc($lectura['numero_contrato'] ?? '-') . '</td>
                        <td>' . esc($lectura['cliente'] ?? '-') . '</td>
                        <td>' . esc($lectura['instalador'] ?? '-') . '</td>
                        <td>' . esc($lectura['fecha_lectura'] ?? '-') . '</td>
                        <td class="right">' . number_format((float)($lectura['valor'] ?? 0), 2) . '</td>
                    </tr>';
            }
        }

        $html .= '
            </tbody>
        </table>';

        $pdf->Ln(12);
        $pdf->writeHTML($html, true, false, true, false, '');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="reporte_lecturas_tomadas.pdf"')
            ->setBody($pdf->Output('reporte_lecturas_tomadas.pdf', 'S'));
    }
}
