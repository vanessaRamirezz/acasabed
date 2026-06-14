<?php

namespace App\Controllers;

use App\Models\MedidorModel;

class MYPDF extends \TCPDF
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

class ReporteMedidores extends BaseController
{
    private MedidorModel $medidoresModel;

    public function __construct()
    {
        $this->medidoresModel = new MedidorModel();
    }

    public function index()
    {
        return view('reportes/reporte_medidores');
    }

    public function generarPDF()
    {
        try {
            $estado = $this->request->getGet('estado');
            $medidores = $this->medidoresModel->getReporteMedidores($estado);
            // log_message('info', 'medidores ' . print_r($medidores, true));

            if (ob_get_length()) {
                ob_end_clean();
            }

            // 🔹 Usar clase personalizada
            $pdf = new MYPDF();
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // 🔹 Logo
            $logo = FCPATH . 'dist/img/agua.png';
            $pdf->Image($logo, 10, 10, 25);

            $mostrarEstado = empty($estado);

            // 🧾 HTML
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
            </style>

            <div class="titulo">REPORTE DE MEDIDORES</div>
            <div class="filtro">';

            if ($estado) {
                $html .= 'Filtro aplicado: ' . strtoupper($estado);
            } else {
                $html .= 'Todos los medidores';
            }

            $html .= '</div>

            <br>

            <table>
                <thead>
                    <tr>
                        <th  width="10%" align="left">No. de serie</th>
                        <th  width="10%" align="left">Fecha instalacion</th>
                        <th  width="10%" align="left">Fecha activado</th>
                        <th  width="10%" align="left">Fecha inactivo</th>
                        <th  width="15%" align="left">Contrato</th>
                        <th  width="35%" align="left">Instalador</th>';

            if ($mostrarEstado) {
                $html .= '<th width="10%" align="left">Estado</th>';
            }

            $html .= '</tr>
            </thead>
            <tbody>';

            foreach ($medidores as $c) {

                // 🔹 Color opcional para estado
                $colorEstado = ($c['estado'] === 'INACTIVO') ? 'red' : 'green';

                $html .= '<tr>
            <td width="10%" align="left">' . $c['numero_serie'] . '</td>
            <td width="10%" align="left">' . $c['fecha_de_instalacion_texto'] . '</td>
            <td width="10%" align="left">' . $c['fecha_de_activacion_texto'] . '</td>
            <td width="10%" align="left">' . $c['fecha_de_desactivacion_texto'] . '</td>
            <td width="15%" align="left">' . $c['numero_contrato'] . '</td>
            <td width="35%" align="left">' . $c['nombre_completo'] . '</td>';

                if ($mostrarEstado) {
                    $html .= '<td width="10%" align="left" style="color:' . $colorEstado . '">'
                        . strtoupper($c['estado']) . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            // 🔹 Espacio para no chocar con logo
            $pdf->Ln(15);

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdfContent = $pdf->Output('reporte_medidores.pdf', 'S');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="reporte_medidores.pdf"')
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
