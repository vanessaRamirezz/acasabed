<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\HistorialCobroInstalacionModel;
use App\Models\PagosInstalacionModel;
use App\Models\SolicitudModel;
use PHPUnit\Event\Telemetry\Info;

class CobrosInstalacion extends BaseController
{
    private $cobrosContratoModel;
    private $historialCobroInstalacionModel;
    private $solicitudesModel;
    private $pagosInstalacionModel;

    public function __construct()
    {
        $this->cobrosContratoModel = new CobroContratoModel();
        $this->historialCobroInstalacionModel = new HistorialCobroInstalacionModel();
        $this->solicitudesModel = new SolicitudModel();
        $this->pagosInstalacionModel = new PagosInstalacionModel();
    }

    public function index()
    {
        return view('cobrosInstalacion/index');
    }

    public function getCobrosRealizados()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->historialCobroInstalacionModel->getHistorial($start, $length, $searchValue);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => $result['data']
            ]);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());

            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }






    public function getDetalleCobroCliente()
    {
        try {
            $idCliente = $this->request->getVar('idCliente'); // ✅ corregido

            if (!$idCliente) {
                return $this->respondError('Debe seleccionar un cliente');
            }

            $detalle = $this->cobrosContratoModel->getDetalleCobroPorCliente($idCliente);

            if (empty($detalle)) {
                return $this->respondError('No se encontró información del cliente');
            }

            return $this->respondSuccess($detalle);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al obtener detalle del cobro');
        }
    }

    private function calcularAplicacionPago($idContrato, $montoPago, $moras)
    {
        if ($montoPago <= 0) {
            throw new \Exception('El monto debe ser mayor a 0');
        }

        // 🔹 Calcular mora total
        $totalMora = 0;
        foreach ($moras as $m) {
            if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
                throw new \Exception('Formato de mora inválido');
            }

            if ((float)$m['mora'] < 0) {
                throw new \Exception('La mora no puede ser negativa');
            }

            $totalMora += (float)$m['mora'];
        }

        // 🔹 Obtener detalle
        $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);
        if (!$detalle) {
            throw new \Exception('No se encontró información');
        }

        $cuotasPendientes = array_values(array_filter($detalle['cuotas'], function ($c) {
            return ($c['monto_cuota'] - $c['cantidad_abonada']) > 0;
        }));

        if (empty($cuotasPendientes)) {
            throw new \Exception('No hay cuotas pendientes');
        }

        // 🔹 Validar mínimo (primera cuota + mora)
        $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
        $montoMinimo = $primeraCuota + $totalMora;

        // ❌ caso 1: ni siquiera alcanza la primera cuota
        if ($montoPago < $primeraCuota) {
            throw new \Exception(
                'El monto no alcanza para cubrir la primera cuota.'
            );
        }

        // ❌ caso 2: mora existe pero no está incluida correctamente
        if ($totalMora > 0 && $montoPago < ($primeraCuota + $totalMora)) {
            throw new \Exception(
                'El monto debe incluir la mora.'
            );
        }
        // $primeraCuota = (float)$cuotasPendientes[0]['saldo_cuota'];
        // $montoMinimo = $primeraCuota + $totalMora;

        // if (round($montoPago, 2) < round($montoMinimo, 2)) {
        //     throw new \Exception(
        //         'El monto debe incluir la mora.'
        //     );
        // }

        // 🔹 Separar monto
        $montoParaCuotas = $montoPago - $totalMora;

        // 🔹 Aplicar SOLO cuotas completas
        $cuotasAplicadas = [];
        $totalAplicable = 0;

        foreach ($cuotasPendientes as $cuota) {
            $saldoCuota = (float)$cuota['saldo_cuota'];

            if ($montoParaCuotas >= ($totalAplicable + $saldoCuota)) {
                $totalAplicable += $saldoCuota;
                $cuotasAplicadas[] = $cuota;
            } else {
                break;
            }
        }

        $totalEsperado = $totalAplicable + $totalMora;

        if (round($montoPago, 2) != round($totalEsperado, 2)) {
            throw new \Exception(
                'El monto debe ser exacto (cuotas completas + recargo si aplica).'
            );
        }

        return [
            'detalle' => $detalle,
            'cuotasAplicadas' => $cuotasAplicadas,
            'montoCuotas' => $totalAplicable,
            'moraTotal' => $totalMora,
            'totalEsperado' => $totalEsperado
        ];
    }


    public function validarCobroInstalacion()
    {
        try {
            $idContrato = $this->request->getPost('idContrato');
            $montoPago = (float)$this->request->getPost('montoPago');
            $moras = json_decode($this->request->getPost('moras'), true) ?? [];

            log_message('debug', 'datos recibidos para validae ' . print_r($this->request->getPost(), true));
            // exit;
            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta');
            }

            $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

            return $this->respondSuccess([
                'montoPago' => $montoPago,
                'montoCuotas' => $resultado['montoCuotas'],
                'moraTotal' => $resultado['moraTotal'],
                'totalEsperado' => $resultado['totalEsperado'],
                'cuotasAplicadas' => array_column($resultado['cuotasAplicadas'], 'id_cobro_instalacion')
            ]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());
        }
    }

    public function registrarPagoInstalacion()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            log_message('info', '===== INICIO registrarPagoInstalacion =====');

            $idContrato = $this->request->getPost('idContrato');
            $montoPago  = (float)$this->request->getPost('montoPago');
            $moras      = json_decode($this->request->getPost('moras'), true) ?? [];

            log_message('info', 'POST recibido: ' . print_r([
                'idContrato' => $idContrato,
                'montoPago'  => $montoPago,
                'moras'      => $moras
            ], true));

            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta');
            }

            // 🔒 Revalidación completa
            $resultado = $this->calcularAplicacionPago($idContrato, $montoPago, $moras);

            log_message('info', 'Resultado cálculo: ' . print_r($resultado, true));

            $cuotasAplicadas = $resultado['cuotasAplicadas'];
            $montoAplicado   = $resultado['montoCuotas'];
            $detalle         = $resultado['detalle'];

            $fechaPago = date('Y-m-d');

            log_message('info', 'Cuotas a procesar: ' . print_r($cuotasAplicadas, true));

            // 🔥 MAPA DE MORAS
            $morasMap = [];

            foreach ($moras as $m) {
                if (!isset($m['id_cobro_instalacion'], $m['mora'])) {
                    log_message('warning', 'Mora inválida ignorada: ' . print_r($m, true));
                    continue;
                }

                $morasMap[(int)$m['id_cobro_instalacion']] = (float)$m['mora'];
            }

            log_message('info', 'Mapa de moras construido: ' . print_r($morasMap, true));

            // 🔥 CORRELATIVO DE PAGO
            $pagosModel = new \App\Models\pagosInstalacionModel();
            $correlativo = $pagosModel->correlativoPago($db);

            log_message('info', 'Correlativo generado: ' . $correlativo);

            // 🔥 INSERT CABECERA PAGO
            $idPago = $pagosModel->insert([
                'correlativo'   => $correlativo,
                'id_contrato'   => $idContrato,
                'id_solicitud'  => $detalle['resumen']['id_solicitud'],
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);

            log_message('info', 'ID pago creado: ' . $idPago);

            if (!$idPago) {
                throw new \Exception('No se pudo crear el pago');
            }

            // 🔥 PROCESAR CUOTAS
            foreach ($cuotasAplicadas as $cuota) {

                log_message('info', 'Procesando cuota: ' . print_r($cuota, true));

                $id = $cuota['id_cobro_instalacion'];

                $moraCuota = $morasMap[$id] ?? 0;

                log_message('info', "Cuota ID {$id} con mora: {$moraCuota}");

                $dataUpdate = [
                    'estado'            => 'CANCELADO',
                    'cantidad_abonada'  => $cuota['saldo_cuota'],
                    'recargo'           => $moraCuota,
                    'fecha_pago'        => $fechaPago
                ];

                log_message('info', 'Update cuota: ' . print_r($dataUpdate, true));

                $updated = $this->cobrosContratoModel->update($id, $dataUpdate);

                log_message('info', 'Resultado update: ' . ($updated ? 'OK' : 'FAIL'));

                if (!$updated) {
                    throw new \Exception("No se actualizó la cuota ID {$id}");
                }

                // 🔥 HISTORIAL POR CUOTA (correcto dentro del loop)

                $historialData = [
                    'id_pago'              => $idPago,
                    'id_contrato'         => $idContrato,
                    'id_solicitud'        => $detalle['resumen']['id_solicitud'],
                    'id_cobro_instalacion' => $id,
                    'recargo_aplicado'    => $moraCuota,
                    'monto_cuota'         => $cuota['saldo_cuota'],
                    'total'               => $cuota['saldo_cuota'] + $moraCuota,
                    'fecha_creacion'      => date('Y-m-d H:i:s')
                ];

                log_message('info', 'Insert historial: ' . print_r($historialData, true));

                $hist = $this->historialCobroInstalacionModel->insert($historialData);

                log_message('info', 'Historial insert ID: ' . $hist);

                if (!$hist) {
                    throw new \Exception('No se pudo guardar historial');
                }
            }

            // 🔥 ACTUALIZAR SALDO
            $nuevoSaldo = max(0, (float)$detalle['resumen']['saldo_pendiente'] - $montoAplicado);

            log_message('info', 'Nuevo saldo a actualizar: ' . $nuevoSaldo);

            $this->solicitudesModel->update(
                $detalle['resumen']['id_solicitud'],
                ['saldo_pendiente' => $nuevoSaldo]
            );

            // exit;
            $db->transCommit();

            log_message('info', '===== FIN OK registrarPagoInstalacion =====');

            return $this->respondOk('Pago aplicado correctamente');
        } catch (\Throwable $th) {

            $db->transRollback();

            log_message('error', 'ERROR registrarPagoInstalacion: ' . $th->getMessage());

            return $this->respondError($th->getMessage());
        }
    }
}
