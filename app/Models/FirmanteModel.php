<?php

namespace App\Models;

use CodeIgniter\Model;

class FirmanteModel extends Model
{
    protected $table = 'firmantes';
    protected $primaryKey = 'id_firmante';
    protected $allowedFields = ['nombre', 'ocupacion'];

    public function insertarFirmantes($nombre, $ocupacion)
    {
        return $this->insert([
            'nombre' => $nombre,
            'ocupacion' => $ocupacion
        ]);
    }

    public function actualizarFirmante($idFirmante, $nombre, $ocupacion)
    {
        return $this->update($idFirmante, [
            'nombre' => $nombre,
            'ocupacion' => $ocupacion
        ]);
    }
}
