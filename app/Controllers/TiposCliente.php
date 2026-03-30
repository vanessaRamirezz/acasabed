<?php

namespace App\Controllers;

use App\Models\TipoClienteModel;

class TiposCliente extends BaseController
{
    private $tipoClienteModel;

    public function __construct()
    {
        $this->tipoClienteModel = new TipoClienteModel();
    }

    public function index()
    {
        return view('tipos_cliente/index');
    }

    public function getTipoCliente()
    {
        try {
            $tipoCliente = $this->tipoClienteModel->findAll();
            // log_message('debug', print_r($tipoCliente, true));
            return $this->respondSuccess($tipoCliente);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los tipos de cliente');
        }
    }

    public function nuevoTipoCliente()
    {
        try {
            $codigo = $this->request->getPost('codigo');
            $tipoCliente = $this->request->getPost('tipoCliente');
            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$codigo) {
                log_message('error', 'El código esta incompleto');

                return $this->respondError('El código esta incompleto');
            }

            if (!$tipoCliente) {
                log_message('error', 'Tipo de Cliente incompleto');

                return $this->respondError('Tipo de Cliente incompleto');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->tipoClienteModel->db;
            $db->transBegin();

            $resultado = $this->tipoClienteModel->insertarNuevoTipoCliente(
                $codigo,
                $tipoCliente,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de tipo de cliente ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo tipo de cliente');
                return $this->respondError('No se pudieron guardar los datos del nuevo tipo de cliente');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Tipo Cliente registrado correctamente');
            return $this->respondOk('Tipo Cliente registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar nuevo tipo de cliente');
        }
    }

    public function editarTipoCliente()
    {
        try {
            $tipoCliente = $this->request->getPost('tipoCliente');
            $idTipoCliente = $this->request->getPost('idTipoCliente');

            // INICIAR TRANSACCIÓN
            $db = $this->tipoClienteModel->db;
            $db->transBegin();

            $resultado = $this->tipoClienteModel->actualizarTipoCliente(
                $idTipoCliente,
                $tipoCliente
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de tipo de cliente ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción editar tipo de cliente');
                return $this->respondError('No se pudieron actualizar los datos del tipo de cliente');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Tipo Cliente actualizado correctamente');
            return $this->respondOk('Tipo Cliente actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar tipo de cliente');
        }
    }
}
