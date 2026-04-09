<?php

namespace App\Models;

use CodeIgniter\Model;

class BeneficiarioModel extends Model
{
    protected $table = 'beneficiarios';
    protected $primaryKey = 'id_beneficiario';
    protected $allowedFields = ['nombre', 'edad', 'parentesco', 'direccion', 'id_cliente'];

    public function insertarBeneficiario($nombreBeneficiario, $edadBeneficiario, $parentescoBeneficiario, $direccionBeneficiario, $idCliente) {
        return $this->insert([
            'nombre' => $nombreBeneficiario,
            'edad' => $edadBeneficiario,
            'parentesco' => $parentescoBeneficiario,
            'direccion' => $direccionBeneficiario,
            'id_cliente' => $idCliente
        ]);
    }
}
