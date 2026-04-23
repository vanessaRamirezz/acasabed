<?php

namespace App\Controllers;

use App\Models\TarifaDetalleModel;
use App\Models\TarifaModel;
use App\Models\TipoClienteModel;

class Tarifario extends BaseController
{
    private $tiposClienteModel;
    private $tarifasModel;
    private $tarifaDetalleModel;

    public function __construct()
    {
        $this->tiposClienteModel = new TipoClienteModel();
        $this->tarifasModel = new TarifaModel();
        $this->tarifaDetalleModel = new TarifaDetalleModel();
    }

    public function index()
    {
        $data['tipoClientes'] = $this->tiposClienteModel->findAll();
        return view('tarifario/index', $data);
    }

    public function getTarifas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->tarifasModel->getTodasTarifas($start, $length, $searchValue);

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

    public function getTarifaDetalle($idTarifa)
    {
        $detalles = $this->tarifaDetalleModel
            ->where('id_tarifa', $idTarifa)
            ->orderBy('desde_n_metros', 'ASC')
            ->findAll();

        return $this->response->setJSON($detalles);
    }

    public function nuevaTarifa()
    {
        $db = null;

        try {
            $request = $this->request->getJSON(true);

            log_message('info', "data JSON recibida:\n" . json_encode($request, JSON_PRETTY_PRINT));
            // exit;
            $codigo = $request['codigo'] ?? null;
            $tipoCliente = $request['tipoCliente'] ?? null;
            $detalles = $request['detalles'] ?? [];

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$codigo) {
                return $this->respondError('El código es requerido');
            }

            if (!$tipoCliente) {
                return $this->respondError('El campo Tipo de cliente es requerido');
            }

            if (empty($detalles) || !is_array($detalles)) {
                return $this->respondError('Debe agregar al menos un rango de tarifa');
            }

            // 🔥 INICIAR TRANSACCIÓN
            $db = \Config\Database::connect();
            $db->transBegin();

            // 🔹 Insert cabecera
            $idTarifa = $this->tarifasModel->insertarNuevaTarifa(
                $codigo,
                $tipoCliente,
                $idUsuario,
                $fechaCreacion
            );

            if (!$idTarifa) {
                $errorDB = $db->error();

                if ($errorDB['code'] == 1062) {
                    $db->transRollback();
                    return $this->respondError('El Código de tarifa ya existe');
                }

                throw new \Exception('Error al insertar tarifa');
            }

            // 🔹 Insert detalles (VALIDANDO)
            foreach ($detalles as $d) {

                $insert = $this->tarifaDetalleModel->insert([
                    'id_tarifa' => $idTarifa,
                    'valor_metro_cubico' => $d['valor_metro_cubico'] ?? 0,
                    'desde_n_metros' => $d['desde'],
                    'hasta_n_metros' => $d['hasta'] ?? null,
                    'pago_minimo' => $d['pago_minimo'] ?? 0
                ]);

                if (!$insert) {
                    throw new \Exception('Error al insertar detalle de tarifa');
                }
            }

            // 🔥 VALIDACIÓN FINAL
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Tarifa registrada correctamente.');
        } catch (\Throwable $th) {

            if ($db && $db->transStatus() !== false) {
                $db->transRollback();
            }

            log_message('error', $th->getMessage());

            return $this->respondError('Error al guardar la tarifa');
        }
    }

    public function editarTarifa()
    {
        $db = null;

        try {

            $request = $this->request->getJSON(true);

            log_message('info', "EDITAR TARIFA:\n" . json_encode($request, JSON_PRETTY_PRINT));

            $codigo       = $request['codigo'] ?? null;
            $tipoCliente  = $request['tipoCliente'] ?? null;
            $detalles     = $request['detalles'] ?? [];
            $idTarifa     = $request['idTarifa'] ?? null;

            if (!$idTarifa) {
                return $this->respondError('ID de tarifa requerido');
            }

            if (!$codigo) {
                return $this->respondError('El código es requerido');
            }

            if (!$tipoCliente) {
                return $this->respondError('El tipo de cliente es requerido');
            }

            if (empty($detalles) || !is_array($detalles)) {
                return $this->respondError('Debe agregar al menos un rango');
            }

            // =========================
            // VALIDAR RANGOS (CRÍTICO)
            // =========================
            usort($detalles, fn($a, $b) => $a['desde'] <=> $b['desde']);

            $finAnterior = null;

            foreach ($detalles as $index => $d) {

                if ($index === 0 && $d['desde'] != 0) {
                    throw new \Exception('El primer rango debe iniciar en 0');
                }

                if ($finAnterior !== null && $d['desde'] != $finAnterior + 1) {
                    throw new \Exception('Los rangos deben ser continuos');
                }

                if (!empty($d['hasta']) && $d['hasta'] <= $d['desde']) {
                    throw new \Exception('El campo hasta debe ser mayor que desde');
                }

                $finAnterior = $d['hasta'];
            }

            // =========================
            // TRANSACCIÓN
            // =========================
            $db = \Config\Database::connect();
            $db->transBegin();

            // 🔹 1. ACTUALIZAR CABECERA
            $update = $this->tarifasModel->update($idTarifa, [
                'id_tipo_cliente' => $tipoCliente
            ]);

            if (!$update) {
                throw new \Exception('Error al actualizar cabecera');
            }

            // =========================
            // 🔥 LÓGICA INTELIGENTE
            // =========================
            $idsRecibidos = [];

            foreach ($detalles as $d) {

                // 🔸 UPDATE si existe ID
                if (!empty($d['id'])) {

                    $updated = $this->tarifaDetalleModel->update($d['id'], [
                        'desde_n_metros' => $d['desde'],
                        'hasta_n_metros' => $d['hasta'] ?? null,
                        'valor_metro_cubico' => $d['valor_metro_cubico'] ?? 0,
                        'pago_minimo' => $d['pago_minimo'] ?? 0
                    ]);

                    if (!$updated) {
                        throw new \Exception('Error al actualizar detalle ID: ' . $d['id']);
                    }

                    $idsRecibidos[] = $d['id'];
                } else {

                    // 🔸 INSERT nuevo
                    $nuevoId = $this->tarifaDetalleModel->insert([
                        'id_tarifa' => $idTarifa,
                        'desde_n_metros' => $d['desde'],
                        'hasta_n_metros' => $d['hasta'] ?? null,
                        'valor_metro_cubico' => $d['valor_metro_cubico'] ?? 0,
                        'pago_minimo' => $d['pago_minimo'] ?? 0
                    ]);

                    if (!$nuevoId) {
                        throw new \Exception('Error al insertar nuevo detalle');
                    }

                    $idsRecibidos[] = $nuevoId;
                }
            }

            // 🔹 ELIMINAR LOS QUE YA NO VIENEN
            if (!empty($idsRecibidos)) {
                $this->tarifaDetalleModel
                    ->where('id_tarifa', $idTarifa)
                    ->whereNotIn('id_tarifa_detalle', $idsRecibidos)
                    ->delete();
            }

            // =========================
            // VALIDAR TRANSACCIÓN
            // =========================
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Tarifa actualizada correctamente');
        } catch (\Throwable $th) {

            if ($db) {
                $db->transRollback();
            }

            log_message('error', 'ERROR EDITAR TARIFA: ' . $th->getMessage());

            return $this->respondError($th->getMessage());
        }
    }
}
