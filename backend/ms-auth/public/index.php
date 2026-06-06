<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

// Inicializa la base de datos (Singleton)
Database::getInstance();

// Crea la aplicacion Slim
$app = AppFactory::create();

// Middleware global de CORS
$app->add(CorsMiddleware::class);

// Parseo de body JSON y form
$app->addBodyParsingMiddleware();

// Manejo de errores
$app->addErrorMiddleware(true, true, true);

// Registra las rutas
$registerRoutes = require __DIR__ . '/../app/Routes/routes.php';
$registerRoutes($app);

$app->run();