<?php

namespace Wortek\Store\Database;

use PDO;
use RuntimeException;
use Throwable;

class SeedRunner
{
    public function __construct(
        private PDO $pdo,
        private string $seedsPath
    ) {
    }

    public function run(): void
    {
        if (!is_dir($this->seedsPath)) {
            throw new RuntimeException('Pasta de seeds não encontrada.');
        }

        $files = glob($this->seedsPath . '/*.sql');

        if ($files === false) {
            throw new RuntimeException('Não foi possível carregar os seeds.');
        }

        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $this->execute($file);
        }
    }

    private function execute(string $file): void
    {
        $seed = basename($file);

        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException(
                "Não foi possível ler o seed {$seed}."
            );
        }

        try {
            $this->pdo->exec($sql);

            echo "[EXECUTADO] {$seed}" . PHP_EOL;
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Erro no seed {$seed}: {$e->getMessage()}",
                0,
                $e
            );
        }
    }
}