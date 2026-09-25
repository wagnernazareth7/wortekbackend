<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Wortek\Store\Config\Database;
use Wortek\Store\Database\SeedRunner;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = Database::getConnection();

    $runner = new SeedRunner(
        $pdo,
        __DIR__ . '/database/seeds'
    );

    $runner->run();

    echo "Seeds concluídos com sucesso." . PHP_EOL;
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ERRO: {$e->getMessage()}" . PHP_EOL
    );

    exit(1);
}