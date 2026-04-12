<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudFirmanteModel extends Model
{
    protected $table = 'solicitud_firmantes';
    protected $primaryKey = 'id_solicitud_firmante';
    protected $allowedFields = ['id_solicitud', 'id_firmante'];
}
