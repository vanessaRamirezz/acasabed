<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoServicioModel extends Model
{
    protected $table = 'tipos_servicios';
    protected $primaryKey = 'id_tipos_servicios';
    protected $allowedFields = ['nombre'];

    public function getTipos()
    {
        return $this->select('id_tipos_servicios, nombre')
            ->orderBy('nombre', 'DESC')
            ->findAll();
    }
}
