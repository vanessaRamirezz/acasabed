<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodoModel extends Model
{
    protected $table = 'periodos';
    protected $primaryKey = 'id_periodo';
    protected $allowedFields = ['fecha_desde', 'fecha_hasta', 'estado', 'id_usuario', 'fecha_creacion'];

    public function getTodosPeriodo($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('periodos');

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAll();

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('fecha_desde', $searchValue)
                ->orLike('fecha_hasta', $searchValue)
                ->orLike('estado', $searchValue)
                ->groupEnd();
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
            ->select('id_periodo as id, fecha_desde as fecha1, fecha_hasta as fecha2, estado as status')
            ->orderBy('id_periodo', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevoPeriodo(
        $desde,
        $hasta,
        $estado,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'estado' => $estado,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarPeriodo(
        $desde,
        $hasta,
        $estado,
        $idPeriodo,
    ) {
        return $this->update($idPeriodo, [
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'estado' => $estado,
        ]);
    }
}
