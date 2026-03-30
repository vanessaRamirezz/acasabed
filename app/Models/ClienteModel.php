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
        'id_colonia',
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
        'comentarios',
        'foto'
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
        $colonia,
        $complementoDireccion,
        $fechaDeVencimientoDui,
        $rutaFrontalDB,
        $rutaReversaDB,
        $comentarios,
        $idUsuario,
        $fechaCreacion,
        $foto
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
            'id_colonia' => $colonia,
            'complemento_direccion' => $complementoDireccion,
            'fecha_vencimiento_dui' => $fechaDeVencimientoDui,
            'dui_frontal' => $rutaFrontalDB,
            'dui_reversa' => $rutaReversaDB,
            'comentarios' => $comentarios,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion,
            'foto' => $foto
        ]);
    }

    public function getTodosClientes($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('clientes');

        // JOIN desde el inicio
        $builder->join(
            'tipos_de_cliente',
            'clientes.id_tipo_cliente = tipos_de_cliente.id_tipo_cliente',
            'left'
        );

        $builder->join(
            'actividades_economicas',
            'clientes.id_actividad_economica = actividades_economicas.id_actividad_economica',
            'left'
        );

        $builder->join(
            'departamentos',
            'clientes.id_departamento = departamentos.id_departamento',
            'left'
        );

        $builder->join(
            'municipios',
            'clientes.id_municipio = municipios.id_municipio',
            'left'
        );

        $builder->join(
            'distritos',
            'clientes.id_distrito = distritos.id_distrito',
            'left'
        );

        $builder->join(
            'colonias',
            'clientes.id_colonia = colonias.id_colonia',
            'left'
        );

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAllResults(false);

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('clientes.codigo', $searchValue)
                ->orLike('clientes.nombre_completo', $searchValue)
                ->orLike('tipos_de_cliente.nombre', $searchValue)
                ->groupEnd();
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                clientes.id_cliente,
                clientes.codigo,
                clientes.nombre_completo AS nombre,
                clientes.sexo,
                clientes.ocupacion,
                clientes.fecha_nacimiento AS fecha_de_nacimiento,
                clientes.telefono AS telefonos,
                clientes.correo AS email,
                clientes.dui AS numero_de_dui,
                clientes.nit AS numero_de_nit,
                clientes.nrc AS numero_de_nrc,
                actividades_economicas.id_actividad_economica,
                actividades_economicas.nombre as nombre_actividad_economica,
                tipos_de_cliente.id_tipo_cliente,
                tipos_de_cliente.nombre AS nombre_tipo_cliente,
                clientes.contacto AS nombre_de_contacto,
                clientes.contacto_dui AS numero_de_dui,
                clientes.contacto_telefono AS numeros_de_telefonos,
                departamentos.id_departamento,
                municipios.id_municipio,
                distritos.id_distrito,
                colonias.id_colonia,
                clientes.complemento_direccion  AS direccion_complemento,
                clientes.fecha_vencimiento_dui AS fecha_de_vencimiento_dui,
                clientes.dui_frontal AS foto_de_dui_frontal,
                clientes.dui_reversa AS foto_de_dui_reversa,
                clientes.comentarios,
                clientes.foto AS foto_de_cliente
        ')
            ->orderBy('clientes.id_cliente', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function obtenerClientePorId($idCliente)
    {
        return $this
            ->where('id_cliente', $idCliente)
            ->first();
    }

    public function actualizarCliente(
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
        $colonia,
        $complementoDireccion,
        $fechaDeVencimientoDui,
        $rutaFrontalDB,
        $rutaReversaDB,
        $comentarios,
        $foto,
        $idCliente
    ) {

        $data = [
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
            'id_colonia' => $colonia,
            'complemento_direccion' => $complementoDireccion,
            'fecha_vencimiento_dui' => $fechaDeVencimientoDui,
            'comentarios' => $comentarios,
        ];

        //SOLO actualizar si vienen datos
        if (!empty($foto)) {
            $data['foto'] = $foto;
        }


        if (!empty($rutaFrontalDB)) {
            $data['dui_frontal'] = $rutaFrontalDB;
        }

        if (!empty($rutaReversaDB)) {
            $data['dui_reversa'] = $rutaReversaDB;
        }

        return $this->update($idCliente, $data);
    }
}
