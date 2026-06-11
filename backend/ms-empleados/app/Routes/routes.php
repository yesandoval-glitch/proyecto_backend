<?php
use App\Controllers\EmpleadoController;
use App\Middleware\AuthMiddleware;

// CORS
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

$emp = new EmpleadoController();

$app->get('/empleados',              [$emp, 'listar'])->add(new AuthMiddleware());
$app->get('/empleados/{id}',         [$emp, 'obtener'])->add(new AuthMiddleware());
$app->post('/empleados',             [$emp, 'crear'])->add(new AuthMiddleware());
$app->put('/empleados/{id}',         [$emp, 'actualizar'])->add(new AuthMiddleware());
$app->patch('/empleados/{id}/estado',[$emp, 'cambiarEstado'])->add(new AuthMiddleware());
