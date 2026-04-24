<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaDetalleModel extends Model
{
    protected $table = 'facturas_detalle';
    protected $primaryKey = 'id_factura_detalle';
    protected $allowedFields = [
        'id_factura',
        'tipo',
        'concepto',
        'mora',
        'monto',
        'id_cobro_instalacion'
    ];
}