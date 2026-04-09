<?php

namespace App\Models;

use CodeIgniter\Model;

class CobroContratoModel extends Model
{
    protected $table = 'contratos_cobros';
    protected $primaryKey = 'id_cobro_instalacion';
    protected $allowedFields = [
        'id_contrato',
        'numero_cuota',
        'monto_cuota',
        'descripcion',
        'estado',
        'fecha_vencimiento',
        'fecha_pago',
        'cantidad_abonada',
        'id_usuario',
        'fecha_creacion',
    ];
}
