<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\SolicitudModel;
use Dompdf\Dompdf;
use NumberFormatter;

class Contratos extends BaseController
{
    private $contratosModel;
    private $solicitudesModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
        $this->solicitudesModel = new SolicitudModel();
    }

    public function index()
    {
        return view('contratos/index');
    }

    public function pdf()
    {
        $request = service('request');

        $total = (float) $request->getPost('monto');
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
        $montoEntero = floor($total);
        $montoDecimal = round(($total - $montoEntero) * 100);
        $textEntero = $fmt->format($montoEntero);
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " con " . $textDecimal . "/100 dólares";

        $data = [
            "numeroContrato" => '',
            "edadRepresentante" => '43',

            "nombre" => $request->getPost('nombre'),
            "edad" => $request->getPost('edad'),
            "dui" => $request->getPost('dui'),
            "montoNumero" => $request->getPost('monto'),
            "montoTexto" => $text,

            "nombreAdministrador" => $request->getPost('nombreAdministrador'),
            "nombreComision1" => $request->getPost('nombreComision1'),
            "nombreComision2" => $request->getPost('nombreComision2'),
        ];

        $html = view('contratos/contrato', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dompdf->stream("contrato.pdf", ["Attachment" => false]);
    }
    public function contrato()
    {
        $request = service('request');

        $encoded = $request->getGet('solicitud');

        if (!$encoded) {
            return "ID no recibido";
        }

        $id = base64_decode($encoded);

        // traer datos reales aquí
        $data = $this->solicitudesModel->getInfoSolicitudPorId($id);
        log_message('info', 'solicitud data ' . print_r($data, true));

        $total = $data['monto'] ?? '';

        $fmt = new \NumberFormatter("es_ES", \NumberFormatter::SPELLOUT);
        $montoEntero = floor($total);
        $montoDecimal = round(($total - $montoEntero) * 100);
        $textEntero = $fmt->format($montoEntero);
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " con " . $textDecimal . "/100 dólares";

        $data = [
            "numeroContrato" => $data['numeroContrato'] ?? '',
            "edadRepresentante" => '43',
            "nombre" => $data['nombre'] ?? '',
            "edad" => $data['edad'] ?? '',
            "dui" => $data['dui'] ?? '',
            "montoNumero" => $total,
            "montoTexto" => $text,
            "nombreAdministrador" => $data['nombreAdministrador'] ?? '',
            "nombreComision1" => $data['nombreComision1'] ?? '',
            "nombreComision2" => $data['nombreComision2'] ?? '',
        ];

        $html = view('contratos/contrato', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dompdf->stream("contrato.pdf", ["Attachment" => false]);
    }
}
