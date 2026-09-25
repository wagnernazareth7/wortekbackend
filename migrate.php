<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Wortek\Store\Config\Database;
use Wortek\Store\Database\MigrationRunner;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = Database::getConnection();

    $runner = new MigrationRunner(
        $pdo,
        __DIR__ . '/database/migrations'
    );

    $runner->run();

    echo "Migrations conclu?das com sucesso." . PHP_EOL;
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ERRO: {$e->getMessage()}" . PHP_EOL
    );

    exit(1);
}
