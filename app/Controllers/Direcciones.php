<?php

namespace App\Controllers;

use App\Models\DireccionModel;

class Direcciones extends BaseController
{

    private $direccionesModel;

    public function __construct()
    {
        $this->direccionesModel = new DireccionModel();
    }

    public function index()
    {
        return view('direcciones/index');
    }

    public function getDirecciones()
    {
        try {
            $idDistrito = $this->request->getPost('idDistrito');

            $direccion = $this->direccionesModel->getDireccionesPorDistrito($idDistrito);
            // log_message('info', 'colonias obtenidas '. print_r($colonias,true));
            // exit;
            return $this->respondSuccess($direccion);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener las direcciones por distrito');
        }
    }

    public function guardarDireccion()
    {

        try {
            $idDistrito = $this->request->getPost('idDistrito');
            $nombreDireccion = $this->request->getPost('nombreDireccion');
            $idUsuario = $_SESSION['id_usuario'];
            // log_message('info', 'id de distrito recibido ' . print_r($idDistrito, true));
            // log_message('info', 'nombre nueva direccion recibido ' . print_r($nombreDireccion, true));

            if (empty($idDistrito) || empty($nombreDireccion)) {
                return $this->respondError('El nombre y distrito son requeridos');
            }

            // INICIAR TRANSACCION
            $db = $this->direccionesModel->db;
            $db->transBegin();

            $resultado = $this->direccionesModel->guardarNuevaDireccion(
                $idDistrito,
                $nombreDireccion,
                $idUsuario
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción de guardar nueva dirección');
                return $this->respondError('No se logro guardar la nueva dirección');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción nueva dirección');
            }

            $db->transCommit();

            log_message('info', 'Zona registrada correctamente');
            return $this->respondOk('Zona registrada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error en insertar dirección: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar la nueva zona');
        }
    }
}
