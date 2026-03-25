<?php

namespace App\Models;

use CodeIgniter\Model;

class DireccionModel extends Model
{
    protected $table = 'direcciones';
    protected $primaryKey = 'id_direccion';
    protected $allowedFields = ['nombre', 'id_distrito', 'id_usuario'];

    public function getDireccionesPorDistrito($idDistrito)
    {
        return $this
            ->where('id_distrito', $idDistrito)
            ->findAll();
    }

    public function guardarNuevaDireccion($idDistrito, $nombreColonia, $idUsuario)
    {
        return $this->insert([
            'id_distrito' => $idDistrito,
            'nombre' => $nombreColonia,
            'id_usuario' => $idUsuario
        ]);
    }
}
