<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\InstaladorModel;
use App\Models\MedidorModel;

class Medidores extends BaseController
{
    private $medidoresModel;
    private $contratosModel;
    private $instaladoresModel;

    public function __construct()
    {
        $this->medidoresModel = new MedidorModel();
        $this->contratosModel = new ContratoModel();
        $this->instaladoresModel = new InstaladorModel();
    }

    public function index()
    {
        return view('medidores/index');
    }

    public function getMedidores()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->medidoresModel->getTodosMedidores($start, $length, $searchValue);

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

    public function getContratos()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $contratos = $this->contratosModel->buscarContratos($search);
            return $this->respondSuccess($contratos);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los contratos');
        }
    }

    public function getSelectInstaladores()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $instaladores = $this->instaladoresModel->buscarInstaladores($search);
            log_message('info', 'datos ' . print_r($instaladores, true));
            // exit;
            return $this->respondSuccess($instaladores);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los instaladores');
        }
    }

    public function nuevoMedidor()
    {
        try {
            log_message('debug', 'POST en nuevo medidor: ' . print_r($this->request->getPost(), true));
            // exit;
            $numeroSerie = $this->request->getPost('numero');

            $fechaInstalacion = $this->request->getPost('fecha');
            $fechaInstalacion = !empty($fechaInstalacion) ? $fechaInstalacion : null;

            $idContrato = $this->request->getPost('contrato');
            $idContrato = !empty($idContrato) ? $idContrato : null;

            $idInstalador = $this->request->getPost('instalador');
            $idInstalador = !empty($idInstalador) ? $idInstalador : null;

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$numeroSerie) {
                log_message('error', 'El numero de serie es requerido');
                return $this->respondError('El numero de serie es requerido');
            }


            // INICIAR TRANSACCIÓN
            $db = $this->medidoresModel->db;
            $db->transBegin();

            $resultado = $this->medidoresModel->insertarNuevoMedidor(
                $numeroSerie,
                $fechaInstalacion,
                $idContrato,
                $idInstalador,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo medidor');
                return $this->respondError('No se pudieron guardar los datos del nuevo medidor');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Medidor registrado correctamente');
            return $this->respondOk('Medidor registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar el nuevo medidor');
        }
    }

    public function editarMedidor()
    {
        try {
            log_message('debug', 'POST en editar medidor: ' . print_r($this->request->getPost(), true));
            // exit;

            $idMedidor = $this->request->getPost('idMedidor');

            $numeroSerie = $this->request->getPost('numero');

            $fechaInstalacion = $this->request->getPost('fecha');
            $fechaInstalacion = !empty($fechaInstalacion) ? $fechaInstalacion : null;

            $idContrato = $this->request->getPost('contrato');
            if (
                $idContrato === '' ||
                $idContrato === 'null' ||
                $idContrato === '-1' ||
                $idContrato === null
            ) {
                $idContrato = null;
            }

            $idInstalador = $this->request->getPost('instalador');
            if (
                $idInstalador === '' ||
                $idInstalador === 'null' ||
                $idInstalador === '-1' ||
                $idInstalador === null
            ) {
                $idInstalador = null;
            }

            if (!$numeroSerie) {
                log_message('error', 'El numero de serie es requerido');
                return $this->respondError('El numero de serie es requerido');
            }


            // INICIAR TRANSACCIÓN
            $db = $this->medidoresModel->db;
            $db->transBegin();

            $resultado = $this->medidoresModel->actualizarMedidor(
                $numeroSerie,
                $fechaInstalacion,
                $idContrato,
                $idInstalador,
                $idMedidor,
            );

            if (!$resultado) {

                $db->transRollback();
                log_message('error', 'Error en transacción editar medidor');
                return $this->respondError('No se pudieron actualizar los datos medidor');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Medidor actualizado correctamente');
            return $this->respondOk('Medidor actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar medidor');
        }
    }
}
