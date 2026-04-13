<?php

namespace App\Models;

use CodeIgniter\Model;

class LecturaModel extends Model
{
    protected $table = 'lecturas';
    protected $primaryKey = 'id_lectura';
    protected $allowedFields = ['id_periodo', 'id_contrato', 'fecha', 'valor', 'id_instalador', 'id_usuario', 'fecha_creacion'];

    public function getTodasLecturas($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('lecturas');

        // JOIN desde el inicio
        $builder->join(
            'periodos',
            'lecturas.id_periodo = periodos.id_periodo',
            'left'
        );

        // JOIN desde el inicio
        $builder->join(
            'contratos',
            'lecturas.id_contrato = contratos.id_contrato',
            'left'
        );

        // JOIN desde el inicio
        $builder->join(
            'instaladores',
            'lecturas.id_instalador = instaladores.id_instalador',
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
                ->like('lecturas.fecha', $searchValue)
                ->orLike('periodos.nombre', $searchValue)
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
                lecturas.id_lectura AS id,
                periodos.id_periodo AS id_de_periodo,
                periodos.nombre AS nombre_de_periodo,
                contratos.id_contrato AS id_de_contrato,
                contratos.numero_contrato AS codigo_de_contrato,
                lecturas.fecha AS fecha_toma_lectura,
                DATE_FORMAT(lecturas.fecha, "%d-%m-%Y") AS fecha_toma_lectura_texto,
                lecturas.valor AS valor_obtenido,
                instaladores.id_instalador AS id_de_instalador,
                instaladores.nombre_completo AS nombre_instalador
            ')
            ->orderBy('lecturas.id_lectura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function insertarNuevaLectura(
        $idPeriodo,
        $idContrato,
        $fecha,
        $valor,
        $idInstalador,
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'id_periodo' => $idPeriodo,
            'id_contrato' => $idContrato,
            'fecha' => $fecha,
            'valor' => $valor,
            'id_instalador' => $idInstalador,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion
        ]);
    }

    public function actualizarLectura(
        $idPeriodo,
        $idContrato,
        $fecha,
        $valor,
        $idInstalador,
        $idLectura
    ) {
        return $this->update($idLectura, [
            'id_periodo' => $idPeriodo,
            'id_contrato' => $idContrato,
            'fecha' => $fecha,
            'valor' => $valor,
            'id_instalador' => $idInstalador,
        ]);
    }
}
