<?php

namespace App\Models;

use CodeIgniter\Model;

class RangoFacturaModel extends Model
{

    protected $table = 'rango_factura';
    protected $primaryKey = 'id_rango_factura';
    protected $allowedFields = [
        'tiraje',
        'numero_inicio',
        'numero_fin',
        'numero_actual',
        'estado',
        'id_usuario',
        'fecha_creacion',
    ];

    function insertarRango(
        $numeroInicio,
        $numeroFinal,
        $estado,
        $idUsuario,
        $fechaCreacion,
        $numeroActual
    ) {
        return $this->insert([
            'numero_inicio' => $numeroInicio,
            'numero_fin' => $numeroFinal,
            'estado' => $estado,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion,
            'numero_actual' => $numeroActual
        ]);
    }

    public function getRangosFacturas($start, $length, $searchValue = '')
    {
        // =============================
        // BUILDER BASE
        // =============================
        $builder = $this->db->table('rango_factura');

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder->countAll();

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->like('estado', $searchValue);
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder->countAllResults(false);
        // false = no reinicia el builder (clave)

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                    id_rango_factura as id, 
                    numero_inicio AS numeroDeInicio, 
                    numero_fin AS numeroFin,
                    estado,

                    DATE_FORMAT(fecha_creacion, "%d-%m-%Y") AS fechaCreacion,
                    numero_actual AS numeroActual
                ')
            ->orderBy('id_rango_factura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function obtenerCorrelativoFactura($db)
    {
        $query = $db->query("
        SELECT *
        FROM rango_factura
        WHERE estado = 'Activo'
        LIMIT 1
        FOR UPDATE
    ");

        $rango = $query->getRowArray();

        if (!$rango) {
            throw new \Exception('No hay rango de facturación activo');
        }

        $actual = (int)$rango['numero_actual'];
        $fin    = (int)$rango['numero_fin'];

        if ($actual > $fin) {

            $db->table('rango_factura')
                ->where('id_rango_factura', $rango['id_rango_factura'])
                ->update(['estado' => 'Finalizado']);

            throw new \Exception('El rango de facturación ya fue consumido');
        }

        $siguiente = $actual + 1;

        $db->table('rango_factura')
            ->where('id_rango_factura', $rango['id_rango_factura'])
            ->update(['numero_actual' => $siguiente]);

        // 🔥 ahora retornas todo lo necesario
        return [
            'correlativo' => $actual,
            'id_rango_factura' => $rango['id_rango_factura'],
            'tiraje' => $rango['tiraje']
        ];
    }
}
