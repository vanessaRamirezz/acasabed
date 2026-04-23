<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaCobroHistorialModel extends Model
{
    protected $table = 'facturas_cobros_historial';
    protected $primaryKey = 'id_factura_cobro_historial';
    protected $allowedFields = [
        'id_factura_cobro',
        'id_contrato',
        'descripcion',
        'monto',
        'fecha_creacion',
        'id_usuario'
    ];
}
