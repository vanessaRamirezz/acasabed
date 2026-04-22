<?php

namespace App\Controllers;

// 👇 AGREGA ESTO AQUÍ
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

use App\Models\ContratoModel;

class ReporteContratos extends BaseController
{
    private $contratosModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
    }

    public function index()
    {
        return view('reportes/reporte_contratos');
    }

    public function generarPDF()
    {
        $estado = $this->request->getGet('estado');
        $contratos = $this->contratosModel->getReporteContratos($estado);

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
            font-size: 9px;
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

    <div class="titulo">REPORTE DE CONTRATOS</div>
    <div class="filtro">';

        if ($estado) {
            $html .= 'Filtro aplicado: ' . strtoupper($estado);
        } else {
            $html .= 'Todos los contratos';
        }

        $html .= '</div>

    <br>

    <table>
        <thead>
            <tr>
                <th  align="left">N° Contrato</th>
                <th  align="left">Usuario</th>
                <th  align="left">Ruta</th>
                <th  align="left">Medidor</th>
                <th  align="left">Tarifa</th>
                <th  align="left">Fecha Creación</th>';

        if ($mostrarEstado) {
            $html .= '<th width="15%" align="left">Estado</th>';
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($contratos as $c) {

            // 🔹 Color opcional para estado
            $colorEstado = ($c['estado'] === 'aprobado') ? 'red' : 'green';

            $html .= '<tr>
            <td align="left">' . $c['numero_contrato'] . '</td>
            <td align="left">' . $c['cliente'] . '</td>
            <td align="left">' . $c['ruta'] . '</td>
            <td align="left">' . $c['medidor'] . '</td>
            <td align="left">' . $c['tarifa'] . '</td>
            <td align="left">' . $c['fechaInicio'] . '</td>';

            if ($mostrarEstado) {
                $html .= '<td align="left" style="color:' . $colorEstado . '">'
                    . strtoupper($c['estado']) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        // 🔹 Espacio para no chocar con logo
        $pdf->Ln(15);

        $pdf->writeHTML($html, true, false, true, false, '');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="reporte_contratos.pdf"')
            ->setBody($pdf->Output('reporte_contratos.pdf', 'S'));
    }
}
