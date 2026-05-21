<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    protected $allowedFields = ['codigo', 'nombre', 'estado'];

    public function buscarServiciosActivos(string $search = '')
    {
        $builder = $this->select('id_servicio AS id, codigo, nombre, CONCAT(codigo, " - ", nombre) AS text')
            ->where('estado', 'ACTIVO');

        if ($search !== '') {
            $builder->groupStart()
                ->like('codigo', $search)
                ->orLike('nombre', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('nombre', 'ASC')
            ->limit(20)
            ->findAll();
    }
}
