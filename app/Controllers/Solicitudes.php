<?php

namespace App\Controllers;

use App\Models\BeneficiarioModel;
use App\Models\ClienteModel;
use App\Models\CobroContratoModel;
use App\Models\ContratoModel;
use App\Models\FirmanteModel;
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
    private $firmantesModel;

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
        $this->firmantesModel = new FirmanteModel();
    }

    public function index()
    {
        return view('solicitudes/index');
    }

    public function formulario_solicitud()
    {
        return view('solicitudes/formulario_solicitud');
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

    public function getFirmantesSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $firmantes = $this->firmantesModel->buscarFirmantes($search);
            return $this->respondSuccess($firmantes);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los firmantes');
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

    public function getBeneficiariosId()
    {
        try {
            $idCliente = $this->request->getPost('idCliente');
            $beneficiariosHistorial = $this->beneficiariosModel->getBeneficiarios($idCliente);
            log_message('info', 'beneficiarios  ' . print_r($beneficiariosHistorial, true));
            // exit;
            return $this->respondSuccess($beneficiariosHistorial);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);
            return $this->respondError('Error al traer los clientes');
        }
    }

    public function getSolicitudesTabla()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->solicitudesModel->getSolicitudesCreadas($start, $length, $searchValue);

            return $this->response->setJSON([
                "draw" => $draw,
                "recordsTotal" => $result['total'],
                "recordsFiltered" => $result['filtered'],
                "data" => $result['data']
            ]);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->response->setJSON([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }
    }

    public function getSolicitudById()
    {
        try {
            $idSolicitud = $this->request->getVar('id');
            $infoSolicitud = $this->solicitudesModel->getInfoSolicitudPorId($idSolicitud);
            return $this->respondSuccess($infoSolicitud);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los clientes');
        }
    }

    public function crearBeneficiario($data)
    {
        $idBeneficiario = $data['idBeneficiario'] ?? null;
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
                'idBeneficiario' => $idBeneficiario,
                'nombre' => $nombre,
                'edad' => $edad,
                'parentesco' => $parentesco,
                'direccion' => $direccion
            ]
        ];
    }

    public function normalizar($valor)
    {
        return trim($valor ?? '') ?: null;
    }

    public function crearSolicitud($data)
    {

        $idSolicitud = $data['idSolicitud'] ?? null;


        $fechaCreacion = $data['fechaCreacion'] ?? null;
        $direccionInmueble = $this->normalizar($data['direccionInmueble'] ?? null);
        $propietario = filter_var($data['propietario'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $inquilino = filter_var($data['inquilino'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $representante = filter_var($data['representante'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $otroCheck = $this->normalizar($data['otroCheck'] ?? null);
        $abonera = filter_var($data['abonera'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hoyoSeco = filter_var($data['hoyoSeco'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $lavable = filter_var($data['lavable'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $otroBaño = $this->normalizar($data['otroBaño'] ?? null);
        $si = filter_var($data['si'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $no = filter_var($data['no'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $aceptaContruccionLetrina = false;
        if ($si === true) {
            $aceptaContruccionLetrina = true;
        } else if ($no == false) {
            $aceptaContruccionLetrina = false;
        }
        $tiempo = $this->normalizar($data['tiempo'] ?? null);
        $contado = filter_var($data['contado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $otroTipoPago = $this->normalizar($data['otro'] ?? null);
        $costoInstalacion = $this->normalizar($data['monto'] ?? null);
        $acuerdo = $this->normalizar($data['acuerdo'] ?? null);


        $fechaSession = $this->normalizar($data['fechaSession'] ?? null);
        $numeroActa = $this->normalizar($data['numeroActa'] ?? null);

        if (!$idSolicitud) {
            $estado = 'CREADA';
        } else {
            $estado = 'APROBADA';
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
                'contado' => $contado,
                'otroTipoPago' => $otroTipoPago,
                'costoInstalacion' => $costoInstalacion,
                'acuerdo' => $acuerdo,
                'fechaSession' => $fechaSession,
                'numeroActa' => $numeroActa,
                'estado' => $estado
            ]
        ];
    }

    private function tieneDatosBeneficiario(array $beneficiario)
    {
        return !empty($beneficiario['nombre'])
            || !empty($beneficiario['edad'])
            || !empty($beneficiario['parentesco'])
            || !empty($beneficiario['direccion']);
    }

    private function tieneDatosFirmante($nombreFirmante, $rolFirmante)
    {
        return !empty($this->valorComparable($nombreFirmante))
            && !empty($this->valorComparable($rolFirmante));
    }

    public function crearFirmantes($data)
    {
        $idFirmante1 = $data['idFirmante1'] ?? null;
        $nombreFirmante1 = $data['nombreFirmante1'] ?? null;
        $puestoFirmante1 = $data['puestoFirmante1'] ?? null;

        $idFirmante2 = $data['idFirmante2'] ?? null;
        $nombreFirmante2 = $data['nombreFirmante2'] ?? null;
        $puestoFirmante2 = $data['puestoFirmante2'] ?? null;

        $idFirmante3 = $data['idFirmante3'] ?? null;
        $nombreFirmante3 = $data['nombreFirmante3'] ?? null;
        $puestoFirmante3 = $data['puestoFirmante3'] ?? null;



        return [
            'valido' => true,
            'data' => [
                'idFirmante1' => $idFirmante1,
                'nombreFirmante1' => $nombreFirmante1,
                'puestoFirmante1' => $puestoFirmante1,

                'idFirmante2' => $idFirmante2,
                'nombreFirmante2' => $nombreFirmante2,
                'puestoFirmante2' => $puestoFirmante2,

                'idFirmante3' => $idFirmante3,
                'nombreFirmante3' => $nombreFirmante3,
                'puestoFirmante3' => $puestoFirmante3
            ]
        ];
    }

    public function crearPlanDePago($data)
    {
        $idPlanDePago = $data['idPlanPago'] ?? null;

        $contado = filter_var($data['contado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $monto = isset($data['monto']) ? (float)$data['monto'] : 0;

        $cantidadDePagos = $contado ? 0 : (isset($data['cantidadDePagos']) ? (int)$data['cantidadDePagos'] : 0);
        $totalCuota = $contado ? 0 : (isset($data['totalCuota']) ? (float)$data['totalCuota'] : 0);

        // VALIDACIÓN SOLO SI NO ES CONTADO
        if (!$contado) {

            $total = $cantidadDePagos * $totalCuota;

            // Redondeo igual que en JS (2 decimales)
            $total = round($total, 2);
            $monto = round($monto, 2);

            if ($total !== $monto) {
                return [
                    'valido' => false,
                    'error' => "Al sumar el total de las cuotas ($total) no coincide con el monto ($monto)"
                ];
            }
        }

        return [
            'valido' => true,
            'data' => [
                'idPlanDePago' => $idPlanDePago,
                'cantidadDePagos' => $cantidadDePagos,
                'totalCuota' => $totalCuota,
            ]
        ];
    }

    public function crearContrato($data)
    {
        $fichaAlcaldia = $this->normalizar($data['fichaAlcaldia'] ?? null);
        $fechaInicio = $data['fechaInicio'] ?? null;
        $fechaVencimiento = $this->normalizar($data['fechaVencimiento'] ?? null);

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


        $direccionMedidor = $this->normalizar($data['direccionMedidor'] ?? null);

        $idTarifa = $data['tarifa'];
        if (
            $idTarifa === '' ||
            $idTarifa === 'null' ||
            $idTarifa === '-1' ||
            $idTarifa === null
        ) {
            $idTarifa = null;
        }

        return [
            'valido' => true,
            'data' => [
                'fichaAlcaldia' => $fichaAlcaldia,
                'fechaInicio' => $fechaInicio,
                'fechaVencimiento' => $fechaVencimiento,
                'idRuta' => $idRuta,
                'idMedidor' => $idMedidor,
                'direccionMedidor' => $direccionMedidor,
                'idTarifa' => $idTarifa,
            ]
        ];
    }

    public function crearCobros($data)
    {
        $contado = filter_var($data['contado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $cantidadDePagos = $contado ? 0 : (int)($data['cantidadDePagos'] ?? 0);
        $totalCuota = $contado ? 0 : (float)($data['totalCuota'] ?? 0);
        $fechaBase = $data['fechaInicio'] ?? null;

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
        $medidor = $data['medidor'];
        if (
            $medidor === '' ||
            $medidor === 'null' ||
            $medidor === '-1' ||
            $medidor === null
        ) {
            $medidor = null;
        }

        // // VALIDACIONES (una por una)
        // if (empty($medidor)) {
        //     return [
        //         'valido' => false,
        //         'error' => 'Seleccionar un medidor es necesario'
        //     ];
        // }


        return [
            'valido' => true,
            'data' => [
                'medidor' => $medidor,
            ]
        ];
    }

    private function valorComparable($valor)
    {
        if ($valor === null) {
            return null;
        }

        return trim((string)$valor);
    }

    private function hayCambios(array $actual, array $nuevo)
    {
        foreach ($nuevo as $campo => $valorNuevo) {
            $valorActual = $actual[$campo] ?? null;

            if ($this->valorComparable($valorActual) !== $this->valorComparable($valorNuevo)) {
                return true;
            }
        }

        return false;
    }

    private function procesarFirmante($idFirmante, $nombreFirmante, $rolFirmante)
    {
        $idFirmante = $this->valorComparable($idFirmante);
        $nombreFirmante = $this->valorComparable($nombreFirmante);
        $rolFirmante = $this->valorComparable($rolFirmante);

        if (!empty($idFirmante)) {
            $firmanteActual = $this->firmantesModel->find($idFirmante);

            if (!$firmanteActual) {
                throw new \Exception("El {$rolFirmante} seleccionado no existe");
            }

            if ($this->hayCambios($firmanteActual, [
                'nombre' => $nombreFirmante,
                'rol' => $rolFirmante
            ])) {
                $firmanteActualizado = $this->firmantesModel->actualizarFirmante($idFirmante, $nombreFirmante, $rolFirmante);

                if (!$firmanteActualizado) {
                    throw new \Exception("Error al actualizar {$rolFirmante}");
                }
            }

            return (int)$idFirmante;
        }

        if (!$this->tieneDatosFirmante($nombreFirmante, $rolFirmante)) {
            return null;
        }

        $nuevoIdFirmante = $this->firmantesModel->insertarFirmantes($nombreFirmante, $rolFirmante);

        if (!$nuevoIdFirmante) {
            throw new \Exception("Error al insertar {$rolFirmante}");
        }

        return $nuevoIdFirmante;
    }

    public function nuevaSolicitud()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {

            log_message('debug', 'POST CREAR SOLICITUD: ' . print_r($this->request->getPost(), true));
            $data = $this->request->getPost();
            $idCliente = $data['idCliente'] ?? null;
            // exit;

            if (!$idCliente) {
                throw new \Exception('Debe seleccionar cliente');
            }

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');
            $idBeneficiario = null;
            $idPlan = null;
            $idAdministrador = null;
            $idComision1 = null;
            $idComision2 = null;

            // =========================
            // 1. CREAR SOLICITUD
            // =========================
            $solicitud = $this->crearSolicitud($data);

            if (!$solicitud['valido']) {
                throw new \Exception($solicitud['error']);
            }

            $numero = $this->solicitudesModel->correlativoSolicitud($db);
            $codigoFormateado = str_pad($numero, 5, '0', STR_PAD_LEFT);

            $idSolicitud = $this->solicitudesModel->insertarSolicitud(
                $codigoFormateado,
                $solicitud['data']['fechaCreacion'],
                $idCliente,
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
                $solicitud['data']['contado'],
                $solicitud['data']['otroTipoPago'],
                $solicitud['data']['costoInstalacion'],
                $solicitud['data']['acuerdo'],
                $solicitud['data']['fechaSession'],
                $solicitud['data']['numeroActa'],
                $solicitud['data']['estado'],
                $idUsuario,
                $fechaCreacion
            );
            log_message('info', 'id de solicitud ' . print_r($idSolicitud, true));

            if (!$idSolicitud) {
                throw new \Exception('Error al insertar solicitud');
            }

            // =========================
            // 2. BENEFICIARIO
            // =========================
            $beneficiario = $this->crearBeneficiario($data);

            if (!$beneficiario['valido']) {
                throw new \Exception($beneficiario['error']);
            }

            if ($beneficiario['data']['idBeneficiario']) {
                $idBeneficiario = $beneficiario['data']['idBeneficiario'];
                $beneficiarioActual = $this->beneficiariosModel->find($idBeneficiario);

                if (!$beneficiarioActual) {
                    throw new \Exception('El beneficiario seleccionado no existe');
                }

                $dataBeneficiario = [
                    'nombre' => $beneficiario['data']['nombre'],
                    'edad' => $beneficiario['data']['edad'],
                    'parentesco' => $beneficiario['data']['parentesco'],
                    'direccion' => $beneficiario['data']['direccion'],
                ];

                if ($this->hayCambios($beneficiarioActual, $dataBeneficiario)) {
                    $beneficiarioActualizado = $this->beneficiariosModel->update($idBeneficiario, $dataBeneficiario);

                    if (!$beneficiarioActualizado) {
                        throw new \Exception('Error al actualizar beneficiario');
                    }
                }
            } elseif ($this->tieneDatosBeneficiario($beneficiario['data'])) {
                $idBeneficiario = $this->beneficiariosModel->insertarBeneficiario(
                    $beneficiario['data']['nombre'],
                    $beneficiario['data']['edad'],
                    $beneficiario['data']['parentesco'],
                    $beneficiario['data']['direccion'],
                    $idCliente
                );
                log_message('info', 'id beneficiario ' . print_r($idBeneficiario, true));

                if (!$idBeneficiario) {
                    throw new \Exception('Error al insertar beneficiario');
                }
            } else {
                $idBeneficiario = null;
            }

            // =========================
            // 3. PLAN DE PAGO
            // =========================
            $plan = $this->crearPlanDePago($data);

            if (!$plan['valido']) {
                throw new \Exception($plan['error']);
            }

            if ($solicitud['data']['contado']) {
                $planDePago = [
                    'cantidad_cuotas' => 0,
                    'monto_cuotas' => $solicitud['data']['costoInstalacion']
                ];
                $insert = $this->planDePagosModel->insert($planDePago);
                log_message('info', 'datos ingresados como plan de pago por contado ' . print_r($planDePago, true));
                if (!$insert) {
                    throw new \Exception('Error al insertar plan de pago contado');
                }
            } else {
                $planDePago = [
                    'cantidad_cuotas' => $plan['data']['cantidadDePagos'],
                    'monto_cuotas' => $plan['data']['totalCuota']
                ];
                $insert = $this->planDePagosModel->insert($planDePago);
                log_message('info', 'datos ingresados como plan de pago por cuotas ' . print_r($planDePago, true));
                if (!$insert) {
                    throw new \Exception('Error al insertar plan de credito');
                }
            }
            log_message('info', 'id plan de pago ' . print_r($insert, true));

            // exit;
            // =========================
            // 4. FIRMANTES
            // =========================
            $firmantes = $this->crearFirmantes($data);

            if (!$firmantes['valido']) {
                throw new \Exception($firmantes['error']);
            }

            $idFirmante1 = $this->procesarFirmante(
                $firmantes['data']['idFirmante1'],
                $firmantes['data']['nombreFirmante1'],
                $firmantes['data']['puestoFirmante1'],
            );

            $idFirmante2 = $this->procesarFirmante(
                $firmantes['data']['idFirmante2'],
                $firmantes['data']['nombreFirmante2'],
                $firmantes['data']['puestoFirmante2'],
            );

            $idFirmante3 = $this->procesarFirmante(
                $firmantes['data']['idFirmante3'],
                $firmantes['data']['nombreFirmante3'],
                $firmantes['data']['puestoFirmante3'],
            );

            log_message('info', 'ids firmantes ' . print_r([
                'idFirmante1' => $idFirmante1,
                'idFirmante2' => $idFirmante2,
                'idFirmante3' => $idFirmante3,
            ], true));

            // =========================
            // 5. ACTUALIZAR SOLICITUD CON SUS RELACIONES
            // =========================
            $solicitudActualizada = $this->solicitudesModel->update($idSolicitud, [
                'id_beneficiario' => $idBeneficiario,
                'id_plan_de_pago' => $insert,
                'saldo_pendiente' => $solicitud['data']['costoInstalacion'],
                'id_firmante_1' => $idFirmante1,
                'id_firmante_2' => $idFirmante2,
                'id_firmante_3' => $idFirmante3
            ]);

            if (!$solicitudActualizada) {
                throw new \Exception('No se actualizaron las relaciones de la solicitud');
            }

            // =========================
            // FINAL
            // =========================
            $db->transCommit();

            log_message('info', 'Datos creado correctamente: ' . json_encode([
                'id_solicitud' => $idSolicitud,
                'id_plan_pago' => $idPlan,
                'id_beneficiario' => $idBeneficiario,
                'idFirmante1' => $idFirmante1,
                'idFirmante2' => $idFirmante2,
                'idFirmante3' => $idFirmante3,
            ]));

            return $this->respondOk('Solicitud creada correctamente con codigo ' . $codigoFormateado);
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->respondError($e->getMessage());
        }
    }

    public function aprobarSolicitud()
    {
        $db = \Config\Database::connect();
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        $db->transBegin();

        try {

            log_message('debug', 'POST EDITAR SOLICITUD: ' . print_r($this->request->getPost(), true));
            // exit;
            $data = $this->request->getPost();
            $idSolicitud = $data['idSolicitud'] ?? null;
            $idCliente = $data['idCliente'] ?? null;

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$idSolicitud) {
                throw new \Exception('Debe seleccionar una solicitud');
            }

            if (!$idCliente) {
                throw new \Exception('Debe seleccionar cliente');
            }

            $solicitudExistente = $this->solicitudesModel->find($idSolicitud);

            if (!$solicitudExistente) {
                throw new \Exception('La solicitud no existe');
            }

            $idBeneficiario = $solicitudExistente['id_beneficiario'] ?? null;
            $idPlan = $solicitudExistente['id_plan_de_pago'] ?? null;
            $idPlanAEliminar = null;
            $idAdministrador = $solicitudExistente['id_nombre_administrador'] ?? null;
            $idComision1 = $solicitudExistente['id_nombre_comision_1'] ?? null;
            $idComision2 = $solicitudExistente['id_nombre_comision_2'] ?? null;

            // =========================
            // 1. ACTUALIZAR SOLICITUD
            // =========================
            $solicitud = $this->crearSolicitud($data);

            if (!$solicitud['valido']) {
                throw new \Exception($solicitud['error']);
            }

            $solicitudActualizada = $this->solicitudesModel->actualizarSolicitud(
                $idSolicitud,
                $solicitud['data']['fechaCreacion'],
                $idCliente,
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
                $solicitud['data']['contado'],
                $solicitud['data']['otroTipoPago'],
                $solicitud['data']['costoInstalacion'],
                $solicitud['data']['acuerdo'],
                $solicitud['data']['fechaSession'],
                $solicitud['data']['numeroActa'],
                $solicitud['data']['estado']
            );
            log_message('info', 'id de solicitud actualizada ' . print_r($idSolicitud, true));

            if (!$solicitudActualizada) {
                throw new \Exception('Error al actualizar solicitud');
            }

            // =========================
            // 2. BENEFICIARIO
            // =========================
            $beneficiario = $this->crearBeneficiario($data);

            if (!$beneficiario['valido']) {
                throw new \Exception($beneficiario['error']);
            }

            if ($beneficiario['data']['idBeneficiario']) {
                $idBeneficiario = $beneficiario['data']['idBeneficiario'];
                $beneficiarioActual = $this->beneficiariosModel->find($idBeneficiario);

                if (!$beneficiarioActual) {
                    throw new \Exception('El beneficiario seleccionado no existe');
                }

                $dataBeneficiario = [
                    'nombre' => $beneficiario['data']['nombre'],
                    'edad' => $beneficiario['data']['edad'],
                    'parentesco' => $beneficiario['data']['parentesco'],
                    'direccion' => $beneficiario['data']['direccion'],
                ];

                if ($this->hayCambios($beneficiarioActual, $dataBeneficiario)) {
                    $beneficiarioActualizado = $this->beneficiariosModel->update($idBeneficiario, $dataBeneficiario);

                    if (!$beneficiarioActualizado) {
                        throw new \Exception('Error al actualizar beneficiario');
                    }
                }
            } elseif ($this->tieneDatosBeneficiario($beneficiario['data'])) {
                $idBeneficiario = $this->beneficiariosModel->insertarBeneficiario(
                    $beneficiario['data']['nombre'],
                    $beneficiario['data']['edad'],
                    $beneficiario['data']['parentesco'],
                    $beneficiario['data']['direccion'],
                    $idCliente
                );
                log_message('info', 'id beneficiario ' . print_r($idBeneficiario, true));

                if (!$idBeneficiario) {
                    throw new \Exception('Error al insertar beneficiario');
                }
            } else {
                $idBeneficiario = $solicitudExistente['id_beneficiario'] ?? null;
            }

            // =========================
            // 3. PLAN DE PAGO
            // =========================
            $plan = $this->crearPlanDePago($data);

            if (!$plan['valido']) {
                throw new \Exception($plan['error']);
            }

            $idPlan = $plan['data']['idPlanDePago'] ?: $idPlan;
            if (!$idPlan) {
                throw new \Exception('La solicitud no tiene plan de pago para actualizar');
            }

            $planActual = $this->planDePagosModel->find($idPlan);
            if (!$planActual) {
                throw new \Exception('El plan de pago no existe');
            }
            log_message('info', 'datos de plan recibido actual ' . print_r($planActual, true));

            $dataPlan = [
                'cantidad_cuotas' => $plan['data']['cantidadDePagos'],
                'monto_cuotas' => $solicitud['data']['contado']
                    ? $solicitud['data']['costoInstalacion']
                    : $plan['data']['totalCuota']
            ];

            if ($solicitud['data']['contado']) {
                log_message('info', 'datos de plan recibido desde la vista contado ' . print_r($dataPlan, true));
            } else {
                log_message('info', 'datos de plan recibido desde la vista credito ' . print_r($dataPlan, true));
            }

            if ($this->hayCambios($planActual, $dataPlan)) {
                $planActualizado = $this->planDePagosModel->actualizarPlanDePago(
                    $idPlan,
                    $dataPlan['cantidad_cuotas'],
                    $dataPlan['monto_cuotas']
                );

                if (!$planActualizado) {
                    throw new \Exception('Error al actualizar plan de pago');
                }
            }


            log_message('info', 'id plan de pago ' . print_r($idPlan, true));
            // exit;
            // =========================
            // 4. FIRMANTES
            // =========================
            $firmantes = $this->crearFirmantes($data);

            if (!$firmantes['valido']) {
                throw new \Exception($firmantes['error']);
            }

            $idFirmante1 = $this->procesarFirmante(
                $firmantes['data']['idFirmante1'],
                $firmantes['data']['nombreFirmante1'],
                $firmantes['data']['puestoFirmante1'],
            );

            $idFirmante2 = $this->procesarFirmante(
                $firmantes['data']['idFirmante2'],
                $firmantes['data']['nombreFirmante2'],
                $firmantes['data']['puestoFirmante2'],
            );

            $idFirmante3 = $this->procesarFirmante(
                $firmantes['data']['idFirmante3'],
                $firmantes['data']['nombreFirmante3'],
                $firmantes['data']['puestoFirmante3'],
            );

            log_message('info', 'ids firmantes ' . print_r([
                'idFirmante1' => $idFirmante1,
                'idFirmante2' => $idFirmante2,
                'idFirmante3' => $idFirmante3,
            ], true));

            // =========================
            // 5. ACTUALIZAR SOLICITUD CON SUS RELACIONES
            // =========================
            $relacionesActualizadas = $this->solicitudesModel->update($idSolicitud, [
                'id_beneficiario' => $idBeneficiario,
                'id_plan_de_pago' => $idPlan,
                'saldo_pendiente' => $solicitud['data']['costoInstalacion'],
                'id_firmante_1' => $idFirmante1,
                'id_firmante_2' => $idFirmante2,
                'id_firmante_3' => $idFirmante3
            ]);

            if (!$relacionesActualizadas) {
                throw new \Exception('No se actualizaron las relaciones de la solicitud');
            }

            if (!empty($idPlanAEliminar)) {
                $planEliminado = $this->planDePagosModel->delete($idPlanAEliminar);

                if (!$planEliminado) {
                    throw new \Exception('No se pudo eliminar el plan de pago anterior');
                }
            }

            // =========================
            // 3. CONTRATO
            // =========================
            $contrato = $this->crearContrato($data);

            if (!$contrato['valido']) {
                throw new \Exception($contrato['error']);
            }

            $numero = $this->solicitudesModel->getCorrelativoSolicitud($idSolicitud);
            log_message('info', 'codigo de solicitud obtenido ' . print_r($numero, true));
            $numeroContrato = 'C-' . $numero['codigo_solicitud'];
            log_message('info', 'codigo de contrato ' . print_r($numeroContrato, true));
            $estadoContrato = 'APROBADO';
            $idContrato = $this->contratosModel->insertarContrato(
                $idSolicitud,
                $numeroContrato,
                $contrato['data']['fichaAlcaldia'],
                $idCliente,
                $contrato['data']['fechaInicio'],
                $contrato['data']['fechaVencimiento'],
                $contrato['data']['idRuta'],
                $contrato['data']['idMedidor'],
                $contrato['data']['direccionMedidor'],
                $contrato['data']['idTarifa'],
                $estadoContrato,
                $fechaCreacion,
                $idUsuario
            );
            log_message('info', 'id de contrato ' . print_r($idContrato, true));

            if (!$idContrato) {
                throw new \Exception('Error al insertar contrato');
            }


            // =========================
            // 5. CUOTAS
            // =========================
            $cobros = $this->crearCobros($data);

            if (!$cobros['valido']) {
                throw new \Exception($cobros['error']);
            }

            $fechaBase = new \DateTimeImmutable($cobros['data']['fechaBase']);

            $sumar = 1;
            if ($solicitud['data']['contado'] == true) {
                log_message('info', 'pago unico');
                $fechaCalculada = $fechaBase->modify("+{$sumar} month")->format('Y-m-d');

                $cuota = [
                    'id_contrato' => $idContrato,
                    'numero_cuota' => 0,
                    'monto_cuota' => $solicitud['data']['costoInstalacion'],
                    'descripcion' => "Pago único por la conexión",
                    'estado' => 'PENDIENTE',
                    'fecha_vencimiento' => $fechaCalculada,
                    // 'fecha_pago' => $fechaCalculada,
                    'id_usuario' => $idUsuario,
                    'fecha_creacion' => $fechaCreacion
                ];

                $insert = $this->contratosCobrosModel->insert($cuota);
                log_message('info', 'id de cobro de contrato ' . print_r($cuota, true));
                if (!$insert) {
                    throw new \Exception('Error al insertar cuotas');
                }
            } else {
                log_message('info', 'pago por cuotas');
                for ($i = 1; $i <= $cobros['data']['cantidadDePagos']; $i++) {

                    $fechaCalculada = $fechaBase->modify("+{$i} month")->format('Y-m-d');

                    $cuota = [
                        'id_contrato' => $idContrato,
                        'numero_cuota' => $i,
                        'monto_cuota' => $cobros['data']['totalCuota'],
                        'descripcion' => "Cuota numero $i de " . $cobros['data']['cantidadDePagos'],
                        'estado' => 'PENDIENTE',
                        'fecha_vencimiento' => $fechaCalculada,
                        // 'fecha_pago' => $fechaCalculada,
                        'id_usuario' => $idUsuario,
                        'fecha_creacion' => $fechaCreacion
                    ];

                    $insert = $this->contratosCobrosModel->insert($cuota);
                    log_message('info', 'id de cobro de contrato ' . print_r($cuota, true));
                    if (!$insert) {
                        throw new \Exception('Error al insertar cuotas');
                    }
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

            if ($idMedidor !== null) {
                $this->medidoresModel->update($idMedidor, [
                    'id_contrato' => $idContrato
                ]);

                if ($this->medidoresModel->db->affectedRows() === 0) {
                    throw new \Exception('No se actualizó el medidor');
                }
            }
            // exit;


            // =========================
            // FINAL
            // =========================
            $db->transCommit();

            log_message('info', 'Datos actualizados correctamente: ' . json_encode([
                'id_solicitud' => $idSolicitud,
                'id_plan_pago' => $idPlan,
                'id_beneficiario' => $idBeneficiario,
                'id_administrador' => $idAdministrador,
                'id_comision_1' => $idComision1,
                'id_comision_2' => $idComision2,
            ]));

            return $this->respondOk('Solicitud aprobada correctamente y contrato generado con numero ' . $numeroContrato);
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->respondError($e->getMessage());
        }
    }

    public function getSolicitudesTablaAprobadas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->solicitudesModel->getSolicitudesAprobadas($start, $length, $searchValue);

            return $this->response->setJSON([
                "draw" => $draw,
                "recordsTotal" => $result['total'],
                "recordsFiltered" => $result['filtered'],
                "data" => $result['data']
            ]);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->response->setJSON([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }
    }

    public function anularSolicitud()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            log_message('info', '=== INICIO anularSolicitud ===');

            $data = $this->request->getPost();
            log_message('debug', 'POST recibido: ' . print_r($data, true));

            $idSolicitud = $data['idSolicitud'] ?? null;
            log_message('info', 'ID recibido: ' . $idSolicitud);

            if (!$idSolicitud) {
                log_message('error', 'ID de solicitud vacío');
                return $this->respondError('El id es requerido');
            }

            // =========================
            // Buscar solicitud
            // =========================
            $dataSolicitud = $this->solicitudesModel->find($idSolicitud);
            log_message('debug', 'Datos DB solicitud: ' . print_r($dataSolicitud, true));

            if (!$dataSolicitud) {
                log_message('error', 'Solicitud no encontrada ID: ' . $idSolicitud);
                return $this->respondError('Solicitud no encontrada');
            }

            if ($dataSolicitud['estado'] == 'ANULADA') {
                log_message('error', 'Solicitud ya se encuentra anulada');
                return $this->respondError('Solicitud ya se encuentra anulada');
            }

            // =========================
            // Preparar datos
            // =========================
            $fechaAnulacion = date('Y-m-d H:i:s');

            $dataAnular = [
                'estado' => 'ANULADA',
                // 'saldo_pendiente' => 0,
                'fecha_anulada' => $fechaAnulacion
            ];

            log_message('info', 'Datos a anular: ' . print_r($dataAnular, true));

            // =========================
            // Update
            // =========================
            $updated = $this->solicitudesModel->update($idSolicitud, $dataAnular);

            log_message('info', 'Resultado update: ' . print_r($updated, true));
            log_message('info', 'Filas afectadas: ' . $this->solicitudesModel->db->affectedRows());

            if (!$updated || $this->solicitudesModel->db->affectedRows() === 0) {
                throw new \Exception('No se pudo actualizar la solicitud');
            }

            // =========================
            // Estado de transacción
            // =========================
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }
            // exit;
            $db->transCommit();

            log_message('info', '=== FIN anularSolicitud OK ===');

            return $this->respondOk('Solicitud anulada correctamente');
        } catch (\Throwable $th) {
            $db->transRollback();

            log_message('error', 'ERROR en anularSolicitud: ' . $th->getMessage());
            log_message('error', 'TRACE: ' . $th->getTraceAsString());

            return $this->respondError('Error al anular la solicitud');
        }
    }
}
