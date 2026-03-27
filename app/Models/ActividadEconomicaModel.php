<?php

namespace App\Models;

use CodeIgniter\Model;

class ActividadEconomicaModel extends Model
{
    protected $table = 'actividades_economicas';
    protected $primaryKey = 'id_actividad_economica';
    protected $allowedFields = ['nombre', 'codigo'];

    public function buscarActividadEcocomica($search)
    {
        return $this->select('id_actividad_economica, codigo, nombre')
            ->like('nombre', $search)
            ->orderBy('id_actividad_economica', 'ASC')
            ->limit(10)
            ->findAll();
    }
}
