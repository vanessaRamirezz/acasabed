<?php

namespace App\Controllers;

use App\Models\PeriodoModel;

class Periodos extends BaseController
{
    private $periodosModel;

    public function __construct()
    {
        $this->periodosModel = new PeriodoModel();
    }

    public function index()
    {
        return view('periodos/index');
    }

    public function getPeriodos()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->periodosModel->getTodosPeriodo($start, $length, $searchValue);

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

    public function nuevoPeriodo()
    {
        try {
            $nombrePeriodo = $this->request->getPost('nombre') ?: null;
            $desde = $this->request->getPost('desde') ?: null;
            $hasta = $this->request->getPost('hasta') ?: null;
            // $estado = $this->request->getPost('estado') ?: null;
            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');
            log_message('debug', print_r($this->request->getPost(), true));

            if(!$nombrePeriodo){
                log_message('error', 'El campo nombre de periodo es requerido');
                return $this->respondError('El campo nombre de periodo es requerido');
            }

            if (!$desde) {
                log_message('error', 'El campo fecha desde es requerido');
                return $this->respondError('El campo fecha desde es requerido');
            }

            // if ($estado == '1') {
            //     $estadoNuevo = 'ACTIVO';
            // } else {
            //     $estadoNuevo = 'CERRADO';
            // }

            // aca vamos a validar que no haya un periodo activo al crear uno nuevo
            $periodoActivo = $this->periodosModel
                ->where('estado', 'Activo')
                ->get()
                ->getRow();
            
            if($periodoActivo){
                log_message('info', 'Ya existe un periodo activo');
                return $this->respondError('Ya existe un periodo activo');
            }
            
            $estadoNuevo = 'ACTIVO';

            // INICIAR TRANSACCIÓN
            $db = $this->periodosModel->db;
            $db->transBegin();

            $resultado = $this->periodosModel->insertarNuevoPeriodo(
                $nombrePeriodo,
                $desde,
                $hasta,
                $estadoNuevo,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo periodo');
                return $this->respondError('No se pudieron guardar los datos del nuevo periodo');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Periodo registrado correctamente');
            return $this->respondOk('Periodo registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar el periodo');
        }
    }

    public function editarPeriodo()
    {
        try {
            $nombrePeriodo = $this->request->getPost('nombre') ?: null;
            $desde = $this->request->getPost('desde') ?: null;
            $hasta = $this->request->getPost('hasta') ?: null;
            $estado = $this->request->getPost('estado') ?: null;
            $idPeriodo = $this->request->getPost('idPeriodo');
            log_message('debug', print_r($this->request->getPost(), true));

            if(!$nombrePeriodo){
                log_message('error', 'El campo nombre de periodo es requerido');
                return $this->respondError('El campo nombre de periodo es requerido');
            }

            if (!$desde) {
                log_message('error', 'El campo fecha desde es requerido');
                return $this->respondError('El campo fecha desde es requerido');
            }

            if ($estado == '1') {
                $estadoNuevo = 'ACTIVO';
            } else {
                $estadoNuevo = 'CERRADO';
            }
            // INICIAR TRANSACCIÓN
            $db = $this->periodosModel->db;
            $db->transBegin();

            $resultado = $this->periodosModel->actualizarPeriodo(
                $nombrePeriodo,
                $desde,
                $hasta,
                $estadoNuevo,
                $idPeriodo
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción editar periodo');
                return $this->respondError('No se pudieron editar los datos del periodo');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Periodo actualizado correctamente');
            return $this->respondOk('Periodo actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar el periodo');
        }
    }
}
