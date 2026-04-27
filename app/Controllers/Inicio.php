<?php

namespace App\Controllers;

use App\Models\PeriodoModel;

class Inicio extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $periodosModel = new PeriodoModel();

        $periodoActivo = $periodosModel
            ->select('id_periodo, nombre, fecha_desde, fecha_hasta')
            ->where('estado', 'ACTIVO')
            ->orderBy('id_periodo', 'DESC')
            ->first();

        $data = [
            'totalClientes' => $db->table('clientes')->countAllResults(),
            'totalRutas' => $db->table('rutas')->countAllResults(),
            'periodoActivo' => $periodoActivo,
            'totalContratosActivos' => $db->table('contratos')
                ->where('estado', 'APROBADO')
                ->countAllResults(),
            'totalSolicitudesActivas' => $db->table('solicitudes')
                ->groupStart()
                ->where('estado', 'CREADA')
                ->orWhere('estado', 'APROBADA')
                ->groupEnd()
                ->countAllResults(),
        ];

        return view('inicio/index', $data);
    }
}
