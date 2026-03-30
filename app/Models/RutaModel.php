<?php

namespace App\Models;

use CodeIgniter\Model;

class RutaModel extends Model
{
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';
    protected $allowedFields = ['codigo', 'nombre', 'fecha_creacion', 'id_usuario'];


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
                ->orLike('nombre', $searchValue)
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
            ->select(
                'id_ruta AS id, 
                codigo AS codigo_ruta, 
                nombre AS nombre_ruta'
            )
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

    public function insertarNuevaRuta($codigo, $nombre, $fechaCreacion, $idUsuario)
    {
        return $this->insert([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'fecha_creacion' => $fechaCreacion,
            'id_usuario' => $idUsuario
        ]);
    }

    public function actualizarRuta($nombre, $idRuta)
    {
        return $this->update($idRuta, [
            'nombre' => $nombre
        ]);
    }
}
