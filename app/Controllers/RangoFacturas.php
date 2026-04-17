<?php

namespace App\Controllers;

use App\Models\RangoFacturaModel;

class RangoFacturas extends BaseController
{
    private $rangoFacturasModel;

    public function __construct()
    {
        $this->rangoFacturasModel = new RangoFacturaModel();
    }

    public function index()
    {
        return view('rango_de_facturas/index');
    }

    public function guardarRango()
    {
        try {
            log_message('debug', print_r($this->request->getPost(), true));
            // exit;

            $numeroInicio = $this->request->getPost('numeroInicio') ?: null;
            $numeroFinal = $this->request->getPost('numeroFin') ?: null;
            $estado = 'Activo';
            $fechaCreacion = date('Y-m-d H:i:s');
            $idUsuario = $_SESSION['id_usuario'];

            if (!$numeroInicio) {
                log_message('error', 'El numero de inicio es requerido');
                return $this->respondError('El campo numero de inicio es requerido');
            }

            if (!$numeroFinal) {
                log_message('error', 'El campo numero final es requerido');
                return $this->respondError('El campo numero final es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->rangoFacturasModel->db;
            $db->transBegin();

            $resultado = $this->rangoFacturasModel->insertarRango(
                $numeroInicio,
                $numeroFinal,
                $estado,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo rango de facturas');
                return $this->respondError('No se pudieron guardar los datos del nuevo rango de facturas');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Rango de facturas registrado correctamente');
            return $this->respondOk('Rango de facturas registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar el rango de facturas');
        }
    }

    public function getRangoFacturas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->rangoFacturasModel->getRangosFacturas($start, $length, $searchValue);

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
