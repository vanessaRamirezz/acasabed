<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifaModel extends Model
{
    protected $table = 'tarifario';
    protected $primaryKey = 'id_tarifa';
    protected $allowedFields = ['codigo', 'id_tipo_cliente', 'valor_metro_cubico', 'desde_n_metros', 'hasta_n_metros', 'pago_minimo', 'id_usuario', 'fecha_creacion'];

    public function getTodasTarifas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('tarifario');

        // JOIN desde el inicio
        $builder->join(
            'tipos_de_cliente',
            'tarifario.id_tipo_cliente = tipos_de_cliente.id_tipo_cliente',
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
                ->like('tarifario.codigo', $searchValue)
                ->orLike('tipos_de_cliente.nombre', $searchValue) // 👈 AQUÍ
                ->orLike('tarifario.hasta_n_metros', $searchValue)
                ->orLike('tarifario.desde_n_metros', $searchValue)
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
                tarifario.id_tarifa,
                tarifario.codigo,
                tipos_de_cliente.nombre AS nombre_tipo_cliente,
                tarifario.valor_metro_cubico,
                tarifario.desde_n_metros,
                tarifario.hasta_n_metros,
                tarifario.pago_minimo
        ')
            ->orderBy('tarifario.id_tarifa', 'DESC')
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
        $valorMetro,
        $desde,
        $hasta,
        $pagoMinimo,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'codigo' => $codigo,
            'id_tipo_cliente' => $tipoCliente,
            'valor_metro_cubico' => $valorMetro,
            'desde_n_metros' => $desde,
            'hasta_n_metros' => $hasta,
            'pago_minimo' => $pagoMinimo,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarTarifa(
        $tipoCliente,
        $valorMetro,
        $desde,
        $hasta,
        $pagoMinimo,
        $idTarifa
    ) {
        return $this->update($idTarifa, [
            'id_tipo_cliente' => $tipoCliente,
            'valor_metro_cubico' => $valorMetro,
            'desde_n_metros' => $desde,
            'hasta_n_metros' => $hasta,
            'pago_minimo' => $pagoMinimo
        ]);
    }

    public function buscarTarifas($search)
    {
        return $this->select('id_tarifa, codigo, desde_n_metros, hasta_n_metros')
            ->like('codigo', $search)
            ->orderBy('id_tarifa', 'ASC')
            ->limit(10)
            ->findAll();
    }
}
