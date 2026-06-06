<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

Database::getInstance();

$app = AppFactory::create();

$app->add(CorsMiddleware::class);
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$registerRoutes = require __DIR__ . '/../app/Routes/routes.php';
$registerRoutes($app);

$app->run();
