<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    protected $allowedFields = ['nombres', 'apellidos', 'dui', 'correo', 'telefono', 'clave', 'id_perfil', 'estado', 'fecha_creacion'];

    public function informacionUsuario($dui)
    {
        return $this->where('dui', $dui)
            ->first();
    }

    public function getAccesosUsuario($dui)
    {
        return $this->select('accesos.url_acceso, accesos.orden_acceso, accesos.icono, accesos.acceso, accesos.agrupacion')
            ->join('perfiles', 'usuarios.id_perfil = perfiles.id_perfil')
            ->join('perfiles_acceso', 'perfiles.id_perfil = perfiles_acceso.id_perfil')
            ->join('accesos', 'perfiles_acceso.id_acceso = accesos.id_acceso')
            ->where('usuarios.dui', $dui)
            // ->orderBy('accesos.orden_acceso', 'ASC')
            ->findAll();
    }

    public function obtenerUsuarios()
    {
        return $this->select('usuarios.*, perfiles.nombre')
            ->join('perfiles', 'usuarios.id_perfil = perfiles.id_perfil')
            ->findAll();
    }

    public function actualizarUsuario($idUsuario, $dui, $nombres, $apellidos, $correo, $perfil, $telefono, $contrasena)
    {
        $data = [
            'dui' => $dui,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'id_perfil' => $perfil,
            'telefono' => $telefono,
        ];

        // Solo actualizar contraseña si viene
        if (!empty($contrasena)) {
            $data['clave'] = $contrasena;
        }

        return $this->update($idUsuario, $data);
    }

    public function insertarNuevoUsuario($dui, $nombres, $apellidos, $correo, $perfil, $telefono, $contrasena, $fecha)
    {
        return $this->insert([
            'dui' => $dui,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'id_perfil' => $perfil,
            'telefono' => $telefono,
            'clave' => $contrasena,
            'fecha_creacion' => $fecha
        ]);
    }

    public function actualizarEstado($idUsuario, $nuevoEstado)
    {
        return $this->update($idUsuario, [
            'estado' => $nuevoEstado
        ]);
    }
}
