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
            ->select('
                    id_periodo as id, 
                    nombre AS nombre_periodo, 

                    DATE_FORMAT(fecha_desde, "%Y-%m-%d") AS fecha1, 
                    DATE_FORMAT(fecha_hasta, "%Y-%m-%d") AS fecha2,

                    DATE_FORMAT(fecha_desde, "%d-%m-%Y") AS fecha1_texto, 
                    DATE_FORMAT(fecha_hasta, "%d-%m-%Y") AS fecha2_texto,

                    estado as status
                ')
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

    public function buscarPeriodos()
    {
        return $this->select('id_periodo AS id, nombre AS periodo')
            ->where('estado', 'ACTIVO')
            ->orderBy('id_periodo', 'ASC')
            ->first();
    }

    public function getPeriodoActivo()
    {
        return $this->select('id_periodo, nombre')
            ->where('estado', 'ACTIVO')
            ->orderBy('id_periodo', 'DESC') // por si hay más de uno
            ->first();
    }

    public function buscarPeriodosSelect($search = '')
    {
        $builder = $this->select('id_periodo AS id, nombre AS text');

        if (!empty($search)) {
            $builder->like('nombre', $search);
        }

        return $builder
            ->orderBy('id_periodo', 'DESC')
            ->limit(15)
            ->findAll();
    }
}
