<?php

namespace App\Models;

use CodeIgniter\Model;

class DistritoModel extends Model
{
    protected $table = 'distritos';
    protected $primaryKey = 'id_distrito';
    protected $allowedFields = ['nombre', 'id_municipio'];

    public function getDistritosPorMunicipios($idMunicipio){
        return $this
            ->where('id_municipio',$idMunicipio)
            ->findAll();
    }
}
