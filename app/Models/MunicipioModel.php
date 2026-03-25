<?php

namespace App\Models;

use CodeIgniter\Model;

class MunicipioModel extends Model
{
    protected $table = 'municipios';
    protected $primaryKey = 'id_municipio';
    protected $allowedFields = ['nombre', 'codigo', 'id_departamento'];

    public function getMunicipiosPorDepartamento($idDepartamento)
    {
        return $this
            ->where('id_departamento',$idDepartamento)
            ->findAll();
    }
}
