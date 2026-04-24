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
        $builder->where('contratos.estado', 'APROBADO');

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

    public function getContratosActivosFacturacionServicio()
    {
        return $this->db->table('contratos')
            ->select("
                contratos.id_contrato,
                contratos.numero_contrato,
                contratos.id_cliente,
                contratos.id_tarifa,
                solicitudes.id_solicitud,
                solicitudes.codigo_solicitud,
                clientes.nombre_completo,
                medidores.numero_serie,
                lecturas.id_lectura,
                lecturas.fecha AS fecha_lectura_actual,
                lecturas.valor AS lectura_actual,
                COALESCE(tarifario.valor_metro_cubico, 0) AS valor_metro_cubico,
                COALESCE(tarifario.pago_minimo, 0) AS pago_minimo
            ", false)
            ->join('solicitudes', 'solicitudes.id_solicitud = contratos.id_solicitud', 'left')
            ->join('clientes', 'clientes.id_cliente = contratos.id_cliente', 'left')
            ->join('medidores', 'medidores.id_medidor = contratos.id_medidor', 'left')
            ->join('lecturas', 'lecturas.id_contrato = contratos.id_contrato', 'left')
            ->join('tarifario', 'tarifario.id_tarifa = contratos.id_tarifa', 'left')
            ->where('contratos.estado', 'APROBADO')
            ->where('solicitudes.estado', 'APROBADA')
            ->orderBy('contratos.id_contrato', 'ASC')
            ->get()
            ->getResult();
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

    public function getReporteContratos($estado = null)
    {
        $builder = $this->db->table('contratos c');
        $builder->select('
            c.numero_contrato,
            c.direccion_medidor,
            c.estado,
            DATE_FORMAT(c.fecha_de_inicio, "%d-%m-%Y") AS fechaInicio,
            cl.nombre_completo as cliente,
            c.id_ruta,
            c.id_medidor,
            c.id_tarifa,
            rutas.nombre AS ruta,
            medidores.numero_serie AS medidor,
            tipos_de_cliente.nombre AS tarifa
        ');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente');
        $builder->join('rutas', 'rutas.id_ruta = c.id_ruta');
        $builder->join('medidores', 'medidores.id_medidor = c.id_medidor');
        $builder->join('tarifas', 'tarifas.id_tarifa = c.id_tarifa');
        $builder->join('tipos_de_cliente', 'tipos_de_cliente.id_tipo_cliente = tarifas.id_tipo_cliente');

        if ($estado) {
            $builder->where('c.estado', $estado);
        }

        return $builder->get()->getResultArray();
    }
}
