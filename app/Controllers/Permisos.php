<?php

namespace App\Controllers;

use App\Models\PerfilModel;

class Permisos extends BaseController
{
    private $perfilesModel;

    public function __construct()
    {
        $this->perfilesModel = new PerfilModel();
    }

    public function index()
    {
        return view('permisos/index');
    }

    public function getPerfiles()
    {
        try {
            $perfiles = $this->perfilesModel->findAll();

            // Recorre cada perfil para obtener sus accesos
            foreach ($perfiles as &$perfil) {
                $idPerfil = $perfil['id_perfil'];  // Obtiene el id_perfil actual
                $datosAcceso = $this->perfilesModel->getAccesosXPerfiles($idPerfil);  // Obtiene los accesos para el perfil actual
                $perfil['accesos'] = $datosAcceso;  // Añade los accesos al array de perfil
            }
            // log_message("info", "accesos por perfil " . print_r($perfiles, true));

            return $this->respondSuccess($perfiles);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los perfiles');
        }
    }

    public function getAccesos()
    {
        try {
            $accesos = $this->perfilesModel->accesosAll();  // Obtiene todos los perfiles

            // Devuelve todos los perfiles con sus accesos
            return $this->respondSuccess($accesos);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Al parecer hay un error al recuperar los datos de acceso.');
        }
    }

    public function editarPermisos()
    {
        try {
            $idPerfil = $this->request->getPost('idPerfil');
            $nPermisos = $this->request->getPost('accesos') ?? [];

            $dataAccesos = $this->perfilesModel->getAccesosXPerfiles($idPerfil);
            $currentPermissions = array_column($dataAccesos, 'id_acceso');

            $toInsert = array_diff($nPermisos, $currentPermissions);
            $toDelete = array_diff($currentPermissions, $nPermisos);

            // INICIAR TRANSACCIÓN
            $db = $this->perfilesModel->db;
            $db->transBegin();

            foreach ($toInsert as $idAcceso) {
                $resultado = $this->perfilesModel->insertAccesoPerfil($idPerfil, $idAcceso);

                if (!$resultado) {
                    $db->transRollback();
                    return $this->respondError('Error al insertar permisos');
                }
            }

            foreach ($toDelete as $idAcceso) {
                $resultado = $this->perfilesModel->deleteAccesoPerfil($idPerfil, $idAcceso);

                if (!$resultado) {
                    $db->transRollback();
                    return $this->respondError('Error al eliminar permisos');
                }
            }

            // verificar si falló
            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', 'Error en transacción editarPermisos');
                return $this->respondError('No se pudieron actualizar los permisos');
            }

            $db->transCommit();

            log_message("info", "Permisos insertados: " . print_r($toInsert, true));
            log_message("info", "Permisos eliminados: " . print_r($toDelete, true));

            return $this->respondOk('Los permisos del perfil se actualizaron correctamente.');
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $e->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $e->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Al parecer hay un error al recuperar los datos.');
        }
    }
}
