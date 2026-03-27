<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    protected $allowedFields = [
        'codigo',
        'dui',
        'sexo',
        'nombre_completo',
        'nit',
        'nrc',
        'ocupacion',
        'id_actividad_economica',
        'id_departamento',
        'id_municipio',
        'id_distrito',
        'id_direccion',
        'complemento_direccion',
        'telefono',
        'fecha_vencimiento_dui',
        'id_tipo_cliente',
        'fecha_nacimiento',
        'correo',
        'dui_frontal',
        'dui_reversa',
        'contacto',
        'contacto_dui',
        'contacto_telefono',
        'id_usuario',
        'fecha_creacion',
        'comentarios'
    ];

    public function insertarNuevoCliente(
        $codigo,
        $nombre,
        $sexo,
        $ocupacion,
        $fechaDeNacimiento,
        $telefonos,
        $correo,
        $dui,
        $nit,
        $nrc,
        $actividadEconomica,
        $tipoCliente,
        $contactoNombre,
        $contactoDui,
        $contactoTelefonos,
        $departamentos,
        $municipios,
        $distritos,
        $direccion,
        $complementoDireccion,
        $fechaDeVencimientoDui,
        $rutaFrontalDB,
        $rutaReversaDB,
        $comentarios,
        $idUsuario,
        $fechaCreacion
    ) {

        return $this->insert([
            'codigo' => $codigo,
            'nombre_completo' => $nombre,
            'sexo' => $sexo,
            'ocupacion' => $ocupacion,
            'fecha_nacimiento' => $fechaDeNacimiento,
            'telefono' => $telefonos,
            'correo' => $correo,
            'dui' => $dui,
            'nit' => $nit,
            'nrc' => $nrc,
            'id_actividad_economica' => $actividadEconomica,
            'id_tipo_cliente' => $tipoCliente,
            'contacto' => $contactoNombre,
            'contacto_dui' => $contactoDui,
            'contacto_telefono' => $contactoTelefonos,
            'id_departamento' => $departamentos,
            'id_municipio' => $municipios,
            'id_distrito' => $distritos,
            'id_direccion' => $direccion,
            'complemento_direccion' => $complementoDireccion,
            'fecha_vencimiento_dui' => $fechaDeVencimientoDui,
            'dui_frontal' => $rutaFrontalDB,
            'dui_reversa' => $rutaReversaDB,
            'comentarios' => $comentarios,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }
}
