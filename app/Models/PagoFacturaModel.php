<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoFacturaModel extends Model{
    protected $table = 'pagos_factura';
    protected $primaryKey = 'id_pago_factura';
    protected $allowedFields = [
        'id_factura',
        'id_periodo',
        'tiraje',
        'correlativo',
        'referencia',
        'estado_excel',
        'monto_pagado',
        'fecha_pago',
        'fecha_carga',
        'id_usuario',
        'archivo_origen'
    ];
}
