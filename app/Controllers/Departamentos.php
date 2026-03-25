<?php

namespace App\Controllers;

use App\Models\DepartamentoModel;

class Departamentos extends BaseController
{

    private $departamentosModel;

    public function __construct()
    {
        $this->departamentosModel = new DepartamentoModel();
    }

    public function index()
    {
        try {
            $departamentos = $this->departamentosModel->findAll();
            // log_message('info', 'departamentos obtenidos '. print_r($departamentos,true));
            // exit;
            return $this->respondSuccess($departamentos);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener los departamentos');
        }
    }
}
