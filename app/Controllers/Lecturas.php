<?php

namespace App\Controllers;

use App\Models\ContratoModel;
use App\Models\LecturaModel;
use App\Models\PeriodoModel;
use PHPUnit\Event\Telemetry\Info;

class ReporteTomaLecturasPDF extends \TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(
            0,
            10,
            'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(),
            0,
            0,
            'C'
        );
    }
}

class Lecturas extends BaseController
{
    private LecturaModel $lecturasModel;
    private PeriodoModel $periodosModel;
    private ContratoModel $contratosModel;

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
            $periodos = $this->periodosModel->buscarPeriodos();
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
            $idRuta = $this->request->getVar('ruta');
            $idDepartamento = $this->request->getVar('departamento');
            $idMunicipio = $this->request->getVar('municipio');
            $idDistrito = $this->request->getVar('distrito');
            $idColonia = $this->request->getVar('colonia');

            if (!$idPeriodo) {
                return $this->respondError('Debe seleccionar periodo');
            }

            $data = $this->contratosModel->getContratosActivosLectura(
                $idPeriodo,
                $idRuta,
                $idDepartamento,
                $idMunicipio,
                $idDistrito,
                $idColonia
            );

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
            $post = $this->request->getPost();
            $lecturas = json_decode($post['lecturas'], true);

            log_message('debug', 'POST completo: ' . json_encode($post, JSON_PRETTY_PRINT));
            log_message('debug', 'LECTURAS decodificadas: ' . json_encode($lecturas, JSON_PRETTY_PRINT));
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

    public function generarPDFTomaLecturas()
    {
        try {
            $idPeriodo = $this->request->getGet('periodo');
            $idRuta = $this->request->getGet('ruta');
            $idDepartamento = $this->request->getGet('departamento');
            $idMunicipio = $this->request->getGet('municipio');
            $idDistrito = $this->request->getGet('distrito');
            $idColonia = $this->request->getGet('colonia');

            if (!$idPeriodo) {
                return $this->response->setStatusCode(400)->setBody('Debe seleccionar un período');
            }

            $periodo = $this->periodosModel->find($idPeriodo);

            if (!$periodo) {
                return $this->response->setStatusCode(404)->setBody('El período seleccionado no existe');
            }

            $contratos = $this->contratosModel->getReporteTomaLecturas(
                $idPeriodo,
                $idRuta,
                $idDepartamento,
                $idMunicipio,
                $idDistrito,
                $idColonia
            );

            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf = new ReporteTomaLecturasPDF();
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            $logo = FCPATH . 'dist/img/agua.png';
            if (file_exists($logo)) {
                $pdf->Image($logo, 10, 10, 22);
            }

            // =============================
            // FILTROS
            // =============================
            $filtrosAplicados = ['Período: ' . ($periodo['nombre'] ?? '-')];

            if (!empty($contratos)) {
                $primero = $contratos[0];

                if (!empty($idRuta) && $idRuta !== '-1' && !empty($primero['ruta'])) {
                    $filtrosAplicados[] = 'Ruta: ' . $primero['ruta'];
                }

                if (!empty($idDepartamento) && $idDepartamento !== '-1' && !empty($primero['departamento'])) {
                    $filtrosAplicados[] = 'Departamento: ' . $primero['departamento'];
                }

                if (!empty($idMunicipio) && $idMunicipio !== '-1' && !empty($primero['municipio'])) {
                    $filtrosAplicados[] = 'Municipio: ' . $primero['municipio'];
                }

                if (!empty($idDistrito) && $idDistrito !== '-1' && !empty($primero['distrito'])) {
                    $filtrosAplicados[] = 'Distrito: ' . $primero['distrito'];
                }

                if (!empty($idColonia) && $idColonia !== '-1' && !empty($primero['colonia'])) {
                    $filtrosAplicados[] = 'Colonia: ' . $primero['colonia'];
                }
            }

            // =============================
            // HTML (FORMATO LISTA)
            // =============================
            $html = '
        <style>
            .titulo {
                text-align: center;
                font-size: 16px;
                font-weight: bold;
            }
            .subtitulo {
                text-align: center;
                font-size: 10px;
                margin-bottom: 10px;
            }
            table {
                width: 100%;
                font-size: 9px;
            }
            th {
                background-color: #003366;
                color: #ffffff;
                padding: 6px;
                text-align: left;
            }
            td {
                padding: 8px 8px;
                vertical-align: bottom;
            }
            .fila {
                border-bottom: 1px solid #ccc;
            }
            .center {
                text-align: center;
            }
            .linea {
                text-align: center;
                vertical-align: middle;
                font-size: 14px;
            }
        </style>

        <div class="titulo">TOMA DE LECTURAS</div>
        <div class="subtitulo">' . esc(implode(' | ', $filtrosAplicados)) . '</div>
        <br>

        <table>
            <thead>
                <tr>
                    <th width="10%">No.</th>
                    <th width="30%">Cliente</th>
                    <th width="10%">Contrato</th>
                    <th width="20%">No. serie medidor</th>
                    <th width="10%">Lectura Anterior</th>
                    <th width="15%">Lectura Actual</th>
                </tr>
            </thead>
            <tbody>';

            // =============================
            // DATOS
            // =============================
            if (empty($contratos)) {
                $html .= '
                <tr>
                    <td colspan="6" class="center">
                        No hay contratos pendientes de lectura para los filtros seleccionados.
                    </td>
                </tr>';
            } else {
                $numero = 1;

                foreach ($contratos as $contrato) {
                    $lecturaAnterior = $contrato['lectura_anterior'];
                    $lecturaAnterior = ($lecturaAnterior !== null && $lecturaAnterior !== '')
                        ? number_format((float)$lecturaAnterior, 2)
                        : '-';

                    $html .= '
                    <tr class="fila">
                        <td width="10%">' . $numero++ . '</td>
                        <td width="30%">' . esc((string)($contrato['nombre_completo'] ?? '-')) . '</td>
                        <td width="10%">' . esc((string)($contrato['numero_contrato'] ?? '-')) . '</td>
                        <td width="20%">' . esc((string)($contrato['numero_serie'] ?? '-')) . '</td>
                        <td width="10%">' . esc($lecturaAnterior) . '</td>
                        <td width="15%" class="linea">_________</td>
                    </tr>';

                    // Espacio cada 25 filas (mejora impresión)
                    if ($numero % 25 == 0) {
                        $html .= '<tr><td colspan="6" style="height:10px;"></td></tr>';
                    }
                }
            }

            $html .= '
            </tbody>
        </table>';

            $pdf->Ln(12);
            $pdf->writeHTML($html, true, false, true, false, '');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="toma_de_lecturas.pdf"')
                ->setBody($pdf->Output('toma_de_lecturas.pdf', 'S'));
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return $this->response->setStatusCode(500)
                ->setBody('Ocurrió un error al generar el PDF de toma de lecturas');
        }
    }
}
