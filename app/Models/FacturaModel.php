<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaModel extends Model
{
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    protected $allowedFields = [
        'id_rango_factura',
        'correlativo',
        'tiraje',
        'id_contrato',
        'id_periodo',
        'id_lectura',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'total',
        'id_usuario',
        'fecha_de_pago',
        'mora',
        'tipo'
    ];

    public function getFacturasExcel(?int $idPeriodo)
    {
        return $this->db->table('facturas f')
            ->select("
            f.tiraje,
            f.correlativo,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            cl.codigo,
            DATE_FORMAT(f.fecha_emision, '%d-%m-%Y') AS fechaEmision,
            f.fecha_emision,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            f.fecha_vencimiento,
            f.total,
            f.estado,
            f.fecha_de_pago
        ")
            ->where('f.id_periodo', $idPeriodo)
            ->where('f.estado', 'PENDIENTE')
            ->join('contratos c', 'c.id_contrato = f.id_contrato')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente')
            ->orderBy('f.id_factura', 'ASC')
            ->get()
            ->getResultArray();
    }

    // modelo para facturas de cobros de instalacion
    public function getHistorial(int $start, int $length, $searchValue = '')
    {
        $db = $this->db;

        /**
         * ==========================================
         * BASE QUERY (SIN JOIN A DETALLE)
         * ==========================================
         */
        $baseBuilder = $db->table('facturas f');

        $baseBuilder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $baseBuilder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $baseBuilder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $baseBuilder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        $baseBuilder->where('f.tipo', 'Instalacion');

        /**
         * ==========================================
         * TOTAL (SIN FILTROS)
         * ==========================================
         */
        $total = (clone $baseBuilder)->countAllResults();

        /**
         * ==========================================
         * FILTRO DE BÚSQUEDA
         * ==========================================
         */
        if (!empty($searchValue)) {
            $baseBuilder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('f.correlativo', $searchValue)
                ->orLike('p.nombre', $searchValue)
                ->groupEnd();
        }

        /**
         * ==========================================
         * TOTAL FILTRADO
         * ==========================================
         */
        $filtered = (clone $baseBuilder)->countAllResults();

        /**
         * ==========================================
         * DATA FINAL
         * ==========================================
         */
        $data = $baseBuilder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            f.estado,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            f.fecha_emision,
            f.fecha_de_pago,
            p.nombre AS periodo
        ")
            ->orderBy('f.id_factura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    // public function obtenerFacturaPorId(int $idFactura)
    // {
    //     $builder = $this->db->table('facturas f');

    //     $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
    //     $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
    //     $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
    //     $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
    //     $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
    //     $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
    //     $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
    //     $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');

    //     $factura = $builder
    //         ->select("
    //         f.id_factura AS id,
    //         f.correlativo,
    //         s.codigo_solicitud,
    //         c.numero_contrato,
    //         cl.nombre_completo AS cliente,

    //         CONCAT_WS(', ',
    //             d.nombre,
    //             m.nombre,
    //             ds.nombre,
    //             col.nombre,
    //             cl.complemento_direccion
    //         ) AS direccion,

    //         f.fecha_emision,
    //         DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
    //         f.fecha_vencimiento,
    //         f.estado,
    //         f.total,
    //         f.mora,
    //         medi.numero_serie
    //     ", false)
    //         ->where('f.id_factura', $idFactura)
    //         ->get()
    //         ->getRowArray();

    //     // detalle para PDF
    //     $detalle = $this->db->table('facturas_detalle fd')
    //         ->select("
    //         fd.id_factura_detalle,
    //         fd.concepto,
    //         fd.mora,
    //         fd.monto,
    //         fd.id_cobro_instalacion
    //     ")
    //         ->where('fd.id_factura', $idFactura)
    //         ->get()
    //         ->getResultArray();

    //     return [
    //         'factura' => $factura,
    //         'detalle' => $detalle
    //     ];
    // }

    public function obtenerFacturaPorId(int $idFactura)
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
        $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
        $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
        $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
        $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');
        $builder->join('lecturas lec', 'lec.id_lectura = f.id_lectura', 'left');
        $builder->join('tarifas tari', 'tari.id_tarifa = c.id_tarifa', 'left');

        $factura = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,

            cl.complemento_direccion AS direccion,

            f.fecha_emision,

            DATE_FORMAT(lec.fecha, '%d-%m-%Y') AS fechaLectura,

            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,

            f.fecha_vencimiento,
            f.estado,
            f.total,
            f.mora,
            f.consumo,

            medi.numero_serie,

            lec.valor AS lecturaActual,

            (
                SELECT l2.valor
                FROM lecturas l2
                WHERE l2.id_contrato = f.id_contrato
                AND l2.id_periodo < f.id_periodo
                ORDER BY l2.id_periodo DESC, l2.id_lectura DESC
                LIMIT 1
            ) AS lecturaAnterior,
            tari.codigo AS codigoTarifa
        ", false)
            ->where('f.id_factura', $idFactura)
            ->get()
            ->getRowArray();

        $detalle = $this->db->table('facturas_detalle fd')
            ->select("
            fd.id_factura_detalle,
            fd.concepto,
            fd.mora,
            fd.monto,
            fd.id_cobro_instalacion
        ")
            ->where('fd.id_factura', $idFactura)
            ->get()
            ->getResultArray();

        return [
            'factura' => $factura,
            'detalle' => $detalle
        ];
    }

    // modelo para facturas de consumo mes a mes
    public function getHistorialFacturas(int $start, int $length, $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        $builder->whereIn('f.tipo', ['Consumo', 'OTRO']);

        // TOTAL SIN FILTRO
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults();

        // FILTRO DE BÚSQUEDA
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('f.correlativo', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike('cl.nombre_completo', $searchValue)
                ->orLike('p.nombre', $searchValue)
                ->groupEnd();
        }

        // TOTAL FILTRADO
        $filteredBuilder = clone $builder;
        $filtered = $filteredBuilder->countAllResults();

        // DATOS
        $data = $builder
            ->select([
                'f.id_factura AS id',
                'f.correlativo',
                'f.tipo',
                'c.numero_contrato',
                'cl.nombre_completo AS cliente',
                'p.nombre AS periodo',
                'f.fecha_emision',
                'f.fecha_vencimiento',
                'f.estado'
            ])
            ->orderBy('f.id_factura', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data'      => $data,
            'total'     => $total,
            'filtered'  => $filtered
        ];
    }

    public function getFacturasConsumoCompletaPorPeriodoYDireccion(
        int $idPeriodo,
        $idRuta = null,
        $idDepartamento = null,
        $idMunicipio = null,
        $idDistrito = null,
        $idColonia = null
    ) {

        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
        $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
        $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
        $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
        $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');
        $builder->join('lecturas lec', 'lec.id_lectura = f.id_lectura', 'left');
        $builder->join('tarifas tari', 'tari.id_tarifa = c.id_tarifa', 'left');
        $builder->join('rutas r', 'r.id_ruta = c.id_ruta', 'left');

        $builder->where('f.id_periodo', $idPeriodo);
        $builder->where('f.tipo', 'Consumo');

        if (!empty($idRuta) && $idRuta !== '-1') {
            $builder->where('r.id_ruta', $idRuta);
        }

        if (!empty($idDepartamento) && $idDepartamento !== '-1') {
            $builder->where('cl.id_departamento', $idDepartamento);
        }

        if (!empty($idMunicipio) && $idMunicipio !== '-1') {
            $builder->where('cl.id_municipio', $idMunicipio);
        }

        if (!empty($idDistrito) && $idDistrito !== '-1') {
            $builder->where('cl.id_distrito', $idDistrito);
        }

        if (!empty($idColonia) && $idColonia !== '-1') {
            $builder->where('cl.id_colonia', $idColonia);
        }

        $facturas = $builder
            ->select("
            f.id_factura,
            f.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            CONCAT_WS(', ',
                d.nombre,
                m.nombre,
                ds.nombre,
                col.nombre,
                cl.complemento_direccion
            ) AS direccion,
            DATE_FORMAT(lec.fecha, '%d-%m-%Y') AS fechaLectura,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            tari.codigo AS codigoTarifa,
            medi.numero_serie,
            lec.valor AS lecturaActual,
            (
                SELECT l2.valor
                FROM lecturas l2
                WHERE l2.id_contrato = f.id_contrato
                AND l2.id_periodo < f.id_periodo
                ORDER BY l2.id_periodo DESC, l2.id_lectura DESC
                LIMIT 1
            ) AS lecturaAnterior,
            f.consumo
        ", false)
            ->orderBy('CASE WHEN c.orden_ruta IS NULL THEN 1 ELSE 0 END', 'ASC', false)
            ->orderBy('c.orden_ruta', 'ASC')
            ->orderBy('f.id_factura', 'ASC')
            // ->limit('10')
            ->get()
            ->getResultArray();

        if (empty($facturas)) {
            return [];
        }

        $ids = array_column($facturas, 'id_factura');

        $detalles = $this->db->table('facturas_detalle')
            ->whereIn('id_factura', $ids)
            ->get()
            ->getResultArray();

        $detallesAgrupados = [];

        foreach ($detalles as $detalle) {
            $detallesAgrupados[$detalle['id_factura']][] = $detalle;
        }

        foreach ($facturas as &$factura) {
            $factura['detalle'] = $detallesAgrupados[$factura['id_factura']] ?? [];
        }

        return $facturas;
    }

    public function getFacturaResumenPorId(int $idFactura)
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('solicitudes s', 's.id_solicitud = c.id_solicitud', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');
        $builder->join('medidores medi', 'medi.id_medidor = c.id_medidor', 'left');
        $builder->join('departamentos d', 'd.id_departamento = cl.id_departamento', 'left');
        $builder->join('municipios m', 'm.id_municipio = cl.id_municipio', 'left');
        $builder->join('distritos ds', 'ds.id_distrito = cl.id_distrito', 'left');
        $builder->join('colonias col', 'col.id_colonia = cl.id_colonia', 'left');
        $builder->join('lecturas lec', 'lec.id_lectura = f.id_lectura', 'left');
        $builder->join('tarifas tari', 'tari.id_tarifa = c.id_tarifa', 'left');

        $factura = $builder
            ->select("
            f.id_factura AS id,
            f.correlativo,
            s.codigo_solicitud,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            cl.complemento_direccion AS direccion,
            DATE_FORMAT(lec.fecha, '%d-%m-%Y') AS fechaLectura,
            DATE_FORMAT(f.fecha_vencimiento, '%d-%m-%Y') AS fechaVencimiento,
            tari.codigo AS codigoTarifa,
            medi.numero_serie,
            lec.valor AS lecturaActual,
            (
                SELECT l2.valor
                FROM lecturas l2
                WHERE l2.id_contrato = f.id_contrato
                AND l2.id_periodo < f.id_periodo
                ORDER BY l2.id_periodo DESC, l2.id_lectura DESC
                LIMIT 1
            ) AS lecturaAnterior,
            f.consumo,
            tari.codigo AS codigoTarifa
        ", false)
            ->where('f.id_factura', $idFactura)
            ->get()
            ->getRowArray();

        // traer detalle (IMPORTANTE para el PDF)
        $detalle = $this->db->table('facturas_detalle')
            ->where('id_factura', $idFactura)
            ->get()
            ->getResultArray();

        return [
            'factura' => $factura,
            'detalle' => $detalle
        ];
    }

    public function getReporteFacturasPorPeriodo($idPeriodo = null, $tipo = 'Todos', $searchValue = '')
    {
        $builder = $this->db->table('facturas f');

        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');
        $builder->join('pagos_factura pf', 'pf.id_factura = f.id_factura', 'left');

        if (!empty($idPeriodo)) {
            $builder->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builder->where('f.estado', $tipo);
        }

        if (!empty($searchValue)) {

            $searchClean = str_replace('-', '', $searchValue);

            $builder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike('c.numero_contrato', $searchValue)
                ->orLike("REPLACE(c.numero_contrato, '-', '')", $searchClean) // 🔥 CLAVE
                ->groupEnd();
        }

        return $builder
            ->select("
            f.id_factura,
            f.tiraje,
            f.correlativo,
            f.total,
            f.estado,
            DATE_FORMAT(f.fecha_de_pago, '%d-%m-%Y') AS fecha_pago,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            p.nombre AS periodo,
            f.tipo AS tipo_factura
        ", false)
            ->groupBy('f.id_factura')
            ->orderBy('f.id_factura', 'DESC')
            // ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getFacturasInstalacionPorPeriodoYDireccion(
        int $idPeriodo,
        $idDepartamento = null,
        $idMunicipio = null,
        $idDistrito = null,
        $idColonia = null
    ) {
        $builder = $this->db->table('facturas f');

        $builder->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner');
        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');

        $builder->where('f.id_periodo', $idPeriodo);
        $builder->where('f.tipo', 'Instalacion');

        if (!empty($idDepartamento) && $idDepartamento !== '-1') {
            $builder->where('cl.id_departamento', $idDepartamento);
        }

        if (!empty($idMunicipio) && $idMunicipio !== '-1') {
            $builder->where('cl.id_municipio', $idMunicipio);
        }

        if (!empty($idDistrito) && $idDistrito !== '-1') {
            $builder->where('cl.id_distrito', $idDistrito);
        }

        if (!empty($idColonia) && $idColonia !== '-1') {
            $builder->where('cl.id_colonia', $idColonia);
        }

        return $builder
            ->distinct()
            ->select('f.id_factura')
            ->groupBy('f.id_factura')
            ->orderBy('f.id_factura', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getResumenFacturasPagadas()
    {
        $builder = $this->db->table('facturas f');

        return $builder
            ->select([
                'COUNT(DISTINCT f.id_factura) as total_facturas',
                'COALESCE(SUM(fd.subtotal), 0) as total_sin_mora',
                'COALESCE(SUM(fd.total_mora), 0) as total_mora',
                'COALESCE(SUM(fd.subtotal + fd.total_mora), 0) as total_pagado'
            ])
            ->join(
                '(SELECT 
                id_factura, 
                SUM(monto) as subtotal,
                SUM(mora) as total_mora
                FROM facturas_detalle 
                GROUP BY id_factura) fd',
                'fd.id_factura = f.id_factura',
                'left'
            )
            ->join('periodos p', 'p.id_periodo = f.id_periodo')
            ->where('p.estado', 'ACTIVO')
            ->where('f.estado', 'PAGADA')
            ->get()
            ->getRowArray();
    }

    public function getResumenContableFacturas($idPeriodo = null, $tipo = 'Todos')
    {
        $builderGeneral = $this->db->table('facturas f');

        if (!empty($idPeriodo)) {
            $builderGeneral->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builderGeneral->where('f.tipo', $tipo);
        }

        $resumenGeneral = $builderGeneral
            ->select("
                COUNT(DISTINCT f.id_factura) AS total_facturas,

                COALESCE(SUM(f.total), 0) AS total_facturado,

                COALESCE(SUM(
                    CASE
                        WHEN f.estado = 'PAGADA' THEN 1
                        ELSE 0
                    END
                ), 0) AS facturas_pagadas,

                COALESCE(SUM(
                    CASE
                        WHEN f.estado IS NULL
                        OR TRIM(f.estado) = ''
                        OR f.estado <> 'PAGADA'
                        THEN 1
                        ELSE 0
                    END
                ), 0) AS facturas_no_pagadas,

                COALESCE(SUM(
                    CASE
                        WHEN f.estado = 'PAGADA'
                        THEN f.total
                        ELSE 0
                    END
                ), 0) AS monto_pagado,

                COALESCE(SUM(
                    CASE
                        WHEN f.estado IS NULL
                        OR TRIM(f.estado) = ''
                        OR f.estado <> 'PAGADA'
                        THEN f.total
                        ELSE 0
                    END
                ), 0) AS monto_no_pagado
            ", false)
            ->get()
            ->getRowArray();

        $builderEstados = $this->db->table('facturas f');

        if (!empty($idPeriodo)) {
            $builderEstados->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builderEstados->where('f.tipo', $tipo);
        }

        $resumenEstados = $builderEstados
            ->select("
                f.estado,
                COUNT(DISTINCT f.id_factura) AS cantidad_facturas,
                COALESCE(SUM(f.total), 0) AS total_facturado
            ", false)
            ->groupBy('f.estado')
            ->get()
            ->getResultArray();

        $builderTipos = $this->db->table('facturas f');

        if (!empty($idPeriodo)) {
            $builderTipos->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builderTipos->where('f.tipo', $tipo);
        }

        $resumenTipos = $builderTipos
            ->select("
                COALESCE(f.tipo, 'SIN TIPO') AS tipo,
                COUNT(DISTINCT f.id_factura) AS cantidad_facturas,
                COALESCE(SUM(f.total), 0) AS total_facturado
            ", false)
            ->groupBy('f.tipo')
            ->get()
            ->getResultArray();

        $builderServicios = $this->db->table('facturas f');
        $builderServicios->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner');
        $builderServicios->join('servicios s', 's.id_servicio = fd.id_servicio', 'left');

        if (!empty($idPeriodo)) {
            $builderServicios->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builderServicios->where('f.tipo', $tipo);
        }

        $builderServicios->where('fd.id_servicio IS NOT NULL', null, false);

        $resumenServicios = $builderServicios
            ->select("
                COALESCE(s.nombre, 'OTROS SERVICIOS') AS servicio,
                COALESCE(SUM(fd.monto), 0) AS subtotal_servicio,
                COUNT(DISTINCT f.id_factura) AS cantidad_facturas
            ", false)
            ->groupBy('s.id_servicio, s.nombre')
            ->orderBy('s.nombre', 'ASC')
            ->get()
            ->getResultArray();

        $builderMora = $this->db->table('facturas f');
        $builderMora->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner');

        if (!empty($idPeriodo)) {
            $builderMora->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($tipo) && $tipo !== 'Todos') {
            $builderMora->where('f.tipo', $tipo);
        }

        return [
            'general' => $resumenGeneral ?: [],
            'estados' => $resumenEstados,
            'tipos' => $resumenTipos,
            'servicios' => $resumenServicios
        ];
    }

    public function getFacturasConsumoPorPeriodoYDireccion(
        int $idPeriodo,
        $idRuta = null,
        $idDepartamento = null,
        $idMunicipio = null,
        $idDistrito = null,
        $idColonia = null
    ) {
        $builder = $this->db->table('facturas f');

        $builder->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner');
        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('rutas r', 'r.id_ruta = c.id_ruta', 'left');

        $builder->where('f.id_periodo', $idPeriodo);
        $builder->where('f.tipo', 'Consumo');

        if (!empty($idRuta) && $idRuta !== '-1') {
            $builder->where('r.id_ruta', $idRuta);
        }

        if (!empty($idDepartamento) && $idDepartamento !== '-1') {
            $builder->where('cl.id_departamento', $idDepartamento);
        }

        if (!empty($idMunicipio) && $idMunicipio !== '-1') {
            $builder->where('cl.id_municipio', $idMunicipio);
        }

        if (!empty($idDistrito) && $idDistrito !== '-1') {
            $builder->where('cl.id_distrito', $idDistrito);
        }

        if (!empty($idColonia) && $idColonia !== '-1') {
            $builder->where('cl.id_colonia', $idColonia);
        }

        return $builder
            ->distinct()
            ->select('f.id_factura')
            ->groupBy('f.id_factura')
            ->orderBy('f.id_factura', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getFacturasConsumoAlcaldiaPorPeriodo(int $idPeriodo)
    {
        return $this->db->table('facturas f')
            ->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner')
            ->join('servicios s', 's.id_servicio = fd.id_servicio', 'left')
            ->join('contratos c', 'c.id_contrato = f.id_contrato', 'left')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
            ->where('f.id_periodo', $idPeriodo)
            ->where('f.tipo', 'Consumo')
            ->select("
                f.id_factura,
                f.estado,
                f.fecha_emision,
                f.fecha_vencimiento,
                f.fecha_de_pago,
                c.ficha_alcaldia,
                c.estado AS estado_contrato,
                c.numero_contrato,
                cl.codigo AS numero_cliente,
                cl.nombre_completo AS cliente,
                SUM(CASE
                    WHEN UPPER(COALESCE(s.nombre, fd.concepto)) LIKE '%ASEO%'
                    THEN COALESCE(fd.monto, 0)
                    ELSE 0
                END) AS aseo,
                SUM(CASE
                    WHEN UPPER(COALESCE(s.nombre, fd.concepto)) LIKE '%ALUMBRADO%'
                    THEN COALESCE(fd.monto, 0)
                    ELSE 0
                END) AS alumbrado
            ", false)
            ->groupBy([
                'f.id_factura',
                'f.estado',
                'f.fecha_emision',
                'f.fecha_vencimiento',
                'f.fecha_de_pago',
                'c.ficha_alcaldia',
                'c.estado',
                'c.numero_contrato',
                'cl.codigo',
                'cl.nombre_completo'
            ])
            ->orderBy('cl.codigo', 'ASC')
            ->orderBy('f.id_factura', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getReporteFacturasDetallePorPeriodo($idPeriodo = null, $searchValue = '', $fecha  = null)
    {
        $builder = $this->db->table('facturas f');

        $builder->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'left'); // 🔥 CAMBIO CLAVE
        $builder->join('contratos c', 'c.id_contrato = f.id_contrato', 'left');
        $builder->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left');
        $builder->join('pagos_factura pf', 'pf.id_factura = f.id_factura', 'left');
        $builder->join('periodos p', 'p.id_periodo = f.id_periodo', 'left');

        if (!empty($idPeriodo)) {
            $builder->where('f.id_periodo', $idPeriodo);
        }

        if (!empty($fecha)) {
            $builder->where('f.fecha_de_pago', $fecha);
        }

        // if (!empty($tipo) && $tipo !== 'Todos') {
        //     $builder->where('f.tipo', $tipo);
        // }

        if (!empty($searchValue)) {

            $searchClean = str_replace('-', '', $searchValue);

            $builder->groupStart()
                ->like('cl.nombre_completo', $searchValue)
                ->orLike("REPLACE(c.numero_contrato, '-', '')", $searchClean)
                ->groupEnd();
        }

        return $builder
            ->select("
            f.id_factura,
            f.tiraje,
            f.correlativo,
            f.total,
            f.estado,
            DATE_FORMAT(f.fecha_de_pago, '%d-%m-%Y') AS fecha_pago,
            c.numero_contrato,
            cl.nombre_completo AS cliente,
            f.tipo AS tipo_factura,
            GROUP_CONCAT(
            CONCAT(
                fd.concepto,'||',
                fd.mora,'||',
                fd.monto
                )
                SEPARATOR '##'
            ) AS detalles,
            p.nombre AS periodo
        ", false)
            ->groupBy('f.id_factura')
            ->orderBy('f.id_factura', 'DESC')
            // ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getReportePagos($idPeriodo = null, $estado = 'Todos')
    {
        return $this->db->table('facturas f')
            ->join('facturas_detalle fd', 'fd.id_factura = f.id_factura', 'inner')
            ->join('servicios s', 's.id_servicio = fd.id_servicio', 'left')
            ->join('contratos c', 'c.id_contrato = f.id_contrato', 'left')
            ->join('clientes cl', 'cl.id_cliente = c.id_cliente', 'left')
            ->where('f.id_periodo', $idPeriodo)
            ->where('f.estado', $estado)
            ->select("
                cl.codigo AS numero_cliente,
                c.ficha_alcaldia,
                cl.nombre_completo AS cliente,
                SUM(
                    CASE
                        WHEN UPPER(fd.concepto) LIKE '%SERVICIO DOMICILIAR%'
                        OR UPPER(fd.concepto) LIKE '%USO DE RED%'
                        OR UPPER(fd.concepto) LIKE '%TABLA DIFERENCIADA%'
                        OR fd.concepto = ''
                        THEN COALESCE(fd.monto, 0)
                        ELSE 0
                    END
                ) AS agua,
                SUM(CASE
                    WHEN UPPER(COALESCE(s.nombre, fd.concepto)) LIKE '%TREN DE ASEO%'
                    THEN COALESCE(fd.monto, 0)
                    ELSE 0
                END) AS aseo,
                SUM(CASE
                    WHEN UPPER(COALESCE(s.nombre, fd.concepto)) LIKE '%ALUMBRADO PUBLICO%'
                    THEN COALESCE(fd.monto, 0)
                    ELSE 0
                END) AS alumbrado,
                SUM(
                    CASE
                        WHEN UPPER(fd.concepto) NOT LIKE '%SERVICIO DOMICILIAR%'
                        AND UPPER(fd.concepto) NOT LIKE '%USO DE RED%'
                        AND UPPER(fd.concepto) NOT LIKE '%TREN DE ASEO%'
                        AND UPPER(fd.concepto) NOT LIKE '%TABLA DIFERENCIADA%'
                        AND UPPER(fd.concepto) NOT LIKE '%ALUMBRADO PUBLICO%'
                        AND TRIM(fd.concepto) <> ''
                        THEN COALESCE(fd.monto,0)
                        ELSE 0
                    END
                ) AS saldoAnterior,



                f.id_factura,
                f.estado,
                f.fecha_emision,
                f.fecha_vencimiento,
                f.fecha_de_pago,
                c.estado AS estado_contrato,
                c.numero_contrato
                
                
            ", false)
            ->groupBy([
                'f.id_factura',
                'f.estado',
                'f.fecha_emision',
                'f.fecha_vencimiento',
                'f.fecha_de_pago',
                'c.ficha_alcaldia',
                'c.estado',
                'c.numero_contrato',
                'cl.codigo',
                'cl.nombre_completo'
            ])
            ->orderBy('cl.codigo', 'ASC')
            ->orderBy('f.id_factura', 'ASC')
            ->get()
            ->getResultArray();
    }
}
