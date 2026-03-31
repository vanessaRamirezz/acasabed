<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodoModel extends Model
{
    protected $table = 'periodos';
    protected $primaryKey = 'id_periodo';
    protected $allowedFields = ['nombre', 'fecha_desde', 'fecha_hasta', 'estado', 'id_usuario', 'fecha_creacion'];

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
                ->like('nombre', $searchValue)
                ->orLike('fecha_hasta', $searchValue)
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
            ->select('id_periodo as id, nombre AS nombre_periodo, fecha_desde as fecha1, fecha_hasta as fecha2, estado as status')
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
        $nombre,
        $desde,
        $hasta,
        $estado,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'nombre' => $nombre,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'estado' => $estado,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarPeriodo(
        $nombre,
        $desde,
        $hasta,
        $estado,
        $idPeriodo,
    ) {
        return $this->update($idPeriodo, [
            'nombre' => $nombre,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'estado' => $estado,
        ]);
    }

    public function buscarPeriodos($search)
    {
        return $this->select('id_periodo AS id, nombre AS periodo')
            ->where('estado','ACTIVO')
            ->like('nombre', $search)
            ->orderBy('id_periodo', 'ASC')
            ->limit(10)
            ->findAll();
    }
}
