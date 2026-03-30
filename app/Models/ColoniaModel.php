<?php

namespace App\Models;

use CodeIgniter\Model;

class ColoniaModel extends Model
{
    protected $table = 'colonias';
    protected $primaryKey = 'id_colonia';
    protected $allowedFields = ['nombre', 'id_distrito', 'id_usuario'];

    public function getColoniasPorDistrito($idDistrito)
    {
        return $this
            ->where('id_distrito', $idDistrito)
            ->findAll();
    }

    public function guardarNuevaColonia($idDistrito, $nombreColonia, $idUsuario)
    {
        return $this->insert([
            'id_distrito' => $idDistrito,
            'nombre' => $nombreColonia,
            'id_usuario' => $idUsuario
        ]);
    }
}
