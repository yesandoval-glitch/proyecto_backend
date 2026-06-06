<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

class Database
{
    private static ?Database $instance = null;
    private Capsule $capsule;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $this->capsule = new Capsule();

        $this->capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port'      => $_ENV['DB_PORT'] ?? '3306',
            'database'  => $_ENV['DB_DATABASE'] ?? 'ms_auth',
            'username'  => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}
