<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifaModel extends Model
{
    protected $table = 'tarifas';
    protected $primaryKey = 'id_tarifa';
    protected $allowedFields = ['codigo', 'id_tipo_cliente', 'id_usuario', 'fecha_creacion'];

    public function getTodasTarifas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('tarifas');

        // JOIN desde el inicio
        $builder->join(
            'tipos_de_cliente',
            'tarifas.id_tipo_cliente = tipos_de_cliente.id_tipo_cliente',
            'inner'
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
                ->like('tarifas.codigo', $searchValue)
                ->orLike('tipos_de_cliente.nombre', $searchValue) // 👈 AQUÍ
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
                tarifas.id_tarifa,
                tarifas.codigo,
                tarifas.id_tipo_cliente,
                tipos_de_cliente.nombre AS nombre_tipo_cliente
        ')
            ->orderBy('tarifas.id_tarifa', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevaTarifa(
        $codigo,
        $tipoCliente,
        $idUsuario,
        $fechaCreacion
    ) {
        $this->insert([
            'codigo' => $codigo,
            'id_tipo_cliente' => $tipoCliente,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);

        return $this->insertID();
    }


    public function buscarTarifas($search)
    {
        return $this->select('tarifas.id_tarifa, tarifas.codigo, tipos_de_cliente.nombre')
            ->join('tipos_de_cliente', 'tipos_de_cliente.id_tipo_cliente = tarifas.id_tipo_cliente')
            ->like('tarifas.codigo', $search)
            ->orderBy('id_tarifa', 'ASC')
            ->limit(10)
            ->findAll();
    }
}
