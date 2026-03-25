<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Login extends BaseController
{
    private $encrypter;

    public function __construct()
    {
        $this->encrypter = \Config\Services::encrypter();
    }

    public function index()
    {
        return view('Login/index');
    }

    public function validarUsuario()
    {
        try {
            $usuariosModel = new UsuarioModel();

            $dui = $this->request->getPost('usuario');
            $clave = $this->request->getPost('password');

            $datosUsuario = $usuariosModel->informacionUsuario($dui);

            if (!$datosUsuario) {
                return $this->respondError('Usuario no encontrado');
            }

            if ($datosUsuario['estado'] == 'SI') {
                if (count($datosUsuario) > 0 && $clave === $this->encrypter->decrypt(base64_decode($datosUsuario['clave']))) {

                    $dataAccesos = $usuariosModel->getAccesosUsuario($datosUsuario['dui']);
                    $session = \Config\Services::session();

                    $datosDeSession = [
                        'id_usuario' => $datosUsuario['id_usuario'],
                        'nombres' => $datosUsuario['nombres'],
                        'apellidos' => $datosUsuario['apellidos'],
                        'dui' => $datosUsuario['dui'],
                        'correo' => $datosUsuario['correo'],
                        'telefono' => $datosUsuario['telefono'],
                        'id_perfil' => $datosUsuario['id_perfil'],
                        'estado' => $datosUsuario['estado'],
                        'accesos' => $dataAccesos,
                    ];

                    $session->set($datosDeSession);
                    $session->regenerate();
                    log_message('debug', 'Sesión actual: ' . print_r(session()->get(), true));

                    // Obtener los url_acceso donde orden_acceso es igual a 1
                    $url_acceso = array_column(array_filter($dataAccesos, function ($item) {
                        return $item['orden_acceso'] === '1';
                    }), 'url_acceso');

                    // Verificar si se encontró un URL de acceso con orden_acceso igual a 1
                    if (!empty($url_acceso)) {
                        // Redireccionar a la primera URL encontrada
                        $redirect_url = $url_acceso[0];
                        return $this->response->setJSON([
                            'status' => 'success',
                            'redirect' => base_url($redirect_url),
                        ]);
                    } else {
                        // Si no se encontró ningún URL de acceso válido, puedes manejar esto según tus necesidades
                        return $this->respondError('No se encontró un URL de acceso válido con orden_acceso igual a 1');
                    }
                } else {
                    return $this->respondError('Usuario o contraseña incorrectos');
                }
            } else {
                return $this->respondError('Usuario inactivo');
            }
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->respondError('Error interno del servidor al iniciar Sessión');
        }
    }

    public function salir()
    {
        try {
            $session = session();
            $session->destroy();
            return redirect()->to(base_url('/'));
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);
        }
    }
}
