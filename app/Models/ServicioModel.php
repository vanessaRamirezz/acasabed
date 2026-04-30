<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    protected $allowedFields = ['codigo', 'nombre', 'estado'];
}
