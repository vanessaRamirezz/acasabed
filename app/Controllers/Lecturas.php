<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;
use PHPUnit\Event\Telemetry\Info;

class Lecturas extends BaseController
{
    private $lecturasModel;
    private $periodosModel;
    private $contratosModel;

    public function __construct()
    {
        $this->lecturasModel = new LecturaModel();
        $this->periodosModel = new PeriodoModel();
        $this->contratosModel = new ContratoModel();
    }

    public function index()
    {
        return view('lecturas/index');
    }

    public function getLecturas()
    {
        try {
            $start = (int)$this->request->getGet('start');
            $length = (int)$this->request->getGet('length');
            $draw = (int)$this->request->getGet('draw');
            $searchValue = $this->request->getGet('searchValue') ?? '';

            $result = $this->lecturasModel->getTodasLecturas($start, $length, $searchValue);

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

    public function getPeriodosSelect()
    {
        try {
            $search = $this->request->getVar('q') ?? '';
            $periodos = $this->periodosModel->buscarPeriodos($search);
            return $this->respondSuccess($periodos);
        } catch (\Throwable $th) {
            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al traer los periodos');
        }
    }

    public function nuevaLectura()
    {
        try {
            log_message('debug', 'POST en nueva lectura: ' . print_r($this->request->getPost(), true));
            // exit;
            $idPeriodo = $this->request->getPost('periodo');
            $idPeriodo = !empty($idPeriodo) ? $idPeriodo : null;

            $idContrato = $this->request->getPost('contrato');
            $idContrato = !empty($idContrato) ? $idContrato : null;

            $fecha = $this->request->getPost('fecha');
            $fecha = !empty($fecha) ? $fecha : null;

            $valor = $this->request->getPost('valor');
            $valor = !empty($valor) ? $valor : null;

            $idInstalador = $this->request->getPost('instalador');
            $idInstalador = !empty($idInstalador) ? $idInstalador : null;

            $idUsuario = $_SESSION['id_usuario'];
            $fechaCreacion = date('Y-m-d H:i:s');

            if (!$idPeriodo) {
                log_message('error', 'El periodo es requerido');
                return $this->respondError('El periodo es requerido');
            }


            // INICIAR TRANSACCIÓN
            $db = $this->lecturasModel->db;
            $db->transBegin();

            $resultado = $this->lecturasModel->insertarNuevaLectura(
                $idPeriodo,
                $idContrato,
                $fecha,
                $valor,
                $idInstalador,
                $idUsuario,
                $fechaCreacion
            );

            if (!$resultado) {

                $db->transRollback();
                log_message('error', 'Error en transacción guardar nueva lectura');
                return $this->respondError('No se pudieron guardar los datos de la nueva lectura');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Lectura registrada correctamente');
            return $this->respondOk('Lectura registrada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al guardar la nueva lectura');
        }
    }

    public function editarLectura()
    {
        try {
            log_message('debug', 'POST en editar lectura: ' . print_r($this->request->getPost(), true));
            // exit;

            $idLectura = $this->request->getPost('idLectura');

            $idPeriodo = $this->request->getPost('periodo');
            if (
                $idPeriodo === '' ||
                $idPeriodo === 'null' ||
                $idPeriodo === '-1' ||
                $idPeriodo === null
            ) {
                $idPeriodo = null;
            }

            $idContrato = $this->request->getPost('contrato');
            if (
                $idContrato === '' ||
                $idContrato === 'null' ||
                $idContrato === '-1' ||
                $idContrato === null
            ) {
                $idContrato = null;
            }

            $fecha = $this->request->getPost('fecha');
            $fecha = !empty($fecha) ? $fecha : null;

            $valor = $this->request->getPost('valor');
            $valor = !empty($valor) ? $valor : null;

            $idInstalador = $this->request->getPost('instalador');
            if (
                $idInstalador === '' ||
                $idInstalador === 'null' ||
                $idInstalador === '-1' ||
                $idInstalador === null
            ) {
                $idInstalador = null;
            }

            if (!$idPeriodo) {
                log_message('error', 'El periodo es requerido');
                return $this->respondError('El periodo es requerido');
            }

            // INICIAR TRANSACCIÓN
            $db = $this->lecturasModel->db;
            $db->transBegin();

            $resultado = $this->lecturasModel->actualizarLectura(
                $idPeriodo,
                $idContrato,
                $fecha,
                $valor,
                $idInstalador,
                $idLectura
            );

            if (!$resultado) {

                $db->transRollback();
                log_message('error', 'Error en transacción editar lectura');
                return $this->respondError('No se pudieron actualizar los datos de la lectura');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->respondError('Error en la transacción');
            }

            $db->transCommit();

            log_message('info', 'Lectura actualizada correctamente');
            return $this->respondOk('Lectura actualizada correctamente.');
        } catch (\Throwable $th) {
            if (isset($db)) {
                $db->transRollback();
            }

            $errorMessage = 'Ocurrió un error: ' . $th->getMessage() . PHP_EOL;
            $errorMessage .= 'Trace: ' . $th->getTraceAsString();
            log_message('error', $errorMessage);

            return $this->respondError('Error al actualizada la lectura');
        }
    }

    // funciones de la nueva forma de insercion masiva de lecturas
    public function getContratosPeriodos()
    {
        try {
            $idPeriodo = $this->request->getVar('periodo');

            if (!$idPeriodo) {
                return $this->respondError('Debe seleccionar periodo');
            }

            $data = $this->contratosModel->getContratosActivosLectura($idPeriodo);

            return $this->respondSuccess($data);
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->respondError('Error al cargar contratos');
        }
    }

    public function guardarLecturasMasivas()
    {
        try {

            $lecturasJson = $this->request->getPost('lecturas');

            log_message(
                'info',
                "DATA LECTURAS MASIVAS:\n" . json_encode(
                    json_decode($lecturasJson, true),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                )
            );
            // exit;
            if (!$lecturasJson) {
                throw new \Exception('No se recibieron lecturas');
            }

            $lecturas = json_decode($lecturasJson, true);

            if (!is_array($lecturas) || empty($lecturas)) {
                throw new \Exception('Formato inválido de lecturas');
            }

            $db = \Config\Database::connect();

            $db->transStart();

            foreach ($lecturas as $l) {

                // 🔒 validación mínima por item
                // if (
                //     empty($l['idContrato']) ||
                //     empty($l['periodo']) ||
                //     empty($l['instalador']) ||
                //     !isset($l['valor'])
                // ) {
                //     throw new \Exception('Datos incompletos en una lectura');
                // }

                $valor = (float) $l['valor'];

                if ($valor <= 0) {
                    continue; // o throw si quieres estricto
                }

                // 🔥 opcional: evitar duplicados por seguridad
                $existe = $db->table('lecturas')
                    ->where('id_contrato', $l['idContrato'])
                    ->where('id_periodo', $l['periodo'])
                    ->countAllResults();

                if ($existe > 0) {
                    continue;
                }

                $db->table('lecturas')->insert([
                    'id_contrato'   => $l['idContrato'],
                    'id_periodo'    => $l['periodo'],
                    'fecha'         => $l['fecha'],
                    'valor'         => $valor,
                    'id_instalador' => $l['instalador'],
                    'id_usuario'    => session()->get('id_usuario') ?? null,
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar las lecturas');
            }

            return $this->respondSuccess([
                'mensaje' => 'Lecturas registradas correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());
        }
    }
}
