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

        log_message('info', 'solicitud data para ver contrato' . print_r($request->getPost(), true));
        // exit;

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

        log_message('info', 'id de solicitud recibido ' . base64_decode($encoded));
        // exit;

        // traer datos reales aquí
        $data = $this->solicitudesModel->getInfoSolicitudPorId($id);
        log_message('info', 'solicitud data ' . print_r($data, true));
        // exit;
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

        $nombreArchivo = 'Contrato_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['nombre']) . '.pdf';

        $dompdf->stream($nombreArchivo, ["Attachment" => false]);
        // $dompdf->stream("contrato.pdf", ["Attachment" => false]);
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

    public function suspenderContratoUnoaUno()
    {
        try {
            log_message('debug', 'POST en suspender contrato: ' . print_r($this->request->getPost(), true));
            // exit;

            $idContrato = $this->request->getPost('idContrato');
            $motivo = $this->request->getPost('motivo');
            $fechaSuspencion = date('Y-m-d H:i:s');

            if (!$idContrato) {
                log_message('error', 'id de contrato viene vacio');
                return $this->respondError('Id de contrato llego vacio');
            }

            if (!$motivo) {
                log_message('error', 'El motivo es requerido');
                return $this->respondError('El motivo es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->contratosModel->db;
            $db->transBegin();

            $estado = 'SUSPENDIDO';
            $resultado = $this->contratosModel->actualizarEstadoContrato(
                $idContrato,
                $estado,
                $motivo,
                $fechaSuspencion
            );

            if (!$resultado) {

                $db->transRollback();
                log_message('error', 'Error en transacción suspender contrato');
                return $this->respondError('No se logro suspender el contrato');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Contrato Suspendido correctamente');
            return $this->respondOk('Contrato suspendido correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al suspender el contrato');
        }
    }
}
