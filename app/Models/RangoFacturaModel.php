<?php

namespace App\Models;

use CodeIgniter\Model;

class RangoFacturaModel extends Model
{

    protected $table = 'rango_factura';
    protected $primaryKey = 'id_rango_factura';
    protected $allowedFields = ['numero_inicio', 'numero_fin', 'estado', 'id_usuario', 'fecha_creacion'];

    function insertarRango(
        $numeroInicio,
        $numeroFinal,
        $estado,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'numero_inicio' => $numeroInicio,
            'numero_fin' => $numeroFinal,
            'estado' => $estado,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function getRangosFacturas($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('rango_factura');

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAll();

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->like('estado', $searchValue);
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder->countAllResults(false);
        // false = no reinicia el builder (clave)

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                    id_rango_factura as id, 
                    numero_inicio AS numeroDeInicio, 
                    numero_fin AS numeroFin,
                    estado,

                    DATE_FORMAT(fecha_creacion, "%d-%m-%Y") AS fechaCreacion, 
                ')
            ->orderBy('id_rango_factura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }
}
