<?php

namespace App\Controllers;

use App\Models\PerfilModel;
use App\Models\UsuarioModel;

class Usuarios extends BaseController
{
    private $usuariosModel;
    private $perfilesModel;
    private $encrypter;

    public function __construct()
    {
        $this->usuariosModel = new UsuarioModel();
        $this->perfilesModel = new PerfilModel();

        $this->encrypter = \Config\Services::encrypter();
    }

    public function index()
    {
        $data['perfiles'] = $this->perfilesModel->findAll();
        return view('usuarios/index', $data);
    }

    public function getUsuarios()
    {
        try {
            $usuarios = $this->usuariosModel->obtenerUsuarios();
            // log_message('debug', print_r($usuarios, true));
            return $this->respondSuccess($usuarios);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los usuarios');
        }
    }

    public function editarUsuario()
    {
        try {

            $dui = $this->request->getPost('dui');
            $nombres = $this->request->getPost('nombres');
            $apellidos = $this->request->getPost('apellidos');
            $correo = $this->request->getPost('correo') ?: null;
            $perfil = $this->request->getPost('perfil');
            $telefono = $this->request->getPost('telefono') ?: null;
            $clave = $this->request->getPost('clave');
            $idUsuario = $this->request->getPost('idUsuario');

            $contrasena = base64_encode($this->encrypter->encrypt($clave));

            // INICIAR TRANSACCIÓN
            $db = $this->usuariosModel->db;
            $db->transBegin();

            $resultado  = $this->usuariosModel->actualizarUsuario(
                $idUsuario,
                $dui,
                $nombres,
                $apellidos,
                $correo,
                $perfil,
                $telefono,
                $contrasena
            );

            // verificar si falló
            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción editar Usuario');
                return $this->respondError('No se pudieron actualizar los datos del usuario');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Los datos del usuario se actualizaron correctamente. ID: ' . $idUsuario);
            return $this->respondOk('Los datos del usuario se actualizaron correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al editar usuario');
        }
    }

    public function nuevoUsuario()
    {
        try {
            $dui = $this->request->getPost('dui');
            $nombres = $this->request->getPost('nombres');
            $apellidos = $this->request->getPost('apellidos');
            $correo = $this->request->getPost('correo') ?: null;
            $perfil = $this->request->getPost('perfil');
            $telefono = $this->request->getPost('telefono') ?: null;
            $clave = $this->request->getPost('clave');
            $contrasena = base64_encode($this->encrypter->encrypt($clave));
            $fecha = date('Y-m-d H:i:s');

            // INICIAR TRANSACCIÓN
            $db = $this->usuariosModel->db;
            $db->transBegin();

            $resultado = $this->usuariosModel->insertarNuevoUsuario(
                $dui,
                $nombres,
                $apellidos,
                $correo,
                $perfil,
                $telefono,
                $contrasena,
                $fecha
            );

            // verificar si falló
            if (!$resultado) {

                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El DUI ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo Usuario');
                return $this->respondError('No se pudieron guardar los datos del nuevo usuario');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Usuario registrado correctamente');
            return $this->respondOk('Usuario registrado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar usuario');
        }
    }

    public function actualizarEstadoUsuario()
    {
        try {
            $idUsuario    = $this->request->getPost('idUsuario');
            $nuevoEstado = $this->request->getPost('nuevoEstado');

            // Log para depuración
            // log_message('debug', 'POST recibido en actualizarEstado -> id: {id}, Estado: {activo}', [
            //     'id'    => $idUsuario,
            //     'activo' => $nuevoEstado
            // ]);


            if (!$idUsuario || !$nuevoEstado) {
                log_message('error', 'Datos incompletos en actualizarEstado. DUI: {dui}, Estado: {estado}', [
                    'dui'    => $idUsuario,
                    'activo' => $nuevoEstado
                ]);

                return $this->respondError('Datos incompletos');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->usuariosModel->db;
            $db->transBegin();

            $resultado = $this->usuariosModel->actualizarEstado($idUsuario, $nuevoEstado);

            // verificar si falló
            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción de actualizara estado de Usuario');
                return $this->respondError('No se logro actualizar el estado del usuario');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción actualizar estado usuario');
            }

            $db->transCommit();

            log_message('info', 'Estado actualizado correctamente para id: ' . $idUsuario);
            return $this->respondOk('Estado actualizado correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizar el estado del usuario desde catch');
        }
    }
}
