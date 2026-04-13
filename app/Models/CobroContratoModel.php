<?php

namespace App\Models;

use CodeIgniter\Model;

class CobroContratoModel extends Model
{
    protected $table = 'contratos_cobros';
    protected $primaryKey = 'id_cobro_instalacion';
    protected $allowedFields = [
        'id_contrato',
        'numero_cuota',
        'monto_cuota',
        'descripcion',
        'estado',
        'fecha_vencimiento',
        'fecha_pago',
        'cantidad_abonada',
        'id_usuario',
        'fecha_creacion',
    ];

    public function getCobrosRealizados($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('contratos_cobros');

        $builder->join('contratos', 'contratos.id_contrato = contratos_cobros.id_contrato', 'left');
        $builder->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud', 'left');
        $builder->join('clientes', 'clientes.id_cliente = contratos.id_cliente', 'left');
        $builder->where('COALESCE(contratos_cobros.cantidad_abonada, 0) >', 0, false);

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
                contratos_cobros.id_cobro_instalacion AS id,
                solicitudes.codigo_solicitud AS codigo_solicitud,
                contratos.numero_contrato AS numero_contrato,
                clientes.nombre_completo AS cliente,
                contratos_cobros.numero_cuota,
                contratos_cobros.monto_cuota,
                COALESCE(contratos_cobros.cantidad_abonada, 0) AS cantidad_abonada,
                (contratos_cobros.monto_cuota - COALESCE(contratos_cobros.cantidad_abonada, 0)) AS saldo_cuota,
                contratos_cobros.estado,
                contratos_cobros.fecha_pago
            ", false)
            ->orderBy('contratos_cobros.fecha_pago', 'DESC')
            ->orderBy('contratos_cobros.id_cobro_instalacion', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function buscarCuentasPendientes($search)
    {
        return $this->db->table('contratos')
            ->select("
                contratos.id_contrato,
                contratos.numero_contrato,
                solicitudes.id_solicitud,
                solicitudes.codigo_solicitud,
                solicitudes.fecha_generacion,
                clientes.nombre_completo,
                SUM(contratos_cobros.monto_cuota - COALESCE(contratos_cobros.cantidad_abonada, 0)) AS saldo_pendiente,
                SUM(
                    CASE
                        WHEN (contratos_cobros.monto_cuota - COALESCE(contratos_cobros.cantidad_abonada, 0)) > 0
                        THEN 1
                        ELSE 0
                    END
                ) AS cuotas_pendientes
            ", false)
            ->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud', 'left')
            ->join('clientes', 'clientes.id_cliente = contratos.id_cliente', 'left')
            ->join('contratos_cobros', 'contratos_cobros.id_contrato = contratos.id_contrato', 'inner')
            ->groupStart()
                ->like('clientes.nombre_completo', $search)
                ->orLike('solicitudes.codigo_solicitud', $search)
                ->orLike('contratos.numero_contrato', $search)
            ->groupEnd()
            ->groupBy('contratos.id_contrato, contratos.numero_contrato, solicitudes.id_solicitud, solicitudes.codigo_solicitud, solicitudes.fecha_generacion, clientes.nombre_completo')
            ->having('saldo_pendiente >', 0)
            ->orderBy('clientes.nombre_completo', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getDetalleCobroPorContrato($idContrato)
    {
        $resumen = $this->db->table('contratos')
            ->select("
                contratos.id_contrato,
                contratos.numero_contrato,
                solicitudes.id_solicitud,
                solicitudes.codigo_solicitud,
                solicitudes.fecha_generacion,
                clientes.id_cliente,
                clientes.nombre_completo,
                COALESCE(solicitudes.saldo_pendiente, 0) AS saldo_solicitud,
                SUM(contratos_cobros.monto_cuota - COALESCE(contratos_cobros.cantidad_abonada, 0)) AS saldo_pendiente,
                SUM(
                    CASE
                        WHEN (contratos_cobros.monto_cuota - COALESCE(contratos_cobros.cantidad_abonada, 0)) > 0
                        THEN 1
                        ELSE 0
                    END
                ) AS cuotas_pendientes,
                COUNT(contratos_cobros.id_cobro_instalacion) AS total_cuotas
            ", false)
            ->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud', 'left')
            ->join('clientes', 'clientes.id_cliente = contratos.id_cliente', 'left')
            ->join('contratos_cobros', 'contratos_cobros.id_contrato = contratos.id_contrato', 'left')
            ->where('contratos.id_contrato', $idContrato)
            ->groupBy('contratos.id_contrato, contratos.numero_contrato, solicitudes.id_solicitud, solicitudes.codigo_solicitud, solicitudes.fecha_generacion, clientes.id_cliente, clientes.nombre_completo, solicitudes.saldo_pendiente')
            ->get()
            ->getRowArray();

        if (!$resumen) {
            return null;
        }

        $cuotas = $this->where('id_contrato', $idContrato)
            ->select("
                id_cobro_instalacion,
                numero_cuota,
                monto_cuota,
                COALESCE(cantidad_abonada, 0) AS cantidad_abonada,
                (monto_cuota - COALESCE(cantidad_abonada, 0)) AS saldo_cuota,
                estado,
                fecha_vencimiento,
                fecha_pago
            ", false)
            ->orderBy('numero_cuota', 'ASC')
            ->findAll();

        return [
            'resumen' => $resumen,
            'cuotas' => $cuotas
        ];
    }

    public function getCuotasPendientesPorContrato($idContrato)
    {
        return $this->where('id_contrato', $idContrato)
            ->where('(monto_cuota - COALESCE(cantidad_abonada, 0)) >', 0, false)
            ->select("
                id_cobro_instalacion,
                numero_cuota,
                monto_cuota,
                COALESCE(cantidad_abonada, 0) AS cantidad_abonada,
                (monto_cuota - COALESCE(cantidad_abonada, 0)) AS saldo_cuota,
                estado
            ", false)
            ->orderBy('numero_cuota', 'ASC')
            ->findAll();
    }
}
