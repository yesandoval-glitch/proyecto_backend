<?php
use App\Controllers\SeguimientoController;
use App\Middleware\AuthMiddleware;

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});

$seg = new SeguimientoController();

$app->get('/seguimiento',                              [$seg, 'listar'])->add(new AuthMiddleware());
$app->get('/seguimiento/reporte',                      [$seg, 'reporte'])->add(new AuthMiddleware());
$app->get('/seguimiento/{id}',                         [$seg, 'obtener'])->add(new AuthMiddleware());
$app->get('/seguimiento/incapacidad/{incapacidad_id}', [$seg, 'porIncapacidad'])->add(new AuthMiddleware());
$app->post('/seguimiento',                             [$seg, 'registrar'])->add(new AuthMiddleware());
