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
            if ($sexo == 'Masculino') {
                $sexo = 'Masculino';
            } else {
                $sexo = 'Femenino';
            }
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

            $colonia = $this->request->getPost('colonia') ?: null;
            if ($colonia == '-1') {
                $colonia = null;
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

            if (!$codigo || !is_numeric($codigo)) {
                return $this->respondError('Código inválido');
            }

            // foto de cliente
            $fotoCliente = $this->request->getFile('fotoDeCliente');

            // copiar fotos de dui
            $fotoFrontal = $this->request->getFile('fotoDuiFrontal');
            $fotoReversa = $this->request->getFile('fotoDuiReversa');

            // verificar si realmente vienen archivos
            $hayArchivos = (
                ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) ||
                ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved()) ||
                ($fotoCliente && $fotoCliente->isValid() && !$fotoCliente->hasMoved())
            );


            if (!preg_match('/^[0-9]+$/', $codigo)) {
                return $this->respondError('Código inválido para ruta');
            }
            $ruta = FCPATH . 'Documentos/' . trim($codigo);

            if ($hayArchivos && !is_dir($ruta)) {
                mkdir($ruta, 0777, true);
            }

            $nombreFrontal = null;
            $nombreReversa = null;
            $foto = null;

            // FOTO DE CLIENTE
            if ($fotoCliente && $fotoCliente->isValid() && !$fotoCliente->hasMoved()) {
                $foto = 'cliente_' . 'codigo' . '_' . $codigo . '.' . $fotoCliente->getExtension();
                $fotoCliente->move($ruta, $foto);

                $archivosGuardados[] = $ruta . '/' . $foto;
            }

            // FOTO FRONTAL
            if ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) {
                // $nombreFrontal = 'dui_frontal_' . time() . '.' . $fotoFrontal->getExtension();
                $nombreFrontal = 'dui_frontal_' . 'codigo' . '_' . $codigo . '.' . $fotoFrontal->getExtension();
                $fotoFrontal->move($ruta, $nombreFrontal);

                $archivosGuardados[] = $ruta . '/' . $nombreFrontal;
            }

            // FOTO REVERSA
            if ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved()) {
                // $nombreReversa = 'dui_reversa_' . time() . '.' . $fotoReversa->getExtension();
                $nombreReversa = 'dui_reversa_' . 'codigo' . '_' . $codigo . '.' . $fotoReversa->getExtension();
                $fotoReversa->move($ruta, $nombreReversa);

                $archivosGuardados[] = $ruta . '/' . $nombreReversa;
            }

            // rutas DB
            $rutaFrontalDB = $nombreFrontal ? 'Documentos/' . $codigo . '/' . $nombreFrontal : null;
            $rutaReversaDB = $nombreReversa ? 'Documentos/' . $codigo . '/' . $nombreReversa : null;
            $rutaFotoDB = $foto ? 'Documentos/' . $codigo . '/' . $foto : null;

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
                $colonia,
                $complementoDireccion,
                $fechaDeVencimientoDui,
                $rutaFrontalDB,
                $rutaReversaDB,
                $comentarios,
                $idUsuario,
                $fechaCreacion,
                $rutaFotoDB
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

    public function getClientes()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->clientesModel->getTodosClientes($start, $length, $searchValue);

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

    public function editarCliente()
    {
        try {
            log_message('debug', 'POST: ' . print_r($this->request->getPost(), true));
            log_message('debug', 'FILES: ' . print_r($_FILES, true));
            // exit;

            $codigo = $this->request->getPost('codigo');
            $idCliente = $this->request->getPost('id-cliente');

            $nombre = $this->request->getPost('nombre') ?: null;
            $sexo = $this->request->getPost('sexo') ?: null;
            $sexo = ($sexo === 'Masculino') ? 'Masculino' : 'Femenino';

            $ocupacion = $this->request->getPost('ocupacion') ?: null;
            $fechaDeNacimiento = $this->request->getPost('fechaDeNacimiento') ?: null;
            $telefonos = $this->request->getPost('telefonos') ?: null;
            $correo = $this->request->getPost('correo') ?: null;
            $dui = $this->request->getPost('dui') ?: null;
            $nit = $this->request->getPost('nit') ?: null;
            $nrc = $this->request->getPost('nrc') ?: null;

            $actividadEconomica = $this->request->getPost('actividadEconomica');

            // limpiar correctamente
            if (
                $actividadEconomica === '' ||
                $actividadEconomica === 'null' ||
                $actividadEconomica === '-1' ||
                $actividadEconomica === null
            ) {
                $actividadEconomica = null;
            }

            $tipoCliente = $this->request->getPost('tipoCliente') ?: null;

            $contactoNombre = $this->request->getPost('contactoNombre') ?: null;
            $contactoDui = $this->request->getPost('contactoDui') ?: null;
            $contactoTelefonos = $this->request->getPost('contactoTelefonos') ?: null;

            $departamentos = $this->request->getPost('departamentos');
            $municipios = $this->request->getPost('municipios');
            $distritos = $this->request->getPost('distritos');
            $colonia = $this->request->getPost('colonia');

            $departamentos = ($departamentos == '-1') ? null : $departamentos;
            $municipios = ($municipios == '-1') ? null : $municipios;
            $distritos = ($distritos == '-1') ? null : $distritos;
            $colonia = ($colonia == '-1') ? null : $colonia;

            $complementoDireccion = $this->request->getPost('complementoDireccion') ?: null;
            $fechaDeVencimientoDui = $this->request->getPost('fechaDeVencimientoDui') ?: null;
            $comentarios = $this->request->getPost('comentarios') ?: null;

            // Obtener cliente actual correctamente
            $clienteActual = $this->clientesModel->obtenerClientePorId($idCliente);

            $rutaFrontalActual = $clienteActual->dui_frontal ?? null;
            $rutaReversaActual = $clienteActual->dui_reverso ?? null;
            $rutaFotoActual = $clienteActual->foto ?? null;

            // foto de cliente
            $fotoCliente = $this->request->getFile('fotoDeCliente');

            // copiar foos de dui
            $fotoFrontal = $this->request->getFile('fotoDuiFrontal');
            $fotoReversa = $this->request->getFile('fotoDuiReversa');

            $hayArchivos = (
                ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) ||
                ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved()) ||
                ($fotoCliente && $fotoCliente->isValid() && !$fotoCliente->hasMoved())
            );

            $rutaFrontalDB = $rutaFrontalActual;
            $rutaReversaDB = $rutaReversaActual;
            $rutaFotoDB = $rutaFotoActual;

            $ruta = FCPATH . 'Documentos/' . $codigo;

            if ($hayArchivos && !is_dir($ruta)) {
                mkdir($ruta, 0777, true);
            }

            $archivosNuevos = [];

            // FOTO DE CLIENTE
            if ($fotoCliente && $fotoCliente->isValid() && !$fotoCliente->hasMoved()) {

                // eliminar anterior si existe
                // if ($rutaFotoActual && file_exists(FCPATH . $rutaFotoActual)) {
                //     unlink(FCPATH . $rutaFotoActual);
                // }

                foreach (glob($ruta . '/cliente_codigo_' . $codigo . '.*') as $file) {
                    unlink($file);
                }

                $foto = 'cliente_codigo_' . $codigo . '.' . $fotoCliente->getExtension();
                $fotoCliente->move($ruta, $foto, true); // 👈 true = overwrite

                $rutaFotoDB = 'Documentos/' . $codigo . '/' . $foto;
            }

            // FRONTAL
            if ($fotoFrontal && $fotoFrontal->isValid() && !$fotoFrontal->hasMoved()) {

                // eliminar anterior si existe
                foreach (glob($ruta . '/dui_frontal_codigo_' . $codigo . '.*') as $file) {
                    unlink($file);
                }
                // if ($rutaFrontalActual && file_exists(FCPATH . $rutaFrontalActual)) {
                //     unlink(FCPATH . $rutaFrontalActual);
                // }

                // $nombreFrontal = 'dui_frontal.' . $fotoFrontal->getExtension();
                $nombreFrontal = 'dui_frontal_codigo_' . $codigo . '.' . $fotoFrontal->getExtension();
                $fotoFrontal->move($ruta, $nombreFrontal, true);

                $rutaFrontalDB = 'Documentos/' . $codigo . '/' . $nombreFrontal;
            }

            // REVERSA
            if ($fotoReversa && $fotoReversa->isValid() && !$fotoReversa->hasMoved()) {

                // if ($rutaReversaActual && file_exists(FCPATH . $rutaReversaActual)) {
                //     unlink(FCPATH . $rutaReversaActual);
                // }

                foreach (glob($ruta . '/dui_reversa_codigo_' . $codigo . '.*') as $file) {
                    unlink($file);
                }

                // $nombreReversa = 'dui_reversa.' . $fotoReversa->getExtension();
                $nombreReversa = 'dui_reversa_codigo_' . $codigo . '.' . $fotoReversa->getExtension();
                $fotoReversa->move($ruta, $nombreReversa, true);

                $rutaReversaDB = 'Documentos/' . $codigo . '/' . $nombreReversa;
            }

            if ($fotoFrontal && $fotoFrontal->isValid()) {

                if ($rutaFrontalActual && file_exists(FCPATH . $rutaFrontalActual)) {
                    unlink(FCPATH . $rutaFrontalActual);
                }
            }

            // TRANSACCIÓN
            $db = $this->clientesModel->db;
            $db->transBegin();

            $resultado = $this->clientesModel->actualizarCliente(
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
                $colonia,
                $complementoDireccion,
                $fechaDeVencimientoDui,
                $rutaFrontalDB,
                $rutaReversaDB,
                $comentarios,
                $rutaFotoDB,
                $idCliente
            );

            if (!$resultado || $db->transStatus() === false) {

                // borrar nuevas si falló
                foreach ($archivosNuevos as $archivo) {
                    if (file_exists($archivo)) unlink($archivo);
                }

                if (is_dir($ruta) && count(scandir($ruta)) == 2) {
                    rmdir($ruta);
                }

                $errorDB = $db->error();
                $db->transRollback(); // SIEMPRE una sola vez aquí

                if ($errorDB['code'] == 1062) {

                    $mensaje = $errorDB['message'];

                    if (strpos($mensaje, 'dui') !== false) {
                        return $this->respondError('El DUI ya está registrado');
                    }

                    if (strpos($mensaje, 'codigo') !== false) {
                        return $this->respondError('El código ya existe');
                    }
                }

                return $this->respondError('Error al actualizar el cliente');
            }

            $db->transCommit();

            // ✅ eliminar viejas SOLO si todo salió bien
            if (isset($nombreFrontal) && $rutaFrontalActual && file_exists(FCPATH . $rutaFrontalActual)) {
                unlink(FCPATH . $rutaFrontalActual);
            }

            if (isset($nombreReversa) && $rutaReversaActual && file_exists(FCPATH . $rutaReversaActual)) {
                unlink(FCPATH . $rutaReversaActual);
            }

            log_message('info', 'Cliente actualizado correctamente');
            return $this->respondOk('Cliente actualizado correctamente.');
        } catch (\Throwable $th) {

            if (!empty($archivosNuevos)) {
                foreach ($archivosNuevos as $archivo) {
                    if (file_exists($archivo)) unlink($archivo);
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
            return $this->respondError('Error al actualizar el cliente');
        }
    }
}
