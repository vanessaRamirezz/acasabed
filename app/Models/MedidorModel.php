<?php

namespace App\Models;

use CodeIgniter\Model;


class MedidorModel extends Model
{
    protected $table = 'medidores';
    protected $primaryKey = 'id_medidor';
    protected $allowedFields = ['numero_serie', 'fecha_instalacion', 'fecha_activacion', 'fecha_desactivacion', 'id_contrato', 'id_instalador', 'id_usuario', 'fecha_creacion', 'estado'];

    public function getTodosMedidores(int $start, int $length, string $searchValue = '')
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
                ->orLike('contratos.numero_contrato', $searchValue)
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
                medidores.estado AS status,
                medidores.fecha_instalacion AS fecha_de_instalacion,
                DATE_FORMAT(medidores.fecha_instalacion, "%d-%m-%Y") AS fecha_de_instalacion_texto,
                medidores.fecha_activacion AS fecha_de_activacion,
                DATE_FORMAT(medidores.fecha_activacion, "%d-%m-%Y") AS fecha_de_activacion_texto,
                medidores.fecha_desactivacion AS fecha_de_desactivacion,
                DATE_FORMAT(medidores.fecha_desactivacion, "%d-%m-%Y") AS fecha_de_desactivacion_texto,
                contratos.id_contrato AS id_de_contrato,
                contratos.numero_contrato AS codigo_de_contrato,
                instaladores.id_instalador AS id_de_instalador,
                instaladores.nombre_completo AS nombre_instalador
            ')
            ->orderBy('medidores.id_medidor', 'DESC')
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
        ?string $numeroSerie,
        ?string $fechaInstalacion,
        ?string $fechaActivacion,
        ?string $fechaDesactivacion,
        ?int $idContrato,
        ?int $idInstalador,
        ?int $idUsuario,
        ?string $fechaCreacion,
        ?string $estado
    ) {
        return $this->insert([
            'numero_serie' => $numeroSerie,
            'fecha_instalacion' => $fechaInstalacion,
            'fecha_activacion' => $fechaActivacion,
            'fecha_desactivacion' => $fechaDesactivacion,
            'id_contrato' => $idContrato,
            'id_instalador' => $idInstalador,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion,
            'estado' => $estado,
        ]);
    }

    public function actualizarMedidor(
        ?string $numeroSerie,
        ?string $fechaInstalacion,
        ?string $fechaActivacion,
        ?string $fechaDesactivacion,
        ?int $idContrato,
        ?int $idInstalador,
        ?int $idMedidor,
        ?string $estado
    ) {
        return $this->update($idMedidor, [
            'numero_serie' => $numeroSerie,
            'fecha_instalacion' => $fechaInstalacion,
            'fecha_activacion' => $fechaActivacion,
            'fecha_desactivacion' => $fechaDesactivacion,
            'id_contrato' => $idContrato,
            'id_instalador' => $idInstalador,
            'estado' => $estado,
        ]);
    }

    public function buscarMedidores(string $search)
    {
        return $this->select('id_medidor, numero_serie')
            ->like('numero_serie', $search)
            ->orderBy('id_medidor', 'ASC')
            ->limit(10)
            ->findAll();
    }

    public function getReporteMedidores($estado = null){
        $builder = $this->db->table('medidores m');
        $builder->select('
            m.numero_serie,
            m.fecha_instalacion,
            DATE_FORMAT(m.fecha_instalacion, "%d-%m-%Y") AS fecha_de_instalacion_texto,
            m.fecha_activacion,
            DATE_FORMAT(m.fecha_activacion, "%d-%m-%Y") AS fecha_de_activacion_texto,
            m.fecha_desactivacion,
            DATE_FORMAT(m.fecha_desactivacion, "%d-%m-%Y") AS fecha_de_desactivacion_texto,
            c.numero_contrato,
            i.nombre_completo,
            m.estado
        ');
        $builder->join('contratos c', 'c.id_contrato = m.id_contrato','left');
        $builder->join('instaladores i', 'i.id_instalador = m.id_instalador','left');

        if($estado){
            $builder->where('m.estado', $estado);
        }

        return $builder->get()->getResultArray();
    }
}
