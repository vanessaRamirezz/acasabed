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
        'fecha_creacion',
        'descripcion'
    ];

    public function getHistorial($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');

        // 🔥 total sin filtros
        $total = $builder->countAllResults(false);

        // 🔍 búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike('s.codigo_solicitud', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('f.correlativo', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        // 📄 data principal
        $data = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            f.fecha_emision,

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

        // 🔥 detalle para PDF
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
}
