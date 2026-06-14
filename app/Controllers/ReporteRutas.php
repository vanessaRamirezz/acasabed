<?php

namespace App\Controllers;

use App\Models\RutaModel;

// AGREGA ESTO AQUÍ
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

class ReporteRutas extends BaseController
{
    private RutaModel $rutasModel;

    public function __construct()
    {
        $this->rutasModel = new RutaModel();
    }

    public function index()
    {
        return view('reportes/reporte_rutas');
    }

    public function reporteRutas()
    {
        try {
            $rutas = $this->rutasModel->findAll();
            log_message('info', 'rutas obtenidas ' . print_r($rutas, true));

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

    <div class="titulo">REPORTE DE RUTAS</div>
    <table>
        <thead>
            <tr>
                <th width="15%" align="left">No.</th>
                <th width="15%" align="left">Fecha Creación</th>
                <th width="15%" align="left">Codigo</th>
                <th width="55%" align="left">Nombre</th>';

            $html .= '</tr>
        </thead>
        <tbody>';

            $numero = 1;
            foreach ($rutas as $r) {

                $fechaFormateada = date('d-m-Y', strtotime($r['fecha_creacion']));

                $html .= '<tr>
            <td width="15%" align="left">' . $numero++ . '</td>
            <td width="15%" align="left">' . $fechaFormateada . '</td>
            <td width="15%" align="left">' . $r['codigo'] . '</td>
            <td width="55%" align="left">' . $r['nombre'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            // 🔹 Espacio para no chocar con logo
            $pdf->Ln(15);

            $pdf->writeHTML($html, true, false, true, false, '');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="reporte_rutas.pdf"')
                ->setBody($pdf->Output('reporte_rutas.pdf', 'S'));
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
