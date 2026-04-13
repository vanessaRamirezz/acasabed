<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialCobroInstalacionModel extends Model
{
    protected $table = 'historial_cobros_instalacion';
    protected $primaryKey = 'id_historial_cobro_instalacion';
    protected $allowedFields = [
        'id_contrato',
        'id_solicitud',
        'id_cliente',
        'monto_cobrado',
        'mora',
        'total_pagado',
        'id_usuario',
        'fecha_creacion',
    ];

    public function getHistorial($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('historial_cobros_instalacion');

        $builder->join('contratos', 'contratos.id_contrato = historial_cobros_instalacion.id_contrato', 'left');
        $builder->join('solicitudes', 'solicitudes.id_solicitud = historial_cobros_instalacion.id_solicitud', 'left');
        $builder->join('clientes', 'clientes.id_cliente = historial_cobros_instalacion.id_cliente', 'left');

        $total = $builder->countAllResults(false);

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('clientes.nombre_completo', $searchValue)
                ->orLike('solicitudes.codigo_solicitud', $searchValue)
                ->orLike('contratos.numero_contrato', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        $data = $builder
            ->select("
                historial_cobros_instalacion.id_historial_cobro_instalacion AS id,
                solicitudes.codigo_solicitud,
                contratos.numero_contrato,
                clientes.nombre_completo AS cliente,
                historial_cobros_instalacion.monto_cobrado,
                historial_cobros_instalacion.mora,
                historial_cobros_instalacion.total_pagado,
                historial_cobros_instalacion.fecha_creacion
            ", false)
            ->orderBy('historial_cobros_instalacion.id_historial_cobro_instalacion', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }
}
