<?php

namespace App\Controllers;

use App\Models\TarifaModel;
use App\Models\TipoClienteModel;

class Tarifario extends BaseController
{
    private $tiposClienteModel;
    private $tarifasModel;

    public function __construct()
    {
        $this->tiposClienteModel = new TipoClienteModel();
        $this->tarifasModel = new TarifaModel();
    }

    public function index()
    {
        $data['tipoClientes'] = $this->tiposClienteModel->findAll();
        return view('tarifario/index', $data);
    }

    public function getTarifas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->tarifasModel->getTodasTarifas($start, $length, $searchValue);

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

    public function nuevaTarifa()
    {
        try {
            $codigo = $this->request->getPost('codigo');
            $tipoCliente = $this->request->getPost('tipoCliente');
            $valorMetro = $this->request->getPost('valorMetro');
            $pagoMinimo = $this->request->getPost('pagoMinimo') ?: null;
            $desde = $this->request->getPost('desde');
            $hasta = $this->request->getPost('hasta') ?: null;
            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$codigo) {
                log_message('error', 'El código es requerido');
                return $this->respondError('El código es requerido');
            }

            if (!$tipoCliente) {
                log_message('error', 'El campo Tipo de cliente es requerido');
                return $this->respondError('El campo Tipo de cliente es requerido');
            }

            if (!$valorMetro) {
                log_message('error', 'El campo valor de metro es requerido');
                return $this->respondError('El campo valor de metrp es requerido');
            }


            // INICIAR TRANSACCIÓN
            $db = $this->tarifasModel->db;
            $db->transBegin();

            $resultado = $this->tarifasModel->insertarNuevaTarifa(
                $codigo,
                $tipoCliente,
                $valorMetro,
                $desde,
                $hasta,
                $pagoMinimo,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de tarifa ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nueva tarifa');
                return $this->respondError('No se pudieron guardar los datos de la nueva tarifa');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Tarifa registrada correctamente');
            return $this->respondOk('Tarifa registrada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar la tarifa');
        }
    }

    public function editarTarifa()
    {
        try {
            $codigo = $this->request->getPost('codigo');
            $tipoCliente = $this->request->getPost('tipoCliente');
            $valorMetro = $this->request->getPost('valorMetro');
            $pagoMinimo = $this->request->getPost('pagoMinimo') ?: null;
            $desde = $this->request->getPost('desde');
            $hasta = $this->request->getPost('hasta') ?: null;
            $idTarifa = $this->request->getPost('idTarifa');

            if (!$codigo) {
                log_message('error', 'El código es requerido');
                return $this->respondError('El código es requerido');
            }

            if (!$tipoCliente) {
                log_message('error', 'El campo Tipo de cliente es requerido');
                return $this->respondError('El campo Tipo de cliente es requerido');
            }

            if (!$valorMetro) {
                log_message('error', 'El campo valor de metro es requerido');
                return $this->respondError('El campo valor de metrp es requerido');
            }


            // INICIAR TRANSACCIÓN
            $db = $this->tarifasModel->db;
            $db->transBegin();

            $resultado = $this->tarifasModel->actualizarTarifa(
                $tipoCliente,
                $valorMetro,
                $desde,
                $hasta,
                $pagoMinimo,
                $idTarifa
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de tarifa ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción editar tarifa');
                return $this->respondError('No se pudieron actualizar los datos de la tarifa');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Tarifa actualizada correctamente');
            return $this->respondOk('Tarifa actualizada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar la tarifa');
        }
    }
}
