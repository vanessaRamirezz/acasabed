<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    protected $allowedFields = ['codigo', 'nombre', 'valor', 'estado', 'id_operacion', 'id_tipos_servicios'];

    public function getServiciosMantenimiento()
    {
        return $this->db->table('servicios s')
            ->select("
                s.id_servicio,
                s.codigo,
                s.nombre,
                s.valor,
                s.estado,
                s.id_operacion,
                o.nombre AS operacion,
                s.id_tipos_servicios,
                ts.nombre AS nombre_tipo
            ", false)
            ->join('operaciones o', 'o.id_operacion = s.id_operacion', 'left')
            ->join('tipos_servicios ts', 'ts.id_tipos_servicios = s.id_tipos_servicios', 'left')
            ->orderBy('s.id_servicio', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function insertarNuevoServicio(
        ?string $codigo,
        ?string $nombre,
        ?float $valor,
        ?string $estado,
        ?int $idOperacion,
        ?int $tipo
    ) {
        return $this->insert([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'monto' => $valor,
            'estado' => $estado,
            'id_operacion' => $idOperacion,
            'id_tipos_servicios' => $tipo
        ]);
    }

    public function actualizarServicio(
        int $idServicio,
        ?string $codigo,
        ?string $nombre,
        ?float $valor,
        ?string $estado,
        ?int $idOperacion,
        ?int $tipo,
    ) {
        return $this->update($idServicio, [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'monto' => $valor,
            'estado' => $estado,
            'id_operacion' => $idOperacion,
            'id_tipos_servicios' => $tipo
        ]);
    }

    public function buscarServiciosActivos(string $search = '')
    {
        $builder = $this->db->table('servicios s')
            ->select("
                s.id_servicio AS id,
                s.codigo,
                s.nombre,
                s.valor,
                s.id_operacion,
                COALESCE(o.nombre, 'SUMA') AS operacion,
                CONCAT(s.codigo, ' - ', s.nombre, ' (', COALESCE(o.nombre, 'SUMA'), ')') AS text
            ", false)
            ->join('operaciones o', 'o.id_operacion = s.id_operacion', 'left')
            ->where('s.estado', 'Activo');

        if ($search !== '') {
            $builder->groupStart()
                ->like('s.codigo', $search)
                ->orLike('s.nombre', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('s.nombre', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }
}
