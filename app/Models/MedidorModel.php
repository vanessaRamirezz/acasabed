<?php

namespace App\Models;

use CodeIgniter\Model;


class MedidorModel extends Model
{
    protected $table = 'medidores';
    protected $primaryKey = 'id_medidor';
    protected $allowedFields = ['numero_serie', 'fecha_instalacion', 'id_contrato', 'id_instalador', 'id_usuario', 'fecha_creacion'];

    public function getTodosMedidores($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('medidores');

        // JOIN desde el inicio
        $builder->join(
            'contratos',
            'medidores.id_contrato = contratos.id_contrato',
            'left'
        );

        // JOIN desde el inicio
        $builder->join(
            'instaladores',
            'medidores.id_instalador = instaladores.id_instalador',
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
                ->like('medidores.numero_serie', $searchValue)
                ->orLike('contratos.codigo', $searchValue)
                ->orLike('instaladores.nombre_completo', $searchValue)
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
                medidores.id_medidor AS id,
                medidores.numero_serie AS numeros_de_serie,
                medidores.fecha_instalacion AS fecha_de_instalacion,
                contratos.id_contrato AS id_de_contrato,
                contratos.codigo AS codigo_de_contrato,
                instaladores.id_instalador AS id_de_instalador,
                instaladores.nombre_completo AS nombre_instalador
            ')
            ->orderBy('id_medidor', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevoMedidor(
        $numeroSerie,
        $fechaInstalacion,
        $idContrato,
        $idInstalador,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'numero_serie' => $numeroSerie,
            'fecha_instalacion' => $fechaInstalacion,
            'id_contrato' => $idContrato,
            'id_instalador' => $idInstalador,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarMedidor(
        $numeroSerie,
        $fechaInstalacion,
        $idContrato,
        $idInstalador,
        $idMedidor
    ) {
        return $this->update($idMedidor, [
            'numero_serie' => $numeroSerie,
            'fecha_instalacion' => $fechaInstalacion,
            'id_contrato' => $idContrato,
            'id_instalador' => $idInstalador,
        ]);
    }
}
