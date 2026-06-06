<?php

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {

    $app->post('/login', [AuthController::class, 'login']);

    $app->post('/logout', [AuthController::class, 'logout'])
        ->add(AuthMiddleware::class);

    $app->get('/validate', [AuthController::class, 'validarToken'])
        ->add(AuthMiddleware::class);
};
