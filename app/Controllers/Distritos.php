<?php

namespace App\Controllers;

use App\Models\DistritoModel;

class Distritos extends BaseController
{

    private $distritosModel;

    public function __construct()
    {
        $this->distritosModel = new DistritoModel();
    }

    public function index()
    {
        try {
            $idMunicipio = $this->request->getPost('idMunicipio');

            $distritos = $this->distritosModel->getDistritosPorMunicipios($idMunicipio);
            // log_message('info', 'distritos obtenidos '. print_r($distritos,true));
            // exit;
            return $this->respondSuccess($distritos);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los distritos');
        }
    }
}
