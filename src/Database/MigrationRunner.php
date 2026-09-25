<?php

namespace Wortek\Store\Database;

use PDO;
use RuntimeException;
use Throwable;

class MigrationRunner
{
    public function __construct(
        private PDO $pdo,
        private string $migrationsPath
    ) {
    }

    public function run(): void
    {
        if (!is_dir($this->migrationsPath)) {
            throw new RuntimeException('Pasta de migrations n?o encontrada.');
        }

        $files = glob($this->migrationsPath . '/*.sql');

        if ($files === false) {
            throw new RuntimeException('N?o foi poss?vel carregar as migrations.');
        }

        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $migration = basename($file);

            if ($migration === '000_create_migrations_table.sql') {
                $this->executeMigrationTable($file);
                continue;
            }

            if ($this->hasRun($migration)) {
                echo "[IGNORADA] {$migration}" . PHP_EOL;
                continue;
            }

            $this->execute($file, $migration);
        }
    }

    private function executeMigrationTable(string $file): void
    {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException('N?o foi poss?vel ler a migration inicial.');
        }

        $this->pdo->exec($sql);
    }

    private function hasRun(string $migration): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM migrations WHERE migration = :migration'
        );

        $stmt->execute([
            'migration' => $migration
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function execute(string $file, string $migration): void
    {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException(
                "N?o foi poss?vel ler {$migration}."
            );
        }

        try {
            /*
             * MariaDB/MySQL executa COMMIT impl?cito em v?rias instru??es DDL,
             * como CREATE TABLE e ALTER TABLE.
             *
             * Por isso, migrations estruturais n?o s?o envolvidas aqui
             * numa transac??o PDO artificial.
             */
            $this->pdo->exec($sql);

            $stmt = $this->pdo->prepare(
                'INSERT INTO migrations (migration) VALUES (:migration)'
            );

            $stmt->execute([
                'migration' => $migration
            ]);

            echo "[EXECUTADA] {$migration}" . PHP_EOL;
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Erro na migration {$migration}: {$e->getMessage()}",
                0,
                $e
            );
        }
    }
}
