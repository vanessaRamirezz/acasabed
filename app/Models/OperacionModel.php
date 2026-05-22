<?php

namespace App\Models;

use CodeIgniter\Model;

class OperacionModel extends Model
{
    protected $table = 'operaciones';
    protected $primaryKey = 'id_operacion';
    protected $allowedFields = ['nombre'];

    public function getOperaciones()
    {
        return $this->select('id_operacion, nombre')
            ->orderBy('nombre', 'DESC')
            ->findAll();
    }
}
