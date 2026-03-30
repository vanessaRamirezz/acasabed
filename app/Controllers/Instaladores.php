<?php

namespace App\Controllers;

use App\Models\InstaladorModel;

class Instaladores extends BaseController
{
    private $instaladoresModel;

    public function __construct()
    {
        $this->instaladoresModel = new InstaladorModel();
    }

    public function index()
    {
        return view('instaladores/index');
    }

    public function getInstaladores()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->instaladoresModel->getTodosInstaladores($start, $length, $searchValue);

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

    public function nuevoInstalador()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $telefono = $this->request->getPost('telefono') ?: null;
            $dui = $this->request->getPost('dui');
            $direccion = $this->request->getPost('direccion') ?: null;
            $correo = $this->request->getPost('correo') ?: null;
            $fechaCreacion = date('Y-m-d H:i:s');
            $idUsuario = $_SESSION['id_usuario'];

            if (!$nombre) {
                log_message('error', 'El nombre es requerido');
                return $this->respondError('El nombre es requerido');
            }

            if (!$dui) {
                log_message('error', 'El campo DUI es requerido');
                return $this->respondError('El campo DUI es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->instaladoresModel->db;
            $db->transBegin();

            $resultado = $this->instaladoresModel->insertarNuevoInstalador(
                $nombre,
                $telefono,
                $dui,
                $direccion,
                $correo,
                $fechaCreacion,
                $idUsuario
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El DUI ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo instalador');
                return $this->respondError('No se pudieron guardar los datos del nuevo instalador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Instalador registrado correctamente');
            return $this->respondOk('Instalador registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar el nuevo instalador');
        }
    }

    public function editarInstalador()
    {
        try {
            $nombre = $this->request->getPost('nombre');
            $telefono = $this->request->getPost('telefono') ?: null;
            $dui = $this->request->getPost('dui');
            $direccion = $this->request->getPost('direccion') ?: null;
            $correo = $this->request->getPost('correo') ?: null;
            $idInstalador = $this->request->getPost('idInstalador');

            if (!$nombre) {
                log_message('error', 'El nombre es requerido');
                return $this->respondError('El nombre es requerido');
            }

            if (!$dui) {
                log_message('error', 'El campo DUI es requerido');
                return $this->respondError('El campo DUI es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->instaladoresModel->db;
            $db->transBegin();

            $resultado = $this->instaladoresModel->actualizarInstalador(
                $nombre,
                $telefono,
                $dui,
                $direccion,
                $correo,
                $idInstalador
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El DUI ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción actualizar instalador');
                return $this->respondError('No se pudieron actualizar los datos del instalador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Instalador actualizado correctamente');
            return $this->respondOk('Instalador actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar el instalador');
        }
    }

    public function actualizarEstadoInstalador()
    {
        try {
            $idInstalador    = $this->request->getPost('idInstalador');
            $nuevoEstado = $this->request->getPost('nuevoEstado');

            // Log para depuración
            // log_message('debug', 'POST recibido en actualizarEstado -> id: {id}, Estado: {activo}', [
            //     'id'    => $idUsuario,
            //     'activo' => $nuevoEstado
            // ]);


            if (!$idInstalador || !$idInstalador) {
                log_message('error', 'Datos incompletos en actualizarEstado. ID: {id}, Estado: {estado}', [
                    'id'    => $idInstalador,
                    'activo' => $nuevoEstado
                ]);

                return $this->respondError('Datos incompletos');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->instaladoresModel->db;
            $db->transBegin();

            $resultado = $this->instaladoresModel->actualizarEstado($idInstalador, $nuevoEstado);

            // verificar si falló
            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción de actualizara estado de Instalador');
                return $this->respondError('No se logro actualizar el estado del Instalador');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción actualizar estado Instalador');
            }

            $db->transCommit();

            log_message('info', 'Estado actualizado correctamente para id: ' . $idInstalador);
            return $this->respondOk('Estado actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar el estado del instalador desde catch');
        }
    }
}
