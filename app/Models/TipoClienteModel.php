<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoClienteModel extends Model
{
    protected $table = 'tipos_de_cliente';
    protected $primaryKey = 'id_tipo_cliente';
    protected $allowedFields = ['codigo', 'nombre', 'id_usuario', 'fecha_creacion'];

    public function insertarNuevoTipoCliente($codigo, $tipoCliente, $idUsuario, $fechaCreacion){
        return $this->insert([
            'codigo' => $codigo,
            'nombre' => $tipoCliente,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarTipoCliente($idTipoCliente, $tipoCliente){
        return $this->update($idTipoCliente, [
            'nombre' => $tipoCliente
        ]);
    }

}
