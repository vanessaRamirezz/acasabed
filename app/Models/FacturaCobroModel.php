<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaCobroModel extends Model
{
    protected $table = 'facturas_cobro';
    protected $primaryKey = 'id_factura_cobro';
    protected $allowedFields = [
        'id_factura',
        'id_contrato',
        'monto_total',
        'fecha_creacion',
        'id_usuario'
    ];
}
