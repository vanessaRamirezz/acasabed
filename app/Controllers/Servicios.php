<?php

namespace App\Controllers;

use App\Models\OperacionModel;
use App\Models\ServicioModel;
use App\Models\TipoServicioModel;

class Servicios extends BaseController
{
    private ServicioModel $serviciosModel;
    private OperacionModel $operacionesModel;
    private TipoServicioModel $tipoServicioModel;

    public function __construct()
    {
        $this->serviciosModel = new ServicioModel();
        $this->operacionesModel = new OperacionModel();
        $this->tipoServicioModel = new TipoServicioModel();
    }

    public function index()
    {
        return view('servicios/index');
    }

    public function getServicios()
    {
        try {
            return $this->respondSuccess($this->serviciosModel->getServiciosMantenimiento());
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al obtener los servicios');
        }
    }

    public function getOperacionesServicio()
    {
        try {
            return $this->respondSuccess($this->operacionesModel->getOperaciones());
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al obtener las operaciones');
        }
    }

    public function getTipoServicio()
    {
        try {
            return $this->respondSuccess($this->tipoServicioModel->getTipos());
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al obtener los tipos de servicio');
        }
    }

    public function nuevoServicio()
    {
        $db = $this->serviciosModel->db;

        try {
            $codigo = trim((string)$this->request->getPost('codigo'));
            $nombre = trim((string)$this->request->getPost('nombre'));
            $valor = (float)$this->request->getPost('valor');
            $tipo = $this->request->getPost('tipo');
            $tipo = (empty($tipo) || (float)$tipo == 0) ? null : (float)$tipo;

            $idOperacion = (int)$this->request->getPost('operacion');
            $estado = 'Activo';

            if ($codigo === '') {
                return $this->respondError('El código es requerido');
            }

            if ($nombre === '') {
                return $this->respondError('El nombre es requerido');
            }

            if ($valor < 0) {
                return $this->respondError('El valor no puede ser negativo');
            }

            if ($idOperacion <= 0) {
                return $this->respondError('Debes seleccionar una operación');
            }

            $db->transBegin();

            $resultado = $this->serviciosModel->insertarNuevoServicio(
                $codigo,
                $nombre,
                $valor,
                $estado,
                $idOperacion,
                $tipo
            );

            if (!$resultado) {
                $errorDB = $db->error();

                if (($errorDB['code'] ?? null) == 1062) {
                    $db->transRollback();
                    return $this->respondError('El código del servicio ya existe');
                }

                $db->transRollback();
                return $this->respondError('No se pudo guardar el servicio');
            }

            $db->transCommit();
            return $this->respondOk('Servicio registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al guardar el servicio');
        }
    }

    public function editarServicio()
    {
        $db = $this->serviciosModel->db;

        try {
            $idServicio = (int)$this->request->getPost('idServicio');
            $codigo = trim((string)$this->request->getPost('codigo'));
            $nombre = trim((string)$this->request->getPost('nombre'));
            $valor = (float)$this->request->getPost('valor');
            $tipo = $this->request->getPost('tipo');
            $tipo = (empty($tipo) || (float)$tipo == 0) ? null : (float)$tipo;
            $idOperacion = (int)$this->request->getPost('operacion');
            $estadoForm = $this->request->getPost('estado');
            $estado = $estadoForm === '0' ? 'Inactivo' : 'Activo';

            if ($idServicio <= 0) {
                return $this->respondError('Servicio inválido');
            }

            if ($codigo === '') {
                return $this->respondError('El código es requerido');
            }

            if ($nombre === '') {
                return $this->respondError('El nombre es requerido');
            }

            if ($valor < 0) {
                return $this->respondError('El valor no puede ser negativo');
            }

            if ($idOperacion <= 0) {
                return $this->respondError('Debes seleccionar una operación');
            }

            $db->transBegin();

            $resultado = $this->serviciosModel->actualizarServicio(
                $idServicio,
                $codigo,
                $nombre,
                $valor,
                $estado,
                $idOperacion,
                $tipo
            );

            if (!$resultado) {
                $errorDB = $db->error();

                if (($errorDB['code'] ?? null) == 1062) {
                    $db->transRollback();
                    return $this->respondError('El código del servicio ya existe');
                }

                $db->transRollback();
                return $this->respondError('No se pudo actualizar el servicio');
            }

            $db->transCommit();
            return $this->respondOk('Servicio actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al actualizar el servicio');
        }
    }
}
