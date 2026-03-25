<?php

namespace App\Controllers;

use App\Models\RutaModel;

class Rutas extends BaseController
{
    private $rutasModel;

    public function __construct()
    {
        $this->rutasModel = new RutaModel();
    }

    public function index()
    {
        return view('rutas/index');
    }

    public function getRutas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->rutasModel->getTodasRutas($start, $length, $searchValue);

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

    public function nuevaRuta()
    {
        try {
            $codigo = $this->request->getPost('codigo');
            $desde = $this->request->getPost('desde');
            $hasta = $this->request->getPost('hasta');
            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$codigo) {
                log_message('error', 'El código es requerido');
                return $this->respondError('El código es requerido');
            }

            if (!$desde) {
                log_message('error', 'El campo desde es requerido');
                return $this->respondError('El campo desde es requerido');
            }

            if (!$hasta) {
                log_message('error', 'El campo hasta es requerido');
                return $this->respondError('El campo hasta es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->rutasModel->db;
            $db->transBegin();

            $resultado = $this->rutasModel->insertarNuevaRuta(
                $codigo,
                $desde,
                $hasta,
                $fechaCreacion,
                $idUsuario
            );

            if (!$resultado) {
                $errorDB = $db->error();

                // Código MySQL para duplicate entry
                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de ruta ya existe');
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nueva ruta');
                return $this->respondError('No se pudieron guardar los datos de la nueva ruta');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Ruta registrada correctamente');
            return $this->respondOk('Ruta registrada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar la nueva ruta');
        }
    }

    public function editarRuta()
    {
        try {
            $desde = $this->request->getPost('desde');
            $hasta = $this->request->getPost('hasta');
            $idRuta = $this->request->getPost('idRuta');

            if (!$desde) {
                log_message('error', 'El campo desde es requerido');
                return $this->respondError('El campo desde es requerido');
            }

            if (!$desde) {
                log_message('error', 'El campo hasta es requerido');
                return $this->respondError('El campo hasta es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->rutasModel->db;
            $db->transBegin();

            $resultado = $this->rutasModel->actualizarRuta(
                $desde,
                $hasta,
                $idRuta
            );

            if (!$resultado) {
                $db->transRollback();
                log_message('error', 'Error en transacción editar ruta');
                return $this->respondError('No se logro actualizar los datos de la nueva ruta');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Ruta actualizada correctamente');
            return $this->respondOk('Ruta actualizada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizada la ruta');
        }
    }
}
