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
            "numeroContrato" => $request->getPost('numeroContrato'),
            "edadRepresentante" => '43',

            "nombre" => $request->getPost('nombre'),
            "edad" => $request->getPost('edad'),
            "dui" => $request->getPost('dui'),
            "montoNumero" => $request->getPost('monto'),
            "montoTexto" => $text,

            "nombreFirmante1" => $request->getPost('nombreFirmante1'),
            "rolFirmante1" => $request->getPost('puestoFirmante1'),

            "nombreFirmante2" => $request->getPost('nombreFirmante2'),
            "rolFirmante2" => $request->getPost('puestoFirmante2'),

            "nombreFirmante3" => $request->getPost('nombreFirmante3'),
            "rolFirmante3" => $request->getPost('puestoFirmante3'),
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

            "nombreFirmante1" => $data['nombreFirmante1'] ?? '',
            "rolFirmante1" => $data['rolFirmante1'] ?? '',

            "nombreFirmante2" => $data['nombreFirmante2'] ?? '',
            "rolFirmante2" => $data['rolFirmante2'] ?? '',

            "nombreFirmante3" => $data['nombreFirmante3'] ?? '',
            "rolFirmante3" => $data['rolFirmante3'] ?? '',
        ];

        $html = view('contratos/contrato', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dompdf->stream("contrato.pdf", ["Attachment" => false]);
    }
}
