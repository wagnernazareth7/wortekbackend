<?php

namespace Wortek\Store\Config;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? '';
        $username = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        if ($database === '') {
            throw new RuntimeException('DB_NAME não está configurado.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            self::$connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return self::$connection;
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Não foi possível estabelecer ligação com a base de dados.',
                0,
                $e
            );
        }
    }
}
