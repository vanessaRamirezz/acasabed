<?php

namespace App\Models;

use CodeIgniter\Model;

class FirmanteModel extends Model
{
    protected $table = 'firmantes';
    protected $primaryKey = 'id_firmante';
    protected $allowedFields = ['nombre'];

    public function insertarFirmantes($nombre)
    {
        return $this->insert([
            'nombre' => $nombre,
        ]);
    }

    public function actualizarFirmante($idFirmante, $nombre)
    {
        return $this->update($idFirmante, [
            'nombre' => $nombre,
        ]);
    }
}
