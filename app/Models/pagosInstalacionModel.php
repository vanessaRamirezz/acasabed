<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosInstalacionModel extends Model
{
    protected $table = 'pagos_instalacion';
    protected $primaryKey = 'id_pago';
    protected $allowedFields = [
        'correlativo',
        'id_contrato',
        'id_solicitud',
        'id_periodo',
        'fecha_creacion',
        'estado'
    ];

    // public function correlativoPago($db)
    // {
    //     $query = $db->query("
    //         SELECT MAX(correlativo) AS max_codigo 
    //         FROM pagos_instalacion
    //         FOR UPDATE
    //     ");

    //     $row = $query->getRow();
    //     $ultimo = $row->max_codigo ?? 0;

    //     $numero = (int)$ultimo + 1;

    //     return str_pad($numero, 5, '0', STR_PAD_LEFT);
    // }

    public function correlativoPago($db)
    {
        $query = $db->query("
        SELECT MAX(CAST(correlativo AS UNSIGNED)) AS max_codigo
        FROM pagos_instalacion
        FOR UPDATE
    ");

        $row = $query->getRow();
        $ultimo = $row->max_codigo ?? 0;

        $numero = (int)$ultimo + 1;

        return str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
    
}
