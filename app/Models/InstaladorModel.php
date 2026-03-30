<?php

namespace App\Models;

use CodeIgniter\Model;

class InstaladorModel extends Model
{
    protected $table = 'instaladores';
    protected $primaryKey = 'id_instalador';
    protected $allowedFields = ['nombre_completo', 'telefono', 'dui', 'direccion', 'correo', 'fecha_creacion', 'id_usuario', 'estado'];

    public function getTodosInstaladores($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('instaladores');

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAll();

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('nombre_completo', $searchValue)
                ->orLike('dui', $searchValue)
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
            ->select('id_instalador AS id,
                    nombre_completo AS nombre_instalador, 
                    telefono AS telefono_instalador, 
                    dui AS dui_instalador, 
                    direccion AS direccion_instalador, 
                    correo AS correo_instalador, 
                    estado AS estado_instalador')
            ->orderBy('id_instalador', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevoInstalador($nombre, $telefono, $dui, $direccion, $correo, $fechaCreacion, $idUsuario)
    {
        return $this->insert([
            'nombre_completo' => $nombre,
            'telefono' => $telefono,
            'dui' => $dui,
            'direccion' => $direccion,
            'correo' => $correo,
            'fecha_creacion' => $fechaCreacion,
            'id_usuario' => $idUsuario
        ]);
    }

    public function actualizarInstalador($nombre, $telefono, $dui, $direccion, $correo, $idInstalador)
    {
        return $this->update($idInstalador, [
            'nombre_completo' => $nombre,
            'telefono' => $telefono,
            'dui' => $dui,
            'direccion' => $direccion,
            'correo' => $correo
        ]);
    }

    public function actualizarEstado($idInstalador, $nuevoEstado)
    {
        return $this->update($idInstalador, [
            'estado' => $nuevoEstado
        ]);
    }
}
