<?php

namespace App\Models;

use CodeIgniter\Model;

class AdministradorModel extends Model
{
    protected $table = 'administradores';
    protected $primaryKey = 'id_administrador';
    protected $allowedFields = [
        'nombre',
        'rol',
        'ocupacion',
        'direccion',
        'edad',
        'dui',
        'estado',
        'id_usuario',
        'fecha_creacion'
    ];

    public function getTodosAdministradores($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('administradores');

        $total = $builder->countAll();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('nombre', $searchValue)
                ->orLike('dui', $searchValue)
                ->orLike('rol', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        $data = $builder
            ->select('id_administrador AS id,
                    nombre AS nombre_administrador,
                    rol AS rol_administrador,
                    ocupacion AS ocupacion_administrador,
                    direccion AS direccion_administrador,
                    edad AS edad_administrador,
                    dui AS dui_administrador,
                    estado AS estado_administrador')
            ->orderBy('id_administrador', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevoAdministrador($nombre, $rol, $ocupacion, $direccion, $edad, $dui, $fechaCreacion, $idUsuario)
    {
        return $this->insert([
            'nombre' => $nombre,
            'rol' => $rol,
            'ocupacion' => $ocupacion,
            'direccion' => $direccion,
            'edad' => $edad,
            'dui' => $dui,
            'fecha_creacion' => $fechaCreacion,
            'id_usuario' => $idUsuario
        ]);
    }

    public function actualizarAdministrador($idAdministrador, $nombre, $rol, $ocupacion, $direccion, $edad, $dui)
    {
        return $this->update($idAdministrador, [
            'nombre' => $nombre,
            'rol' => $rol,
            'ocupacion' => $ocupacion,
            'direccion' => $direccion,
            'edad' => $edad,
            'dui' => $dui
        ]);
    }

    public function actualizarEstado($idAdministrador, $nuevoEstado)
    {
        return $this->update($idAdministrador, [
            'estado' => $nuevoEstado
        ]);
    }

    public function buscarAdministradores($search)
    {
        $builder = $this->select('id_administrador, nombre, rol, ocupacion, direccion, edad, dui')
            ->where('estado', 'SI')
            ->orderBy('id_administrador', 'ASC')
            ->limit(10);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('nombre', $search)
                ->orLike('rol', $search)
                ->orLike('dui', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
