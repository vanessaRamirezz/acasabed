<?php

namespace App\Controllers;

use App\Models\MunicipioModel;

class Municipios extends BaseController
{

    private $municipiosModel;

    public function __construct()
    {
        $this->municipiosModel = new MunicipioModel();
    }

    public function index()
    {
        try {
            $idDepartamento = $this->request->getPost('idDepartamento');

            $municipios = $this->municipiosModel->getMunicipiosPorDepartamento($idDepartamento);
            // log_message('info', 'municipios obtenidos '. print_r($municipios,true));
            // exit;
            return $this->respondSuccess($municipios);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los municipios');
        }
    }
}
