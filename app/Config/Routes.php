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

    //DEPARTAMENTOS, MUNICIPIOS, DIRECCIONES
    $routes->get('getDepartamentos', 'Departamentos::index');
    $routes->post('getMunicipios', 'Municipios::index');
    $routes->post('getDistritos', 'Distritos::index');
    $routes->post('getDirecciones', 'Direcciones::getDirecciones');

    // DIRECCION
    $routes->get('direcciones', 'Direcciones::index');
    $routes->post('guardarDireccion', 'Direcciones::guardarDireccion');

    // TIPOS DE CLIENTES
    $routes->get('tipos_cliente', 'TiposCliente::index');
    $routes->get('getTipoCliente', 'TiposCliente::getTipoCliente');
    $routes->post('nuevoTipoCliente', 'TiposCliente::nuevoTipoCliente');
    $routes->post('editarTipoCliente', 'TiposCliente::editarTipoCliente');

    // RUTAS
    $routes->get('rutas', 'Rutas::index');
    $routes->get('getRutas', 'Rutas::getRutas');
    $routes->post('nuevaRuta', 'Rutas::nuevaRuta');
    $routes->post('editarRuta', 'Rutas::editarRuta');

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
});
