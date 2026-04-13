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
    ];

    public function actualizarPlanDePago($idPlanDePago, $cantidadCuotas, $montoCuotas)
    {
        return $this->update($idPlanDePago, [
            'cantidad_cuotas' => $cantidadCuotas,
            'monto_cuotas' => $montoCuotas,
        ]);
    }
}
