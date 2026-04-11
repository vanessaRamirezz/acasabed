<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use Dompdf\Dompdf;
use NumberFormatter;

class Contratos extends BaseController
{
    private $contratosModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
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
        ];

        $html = view('contratos/contrato', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dompdf->stream("contrato.pdf", ["Attachment" => false]);
    }

    
}
