<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudModel extends Model
{
    protected $table = 'solicitudes';
    protected $primaryKey = 'id_solicitud';
    protected $allowedFields = [
        'solicitudescol',
        'codigo_solicitud',
        'fecha_generacion',
        'id_cliente',
        'id_beneficiario',
        'direccion_inmueble',
        'propietario',
        'inquilino',
        'representante',
        'otro',
        'abonera',
        'hoyo_seco',
        'lavable',
        'otro_tipo_baño',
        'acepta_construccion_letrina',
        'tiempo_construccion',
        'contado',
        'otro_tipo_de_pago',
        'costo_instalacion',
        'id_plan_de_pago',
        'interes',
        'saldo_pendiente',
        'acuerdo',
        'fecha_session',
        'numero_acta',
        'estado',
        'id_usuario',
        'fecha_creacion',
        'id_firmante',
        'id_nombre_administrador',
        'id_nombre_comision_1',
        'id_nombre_comision_2'
    ];

    public function correlativoSolicitud($db)
    {
        $query = $db->query("
            SELECT MAX(codigo_solicitud) AS max_codigo 
            FROM solicitudes 
            FOR UPDATE
        ");

        $row = $query->getRow();

        $ultimo = $row->max_codigo ?? 0;

        return (int)$ultimo + 1;
    }

    public function getCorrelativoSolicitud($idSolicitud)
    {
        return $this->select('codigo_solicitud')
            ->where('id_solicitud', $idSolicitud)
            ->first();
    }

    // preguntar si la direccion seria la misma del cliente
    public function insertarSolicitud(
        $codigoSolicitud,
        $fechaGeneracion,
        $idCliente,
        $direccionInmueble,
        $propietario,
        $inquilino,
        $representante,
        $otro,
        $abonera,
        $hoyoSeco,
        $lavable,
        $otroTipoBaño,
        $aceptaContruccionLetrina,
        $tiempoConstruccion,
        $contado,
        $otroTipoPaago,
        $costoInstalacion,
        $acuerdo,
        $fechaSession,
        $numeroActa,
        $estado,
        $idUsuario,
        $fechaCreacion,
    ) {
        return $this->insert([
            'codigo_solicitud' => $codigoSolicitud,
            'fecha_generacion' => $fechaGeneracion,
            'id_cliente' => $idCliente,
            'direccion_inmueble' => $direccionInmueble,
            'propietario' => $propietario,
            'inquilino' => $inquilino,
            'representante' => $representante,
            'otro' => $otro,
            'abonera' => $abonera,
            'hoyo_seco' => $hoyoSeco,
            'lavable' => $lavable,
            'otro_tipo_baño' => $otroTipoBaño,
            'acepta_construccion_letrina' => $aceptaContruccionLetrina,
            'tiempo_construccion' => $tiempoConstruccion,
            'contado' => $contado,
            'otro_tipo_de_pago' => $otroTipoPaago,
            'costo_instalacion' => $costoInstalacion,
            'acuerdo' => $acuerdo,
            'fecha_session' => $fechaSession,
            'numero_acta' => $numeroActa,
            'estado' => $estado,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion,
        ]);
    }

    public function actualizarSolicitud(
        $idSolicitud,
        $fechaGeneracion,
        $idCliente,
        $direccionInmueble,
        $propietario,
        $inquilino,
        $representante,
        $otro,
        $abonera,
        $hoyoSeco,
        $lavable,
        $otroTipoBaño,
        $aceptaContruccionLetrina,
        $tiempoConstruccion,
        $contado,
        $otroTipoPaago,
        $costoInstalacion,
        $acuerdo,
        $fechaSession,
        $numeroActa,
        $estado
    ) {
        return $this->update($idSolicitud, [
            'fecha_generacion' => $fechaGeneracion,
            'id_cliente' => $idCliente,
            'direccion_inmueble' => $direccionInmueble,
            'propietario' => $propietario,
            'inquilino' => $inquilino,
            'representante' => $representante,
            'otro' => $otro,
            'abonera' => $abonera,
            'hoyo_seco' => $hoyoSeco,
            'lavable' => $lavable,
            'otro_tipo_baño' => $otroTipoBaño,
            'acepta_construccion_letrina' => $aceptaContruccionLetrina,
            'tiempo_construccion' => $tiempoConstruccion,
            'contado' => $contado,
            'otro_tipo_de_pago' => $otroTipoPaago,
            'costo_instalacion' => $costoInstalacion,
            'acuerdo' => $acuerdo,
            'fecha_session' => $fechaSession,
            'numero_acta' => $numeroActa,
            'estado' => $estado,
        ]);
    }

    public function getSolicitudesCreadas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('solicitudes');

        // JOIN desde el inicio
        $builder->join(
            'clientes',
            'solicitudes.id_cliente = clientes.id_cliente',
            'left'
        );

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder
            ->where('solicitudes.estado', 'CREADA')
            ->countAllResults(false);

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('solicitudes.numero_solicitud', $searchValue)
                ->orLike('clientes.nombre_completo', $searchValue)
                ->groupEnd();
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder
            ->where('solicitudes.estado', 'CREADA')
            ->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                solicitudes.id_solicitud AS id,
                solicitudes.codigo_solicitud AS cod_solicitud,
                clientes.nombre_completo AS nombre,
                solicitudes.estado AS estado,
                solicitudes.fecha_generacion AS fechaGeneracion
        ')
            ->where('solicitudes.estado', 'CREADA')
            ->orderBy('solicitudes.id_solicitud', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    public function getInfoSolicitudPorId($idSolicitud)
    {
        if (empty($idSolicitud)) {
            return null;
        }

        return $this->select("
                solicitudes.id_solicitud,
                solicitudes.codigo_solicitud AS codigoSolicitud,
                solicitudes.fecha_generacion AS fechaCreacion,
                solicitudes.id_cliente,
                clientes.nombre_completo AS nombre,
                clientes.edad,
                clientes.dui,
                clientes.nit,
                clientes.extendido,
                clientes.fecha,
                clientes.lugar_nacimiento AS lugarNacimiento,
                clientes.fecha_nacimiento AS fechaNacimiento,
                clientes.estado_familiar AS estadoFamiliar,
                clientes.numero_grupo_familiar AS numeroGrupoFamiliar,
                CONCAT_WS(', ',
                    departamentos.nombre,
                    municipios.nombre,
                    distritos.nombre,
                    colonias.nombre,
                    clientes.complemento_direccion
                ) AS direccion,
                clientes.lugar_de_trabajo AS lugarDeTrabajo,
                clientes.ocupacion,
                clientes.telefono AS telefonos,
                beneficiarios.id_beneficiario AS idBeneficiario,
                beneficiarios.nombre AS nombreBeneficiario,
                beneficiarios.edad AS edadBeneficiario,
                beneficiarios.parentesco AS parentescoBeneficiario,
                beneficiarios.direccion AS direccionBeneficiario,
                solicitudes.direccion_inmueble AS direccionInmueble,
                solicitudes.propietario,
                solicitudes.inquilino,
                solicitudes.representante,
                solicitudes.otro AS otroCheck,
                solicitudes.abonera,
                solicitudes.hoyo_seco AS hoyoSeco,
                solicitudes.lavable,
                solicitudes.`otro_tipo_baño` AS otroBaño,
                solicitudes.acepta_construccion_letrina AS aceptaConstruccionLetrina,
                solicitudes.tiempo_construccion AS tiempo,
                solicitudes.costo_instalacion AS monto,
                solicitudes.contado,
                solicitudes.otro_tipo_de_pago AS otroTipoPago,
                plan_de_pago.id_plan_de_pago AS idPlanDePago,
                plan_de_pago.cantidad_cuotas AS cantidadDePagos,
                plan_de_pago.monto_cuotas AS totalCuota,
                solicitudes.interes AS interesACobrar,
                solicitudes.saldo_pendiente AS saldoPendiente,
                solicitudes.acuerdo,
                solicitudes.fecha_session AS fechaSession,
                solicitudes.numero_acta AS numeroActa,
                solicitudes.estado,
                admin.id_firmante AS idAdministrador,
                admin.nombre AS nombreAdministrador,
                com1.id_firmante AS idComision1,
                com1.nombre AS nombreComision1,
                com2.id_firmante AS idComision2,
                com2.nombre AS nombreComision2,
                contratos.ficha_alcaldia AS fichaAlcaldia,
                contratos.fecha_de_inicio AS fechaInicio,
                contratos.fecha_de_vencimiento AS fechaVencimiento,
                rutas.id_ruta AS idRuta,
                rutas.nombre AS nombreRuta,
                medidores.id_medidor AS idMedidor,
                medidores.numero_serie AS numeroSerie,
                contratos.direccion_medidor AS direccionMedidor,
                tarifario.id_tarifa AS idTarifa,
                tarifario.codigo AS codigoTarifa,
                tarifario.desde_n_metros AS desde,
                tarifario.hasta_n_metros AS hasta,
                contratos.numero_contrato AS numeroContrato
            ", false)
            ->join('clientes', 'clientes.id_cliente = solicitudes.id_cliente', 'left')
            ->join('beneficiarios', 'beneficiarios.id_beneficiario = solicitudes.id_beneficiario', 'left')
            ->join('plan_de_pago', 'plan_de_pago.id_plan_de_pago = solicitudes.id_plan_de_pago', 'left')
            ->join('contratos', 'contratos.id_solicitud = solicitudes.id_solicitud', 'left')
            ->join('firmantes AS admin', 'admin.id_firmante = solicitudes.id_nombre_administrador', 'left')
            ->join('firmantes AS com1', 'com1.id_firmante = solicitudes.id_nombre_comision_1', 'left')
            ->join('firmantes AS com2', 'com2.id_firmante = solicitudes.id_nombre_comision_2', 'left')
            ->join('departamentos', 'departamentos.id_departamento = clientes.id_departamento', 'left')
            ->join('municipios', 'municipios.id_municipio = clientes.id_municipio', 'left')
            ->join('distritos', 'distritos.id_distrito = clientes.id_distrito', 'left')
            ->join('colonias', 'colonias.id_colonia = clientes.id_colonia', 'left')
            ->join('rutas', 'rutas.id_ruta = contratos.id_ruta', 'left')
            ->join('medidores', 'medidores.id_medidor = contratos.id_medidor', 'left')
            ->join('tarifario', 'tarifario.id_tarifa = contratos.id_tarifa', 'left')
            ->where('solicitudes.id_solicitud', $idSolicitud)
            ->first();
    }

    public function getSolicitudesAceptadas($start, $length, $searchValue = '')
    {
        $builder = $this->db->table('solicitudes');

        // JOIN desde el inicio
        $builder->join(
            'clientes',
            'solicitudes.id_cliente = clientes.id_cliente',
            'left'
        );

        // =============================
        // TOTAL SIN FILTRO
        // =============================
        $total = $builder
            ->where('solicitudes.estado', 'ACEPTADA')
            ->countAllResults(false);

        // =============================
        // FILTRO
        // =============================
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('solicitudes.numero_solicitud', $searchValue)
                ->orLike('clientes.nombre_completo', $searchValue)
                ->groupEnd();
        }

        // =============================
        // TOTAL FILTRADO
        // =============================
        $filtered = $builder
            ->where('solicitudes.estado', 'ACEPTADA')
            ->countAllResults(false);

        // =============================
        // DATA
        // =============================
        $data = $builder
            ->select('
                solicitudes.id_solicitud AS id,
                solicitudes.codigo_solicitud AS cod_solicitud,
                clientes.nombre_completo AS nombre,
                solicitudes.estado AS estado,
                solicitudes.fecha_generacion AS fechaGeneracion
        ')
            ->where('solicitudes.estado', 'ACEPTADA')
            ->orderBy('solicitudes.id_solicitud', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }
}
