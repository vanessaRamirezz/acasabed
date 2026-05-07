<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoClienteModel extends Model
{
    protected $table = 'tipos_de_cliente';
    protected $primaryKey = 'id_tipo_cliente';
    protected $allowedFields = ['codigo', 'nombre', 'id_usuario', 'fecha_creacion'];

    public function insertarNuevoTipoCliente(?string $codigo, ?string $tipoCliente, ?int $idUsuario, ?string $fechaCreacion){
        return $this->insert([
            'codigo' => $codigo,
            'nombre' => $tipoCliente,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarTipoCliente(?int $idTipoCliente, ?string $tipoCliente){
        return $this->update($idTipoCliente, [
            'nombre' => $tipoCliente
        ]);
    }

}
