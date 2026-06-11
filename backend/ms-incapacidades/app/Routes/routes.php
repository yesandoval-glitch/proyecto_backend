<?php
use App\Controllers\IncapacidadController;
use App\Middleware\AuthMiddleware;

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

$inc = new IncapacidadController();

$app->get('/incapacidades',               [$inc, 'listar'])->add(new AuthMiddleware());
$app->get('/incapacidades/{id}',          [$inc, 'obtener'])->add(new AuthMiddleware());
$app->post('/incapacidades',              [$inc, 'crear'])->add(new AuthMiddleware());
$app->put('/incapacidades/{id}',          [$inc, 'actualizar'])->add(new AuthMiddleware());
$app->patch('/incapacidades/{id}/estado', [$inc, 'cambiarEstado'])->add(new AuthMiddleware());
