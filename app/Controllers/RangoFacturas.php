<?php

namespace App\Controllers;

use App\Models\RangoFacturaModel;

class RangoFacturas extends BaseController
{
    private $rangoFacturasModel;

    public function __construct()
    {
        $this->rangoFacturasModel = new RangoFacturaModel();
    }

    public function index()
    {
        return view('rango_de_facturas/index');
    }

    public function guardarRango()
    {
        $db = \Config\Database::connect();

        try {
            $numeroInicio = (int)$this->request->getPost('numeroInicio');
            $numeroFinal  = (int)$this->request->getPost('numeroFin');
            $estado = 'Activo';
            $fechaCreacion = date('Y-m-d H:i:s');
            $idUsuario = $_SESSION['id_usuario'];

            // 🔴 Validaciones básicas
            if (!$numeroInicio || !$numeroFinal) {
                return $this->respondError('Inicio y fin son requeridos');
            }

            if ($numeroInicio >= $numeroFinal) {
                return $this->respondError('El número inicial debe ser menor que el final');
            }

            $db->transBegin();

            // 🔴 1. Validar que no haya otro activo
            $activo = $db->table('rango_factura')
                ->where('estado', 'Activo')
                ->get()
                ->getRow();

            if ($activo) {
                $db->transRollback();
                return $this->respondError('Ya existe un tiraje activo');
            }

            // 🔴 2. Validar solapamiento
            $solapado = $db->table('rango_factura')
                ->groupStart()
                ->where('numero_inicio <=', $numeroFinal)
                ->where('numero_fin >=', $numeroInicio)
                ->groupEnd()
                ->get()
                ->getRow();

            if ($solapado) {
                $db->transRollback();
                return $this->respondError('El rango se solapa con otro existente');
            }

            // 🔴 3. Obtener siguiente tiraje
            $maxTiraje = $db->table('rango_factura')
                ->selectMax('tiraje')
                ->get()
                ->getRow()
                ->tiraje ?? 0;

            $nuevoTiraje = $maxTiraje + 1;

            // 🔴 4. numero_actual inicia en numero_inicio
            $numeroActual = $numeroInicio;

            // 🔴 5. Insertar
            $resultado = $this->rangoFacturasModel->insert([
                'tiraje'         => $nuevoTiraje,
                'numero_inicio'  => $numeroInicio,
                'numero_fin'     => $numeroFinal,
                'numero_actual'  => $numeroActual,
                'estado'         => $estado,
                'id_usuario'     => $idUsuario,
                'fecha_creacion' => $fechaCreacion,
            ]);

            if (!$resultado) {
                $db->transRollback();
                return $this->respondError('No se pudo guardar el rango');
            }

            $db->transCommit();

            return $this->respondOk("Tiraje {$nuevoTiraje} creado correctamente");
        } catch (\Throwable $th) {
            $db->transRollback();
            log_message('error', $th->getMessage());
            return $this->respondError('Error al guardar el rango');
        }
    }

    public function getRangoFacturas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->rangoFacturasModel->getRangosFacturas($start, $length, $searchValue);

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
}
