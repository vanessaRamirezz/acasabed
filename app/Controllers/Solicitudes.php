<?php

namespace App\Controllers;

use App\Models\BeneficiarioModel;
use App\Models\ClienteModel;
use App\Models\CobroContratoModel;
use App\Models\ContratoModel;
use App\Models\MedidorModel;
use App\Models\PlanDePagoModel;
use App\Models\RutaModel;
use App\Models\SolicitudModel;
use App\Models\TarifaModel;

class Solicitudes extends BaseController
{
    private $clientesModel;
    private $beneficiariosModel;
    private $solicitudesModel;
    private $contratosModel;
    private $rutasModel;
    private $medidoresModel;
    private $tarifasModel;
    private $planDePagosModel;
    private $contratosCobrosModel;

    public function __construct()
    {
        $this->clientesModel = new ClienteModel();
        $this->beneficiariosModel = new BeneficiarioModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->contratosModel = new ContratoModel();
        $this->rutasModel = new RutaModel();
        $this->medidoresModel =  new MedidorModel();
        $this->tarifasModel = new TarifaModel();
        $this->planDePagosModel = new PlanDePagoModel();
        $this->contratosCobrosModel = new CobroContratoModel();
    }

    public function index()
    {
        return view('solicitudes/index');
    }

    public function getClientesSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $clientes = $this->clientesModel->buscarClientes($search);
            return $this->respondSuccess($clientes);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los clientes');
        }
    }

    public function getRutasSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $rutas = $this->rutasModel->buscarRutas($search);
            return $this->respondSuccess($rutas);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer las rutas');
        }
    }

    public function getMedidoresSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $medidores = $this->medidoresModel->buscarMedidores($search);
            return $this->respondSuccess($medidores);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los medidores');
        }
    }

    public function getTarifasSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $tarifas = $this->tarifasModel->buscarTarifas($search);
            return $this->respondSuccess($tarifas);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer las tarifas');
        }
    }

    public function crearBeneficiario($data)
    {
        $nombre = $data['nombreBeneficiario'] ?? null;
        $edad = $data['edadBeneficiario'] ?? null;
        $parentesco = $data['parentescoBeneficiario'] ?? null;
        $direccion = $data['direccionBeneficiario'] ?? null;

        // // VALIDACIONES (una por una)
        // if (empty($nombre)) {
        //     return [
        //         'valido' => false,
        //         'error' => 'El nombre del beneficiario es requerido'
        //     ];
        // }

        // if (!is_numeric($edad) || $edad <= 0) {
        //     return [
        //         'valido' => false,
        //         'error' => 'La edad debe ser un número válido'
        //     ];
        // }

        // if (empty($parentesco)) {
        //     return [
        //         'valido' => false,
        //         'error' => 'El parentesco es requerido'
        //     ];
        // }

        return [
            'valido' => true,
            'data' => [
                'nombre' => $nombre,
                'edad' => $edad,
                'parentesco' => $parentesco,
                'direccion' => $direccion
            ]
        ];
    }

    public function crearSolicitud($data)
    {

        $fechaCreacion = $data['fechaCreacion'] ?? null;
        $direccionInmueble = $data['direccionInmueble'] ?? null;
        $propietario = filter_var($data['propietario'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $inquilino = filter_var($data['inquilino'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $representante = filter_var($data['representante'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $otroCheck = $data['otroCheck'] ?? null;
        $abonera = $data['abonera'] ?? null;
        $hoyoSeco = $data['hoyoSeco'] ?? null;
        $lavable = $data['lavable'] ?? null;
        $otroBaño = $data['otroBaño'] ?? null;
        $si = filter_var($data['si'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $no = filter_var($data['no'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $tiempo = $data['tiempo'] ?? null;
        $aceptaContruccionLetrina = false;
        if ($si === true) {
            $aceptaContruccionLetrina = true;
        } else if ($no == false) {
            $aceptaContruccionLetrina = false;
        }

        return [
            'valido' => true,
            'data' => [
                'fechaCreacion' => $fechaCreacion,
                'direccionInmueble' => $direccionInmueble,
                'propietario' => $propietario,
                'inquilino' => $inquilino,
                'representante' => $representante,
                'otroCheck' => $otroCheck,
                'abonera' => $abonera,
                'hoyoSeco' => $hoyoSeco,
                'lavable' => $lavable,
                'otroBaño' => $otroBaño,
                'aceptaContruccionLetrina' => $aceptaContruccionLetrina,
                'tiempo' => $tiempo,
            ]
        ];
    }

    public function crearContrato($data)
    {
        $fichaAlcaldia = $data['numeroActa'] ?? null; // preguntar aca
        $fechaInicio = $data['fechaInicio'] ?? null;
        $fechaVencimiento = $data['fechaVencimiento'] ?? null;
        $estadoContrato = $data['estado'] ?? null;

        $idRuta = $data['ruta'];
        if (
            $idRuta === '' ||
            $idRuta === 'null' ||
            $idRuta === '-1' ||
            $idRuta === null
        ) {
            $idRuta = null;
        }

        $idMedidor = $data['medidor'];
        if (
            $idMedidor === '' ||
            $idMedidor === 'null' ||
            $idMedidor === '-1' ||
            $idMedidor === null
        ) {
            $idMedidor = null;
        }


        $direccionMedidor = $data['direccionMedidor'] ?? null;

        $idTarifa = $data['tarifa'];
        if (
            $idTarifa === '' ||
            $idTarifa === 'null' ||
            $idTarifa === '-1' ||
            $idTarifa === null
        ) {
            $idTarifa = null;
        }

        $contado = filter_var($data['contado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $otro = $data['otro'] ?? null;
        $monto = $data['monto'] ?? null;
        $idPlanDePago = null;
        $saldoPendiente = $monto;

        return [
            'valido' => true,
            'data' => [
                'fichaAlcaldia' => $fichaAlcaldia,
                'fechaInicio' => $fechaInicio,
                'fechaVencimiento' => $fechaVencimiento,
                'estadoContrato' => $estadoContrato,
                'idRuta' => $idRuta,
                'idMedidor' => $idMedidor,
                'direccionMedidor' => $direccionMedidor,
                'idTarifa' => $idTarifa,
                'contado' => $contado,
                'otro' => $otro,
                'monto' => $monto,
                'idPlanDePago' => $idPlanDePago,
                'saldoPendiente' => $saldoPendiente,
            ]
        ];
    }

    public function crearPlanDePago($data)
    {
        $cantidadDePagos = $data['cantidadDePagos'] ?? null;
        $totalCuota = $data['totalCuota'] ?? null;

        return [
            'valido' => true,
            'data' => [
                'cantidadDePagos' => $cantidadDePagos,
                'totalCuota' => $totalCuota,
            ]
        ];
    }

    public function crearCobros($data)
    {
        $cantidadDePagos = (int)$data['cantidadDePagos'] ?? null;
        $totalCuota = (int)$data['totalCuota'] ?? null;
        $fechaBase = $data['fecha'] ?? null;

        return [
            'valido' => true,
            'data' => [
                'cantidadDePagos' => $cantidadDePagos,
                'totalCuota' => $totalCuota,
                'fechaBase' => $fechaBase
            ]
        ];
    }

    public function validarMedidor($data)
    {
        $medidor = $data['medidor'] ?? null;


        // // VALIDACIONES (una por una)
        if (empty($medidor)) {
            return [
                'valido' => false,
                'error' => 'Seleccionar un medidor es necesario'
            ];
        }


        return [
            'valido' => true,
            'data' => [
                'medidor' => $medidor,
            ]
        ];
    }

    public function nuevaSolicitudContrato()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {

            log_message('debug', 'POST: ' . print_r($this->request->getPost(), true));
            $data = $this->request->getPost();
            $idCliente = $data['idCliente'] ?? null;

            if (!$idCliente) {
                throw new \Exception('Debe seleccionar cliente');
            }

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            // =========================
            // 1. BENEFICIARIO
            // =========================
            $beneficiario = $this->crearBeneficiario($data);

            if (!$beneficiario['valido']) {
                throw new \Exception($beneficiario['error']);
            }

            $idBeneficiario = $this->beneficiariosModel->insertarBeneficiario(
                $beneficiario['data']['nombre'],
                $beneficiario['data']['edad'],
                $beneficiario['data']['parentesco'],
                $beneficiario['data']['direccion'],
                $idCliente
            );

            if (!$idBeneficiario) {
                throw new \Exception('Error al insertar beneficiario');
            }

            // =========================
            // 2. SOLICITUD
            // =========================
            $solicitud = $this->crearSolicitud($data);

            if (!$solicitud['valido']) {
                throw new \Exception($solicitud['error']);
            }

            $numero = $this->solicitudesModel->correlativoSolicitud($db);
            $codigoFormateado = str_pad($numero, 5, '0', STR_PAD_LEFT);

            $idSolicitud = $this->solicitudesModel->insertarSolicitud(
                $codigoFormateado,
                $fechaCreacion,
                $idCliente,
                $idBeneficiario,
                $solicitud['data']['direccionInmueble'],
                $solicitud['data']['propietario'],
                $solicitud['data']['inquilino'],
                $solicitud['data']['representante'],
                $solicitud['data']['otroCheck'],
                $solicitud['data']['abonera'],
                $solicitud['data']['hoyoSeco'],
                $solicitud['data']['lavable'],
                $solicitud['data']['otroBaño'],
                $solicitud['data']['aceptaContruccionLetrina'],
                $solicitud['data']['tiempo'],
                $idUsuario,
                $fechaCreacion
            );

            if (!$idSolicitud) {
                throw new \Exception('Error al insertar solicitud');
            }

            // =========================
            // 3. CONTRATO
            // =========================
            $contrato = $this->crearContrato($data);

            if (!$contrato['valido']) {
                throw new \Exception($contrato['error']);
            }

            $numeroContrato = 'C-' . $codigoFormateado;

            $idContrato = $this->contratosModel->insertarContrato(
                $idSolicitud,
                $numeroContrato,
                $contrato['data']['fichaAlcaldia'],
                $idCliente,
                $contrato['data']['fechaInicio'],
                $contrato['data']['fechaVencimiento'],
                $contrato['data']['estadoContrato'],
                $contrato['data']['idRuta'],
                $contrato['data']['idMedidor'],
                $contrato['data']['direccionMedidor'],
                $contrato['data']['idTarifa'],
                $contrato['data']['contado'],
                $contrato['data']['otro'],
                $contrato['data']['monto'],
                $contrato['data']['idPlanDePago'],
                $contrato['data']['saldoPendiente'],
                $fechaCreacion,
                $idUsuario
            );

            if (!$idContrato) {
                throw new \Exception('Error al insertar contrato');
            }

            // =========================
            // 4. PLAN DE PAGO
            // =========================
            $plan = $this->crearPlanDePago($data);

            if (!$plan['valido']) {
                throw new \Exception($plan['error']);
            }

            $idPlan = $this->planDePagosModel->guardarPlanDePago(
                $plan['data']['cantidadDePagos'],
                $plan['data']['totalCuota'],
                $idContrato
            );

            if (!$idPlan) {
                throw new \Exception('Error al insertar plan de pago');
            }

            // =========================
            // 5. CUOTAS
            // =========================
            $cobros = $this->crearCobros($data);

            if (!$cobros['valido']) {
                throw new \Exception($cobros['error']);
            }

            $fechaBase = new \DateTimeImmutable($cobros['data']['fechaBase']);

            for ($i = 1; $i <= $cobros['data']['cantidadDePagos']; $i++) {

                $fechaCalculada = $fechaBase->modify("+{$i} month")->format('Y-m-d');

                $cuota = [
                    'id_contrato' => $idContrato,
                    'numero_cuota' => $i,
                    'monto_cuota' => $cobros['data']['totalCuota'],
                    'descripcion' => "Cuota $i",
                    'estado' => 'PENDIENTE',
                    'fecha_vencimiento' => $fechaCalculada,
                    'fecha_pago' => $fechaCalculada,
                    'id_usuario' => $idUsuario,
                    'fecha_creacion' => $fechaCreacion
                ];

                $insert = $this->contratosCobrosModel->insert($cuota);

                if (!$insert) {
                    throw new \Exception('Error al insertar cuotas');
                }
            }

            // =========================
            // 6. MEDIDOR
            // =========================
            $medidores = $this->validarMedidor($data);

            if (!$medidores['valido']) {
                throw new \Exception($medidores['error']);
            }

            $idMedidor = $medidores['data']['medidor'];

            $this->medidoresModel->update($idMedidor, [
                'id_contrato' => $idContrato
            ]);

            if ($this->medidoresModel->db->affectedRows() === 0) {
                throw new \Exception('No se actualizó el medidor');
            }

            // =========================
            // 7. ACTUALIZAR EN CONTRATO EL NUEVO PLAN DE PAGO
            // =========================
            $this->contratosModel->update($idContrato, [
                'id_plan_de_pago' => $idPlan
            ]);

            if ($this->contratosModel->db->affectedRows() === 0) {
                throw new \Exception('No se actualizó el contrato con su nuevo plan de pago');
            }

            // =========================
            // FINAL
            // =========================
            $db->transCommit();

            log_message('info', 'Datos creado correctamente: ' . json_encode([
                'id_solicitud' => $idSolicitud,
                'id_contrato' => $idContrato,
                'id_plan_pago' => $idPlan,
                'id_beneficiario' => $idBeneficiario,
                'id_medidor' => $idMedidor
            ]));

            return $this->respondOk('Proceso completado correctamente');
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->respondError($e->getMessage());
        }
    }

    // public function nuevaSolicitudContrato()
    // {
    //     $db = \Config\Database::connect();

    //     try {
    //         log_message('debug', 'POST: ' . print_r($this->request->getPost(), true));
    //         $data = $this->request->getPost();

    //         $db->transBegin();

    //         $idCliente = $this->request->getPost('idCliente') ?: null;
    //         $idUsuario = $_SESSION['id_usuario'];
    //         $fechaCreacion = date('Y-m-d H:i:s');

    //         // =========================
    //         // 1. BENEFICIARIO
    //         // =========================
    //         $beneficiario = $this->crearBeneficiario($data);
    //         if (!$beneficiario['valido']) {
    //             $db->transRollback();
    //             log_message('error', 'Error beneficiario: ' . $beneficiario['error']);
    //             return $this->respondError($beneficiario['error']);
    //         }
    //         $idBeneficiario = $this->beneficiariosModel->insertarBeneficiario(
    //             $beneficiario['data']['nombre'],
    //             $beneficiario['data']['edad'],
    //             $beneficiario['data']['parentesco'],
    //             $beneficiario['data']['direccion'],
    //             $idCliente
    //         );
    //         log_message('debug', 'id_beneficiario: ' . print_r($idBeneficiario, true));
    //         if (!$idBeneficiario) {
    //             throw new \Exception('Error al insertar beneficiario');
    //         }

    //         // =========================
    //         // 2. SOLICITUD
    //         // =========================
    //         $numero = $this->solicitudesModel->correlativoSolicitud($db);
    //         $codigoFormateado = str_pad($numero, 5, '0', STR_PAD_LEFT);
    //         log_message('debug', 'codigo de solicitud: ' . print_r($codigoFormateado, true));
    //         $solicitud = $this->crearSolicitud($data);
    //         if (!$solicitud['valido']) {
    //             $db->transRollback();
    //             log_message('error', 'Error solicitud: ' . $solicitud['error']);
    //             return $this->respondError($solicitud['error']);
    //         }
    //         $idSolicitud = $this->solicitudesModel->insertarSolicitud(
    //             $codigoFormateado,
    //             $fechaCreacion,
    //             $idCliente,
    //             $idBeneficiario,
    //             $solicitud['data']['direccionInmueble'],
    //             $solicitud['data']['propietario'],
    //             $solicitud['data']['inquilino'],
    //             $solicitud['data']['representante'],
    //             $solicitud['data']['otroCheck'],
    //             $solicitud['data']['abonera'],
    //             $solicitud['data']['hoyoSeco'],
    //             $solicitud['data']['lavable'],
    //             $solicitud['data']['otroBaño'],
    //             $solicitud['data']['aceptaContruccionLetrina'],
    //             $solicitud['data']['tiempo'],
    //             $idUsuario,
    //             $fechaCreacion
    //         );
    //         log_message('debug', 'id_solicitud: ' . print_r($idSolicitud, true));
    //         if (!$idSolicitud) {
    //             throw new \Exception('Error al insertar solicitud');
    //         }

    //         // =========================
    //         // 3. CONTRATO            actualizar en tabla de medidores se necesita el id de contrato
    //         // =========================
    //         $numeroContrato = 'C-' . $codigoFormateado;
    //         log_message('debug', 'codigo de contrato: ' . print_r($numeroContrato, true));
    //         $contrato = $this->crearContrato($data);
    //         if (!$contrato['valido']) {
    //             $db->transRollback();
    //             log_message('error', 'Error contrato: ' . $contrato['error']);
    //             return $this->respondError($contrato['error']);
    //         }
    //         $idContrato = $this->contratosModel->insertarContrato(
    //             $idSolicitud,
    //             $numeroContrato,
    //             $contrato['data']['fichaAlcaldia'],
    //             $idCliente,
    //             $contrato['data']['fechaInicio'],
    //             $contrato['data']['fechaVencimiento'],
    //             $contrato['data']['estadoContrato'],
    //             $contrato['data']['idRuta'],
    //             $contrato['data']['idMedidor'],
    //             $contrato['data']['direccionMedidor'],
    //             $contrato['data']['idTarifa'],
    //             $contrato['data']['contado'],
    //             $contrato['data']['otro'],
    //             $contrato['data']['monto'],
    //             $contrato['data']['idPlanDePago'],
    //             $contrato['data']['saldoPendiente'],
    //             $fechaCreacion,
    //             $idUsuario
    //         );
    //         log_message('debug', 'id_contrato: ' . print_r($idContrato, true));
    //         if (!$idContrato) {
    //             throw new \Exception('Error al insertar contrato');
    //         }

    //         // =========================
    //         // 4. PLAN DE PAGO
    //         // =========================
    //         $planDePago = $this->crearPlanDePago($data);
    //         if (!$planDePago['valido']) {
    //             $db->transRollback();
    //             log_message('error', 'Error plan de pago: ' . $planDePago['error']);
    //             return $this->respondError($planDePago['error']);
    //         }
    //         $idPlanDePago = $this->planDePagosModel->guardarPlanDePago(
    //             $planDePago['data']['cantidadDePagos'],
    //             $planDePago['data']['montoCuotas'],
    //             $idContrato
    //         );
    //         log_message('debug', 'id_plan_de_pago: ' . print_r($idPlanDePago, true));
    //         if (!$idPlanDePago) {
    //             throw new \Exception('Error al insertar el plan de pago');
    //         }

    //         // =========================
    //         // 4. CUOTAS
    //         // =========================
    //         $cobros = $this->crearCobros($data);
    //         if (!$cobros['valido']) {
    //             $db->transRollback();
    //             log_message('error', 'Error en cobros: ' . $cobros['error']);
    //             return $this->respondError($cobros['error']);
    //         }
    //         $fecha = $cobros['data']['fechaBase'];

    //         for ($i = 1; $i <= $cobros['data']['cantidadDePagos']; $i++) {

    //             $fechaCalculada = date('Y-m-d', strtotime("$fecha +$i month"));

    //             $cuota = [
    //                 'id_contrato' => $idContrato,
    //                 'numero_cuota' => $i,
    //                 'monto_cuota' => $cobros['data']['totalCuota'],
    //                 'descripcion' => 'Cuota numero ' . $i,
    //                 'estado' => 'PENDIENTE',
    //                 'fecha_vencimiento' => $fechaCalculada,
    //                 'fecha_pago' => $fechaCalculada,
    //                 'cantidad_abonada' => null,
    //                 'id_usuario' => $idUsuario,
    //                 'fecha_creacion' => $fechaCreacion
    //             ];

    //             $this->contratosCobrosModel->insert($cuota);

    //             log_message('debug', 'datos insertados en cuotas: ' . print_r($cuota, true));
    //             $insert = $this->contratosCobrosModel->insert($cuota);
    //             if (!$insert) {
    //                 throw new \Exception('Error al insertar cuotas');
    //             }
    //         }

    //         // =========================
    //         // FINAL
    //         // =========================
    //         if ($db->transStatus() === false) {
    //             throw new \Exception('Error en la transacción');
    //         }

    //         $db->transCommit();

    //         return $this->respondOk('Solicitud y contrato generados correctamente');
    //     } catch (\Throwable $th) {

    //         $db->transRollback();

    //         log_message('error', $th->getMessage());

    //         return $this->respondError('Error al generar solicitud completa');
    //     }
    // }
}
