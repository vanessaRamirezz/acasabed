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
            ->join('contratos c', 'c.id_contrato = f.id_contrato')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente')
            ->orderBy('f.id_factura', 'DESC')
            ->get()
            ->getResultArray();
    }
}
