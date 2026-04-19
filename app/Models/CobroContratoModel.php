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
        'id_usuario',
        'fecha_creacion',
        'recargo',
    ];

    public function getCobrosRealizados($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('contratos_cobros');

        $builder->join('contratos', 'contratos.id_contrato = contratos_cobros.id_contrato', 'left');
        $builder->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud', 'left');
        $builder->join('clientes', 'clientes.id_cliente = contratos.id_cliente', 'left');


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
                COALESCE(solicitudes.costo_instalacion, 0) AS costo,
                COALESCE(solicitudes.saldo_pendiente, 0) AS saldo_pendiente,
                SUM(
                    CASE
                        WHEN contratos_cobros.estado = 'PENDIENTE'
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
                descripcion,
                estado,
                fecha_vencimiento,
                fecha_pago,
                recargo
            ", false)
            ->orderBy('numero_cuota', 'ASC')
            ->findAll();

        return [
            'resumen' => $resumen,
            'cuotas' => $cuotas
        ];
    }


    public function getDetalleCobroPorCliente($idCliente)
    {
        // 1. Obtener todos los contratos del cliente
        $contratos = $this->db->table('contratos')
            ->select('id_contrato')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getResultArray();

        if (empty($contratos)) {
            return [];
        }

        $resultado = [];

        // 2. Reutilizar tu lógica existente
        foreach ($contratos as $contrato) {
            $detalle = $this->getDetalleCobroPorContrato($contrato['id_contrato']);

            if ($detalle) {
                $resultado[] = $detalle;
            }
        }

        return $resultado;
    }

    //metodos para generar la factura 
    public function getContratosParaFacturar()
    {
        return $this->db->table('contratos')
            ->select("
            contratos.id_contrato,
            contratos.id_solicitud,
            contratos.numero_contrato,
            solicitudes.id_cliente,
            solicitudes.codigo_solicitud,
            solicitudes.saldo_pendiente
        ")
            ->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud')
            ->where('solicitudes.estado', 'APROBADA')
            ->where('contratos.estado', 'APROBADO')
            ->where('solicitudes.saldo_pendiente >', 0)
            ->get()
            ->getResult();
    }

    public function getCuotasPendientesPorContrato($idContrato)
    {
        return $this->db->table('contratos_cobros')
            ->select("
            id_cobro_instalacion,
            id_contrato,
            numero_cuota,
            monto_cuota,
            estado,
            fecha_vencimiento,
            recargo
        ")
            ->where('id_contrato', $idContrato)
            ->orderBy('numero_cuota', 'ASC')
            ->get()
            ->getResult();
    }
}
