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
        'extendido',
        'fecha',
        'sexo',
        'nombre_completo',
        'edad',
        'nit',
        'nrc',
        'ocupacion',
        'estado_familiar',
        'numero_grupo_familiar',
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
        'lugar_nacimiento',
        'lugar_de_trabajo',
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
        $edad,
        $sexo,
        $ocupacion,
        $estadoFamiliar,
        $numeroGrupoFamiliar,
        $lugarNacimiento,
        $fechaDeNacimiento,
        $lugarDeTrabajo,
        $telefonos,
        $correo,
        $dui,
        $extendido,
        $fecha,
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
            'edad' => $edad,
            'sexo' => $sexo,
            'ocupacion' => $ocupacion,
            'estado_familiar' => $estadoFamiliar,
            'numero_grupo_familiar' => $numeroGrupoFamiliar,
            'lugar_nacimiento' => $lugarNacimiento,
            'fecha_nacimiento' => $fechaDeNacimiento,
            'lugar_de_trabajo' => $lugarDeTrabajo,
            'telefono' => $telefonos,
            'correo' => $correo,
            'dui' => $dui,
            'extendido' => $extendido,
            'fecha' => $fecha,
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
                clientes.edad,
                clientes.sexo,
                clientes.ocupacion,
                clientes.estado_familiar,
                clientes.numero_grupo_familiar,
                clientes.lugar_nacimiento AS lugar_de_nacimiento,
                clientes.fecha_nacimiento AS fecha_de_nacimiento,
                clientes.lugar_de_trabajo AS lugar_trabajo,
                clientes.telefono AS telefonos,
                clientes.correo AS email,
                clientes.dui AS numero_de_dui,
                clientes.extendido,
                clientes.fecha,
                clientes.nit AS numero_de_nit,
                clientes.nrc AS numero_de_nrc,
                actividades_economicas.id_actividad_economica,
                actividades_economicas.nombre as nombre_actividad_economica,
                tipos_de_cliente.id_tipo_cliente,
                tipos_de_cliente.nombre AS nombre_tipo_cliente,
                clientes.contacto AS nombre_de_contacto,
                clientes.contacto_dui AS numero_de_dui_contacto,
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
        $edad,
        $sexo,
        $ocupacion,
        $estadoFamiliar,
        $numeroGrupoFamiliar,
        $lugarNacimiento,
        $fechaDeNacimiento,
        $lugarDeTrabajo,
        $telefonos,
        $correo,
        $dui,
        $extendido,
        $fecha,
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
            'edad' => $edad,
            'sexo' => $sexo,
            'ocupacion' => $ocupacion,
            'estado_familiar' => $estadoFamiliar,
            'numero_grupo_familiar' => $numeroGrupoFamiliar,
            'lugar_nacimiento' => $lugarNacimiento,
            'fecha_nacimiento' => $fechaDeNacimiento,
            'lugar_de_trabajo' => $lugarDeTrabajo,
            'telefono' => $telefonos,
            'correo' => $correo,
            'dui' => $dui,
            'extendido' => $extendido,
            'fecha' => $fecha,
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

    public function buscarClientes($search)
    {
        return $this->select("
                clientes.id_cliente,
                clientes.nombre_completo,
                clientes.edad,
                clientes.dui,
                clientes.extendido,
                clientes.fecha,
                clientes.lugar_nacimiento,
                clientes.fecha_nacimiento,
                clientes.estado_familiar,
                clientes.numero_grupo_familiar,
                CONCAT_WS(', ',
                    departamentos.nombre,
                    municipios.nombre,
                    distritos.nombre,
                    colonias.nombre,
                    clientes.complemento_direccion
                ) AS direccion_completa,
                clientes.lugar_de_trabajo AS lugar_trabajo,
                clientes.ocupacion,
                clientes.telefono
            ", false)
            ->join('departamentos', 'departamentos.id_departamento = clientes.id_departamento', 'left')
            ->join('municipios', 'municipios.id_municipio = clientes.id_municipio', 'left')
            ->join('distritos', 'distritos.id_distrito = clientes.id_distrito', 'left')
            ->join('colonias', 'colonias.id_colonia = clientes.id_colonia', 'left')
            ->like('clientes.nombre_completo', $search)
            ->orderBy('clientes.id_cliente', 'DESC')
            ->limit(10)
            ->findAll();
    }
}
