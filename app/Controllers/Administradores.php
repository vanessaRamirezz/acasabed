<?php

namespace App\Controllers;

use App\Models\AdministradorModel;
use App\Models\FirmanteModel;

class Administradores extends BaseController
{
    private AdministradorModel $administradoresModel;
    private FirmanteModel $firmantesModel;

    public function __construct()
    {
        $this->administradoresModel = new AdministradorModel();
        $this->firmantesModel = new FirmanteModel();
    }

    public function index()
    {
        return view('administradores/index');
    }

    public function getAdministradores()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->administradoresModel->getTodosAdministradores($start, $length, $searchValue);

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

    public function nuevoAdministrador()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $rol = $this->request->getPost('rol') ?: null;
            $ocupacion = $this->request->getPost('ocupacion') ?: null;
            $direccion = $this->request->getPost('direccion') ?: null;
            $edad = $this->request->getPost('edad') ?: null;
            $dui = $this->request->getPost('dui');
            $fechaCreacion = date('Y-m-d H:i:s');
            $idUsuario = $_SESSION['id_usuario'];

            if (!$nombre) {
                return $this->respondError('El nombre es requerido');
            }

            if (!$dui) {
                return $this->respondError('El campo DUI es requerido');
            }

            $db = $this->administradoresModel->db;
            $db->transBegin();

            $resultado = $this->administradoresModel->insertarNuevoAdministrador(
                $nombre,
                $rol,
                $ocupacion,
                $direccion,
                $edad,
                $dui,
                $fechaCreacion,
                $idUsuario
            );

            if (!$resultado) {
                $errorDB = $db->error();

                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El DUI ya existe');
                }

                $db->transRollback();
                return $this->respondError('No se pudieron guardar los datos del administrador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Administrador registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al guardar el administrador');
        }
    }

    public function editarAdministrador()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $rol = $this->request->getPost('rol') ?: null;
            $ocupacion = $this->request->getPost('ocupacion') ?: null;
            $direccion = $this->request->getPost('direccion') ?: null;
            $edad = $this->request->getPost('edad') ?: null;
            $dui = $this->request->getPost('dui');
            $idAdministrador = $this->request->getPost('idAdministrador');

            if (!$nombre) {
                return $this->respondError('El nombre es requerido');
            }

            if (!$dui) {
                return $this->respondError('El campo DUI es requerido');
            }

            $db = $this->administradoresModel->db;
            $db->transBegin();

            $resultado = $this->administradoresModel->actualizarAdministrador(
                $idAdministrador,
                $nombre,
                $rol,
                $ocupacion,
                $direccion,
                $edad,
                $dui
            );

            if (!$resultado) {
                $errorDB = $db->error();

                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El DUI ya existe');
                }

                $db->transRollback();
                return $this->respondError('No se pudieron actualizar los datos del administrador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Administrador actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al actualizar el administrador');
        }
    }

    public function actualizarEstadoAdministrador()
    {
        try {
            $idAdministrador = $this->request->getPost('idAdministrador');
            $nuevoEstado = $this->request->getPost('nuevoEstado');

            if (!$idAdministrador || !$nuevoEstado) {
                return $this->respondError('Datos incompletos');
            }

            $db = $this->administradoresModel->db;
            $db->transBegin();

            $resultado = $this->administradoresModel->actualizarEstado($idAdministrador, $nuevoEstado);

            if (!$resultado) {
                $db->transRollback();
                return $this->respondError('No se logró actualizar el estado del administrador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Estado actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al actualizar el estado del administrador');
        }
    }

    public function getFirmantes()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->firmantesModel->getTodosFirmantes($start, $length, $searchValue);

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

    public function nuevoFirmante()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $rol = $this->request->getPost('rol');

            if (!$nombre) {
                return $this->respondError('El nombre es requerido');
            }

            if (!$rol) {
                return $this->respondError('El rol es requerido');
            }

            $db = $this->firmantesModel->db;
            $db->transBegin();

            $resultado = $this->firmantesModel->insertarFirmantes($nombre, $rol);

            if (!$resultado) {
                $db->transRollback();
                return $this->respondError('No se pudieron guardar los datos del firmante');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Firmante registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al guardar el firmante');
        }
    }

    public function editarFirmante()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $rol = $this->request->getPost('rol');
            $idFirmante = $this->request->getPost('idFirmante');

            if (!$nombre) {
                return $this->respondError('El nombre es requerido');
            }

            if (!$rol) {
                return $this->respondError('El rol es requerido');
            }

            $db = $this->firmantesModel->db;
            $db->transBegin();

            $resultado = $this->firmantesModel->actualizarFirmante($idFirmante, $nombre, $rol);

            if (!$resultado) {
                $db->transRollback();
                return $this->respondError('No se pudieron actualizar los datos del firmante');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Firmante actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());
            return $this->respondError('Error al actualizar el firmante');
        }
    }
}
