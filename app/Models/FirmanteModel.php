<?php

namespace App\Models;

use CodeIgniter\Model;

class FirmanteModel extends Model
{
    protected $table = 'firmantes';
    protected $primaryKey = 'id_firmante';
    protected $allowedFields = ['nombre', 'rol'];

    public function insertarFirmantes($nombre, $rol)
    {
        return $this->insert([
            'nombre' => $nombre,
            'rol' => $rol,
        ]);
    }

    public function actualizarFirmante($idFirmante, $nombre, $rol)
    {
        return $this->update($idFirmante, [
            'nombre' => $nombre,
            'rol' => $rol
        ]);
    }

    public function buscarFirmantes($search)
    {
        return $this->select('id_firmante, nombre, rol')
            ->like('nombre', $search)
            ->orderBy('id_firmante', 'ASC')
            ->limit(5)
            ->findAll();
    }
}
