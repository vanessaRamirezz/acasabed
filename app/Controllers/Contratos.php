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

    public function getContratosTabla()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->contratosModel->getTodosContratos($start, $length, $searchValue);

            return $this->response->setJSON([
                "draw" => $draw,
                "recordsTotal" => $result['total'],
                "recordsFiltered" => $result['filtered'],
                "data" => $result['data']
            ]);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->response->setJSON([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }
    }
}
