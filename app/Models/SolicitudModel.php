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
        'id_usuario',
        'fecha_creacion'
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

    // preguntar si la direccion seria la misma del cliente
    public function insertarSolicitud(
        $codigoSolicitud,
        $fechaGeneracion,
        $idCliente,
        $idBeneficiario,
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
        $idUsuario,
        $fechaCreacion
    ) {
        return $this->insert([
            'codigo_solicitud' => $codigoSolicitud,
            'fecha_generacion' => $fechaGeneracion,
            'id_cliente' => $idCliente,
            'id_beneficiario' => $idBeneficiario,
            'direccion_inmueble' => $direccionInmueble,
            'propietario' => $propietario,
            'inquilino' => $inquilino,
            'representante' => $representante,
            'otro' => $otro,
            'abonera' => $abonera,
            'hoyo_seco' => $hoyoSeco,
            'lavable' => $lavable,
            'otro_tipo_naño' => $otroTipoBaño,
            'acepta_construccion_letrina' => $aceptaContruccionLetrina,
            'tiempo_construccion' => $tiempoConstruccion,
            'id_usuario' => $idUsuario,
            'fecha_creacion' => $fechaCreacion,
        ]);
    }
}
