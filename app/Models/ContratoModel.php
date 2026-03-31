<?php

namespace App\Models;

use codeIgniter\Model;

class ContratoModel extends Model
{
    protected $table = 'contratos';
    protected $primaryKey = 'id_contrato';
    protected $allowedFields = ['codigo'];


    public function buscarContratos($search)
    {
        return $this->select('id_contrato, codigo')
            ->like('codigo', $search)
            ->orderBy('id_contrato', 'ASC')
            ->limit(10)
            ->findAll();
    }
}
