<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaModel extends Model
{
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    protected $allowedFields = [
        'id_rango_factura',
        'correlativo',
        'tiraje',
        'id_contrato',
        'id_periodo',
        'id_lectura',
        'fecha_emision',
        'fecha_vencimiento',
        'saldo_pendiente',
        'estado',
        'total',
        'id_usuario',
        'fecha_de_pago'
    ];

    public function getFacturasExcel($idPeriodo)
    {
        return $this->db->table('facturas f')
            ->select("
            f.tiraje,
            f.correlativo,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            cl.codigo,
            DATE_FORMAT(f.fecha_emision, '%d-%m-%Y') AS fechaEmision,
            f.fecha_emision,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            f.fecha_vencimiento,
            f.total,
            f.saldo_pendiente,
            f.estado,
            f.fecha_de_pago
        ")
            ->where('f.id_periodo', $idPeriodo)
            ->where('f.estado', 'PENDIENTE')
            ->join('contratos c', 'c.id_contrato = f.id_contrato')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente')
            ->orderBy('f.id_factura', 'DESC')
            ->get()
            ->getResultArray();
    }

    // modelo para facturas de cobros de instalacion
    public function getHistorial($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        //total sin filtros
        $total = $builder->countAllResults(false);

        // búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike('s.codigo_solicitud', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('f.correlativo', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        // data principal
        $data = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            f.estado,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            f.fecha_emision,
            f.fecha_de_pago,
            p.nombre AS periodo,

            (
                SELECT SUM(fd.monto - fd.mora)
                FROM facturas_detalle fd
                WHERE fd.id_factura = f.id_factura
            ) AS total_cuotas,

            (
                SELECT SUM(fd.mora)
                FROM facturas_detalle fd
                WHERE fd.id_factura = f.id_factura
            ) AS total_mora

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

    public function obtenerFacturaPorId($idFactura)
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
        $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
        $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
        $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
        $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');

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

            f.fecha_emision,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            f.fecha_vencimiento,
            f.estado,
            f.total,
            f.saldo_pendiente,

            medi.numero_serie,

            (
                SELECT COALESCE(SUM(fd.monto - fd.mora),0)
                FROM facturas_detalle fd
                WHERE fd.id_factura = f.id_factura
            ) AS total_cuotas,

            (
                SELECT COALESCE(SUM(fd.mora),0)
                FROM facturas_detalle fd
                WHERE fd.id_factura = f.id_factura
            ) AS total_mora

        ", false)
            ->where('f.id_factura', $idFactura)
            ->get()
            ->getRowArray();

        // detalle para PDF
        $detalle = $this->db->table('facturas_detalle fd')
            ->select("
            fd.id_factura_detalle,
            fd.tipo,
            fd.concepto,
            fd.monto,
            fd.mora,
            fd.id_cobro_instalacion
        ")
            ->where('fd.id_factura', $idFactura)
            ->get()
            ->getResultArray();

        return [
            'factura' => $factura,
            'detalle' => $detalle
        ];
    }

    // modelo para facturas de consumo mes a mes
    public function getHistorialFacturas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner');
        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        $builder->where('fd.tipo', 'Consumo');

        // CLAVE PARA EVITAR DUPLICADOS
        $builder->distinct();
        $builder->select('f.id_factura');

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

        // SELECT FINAL (ya con columnas reales)
        $data = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            p.nombre AS periodo,
            f.fecha_emision,
            f.fecha_vencimiento,
            f.estado
        ")
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
