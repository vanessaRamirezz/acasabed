<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaServicioModel extends Model
{
    protected $table = 'facturas_anterior';
    protected $primaryKey = 'id_factura';
    protected $allowedFields = [
        'correlativo',
        'id_contrato',
        'id_periodo',
        'id_lectura',
        'fecha_emision',
        'fecha_vencimiento',
        'alumbrado',
        'tren_de_aseo',
        'consumo_mes',
        'monto_servicio',
        'saldo_pendiente',
        'estado',
        'mora',
        'total',
        'id_usuario'
    ];

    public function actualizarFacturasVencidas($fechaActual)
    {
        return $this->db->table($this->table)
            ->where('fecha_vencimiento <', $fechaActual)
            ->where('estado', 'PENDIENTE')
            ->update(['estado' => 'VENCIDA']);
    }

    public function existeFacturaPeriodoContrato($idContrato, $idPeriodo)
    {
        return $this->where('id_contrato', $idContrato)
            ->where('id_periodo', $idPeriodo)
            ->countAllResults() > 0;
    }

    public function getFacturasPendientesContrato($idContrato)
    {
        return $this->where('id_contrato', $idContrato)
            ->whereIn('estado', ['PENDIENTE', 'VENCIDA'])
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();
    }

    public function getHistorialFacturas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        $total = $builder->countAllResults(false);

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('f.correlativo', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('cl.nombre_completo', $searchValue)
                ->orLike('p.nombre', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        $data = $builder
            ->select("
                f.id_factura AS id,
                f.correlativo,
                c.numero_contrato,
                s.codigo_solicitud,
                cl.nombre_completo AS cliente,
                p.nombre AS periodo,
                f.fecha_emision,
                f.fecha_vencimiento,
                f.consumo_mes,
                f.monto_servicio,
                f.saldo_pendiente,
                f.estado,
                f.mora
            ", false)
            ->orderBy('f.id_factura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    // public function getFacturaResumenPorId($idFactura)
    // {
    //     return $this->db->table('facturas f')
    //         ->select("
    //             f.id_factura,
    //             f.correlativo,
    //             f.id_contrato,
    //             f.id_periodo,
    //             f.fecha_emision,
    //             f.fecha_vencimiento,
    //             f.alumbrado,
    //             f.tren_de_aseo,
    //             f.consumo_mes,
    //             f.monto_servicio,
    //             f.saldo_pendiente,
    //             f.estado,
    //             f.mora,
    //             f.id_lectura,
    //             c.numero_contrato,
    //             c.id_cliente,
    //             cl.nombre_completo AS cliente,
    //             CONCAT_WS(', ',
    //                 d.nombre,
    //                 m.nombre,
    //                 ds.nombre,
    //                 col.nombre,
    //                 cl.complemento_direccion
    //             ) AS direccion,
    //             s.codigo_solicitud,
    //             p.nombre AS periodo,
    //             medi.numero_serie,
    //             lec.valor AS lecturaActual,
    //             (
    //                 SELECT l2.valor
    //                 FROM lecturas l2
    //                 WHERE l2.id_contrato = f.id_contrato
    //                 AND l2.id_periodo < f.id_periodo
    //                 ORDER BY l2.id_periodo DESC, l2.id_lectura DESC
    //                 LIMIT 1
    //             ) AS lecturaAnterior,
    //             DATE_FORMAT(lec.fecha, '%d-%m-%Y') AS fechaLectura,
    //             tari.codigo AS codigoTarifa
    //         ", false)
    //         ->join('contratos c', 'c.id_contrato = f.id_contrato', 'left')
    //         ->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left')
    //         ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
    //         ->join('periodos p', 'p.id_periodo = f.id_periodo', 'left')
    //         ->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left')
    //         ->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left')
    //         ->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left')
    //         ->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left')
    //         ->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left')
    //         ->join('lecturas lec', 'lec.id_lectura = f.id_lectura', 'left')
    //         ->join('tarifario tari', 'tari.id_tarifa = c.id_tarifa', 'left')

    //         ->where('f.id_factura', $idFactura)
    //         ->get()
    //         ->getRowArray();
    // }

    public function getFacturasPendientesCliente($idCliente)
    {
        $subQuery = $this->db->table('facturas f2')
            ->select('MAX(f2.id_factura)')
            ->join('contratos c2', 'c2.id_contrato = f2.id_contrato')
            ->where('c2.id_cliente', $idCliente)
            ->whereIn('f2.estado', ['PENDIENTE', 'VENCIDA'])
            ->groupBy('f2.id_contrato')
            ->getCompiledSelect();

        return $this->db->table('facturas f')
            ->select("
                f.id_factura,
                f.correlativo,
                f.id_contrato,
                f.fecha_emision,
                f.fecha_vencimiento,
                f.saldo_pendiente,
                f.estado,
                f.consumo_mes,
                f.monto_factura,
                f.saldo_anterior,
                f.mora,
                c.numero_contrato,
                s.codigo_solicitud,
                cl.nombre_completo AS cliente,
                p.nombre AS periodo,
                medi.numero_serie
            ", false)
            ->join('contratos c', 'c.id_contrato = f.id_contrato', 'left')
            ->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
            ->join('periodos p', 'p.id_periodo = f.id_periodo', 'left')
            ->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left')
            ->where("f.id_factura IN ($subQuery)", null, false)
            ->orderBy('f.id_factura', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getFacturaResumenPorId($idFactura)
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');
        $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
        $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
        $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
        $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
        $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');
        $builder->join('lecturas lec', 'lec.id_lectura = f.id_lectura', 'left');
        $builder->join('tarifario tari', 'tari.id_tarifa = c.id_tarifa', 'left');

        $factura = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            CONCAT_WS(', ',
                    d.nombre,
                    m.nombre,
                    ds.nombre,
                    col.nombre,
                    cl.complemento_direccion
                ) AS direccion,
            DATE_FORMAT(lec.fecha, '%d-%m-%Y') AS fechaLectura,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            tari.codigo AS codigoTarifa,
            medi.numero_serie,
            lec.valor AS lecturaActual,
            (
                SELECT l2.valor
                FROM lecturas l2
                WHERE l2.id_contrato = f.id_contrato
                AND l2.id_periodo < f.id_periodo
                ORDER BY l2.id_periodo DESC, l2.id_lectura DESC
                LIMIT 1
            ) AS lecturaAnterior,
            f.consumo_mes,
            tari.codigo AS codigoTarifa
        ", false)
            ->where('f.id_factura', $idFactura)
            ->get()
            ->getRowArray();

        // traer detalle (IMPORTANTE para el PDF)
        $detalle = $this->db->table('facturas_detalle')
            ->where('id_factura', $idFactura)
            ->get()
            ->getResultArray();

        return [
            'factura' => $factura,
            'detalle' => $detalle
        ];
    }
}
