<?php

namespace App\Models;

use CodeIgniter\Model;

class FirmanteModel extends Model
{
    protected $table = 'firmantes';
    protected $primaryKey = 'id_firmante';
    protected $allowedFields = ['nombre', 'rol'];

    public function getTodosFirmantes($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('firmantes');

        $total = $builder->countAll();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('nombre', $searchValue)
                ->orLike('rol', $searchValue)
                ->groupEnd();
        }

        $filtered = $builder->countAllResults(false);

        $data = $builder
            ->select('id_firmante AS id,
                    nombre AS nombre_firmante,
                    rol AS rol_firmante')
            ->orderBy('id_firmante', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarFirmantes($nombre, $rol)
    {
        return $this->insert([
            'nombre' => $nombre,
            'rol' => $rol,
        ]);
    }

    public function actualizarFirmante($idFirmante, $nombre, $rol)
    {
        return $this->update($idFirmante, [
            'nombre' => $nombre,
            'rol' => $rol
        ]);
    }

    public function buscarFirmantes($search)
    {
        $builder = $this->select('id_firmante, nombre, rol')
            ->orderBy('id_firmante', 'ASC')
            ->limit(10);

        if (!empty($search)) {
            $builder->groupStart()
                ->like('nombre', $search)
                ->orLike('rol', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
