<?php

/* 1er Sprint (12/06)

❖ Dar de alta y listar usuarios(mozo, bartender...)
❖ Dar de alta y listar productos(bebidas y comidas)
❖ Dar de alta y listar mesas
❖ Dar de alta y listar pedidos

2do Sprint (19/06)

❖ Usar MW de usuarios/perfiles
❖ Verificar usuarios para las tareas de abm
❖ Manejo del estado del pedido

3er Sprint (26/06)

❖ Carga de datos desde un archivo .CSV
❖ Descarga de archivos .CSV

4to Sprint (03/07)

❖ Hacer todo el circuito de un pedido.
❖ Manejo del estado del pedido + estadísticas 30 días
❖ Descarga de archivos PDF
❖ Seguimiento de las acciones de los empleados.
❖ Manejo del estado de los empleado*/


// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr7Middlewares\Middleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require_once  './middlewares/CheckMozoMiddleware.php';
require_once  './middlewares/CheckTokenMiddleware.php';
require_once  './middlewares/CheckSocioMiddleware.php';

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/AutenticadorController.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ComandaController.php';
require_once './controllers/ProductoPedidoController.php';
require_once './controllers/EncuestaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->post('/cargarUsuario', \EmpleadoController::class . ':CargarUno')->add(new CheckSocioMiddleware());;
    $group->get('/traerUsuarios', \EmpleadoController::class . ':TraerTodos');
    $group->post('/cargarPlato', \ProductoController::class . ':CargarUno')->add(new CheckSocioMiddleware());
    $group->get('/traerProductos', \ProductoController::class . ':TraerTodos');
    $group->post('/altaDeMesa', \MesaController::class . ':CargarUno')->add(new CheckMozoMiddleware());
    $group->post('/altaPedido', \ComandaController::class . ':CargarUno')->add(new CheckMozoMiddleware());
    $group->get('/traerPedidos', \ComandaController::class . ':TraerTodos');
    $group->post('/altaPendiente', \ProductoPedidoController::class . ':CargarUno')->add(new CheckMozoMiddleware());
    $group->get('/traerPendientes', \ProductoPedidoController::class . ':TraerPendientesPersonales');
    $group->get('/traerPendientesSector', \ProductoPedidoController::class . ':TraerPendientesSector');
    $group->post('/asignarPendientes', \ProductoPedidoController::class . ':AsignarPendientesEmpleado');
    $group->get('/traerTodosPendientes', \ProductoPedidoController::class . ':TraerTodosPendientes');
    $group->get('/traerPendientesSocio', \ComandaController::class . ':TraerComandasTiempo')->add(new CheckSocioMiddleware());
    $group->post('/completarPedido', \ProductoPedidoController::class . ':CompletarPedido');
    $group->get('/traerComandasListas', \ProductoPedidoController::class . ':TraerPendientesPersonales');
    $group->get('/traerPendienteMozo', \ComandaController::class . ':TraerTodasTerminadas')->add(new CheckMozoMiddleware());
    $group->delete('/cerrarComanda', \ComandaController::class . ':BorrarUno')->add(new CheckMozoMiddleware());
    $group->post('/cerrarCuenta', \MesaController::class . ':CerrarCuenta')->add(new CheckMozoMiddleware());
    $group->post('/cerrarMesa', \MesaController::class . ':CerrarMesa')->add(new CheckSocioMiddleware());
    $group->delete('/borrarEmpleado', \EmpleadoController::class . ':BorrarUno')->add(new CheckSocioMiddleware());
    $group->post('/exportarCSV', \ProductoController::class . ':ExportarTabla')->add(new CheckSocioMiddleware());
    $group->post('/cargarCSV', \ProductoController::class . ':ImportarTabla')->add(new CheckSocioMiddleware());
  })->add(new CheckTokenMiddleware());

//Genero el token
$app->post('/login', \AutentificadorController::class . ':CrearTokenLogin');
$app->get('/esperaMesa', \MesaController::class . ':TraerEsperaMesa');
$app->post('/encuesta', \EncuestaController::class . ':CargarUno');


$app->get('[/]', function (Request $request, Response $response) {    
    $response->getBody()->write("La Comanda - TP Programacion III ");
    return $response;
});

$app->run();
