<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoModel extends Model
{
    protected $table = 'contratos';
    protected $primaryKey = 'id_contrato';
    protected $allowedFields = [
        'id_solicitud',
        'numero_contrato',
        'ficha_alcaldia',
        'id_cliente',
        'fecha_de_inicio',
        'fecha_de_vencimiento',
        'estado_contrato',
        'id_ruta',
        'id_medidor',
        'direccion_medidor',
        'id_tarifa',
        'contado',
        'otro',
        'costo_instalacion',
        'id_plan_de_pago',
        'saldo_pendiente',
        'fecha_creacion',
        'id_usuario'
    ];

    public function buscarContratos($search)
    {
        return $this->select('id_contrato, numero_contrato AS codigo')
            ->like('numero_contrato', $search)
            ->orderBy('id_contrato', 'DESC')
            ->limit(10)
            ->findAll();
    }

    public function insertarContrato(
        $idSolicitud,
        $numeroContrato,
        $fichaAlcaldia,
        $idCliente,
        $fechaInicio,
        $fechaVencimiento,
        $estadoContrato,
        $idRuta,
        $idMedidor,
        $direccionMedidor,
        $idTarifa,
        $contado,
        $otro,
        $costoInstalacion,
        $idPlanDePago,
        $saldoPendiente,
        $fechaCreacion,
        $idUsuario
    ) {
        return $this->insert([
            'id_solicitud' => $idSolicitud,
            'numero_contrato' => $numeroContrato,
            'ficha_alcaldia' => $fichaAlcaldia,
            'id_cliente' => $idCliente,
            'fecha_de_inicio' => $fechaInicio,
            'fecha_de_vencimiento' => $fechaVencimiento,
            'estado_contrato' => $estadoContrato,
            'id_ruta' => $idRuta,
            'id_medidor' => $idMedidor,
            'direccion_medidor' => $direccionMedidor,
            'id_tarifa' => $idTarifa,
            'contado' => $contado,
            'otro' => $otro,
            'costo_instalacion' => $costoInstalacion,
            'id_plan_de_pago' => $idPlanDePago,
            'saldo_pendiente' => $saldoPendiente,
            'fecha_creacion' => $fechaCreacion,
            'id_usuario' => $idUsuario,
        ]);
    }

    public function getTodosContratos($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('contratos');

        // JOIN desde el inicio
        $builder->join(
            'solicitudes',
            'contratos.id_solicitud = solicitudes.id_solicitud',
            'left'
        );

        // JOIN desde el inicio
        $builder->join(
            'clientes',
            'contratos.id_cliente = clientes.id_cliente',
            'left'
        );

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAllResults(false);

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('contratos.numero_contrato', $searchValue)
                ->orLike('clientes.nombre_completo', $searchValue)
                ->groupEnd();
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                contratos.id_contrato,
                contratos.numero_contrato AS cod_contrato,
                solicitudes.codigo_solicitud AS cod_solicitud,
                clientes.nombre_completo AS nombre,
                contratos.estado_contrato AS estado
        ')
            ->orderBy('contratos.id_contrato', 'DESC')
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
