<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifaDetalleModel extends Model
{
    protected $table = 'tarifas_detalle';
    protected $primaryKey = 'id_tarifa_detalle';
    protected $allowedFields = ['id_tarifa', 'valor_metro_cubico', 'desde_n_metros', 'hasta_n_metros', 'pago_minimo'];

}
