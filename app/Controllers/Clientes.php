<?php

namespace App\Controllers;

use App\Models\ActividadEconomicaModel;
use App\Models\ClienteModel;
use App\Models\TipoClienteModel;

class Clientes extends BaseController
{
    private $tiposClientesModel;
    private $actividadesModel;
    private $clientesModel;

    public function __construct()
    {
        $this->tiposClientesModel = new TipoClienteModel();
        $this->actividadesModel = new ActividadEconomicaModel();
        $this->clientesModel = new ClienteModel();
    }

    public function index()
    {
        $data['tipoClientes'] = $this->tiposClientesModel->findAll();
        return view('clientes/index', $data);
    }

    public function getActividades()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $actividades = $this->actividadesModel->buscarActividadEcocomica($search);
            return $this->respondSuccess($actividades);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer las actividades economicas');
        }
    }

    public function nuevoCliente()
    {
        try {
            log_message('debug', 'POST: ' . print_r($this->request->getPost(), true));
            log_message('debug', 'FILES: ' . print_r($_FILES, true));

            $codigo = $this->request->getPost('codigo');
            $nombre = $this->request->getPost('nombre') ?: null;
            $sexo = $this->request->getPost('sexo') ?: null;
            $ocupacion = $this->request->getPost('ocupacion') ?: null;
            $fechaDeNacimiento = $this->request->getPost('fechaDeNacimiento') ?: null;
            $telefonos = $this->request->getPost('telefonos') ?: null;
            $correo = $this->request->getPost('correo') ?: null;
            $dui = $this->request->getPost('dui') ?: null;
            $nit = $this->request->getPost('nit') ?: null;
            $nrc = $this->request->getPost('nrc') ?: null;

            $actividadEconomica = $this->request->getPost('actividadEconomica');
            $actividadEconomica = !empty($actividadEconomica) ? $actividadEconomica : null;

            $tipoCliente = $this->request->getPost('tipoCliente') ?: null;
            $contactoNombre = $this->request->getPost('contactoNombre') ?: null;
            $contactoDui = $this->request->getPost('contactoDui') ?: null;
            $contactoTelefonos = $this->request->getPost('contactoTelefonos') ?: null;

            $departamentos = $this->request->getPost('departamentos') ?: null;
            if ($departamentos == '-1') {
                $departamentos = null;
            }

            $municipios = $this->request->getPost('municipios') ?: null;
            if ($municipios == '-1') {
                $municipios = null;
            }

            $distritos = $this->request->getPost('distritos') ?: null;
            if ($distritos == '-1') {
                $distritos = null;
            }

            $direccion = $this->request->getPost('direccion') ?: null;
            if ($direccion == '-1') {
                $direccion = null;
            }

            $complementoDireccion = $this->request->getPost('complementoDireccion') ?: null;
            $fechaDeVencimientoDui = $this->request->getPost('fechaDeVencimientoDui') ?: null;
            $comentarios = $this->request->getPost('comentarios') ?: null;
            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');


            if (!$codigo) {
                log_message('error', 'El código es requerido');
                return $this->respondError('El código es requerido');
            }

            // copiar fotos de dui
            $fotoFrontal = $this->request->getFile('fotoDuiFrontal');
            $fotoReversa = $this->request->getFile('fotoDuiReversa');

            // verificar si realmente vienen archivos
            $hayArchivos = (
                ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) ||
                ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved())
            );

            $ruta = FCPATH . 'DUI/' . $codigo;

            if ($hayArchivos && !is_dir($ruta)) {
                mkdir($ruta, 0777, true);
            }

            $nombreFrontal = null;
            $nombreReversa = null;

            // FOTO FRONTAL
            if ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) {
                $nombreFrontal = 'dui_frontal_' . time() . '.' . $fotoFrontal->getExtension();
                $fotoFrontal->move($ruta, $nombreFrontal);

                $archivosGuardados[] = $ruta . '/' . $nombreFrontal;
            }

            // FOTO REVERSA
            if ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved()) {
                $nombreReversa = 'dui_reversa_' . time() . '.' . $fotoReversa->getExtension();
                $fotoReversa->move($ruta, $nombreReversa);

                $archivosGuardados[] = $ruta . '/' . $nombreReversa;
            }

            $rutaFrontalDB = $nombreFrontal ? 'DUI/' . $codigo . '/' . $nombreFrontal : null;
            $rutaReversaDB = $nombreReversa ? 'DUI/' . $codigo . '/' . $nombreReversa : null;

            // INICIAR TRANSACCIÓN
            $db = $this->clientesModel->db;
            $db->transBegin();

            $resultado = $this->clientesModel->insertarNuevoCliente(
                $codigo,
                $nombre,
                $sexo,
                $ocupacion,
                $fechaDeNacimiento,
                $telefonos,
                $correo,
                $dui,
                $nit,
                $nrc,
                $actividadEconomica,
                $tipoCliente,
                $contactoNombre,
                $contactoDui,
                $contactoTelefonos,
                $departamentos,
                $municipios,
                $distritos,
                $direccion,
                $complementoDireccion,
                $fechaDeVencimientoDui,
                $rutaFrontalDB,
                $rutaReversaDB,
                $comentarios,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {
                if (isset($archivosGuardados)) {
                    foreach ($archivosGuardados as $archivo) {
                        if (file_exists($archivo)) {
                            unlink($archivo);
                        }
                    }
                }

                if (isset($ruta) && is_dir($ruta) && count(scandir($ruta)) == 2) {
                    rmdir($ruta);
                }
                $errorDB = $db->error();

                if ($errorDB['code'] == 1062) {

                    $mensaje = $errorDB['message'];

                    if (strpos($mensaje, 'codigo') !== false) {
                        $error = 'El código de cliente ya existe';
                    } elseif (strpos($mensaje, 'dui') !== false) {
                        $error = 'El DUI ya está registrado';
                    }

                    $db->transRollback();
                    return $this->respondError($error);
                }

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nuevo cliente');
                return $this->respondError('No se pudieron guardar los datos del nuevo cliente');
            }

            if ($db->transStatus() === false) {
                if (isset($archivosGuardados)) {
                    foreach ($archivosGuardados as $archivo) {
                        if (file_exists($archivo)) {
                            unlink($archivo);
                        }
                    }
                }

                if (isset($ruta) && is_dir($ruta) && count(scandir($ruta)) == 2) {
                    rmdir($ruta);
                }

                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Cliente registrado correctamente');
            return $this->respondOk('Cliente registrado correctamente.');
        } catch (\Throwable $th) {

            if (isset($archivosGuardados)) {
                foreach ($archivosGuardados as $archivo) {
                    if (file_exists($archivo)) {
                        unlink($archivo);
                    }
                }
            }

            if (isset($ruta) && is_dir($ruta) && count(scandir($ruta)) == 2) {
                rmdir($ruta);
            }

            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar el cliente');
        }
    }
}
