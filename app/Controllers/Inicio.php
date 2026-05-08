<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\ContratoModel;
use App\Models\FacturaModel;
use App\Models\PeriodoModel;
use App\Models\RutaModel;
use App\Models\SolicitudModel;

class Inicio extends BaseController
{
    private PeriodoModel $periodosModel;
    private ClienteModel $clientesModel;
    private RutaModel $rutasModel;
    private ContratoModel $contratosModel;
    private SolicitudModel $solicitudesModel;
    private FacturaModel $facturasModel;

    public function __construct()
    {
        $this->periodosModel = new PeriodoModel();
        $this->clientesModel = new ClienteModel();
        $this->rutasModel = new RutaModel();
        $this->contratosModel = new ContratoModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->facturasModel = new FacturaModel();
    }

    public function index()
    {
        $periodoActivo = $this->periodosModel
            ->select('id_periodo, nombre, 
                    DATE_FORMAT(fecha_desde, "%d-%m-%Y") AS fecha_desde, 
                    DATE_FORMAT(fecha_hasta, "%d-%m-%Y") AS fecha_hasta')
            ->where('estado', 'ACTIVO')
            ->orderBy('id_periodo', 'DESC')
            ->first();

        $resumenFacturas = $this->facturasModel->getResumenFacturasPagadas();

        $data = [
            'totalClientes' => $this->clientesModel->countAllResults(),
            'totalRutas' => $this->rutasModel->countAllResults(),
            'periodoActivo' => $periodoActivo,
            'totalContratosActivos' => $this->contratosModel
                ->where('estado', 'APROBADO')
                ->countAllResults(),
            'totalSolicitudesActivas' => $this->solicitudesModel
                ->groupStart()
                // ->where('estado', 'CREADA')
                ->orWhere('estado', 'APROBADA')
                ->groupEnd()
                ->countAllResults(),
            'resumenFacturas' => $resumenFacturas,
        ];

        return view('inicio/index', $data);
    }
}
