<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanDePagoModel extends Model
{
    protected $table = 'plan_de_pago';
    protected $primaryKey = 'id_plan_de_pago';
    protected $allowedFields = [
        'cantidad_cuotas',
        'monto_cuotas',
        'id_contrato'
    ];

    public function guardarPlanDePago($cantidadCuotas, $montoCuotas, $idContrato)
    {
        return $this->insert([
            'cantidad_cuotas' => $cantidadCuotas,
            'monto_cuotas' => $montoCuotas,
            'id_contrato' => $idContrato
        ]);
    }
}
