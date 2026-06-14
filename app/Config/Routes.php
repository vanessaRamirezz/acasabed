<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// Rutas para el login
$routes->get('/', 'Login::index');
$routes->post('validar', 'Login::validarUsuario');
$routes->get('salir', 'Login::salir');


$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('inicio', 'Inicio::index');

    // PERMISOS
    $routes->get('permisos', 'Permisos::index');
    $routes->get('getPerfiles', 'Permisos::getPerfiles');
    $routes->get('getAccesos', 'Permisos::getAccesos');
    $routes->post('editarPermisos', 'Permisos::editarPermisos');

    // USUARIOS
    $routes->get('usuarios', 'Usuarios::index');
    $routes->get('getUsuarios', 'Usuarios::getUsuarios');
    $routes->post('nuevoUsuario', 'Usuarios::nuevoUsuario');
    $routes->post('editarUsuario', 'Usuarios::editarUsuario');
    $routes->post('actualizarEstadoUsuario', 'Usuarios::actualizarEstadoUsuario');

    //DEPARTAMENTOS, MUNICIPIOS, COLONIAS
    $routes->get('getDepartamentos', 'Departamentos::index');
    $routes->post('getMunicipios', 'Municipios::index');
    $routes->post('getDistritos', 'Distritos::index');
    $routes->post('getColonias', 'Colonias::getColonias');

    // COLONIAS
    $routes->get('colonias', 'Colonias::index');
    $routes->post('guardarColonia', 'Colonias::guardarColonia');

    // TIPOS DE CLIENTES
    $routes->get('tipos_cliente', 'TiposCliente::index');
    $routes->get('getTipoCliente', 'TiposCliente::getTipoCliente');
    $routes->post('nuevoTipoCliente', 'TiposCliente::nuevoTipoCliente');
    $routes->post('editarTipoCliente', 'TiposCliente::editarTipoCliente');

    // SERVICIOS
    $routes->get('servicios', 'Servicios::index');
    $routes->get('getServicios', 'Servicios::getServicios');
    $routes->get('getOperacionesServicio', 'Servicios::getOperacionesServicio');
    $routes->get('getTipoServicio', 'Servicios::getTipoServicio');
    $routes->post('nuevoServicio', 'Servicios::nuevoServicio');
    $routes->post('editarServicio', 'Servicios::editarServicio');

    // RUTAS
    $routes->get('rutas', 'Rutas::index');
    $routes->get('getRutas', 'Rutas::getRutas');
    $routes->post('nuevaRuta', 'Rutas::nuevaRuta');
    $routes->post('editarRuta', 'Rutas::editarRuta');
    $routes->get('selectRuta', 'Rutas::selectRuta');

    // ORDEN DE CONTRATOS POR RUTA
    $routes->get('orden_rutas', 'OrdenRutas::index');
    $routes->get('getContratosOrdenRuta', 'OrdenRutas::getContratosPorRuta');
    $routes->post('guardarOrdenContratosRuta', 'OrdenRutas::guardarOrdenContratos');

    // INSTALADORES
    $routes->get('instaladores', 'Instaladores::index');
    $routes->get('getInstaladores', 'Instaladores::getInstaladores');
    $routes->post('nuevoInstalador', 'Instaladores::nuevoInstalador');
    $routes->post('editarInstalador', 'Instaladores::editarInstalador');
    $routes->post('actualizarEstadoInstalador', 'Instaladores::actualizarEstadoInstalador');

    // TARIFARIO
    $routes->get('tarifario', 'Tarifario::index');
    $routes->get('getTarifas', 'Tarifario::getTarifas');
    $routes->post('nuevaTarifa', 'Tarifario::nuevaTarifa');
    $routes->post('editarTarifa', 'Tarifario::editarTarifa');
    $routes->get('getTarifaDetalle/(:num)', 'Tarifario::getTarifaDetalle/$1');

    // PERIODOS
    $routes->get('periodos', 'Periodos::index');
    $routes->get('getPeriodos', 'Periodos::getPeriodos');
    $routes->post('nuevoPeriodo', 'Periodos::nuevoPeriodo');
    $routes->post('editarPeriodo', 'Periodos::editarPeriodo');

    // CLIENTES
    $routes->get('clientes', 'Clientes::index');
    $routes->get('getActividades', 'Clientes::getActividades');
    $routes->get('getClientes', 'Clientes::getClientes');
    $routes->post('nuevoCliente', 'Clientes::nuevoCliente');
    $routes->post('editarCliente', 'Clientes::editarCliente');

    // MEDIDORES
    $routes->get('medidores', 'Medidores::index');
    $routes->get('getMedidores', 'Medidores::getMedidores');
    $routes->get('getContratos', 'Medidores::getContratos');
    $routes->get('getSelectInstaladores', 'Medidores::getSelectInstaladores');
    $routes->post('nuevoMedidor', 'Medidores::nuevoMedidor');
    $routes->post('editarMedidor', 'Medidores::editarMedidor');

    // LECTURAS
    $routes->get('lecturas', 'Lecturas::index');
    $routes->get('getLecturas', 'Lecturas::getLecturas');
    $routes->get('getPeriodosSelect', 'Lecturas::getPeriodosSelect');
    $routes->post('nuevaLectura', 'Lecturas::nuevaLectura');
    $routes->post('editarLectura', 'Lecturas::editarLectura');
    $routes->get('getContratosPeriodos', 'Lecturas::getContratosPeriodos');
    $routes->post('guardarLecturasMasivas', 'Lecturas::guardarLecturasMasivas');
    $routes->get('reporte-toma-lecturas/pdf', 'Lecturas::generarPDFTomaLecturas');

    // SOLICITUDES
    $routes->get('solicitudes', 'Solicitudes::index');
    $routes->get('nueva_solicitud', 'Solicitudes::formulario_solicitud');
    $routes->get('getClientesSelect', 'Solicitudes::getClientesSelect');
    $routes->post('nuevaSolicitud', 'Solicitudes::nuevaSolicitud');
    $routes->post('aprobarSolicitud', 'Solicitudes::aprobarSolicitud');
    $routes->post('anularSolicitud', 'Solicitudes::anularSolicitud');
    $routes->get('getRutasSelect', 'Solicitudes::getRutasSelect');
    $routes->get('getFirmantesSelect', 'Solicitudes::getFirmantesSelect');
    $routes->get('getMedidoresSelect', 'Solicitudes::getMedidoresSelect');
    $routes->get('getTarifasSelect', 'Solicitudes::getTarifasSelect');
    $routes->post('getBeneficiariosId', 'Solicitudes::getBeneficiariosId');
    $routes->get('getSolicitudesTabla', 'Solicitudes::getSolicitudesTabla');
    $routes->get('getSolicitudById', 'Solicitudes::getSolicitudById');
    $routes->get('getSolicitudesTablaAprobadas', 'Solicitudes::getSolicitudesTablaAprobadas');
    $routes->post('editarSolicitudAprobada', 'Solicitudes::editarSolicitudAprobada');


    // CONTRATOS
    $routes->get('contratos', 'Contratos::index');
    $routes->post('contratos/pdf', 'Contratos::pdf'); // para ver desde el formulario
    $routes->get('contratos/contrato', 'Contratos::contrato');
    $routes->get('getContratosTabla', 'Contratos::getContratosTabla');
    $routes->post('suspenderContratoUnoaUno', 'Contratos::suspenderContratoUnoaUno');

    // COBROS DE INSTALACION
    $routes->get('cobros_instalacion', 'CobrosInstalacion::index');
    $routes->get('getCobrosRealizados', 'CobrosInstalacion::getCobrosRealizados');
    $routes->get('getDetalleCobroCliente', 'CobrosInstalacion::getDetalleCobroCliente');
    $routes->post('generarFacturasCobros', 'CobrosInstalacion::generarFacturasCobros');
    $routes->get('imprimirFacturasCobroPeriodoActivo', 'CobrosInstalacion::imprimirFacturasCobroPeriodoActivo');
    $routes->get('facturaCobroInstalacion/(:num)', 'CobrosInstalacion::facturaCobroInstalacion/$1');
    // $routes->post('validarCobroInstalacion', 'CobrosInstalacion::validarCobroInstalacion');
    // $routes->post('registrarPagoInstalacion', 'CobrosInstalacion::registrarPagoInstalacion');

    // FACTURACION DEL SERVICIO
    $routes->get('facturacion_servicio', 'FacturacionServicio::index');
    $routes->get('getFacturasServicio', 'FacturacionServicio::getFacturasServicio');
    $routes->get('getContratosFacturacionOtro', 'FacturacionServicio::getContratosFacturacionOtro');
    $routes->get('getServiciosFacturacionOtro', 'FacturacionServicio::getServiciosFacturacionOtro');
    $routes->get('facturaCobroServicio/(:num)', 'FacturacionServicio::facturaCobroServicio/$1');
    $routes->post('generarFacturasServicio', 'FacturacionServicio::generarFacturasServicio');
    $routes->post('crearFacturaOtro', 'FacturacionServicio::crearFacturaOtro');
    $routes->get('imprimirFacturasConsumoPeriodoActivo', 'FacturacionServicio::imprimirFacturasConsumoPeriodoActivo');


    // RANGO DE FACTURAS
    $routes->get('rango_de_facturas', 'RangoFacturas::index');
    $routes->post('guardarRango', 'RangoFacturas::guardarRango');
    $routes->get('getRangoFacturas', 'RangoFacturas::getRangoFacturas');

    //REPORTES
    //REPORTE DE CONTRATOS
    $routes->get('reporte_contratos', 'ReporteContratos::index');
    $routes->get('reporte-contratos/pdf', 'ReporteContratos::generarPDF');
    //REPORTE DE RUTAS
    $routes->get('reporte_rutas', 'ReporteRutas::index');
    $routes->get('reporte-rutas/pdf', 'ReporteRutas::reporteRutas');
    //REPORTE DE FACTURAS
    $routes->get('reporte_facturas', 'ReporteFacturas::index');
    $routes->get('getPeriodosReporteSelect', 'ReporteFacturas::getPeriodosReporteSelect');
    $routes->get('reporte-facturas/pdf', 'ReporteFacturas::generarPDF');
    $routes->get('reporte_resumen_facturas', 'ReporteResumenFacturas::index');
    $routes->get('getPeriodosResumenFacturasSelect', 'ReporteResumenFacturas::getPeriodosSelect');
    $routes->get('reporte-resumen-facturas/pdf', 'ReporteResumenFacturas::generarPDF');
    //REPORTE DE CLIENTES
    $routes->get('reporte_clientes', 'ReporteClientes::index');
    $routes->get('reporte-clientes/pdf', 'ReporteClientes::generarPDF');
    //REPORTE DE LECTURAS
    $routes->get('reporte_lecturas', 'ReporteLecturas::index');
    $routes->get('reporte-lecturas/pdf', 'ReporteLecturas::generarPDF');
    //REPORTE MEDIDORES
    $routes->get('reporte_medidores', 'ReporteMedidores::index');
    $routes->get('reporte-medidores/pdf', 'ReporteMedidores::generarPDF');

    // PLANTILLAS
    $routes->get('cargar_generar_plantillas', 'CargarGenerarPlantillas::index');
    $routes->get('facturas/exportar-excel', 'CargarGenerarPlantillas::exportarExcel');
    $routes->get('facturas/exportar-excel-alcaldia', 'CargarGenerarPlantillas::exportarExcelAlcaldia');
    $routes->post('facturas/importar-excel', 'CargarGenerarPlantillas::importarExcel');
    $routes->post('facturas/cancelar-importacion-excel', 'CargarGenerarPlantillas::cancelarImportacionExcelPeriodoActivo');
    $routes->post('cargarExcelAlcaldia', 'CargarGenerarPlantillas::cargarExcelAlcaldia');
    $routes->post('cancelarExcelAlcaldia', 'CargarGenerarPlantillas::cancelarExcelAlcaldia');
    $routes->get('validarExcelCargado', 'CargarGenerarPlantillas::validarExcelCargado');
});
