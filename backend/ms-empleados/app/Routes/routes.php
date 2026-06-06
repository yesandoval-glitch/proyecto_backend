<?php

use App\Controllers\EmpleadoController;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    // Todas las rutas de empleados requieren autenticacion
    $app->group('/empleados', function (RouteCollectorProxy $group) {

        $group->get('',             [EmpleadoController::class, 'index']);
        $group->get('/{id}',        [EmpleadoController::class, 'show']);
        $group->post('',            [EmpleadoController::class, 'store']);
        $group->put('/{id}',        [EmpleadoController::class, 'update']);
        $group->patch('/{id}/estado', [EmpleadoController::class, 'cambiarEstado']);

    })->add(AuthMiddleware::class);
};
