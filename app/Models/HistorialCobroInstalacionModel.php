<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialCobroInstalacionModel extends Model
{
    protected $table = 'historial_cobros_instalacion';
    protected $primaryKey = 'id_historial_cobro_instalacion';
    protected $allowedFields = [
        'id_pago',
        'id_contrato',
        'id_solicitud',
        'id_cobro_instalacion',
        'recargo_aplicado',
        'monto_cuota',
        'total_pagado',
        'fecha_creacion',
    ];

    public function getHistorial($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('pagos_instalacion pi');

        $builder->join('contratos c', 'c.id_contrato = pi.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = pi.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');

        // 🔥 total sin filtros
        $total = $builder->countAllResults(false);

        // 🔍 búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike('s.codigo_solicitud', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('pi.correlativo', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        // 📄 data principal (facturas/pagos)
        $data = $builder
            ->select("
            pi.id_pago AS id,
            pi.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            pi.fecha_creacion,

            -- 🔥 totales calculados desde detalle
            (
                SELECT SUM(h.total)
                FROM historial_cobros_instalacion h
                WHERE h.id_pago = pi.id_pago
            ) AS total_pagado,

            (
                SELECT SUM(h.monto_cuota)
                FROM historial_cobros_instalacion h
                WHERE h.id_pago = pi.id_pago
            ) AS total_cuotas,

            (
                SELECT SUM(h.recargo_aplicado)
                FROM historial_cobros_instalacion h
                WHERE h.id_pago = pi.id_pago
            ) AS total_mora

        ", false)
            ->orderBy('pi.id_pago', 'DESC')
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
