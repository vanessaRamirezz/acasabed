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
        'estado',
        'motivo_suspension',
        'fecha_suspension',
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
        $estadoContrato,
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
            'estado' => $estadoContrato,
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
            ->groupStart()
            ->where('contratos.estado', 'APROBADO')
            ->orWhere('contratos.estado', 'SUSPENDIDO')
            ->groupEnd()
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
            ->groupStart()
            ->where('contratos.estado', 'APROBADO')
            ->orWhere('contratos.estado', 'SUSPENDIDO')
            ->groupEnd()
            ->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                solicitudes.id_solicitud AS id,
                contratos.id_contrato AS id_contrato,
                contratos.numero_contrato AS cod_contrato,
                solicitudes.codigo_solicitud AS cod_solicitud,
                clientes.nombre_completo AS nombre,
                contratos.estado AS estadoContrato,
                contratos.motivo_suspension AS motivoSuspencion,
                contratos.fecha_de_inicio AS fecha,
                DATE_FORMAT(contratos.fecha_de_inicio, "%d-%m-%Y") AS fechaTexto
        ')
            ->groupStart()
            ->where('contratos.estado', 'APROBADO')
            ->orWhere('contratos.estado', 'SUSPENDIDO')
            ->groupEnd()
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

    public function getContratosActivosLectura($idPeriodo)
    {
        $builder = $this->db->table('contratos');

        $builder->select('
        contratos.id_contrato,
        contratos.numero_contrato,
        clientes.nombre_completo,
        solicitudes.codigo_solicitud
    ');

        $builder->join('solicitudes', 'contratos.id_solicitud = solicitudes.id_solicitud', 'left');
        $builder->join('clientes', 'contratos.id_cliente = clientes.id_cliente', 'left');

        $builder->where('solicitudes.estado', 'APROBADA');

        // 🔥 subquery separado (CORRECTO)
        $subQuery = $this->db->table('lecturas')
            ->select('1')
            ->where('lecturas.id_contrato = contratos.id_contrato', null, false)
            ->where('lecturas.id_periodo', $idPeriodo)
            ->getCompiledSelect();

        $builder->where("NOT EXISTS ($subQuery)", null, false);

        return $builder
            ->orderBy('contratos.id_contrato', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function actualizarEstadoContrato($idContrato, $estado, $motivo, $fechaSuspension)
    {
        return $this->update(
            $idContrato,
            [
                'estado' => $estado,
                'motivo_suspension' => $motivo,
                'fecha_suspension' => $fechaSuspension
            ]
        );
    }
}
