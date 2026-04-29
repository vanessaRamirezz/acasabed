<?php

namespace App\Models;

use CodeIgniter\Model;

class LecturaModel extends Model
{
    protected $table = 'lecturas';
    protected $primaryKey = 'id_lectura';
    protected $allowedFields = ['id_periodo', 'id_contrato', 'fecha', 'valor', 'id_instalador', 'id_usuario', 'fecha_creacion'];

    public function getUltimaLecturaAnterior($idContrato, $idPeriodoActual)
    {
        return $this->select('id_lectura, id_periodo, fecha, valor')
            ->where('id_contrato', $idContrato)
            ->where('id_periodo <', $idPeriodoActual)
            ->orderBy('id_periodo', 'DESC')
            ->orderBy('id_lectura', 'DESC')
            ->first();
    }

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

    public function getLecturaActual($idContrato, $idPeriodo)
    {
        return $this->select('id_lectura, valor')
            ->where('id_contrato', $idContrato)
            ->where('id_periodo', $idPeriodo)
            ->first();
    }

    public function getReporteLecturasTomadas(
        $idPeriodo = null,
        $idContrato = null,
        $idInstalador = null,
        $idDepartamento = null,
        $idMunicipio = null,
        $idDistrito = null,
        $idColonia = null
    ) {
        $builder = $this->db->table('lecturas');

        $builder->join('periodos', 'lecturas.id_periodo = periodos.id_periodo', 'left');
        $builder->join('contratos', 'lecturas.id_contrato = contratos.id_contrato', 'left');
        $builder->join('clientes', 'contratos.id_cliente = clientes.id_cliente', 'left');
        $builder->join('instaladores', 'lecturas.id_instalador = instaladores.id_instalador', 'left');
        $builder->join('departamentos', 'clientes.id_departamento = departamentos.id_departamento', 'left');
        $builder->join('municipios', 'clientes.id_municipio = municipios.id_municipio', 'left');
        $builder->join('distritos', 'clientes.id_distrito = distritos.id_distrito', 'left');
        $builder->join('colonias', 'clientes.id_colonia = colonias.id_colonia', 'left');

        if (!empty($idPeriodo) && $idPeriodo !== '-1') {
            $builder->where('lecturas.id_periodo', $idPeriodo);
        }

        if (!empty($idContrato) && $idContrato !== '-1') {
            $builder->where('lecturas.id_contrato', $idContrato);
        }

        if (!empty($idInstalador) && $idInstalador !== '-1') {
            $builder->where('lecturas.id_instalador', $idInstalador);
        }

        if (!empty($idDepartamento) && $idDepartamento !== '-1') {
            $builder->where('clientes.id_departamento', $idDepartamento);
        }

        if (!empty($idMunicipio) && $idMunicipio !== '-1') {
            $builder->where('clientes.id_municipio', $idMunicipio);
        }

        if (!empty($idDistrito) && $idDistrito !== '-1') {
            $builder->where('clientes.id_distrito', $idDistrito);
        }

        if (!empty($idColonia) && $idColonia !== '-1') {
            $builder->where('clientes.id_colonia', $idColonia);
        }

        return $builder
            ->select("
                lecturas.id_lectura,
                lecturas.id_periodo,
                lecturas.id_contrato,
                lecturas.id_instalador,
                periodos.nombre AS periodo,
                contratos.numero_contrato,
                clientes.codigo AS codigo_cliente,
                clientes.nombre_completo AS cliente,
                instaladores.nombre_completo AS instalador,
                DATE_FORMAT(lecturas.fecha, '%d-%m-%Y') AS fecha_lectura,
                lecturas.valor,
                departamentos.nombre AS departamento,
                municipios.nombre AS municipio,
                distritos.nombre AS distrito,
                colonias.nombre AS colonia,
                clientes.complemento_direccion,
                CONCAT_WS(', ',
                    departamentos.nombre,
                    municipios.nombre,
                    distritos.nombre,
                    colonias.nombre,
                    clientes.complemento_direccion
                ) AS direccion_cliente
            ", false)
            ->orderBy('lecturas.fecha', 'DESC')
            ->orderBy('lecturas.id_lectura', 'DESC')
            ->get()
            ->getResultArray();
    }
}
