<?php

namespace App\Controllers;

use App\Models\ColoniaModel;

class Colonias extends BaseController
{

    private $coloniasModel;

    public function __construct()
    {
        $this->coloniasModel = new ColoniaModel();
    }

    public function index()
    {
        return view('colonias/index');
    }

    public function getColonias()
    {
        try {
            $idDistrito = $this->request->getPost('idDistrito');

            $colonias = $this->coloniasModel->getColoniasPorDistrito($idDistrito);
            // log_message('info', 'colonias obtenidas '. print_r($colonias,true));
            // exit;
            return $this->respondSuccess($colonias);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener las colonias por distrito');
        }
    }

    public function guardarColonia()
    {

        try {
            $idDistrito = $this->request->getPost('idDistrito');
            $nombreColonia = $this->request->getPost('nombreColonia');
            $idUsuario = $_SESSION['id_usuario'];
            // log_message('info', 'id de distrito recibido ' . print_r($idDistrito, true));
            // log_message('info', 'nombre nueva de colonia recibido ' . print_r($nombreColonia, true));

            if (empty($idDistrito) || empty($nombreColonia)) {
                return $this->respondError('El nombre y distrito son requeridos');
            }

            // INICIAR TRANSACCION
            $db = $this->coloniasModel->db;
            $db->transBegin();

            $resultado = $this->coloniasModel->guardarNuevaColonia(
                $idDistrito,
                $nombreColonia,
                $idUsuario
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción de guardar nueva colonia');
                return $this->respondError('No se logro guardar la nueva colonia');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción nueva colonia');
            }

            $db->transCommit();

            log_message('info', 'Colonia registrada correctamente');
            return $this->respondOk('Colonia registrada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error en insertar colonia: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar la nueva colonia');
        }
    }
}
