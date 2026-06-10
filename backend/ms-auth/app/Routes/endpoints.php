<?php
use App\Controllers\AuthController;

// CORS
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$auth = new AuthController();

$app->post('/auth/login',  [$auth, 'login']);
$app->post('/auth/logout', [$auth, 'logout']);
$app->get('/auth/validar', [$auth, 'validar']);
