<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\RutaModel;

class OrdenRutas extends BaseController
{
    private ContratoModel $contratosModel;
    private RutaModel $rutasModel;

    public function __construct()
    {
        $this->contratosModel = new ContratoModel();
        $this->rutasModel = new RutaModel();
    }

    public function index()
    {
        return view('orden_rutas/index');
    }

    public function getContratosPorRuta()
    {
        try {
            $idRuta = $this->request->getGet('idRuta');

            if (empty($idRuta) || $idRuta === '-1') {
                return $this->respondError('Debe seleccionar una ruta');
            }

            $ruta = $this->rutasModel->find($idRuta);
            if (!$ruta) {
                return $this->respondError('La ruta seleccionada no existe');
            }

            return $this->respondSuccess($this->contratosModel->getContratosPorRutaOrden($idRuta));
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al cargar contratos de la ruta');
        }
    }

    public function guardarOrdenContratos()
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $idRuta = $this->request->getPost('idRuta');
            $ordenesJson = $this->request->getPost('ordenes');

            if (empty($idRuta) || $idRuta === '-1') {
                throw new \Exception('Debe seleccionar una ruta');
            }

            $ordenes = json_decode((string)$ordenesJson, true);
            if (!is_array($ordenes) || empty($ordenes)) {
                throw new \Exception('No hay contratos para ordenar');
            }

            $contratosActuales = $this->contratosModel->getContratosPorRutaOrden($idRuta);
            $contratosPorId = [];
            foreach ($contratosActuales as $contrato) {
                $contratosPorId[(int)$contrato['id_contrato']] = $contrato;
            }

            $movidos = [];
            $sinCambios = [];

            foreach ($ordenes as $index => $item) {
                $idContrato = (int)($item['idContrato'] ?? 0);
                $ordenNuevo = (int)($item['orden'] ?? 0);
                $ordenOriginal = (int)($item['ordenOriginal'] ?? 0);

                if ($idContrato <= 0 || !isset($contratosPorId[$idContrato])) {
                    throw new \Exception('Hay contratos que no pertenecen a la ruta seleccionada');
                }

                if ($ordenNuevo <= 0) {
                    throw new \Exception('Todos los contratos deben tener un orden mayor a cero');
                }

                $itemNormalizado = [
                    'id_contrato' => $idContrato,
                    'orden_nuevo' => $ordenNuevo,
                    'orden_original' => $ordenOriginal,
                    'posicion' => $index,
                ];

                if ($ordenNuevo !== $ordenOriginal || $ordenOriginal <= 0) {
                    $movidos[] = $itemNormalizado;
                } else {
                    $sinCambios[] = $itemNormalizado;
                }
            }

            usort($sinCambios, function ($a, $b) {
                return [$a['orden_original'], $a['posicion']] <=> [$b['orden_original'], $b['posicion']];
            });

            usort($movidos, function ($a, $b) {
                return [$a['orden_nuevo'], $a['posicion']] <=> [$b['orden_nuevo'], $b['posicion']];
            });

            $ordenFinal = array_values($sinCambios);

            foreach ($movidos as $movido) {
                $posicion = max(0, min($movido['orden_nuevo'] - 1, count($ordenFinal)));
                array_splice($ordenFinal, $posicion, 0, [$movido]);
            }

            foreach ($ordenFinal as $index => $contrato) {
                $orden = $index + 1;
                $actualizado = $this->contratosModel->actualizarOrdenRutaContrato(
                    $contrato['id_contrato'],
                    $orden
                );

                if (!$actualizado) {
                    throw new \Exception('No se pudo actualizar el orden de los contratos');
                }
            }

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            $db->transCommit();

            return $this->respondOk('Orden de contratos actualizado correctamente');
        } catch (\Throwable $th) {
            $db->transRollback();
            log_message('error', $th->getMessage());
            return $this->respondError($th->getMessage());
        }
    }
}
