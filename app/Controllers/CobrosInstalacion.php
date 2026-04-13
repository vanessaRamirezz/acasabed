<?php

namespace App\Controllers;

use App\Models\CobroContratoModel;
use App\Models\HistorialCobroInstalacionModel;
use App\Models\SolicitudModel;

class CobrosInstalacion extends BaseController
{
    private $cobrosContratoModel;
    private $historialCobroInstalacionModel;
    private $solicitudesModel;

    public function __construct()
    {
        $this->cobrosContratoModel = new CobroContratoModel();
        $this->historialCobroInstalacionModel = new HistorialCobroInstalacionModel();
        $this->solicitudesModel = new SolicitudModel();
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

            if ($this->tablaHistorialExiste()) {
                $result = $this->historialCobroInstalacionModel->getHistorial($start, $length, $searchValue);
            } else {
                $result = $this->cobrosContratoModel->getCobrosRealizados($start, $length, $searchValue);
            }

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

    public function buscarCuentas()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $cuentas = $this->cobrosContratoModel->buscarCuentasPendientes($search);
            return $this->respondSuccess($cuentas);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al buscar cuentas de cobro');
        }
    }

    public function getDetalleCobro()
    {
        try {
            $idContrato = $this->request->getVar('idContrato');

            if (!$idContrato) {
                return $this->respondError('Debe seleccionar una cuenta');
            }

            $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);

            if (!$detalle) {
                return $this->respondError('No se encontró información del cobro');
            }

            return $this->respondSuccess($detalle);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al obtener detalle del cobro');
        }
    }

    public function validarCobro()
    {
        try {
            $idContrato = $this->request->getPost('idContrato');
            $montoPago = (float)($this->request->getPost('montoPago') ?? 0);
            $cobrarMora = filter_var($this->request->getPost('cobrarMora') ?? false, FILTER_VALIDATE_BOOLEAN);
            $mora = $cobrarMora ? (float)($this->request->getPost('mora') ?? 0) : 0;

            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta para cobrar');
            }

            if ($montoPago <= 0) {
                throw new \Exception('El monto a cancelar debe ser mayor a 0');
            }

            if ($mora < 0) {
                throw new \Exception('La mora no puede ser negativa');
            }

            $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);

            if (!$detalle) {
                throw new \Exception('No se encontró información del cobro');
            }

            $saldoPendiente = (float)$detalle['resumen']['saldo_pendiente'];

            if ($montoPago > $saldoPendiente) {
                throw new \Exception('El monto a cancelar no puede ser mayor al saldo pendiente');
            }

            return $this->respondSuccess([
                'saldoPendiente' => $saldoPendiente,
                'montoPago' => $montoPago,
                'mora' => $mora,
                'totalRecibido' => $montoPago + $mora,
                'cuotasPendientes' => (int)$detalle['resumen']['cuotas_pendientes']
            ]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());
        }
    }

    public function registrarPago()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $idContrato = $this->request->getPost('idContrato');
            $montoPago = (float)($this->request->getPost('montoPago') ?? 0);
            $cobrarMora = filter_var($this->request->getPost('cobrarMora') ?? false, FILTER_VALIDATE_BOOLEAN);
            $mora = $cobrarMora ? (float)($this->request->getPost('mora') ?? 0) : 0;

            if (!$idContrato) {
                throw new \Exception('Debe seleccionar una cuenta para cobrar');
            }

            if ($montoPago <= 0) {
                throw new \Exception('El monto a pagar debe ser mayor a 0');
            }

            $detalle = $this->cobrosContratoModel->getDetalleCobroPorContrato($idContrato);

            if (!$detalle) {
                throw new \Exception('No se encontró información del contrato a cobrar');
            }

            $saldoPendiente = (float)$detalle['resumen']['saldo_pendiente'];

            if ($saldoPendiente <= 0) {
                throw new \Exception('Este contrato no tiene saldo pendiente');
            }

            if ($montoPago > $saldoPendiente) {
                throw new \Exception('El monto a pagar no puede ser mayor al saldo pendiente');
            }

            $cuotasPendientes = $this->cobrosContratoModel->getCuotasPendientesPorContrato($idContrato);

            if (empty($cuotasPendientes)) {
                throw new \Exception('No hay cuotas pendientes para aplicar el pago');
            }

            $restante = $montoPago;
            $montoAplicado = 0;
            $fechaPago = date('Y-m-d');
            $idUsuario = $_SESSION['id_usuario'] ?? null;

            foreach ($cuotasPendientes as $cuota) {
                if ($restante <= 0) {
                    break;
                }

                $saldoCuota = (float)$cuota['saldo_cuota'];

                if ($saldoCuota <= 0) {
                    continue;
                }

                $abono = min($restante, $saldoCuota);
                $nuevoAbonado = (float)$cuota['cantidad_abonada'] + $abono;
                $estado = $nuevoAbonado >= (float)$cuota['monto_cuota'] ? 'PAGADO' : 'ABONO PARCIAL';

                $actualizado = $this->cobrosContratoModel->update($cuota['id_cobro_instalacion'], [
                    'cantidad_abonada' => $nuevoAbonado,
                    'estado' => $estado,
                    'fecha_pago' => $fechaPago,
                    'id_usuario' => $idUsuario,
                ]);

                if (!$actualizado) {
                    throw new \Exception('No se pudo actualizar una de las cuotas del cobro');
                }

                $restante -= $abono;
                $montoAplicado += $abono;
            }

            if ($montoAplicado <= 0) {
                throw new \Exception('No se pudo aplicar el pago');
            }

            $nuevoSaldoSolicitud = max(0, ((float)$detalle['resumen']['saldo_solicitud']) - $montoAplicado);

            $solicitudActualizada = $this->solicitudesModel->update($detalle['resumen']['id_solicitud'], [
                'saldo_pendiente' => $nuevoSaldoSolicitud
            ]);

            if (!$solicitudActualizada) {
                throw new \Exception('No se pudo actualizar el saldo pendiente de la solicitud');
            }

            if ($this->tablaHistorialExiste()) {
                $historial = $this->historialCobroInstalacionModel->insert([
                    'id_contrato' => $idContrato,
                    'id_solicitud' => $detalle['resumen']['id_solicitud'],
                    'id_cliente' => $detalle['resumen']['id_cliente'],
                    'monto_cobrado' => $montoAplicado,
                    'mora' => $mora,
                    'total_pagado' => $montoAplicado + $mora,
                    'id_usuario' => $idUsuario,
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                ]);

                if (!$historial) {
                    throw new \Exception('No se pudo guardar el historial del cobro');
                }
            }

            $db->transCommit();

            return $this->respondOk('Pago aplicado correctamente');
        } catch (\Throwable $th) {
            $db->transRollback();

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError($th->getMessage());
        }
    }

    private function tablaHistorialExiste()
    {
        return $this->cobrosContratoModel->db->tableExists('historial_cobros_instalacion');
    }
}
