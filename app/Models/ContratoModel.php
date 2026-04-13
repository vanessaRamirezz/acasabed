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
        'id_ruta',
        'id_medidor',
        'direccion_medidor',
        'id_tarifa',
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
        $idRuta,
        $idMedidor,
        $direccionMedidor,
        $idTarifa,
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
            'id_ruta' => $idRuta,
            'id_medidor' => $idMedidor,
            'direccion_medidor' => $direccionMedidor,
            'id_tarifa' => $idTarifa,
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
        $total = $builder
            ->where('solicitudes.estado', 'APROBADA')
            ->orWhere('solicitudes.estado', 'ANULADA')
            ->countAllResults(false);

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
        $filtered = $builder
            ->where('solicitudes.estado', 'APROBADA')
            ->orWhere('solicitudes.estado', 'ANULADA')
            ->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                solicitudes.id_solicitud AS id,
                contratos.numero_contrato AS cod_contrato,
                solicitudes.codigo_solicitud AS cod_solicitud,
                clientes.nombre_completo AS nombre,
                contratos.fecha_de_inicio AS fecha,
                DATE_FORMAT(contratos.fecha_de_inicio, "%d-%m-%Y") AS fechaTexto
        ')
            ->where('solicitudes.estado', 'APROBADA')
            ->orWhere('solicitudes.estado', 'ANULADA')
            ->orderBy('solicitudes.id_solicitud', 'DESC')
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
