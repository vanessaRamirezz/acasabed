<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\TipoClienteModel;

class ReporteClientesPDF extends \TCPDF
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

class ReporteClientes extends BaseController
{
    private $clientesModel;
    private $tiposClientesModel;

    public function __construct()
    {
        $this->clientesModel = new ClienteModel();
        $this->tiposClientesModel = new TipoClienteModel();
    }

    public function index()
    {
        return view('reportes/reporte_clientes', [
            'tiposCliente' => $this->tiposClientesModel->findAll()
        ]);
    }

    public function generarPDF()
    {
        $idDepartamento = $this->request->getGet('departamento');
        $idMunicipio = $this->request->getGet('municipio');
        $idDistrito = $this->request->getGet('distrito');
        $idColonia = $this->request->getGet('colonia');
        $idTipoCliente = $this->request->getGet('tipoCliente');

        $clientes = $this->clientesModel->getReporteClientesPorDireccion(
            $idDepartamento,
            $idMunicipio,
            $idDistrito,
            $idColonia,
            $idTipoCliente
        );

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = new ReporteClientesPDF();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $logo = FCPATH . 'dist/img/agua.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, 10, 10, 22);
        }

        $filtrosAplicados = [];

        if (!empty($clientes)) {
            $primero = $clientes[0];

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

            if (!empty($idTipoCliente) && $idTipoCliente !== '-1' && !empty($primero['tipo_cliente'])) {
                $filtrosAplicados[] = 'Tipo de cliente: ' . $primero['tipo_cliente'];
            }
        }

        if (empty($filtrosAplicados)) {
            $filtrosAplicados[] = 'Todos los clientes';
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
        </style>

        <div class="titulo">REPORTE DE CLIENTES</div>
        <div class="filtro">' . esc(implode(' | ', $filtrosAplicados)) . '</div>

        <br>

        <table>
            <thead>
                <tr>
                    <th align="left">No.</th>
                    <th align="left">Código</th>
                    <th align="left">Cliente</th>
                    <th align="left">DUI</th>
                    <th align="left">NIT</th>
                    <th align="left">Teléfono</th>
                    <th align="left">Tipo</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($clientes)) {
            $html .= '
                <tr>
                    <td colspan="8" class="center">No hay clientes para los filtros seleccionados.</td>
                </tr>';
        } else {
            $numero = 1;

            foreach ($clientes as $cliente) {
                $direccion = implode(', ', array_filter([
                    $cliente['departamento'] ?? null,
                    $cliente['municipio'] ?? null,
                    $cliente['distrito'] ?? null,
                    $cliente['colonia'] ?? null,
                    $cliente['complemento_direccion'] ?? null,
                ]));

                $html .= '
                    <tr>
                        <td align="left">' . $numero++ . '</td>
                        <td align="left">' . esc($cliente['codigo'] ?? '-') . '</td>
                        <td align="left">' . esc($cliente['nombre_completo'] ?? '-') . '</td>
                        <td align="left">' . esc($cliente['dui'] ?? '-') . '</td>
                        <td align="left">' . esc($cliente['nit'] ?? '-') . '</td>
                        <td align="left">' . esc($cliente['telefono'] ?? '-') . '</td>
                        <td align="left">' . esc($cliente['tipo_cliente'] ?? '-') . '</td>

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
            ->setHeader('Content-Disposition', 'inline; filename="reporte_clientes.pdf"')
            ->setBody($pdf->Output('reporte_clientes.pdf', 'S'));
    }
}
