<?php

namespace App\Models;

use CodeIgniter\Model;

class RutaModel extends Model
{
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';
    protected $allowedFields = ['codigo', 'desde', 'hasta', 'fecha_creacion', 'id_usuario'];


    public function getTodasRutas($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('rutas');

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAll();

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('codigo', $searchValue)
                ->orLike('desde', $searchValue)
                ->orLike('hasta', $searchValue)
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
            ->select('id_ruta, codigo, desde, hasta')
            ->orderBy('id_ruta', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevaRuta($codigo, $desde, $hasta, $fechaCreacion, $idUsuario)
    {
        return $this->insert([
            'codigo' => $codigo,
            'desde' => $desde,
            'hasta' => $hasta,
            'fecha_creacion' => $fechaCreacion,
            'id_usuario' => $idUsuario
        ]);
    }

    public function actualizarRuta($desde, $hasta, $idRuta)
    {
        return $this->update($idRuta, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }
}
