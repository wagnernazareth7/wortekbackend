<?php

namespace Wortek\Store\Repositories;

use PDO;
use RuntimeException;
use Throwable;

class StockRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function all(?string $search = null): array
    {
        $sql = '
            SELECT
                s.id,
                s.produto_id,
                p.sku,
                p.nome AS produto_nome,
                p.unidade,
                p.stock_minimo,
                s.quantidade,
                s.quantidade_reservada,
                (s.quantidade - s.quantidade_reservada)
                    AS quantidade_disponivel,
                CASE
                    WHEN s.quantidade <= p.stock_minimo
                        THEN 1
                    ELSE 0
                END AS stock_baixo,
                s.updated_at
            FROM stock s
            INNER JOIN produtos p
                ON p.id = s.produto_id
        ';

        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= '
                WHERE (
                    p.nome LIKE :nome
                    OR p.sku LIKE :sku
                    OR p.marca LIKE :marca
                )
            ';

            $value = '%' . trim($search) . '%';

            $params = [
                'nome' => $value,
                'sku' => $value,
                'marca' => $value,
            ];
        }

        $sql .= ' ORDER BY p.nome ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findByProductId(int $produtoId): ?array
    {
        $statement = $this->pdo->prepare(
            '
            SELECT
                s.id,
                s.produto_id,
                p.sku,
                p.nome AS produto_nome,
                p.unidade,
                p.stock_minimo,
                s.quantidade,
                s.quantidade_reservada,
                (s.quantidade - s.quantidade_reservada)
                    AS quantidade_disponivel,
                CASE
                    WHEN s.quantidade <= p.stock_minimo
                        THEN 1
                    ELSE 0
                END AS stock_baixo,
                s.updated_at
            FROM stock s
            INNER JOIN produtos p
                ON p.id = s.produto_id
            WHERE s.produto_id = :produto_id
            LIMIT 1
            '
        );

        $statement->execute([
            'produto_id' => $produtoId,
        ]);

        $stock = $statement->fetch();

        return $stock ?: null;
    }

    public function movements(
        ?int $produtoId = null
    ): array {
        $sql = '
            SELECT
                m.id,
                m.produto_id,
                p.sku,
                p.nome AS produto_nome,
                m.tipo,
                m.quantidade,
                m.stock_anterior,
                m.stock_posterior,
                m.origem_tipo,
                m.origem_id,
                m.observacoes,
                m.created_by,
                u.nome AS criado_por,
                m.created_at
            FROM movimentacoes_stock m
            INNER JOIN produtos p
                ON p.id = m.produto_id
            LEFT JOIN utilizadores u
                ON u.id = m.created_by
        ';

        $params = [];

        if ($produtoId !== null) {
            $sql .= '
                WHERE m.produto_id = :produto_id
            ';

            $params['produto_id'] = $produtoId;
        }

        $sql .= ' ORDER BY m.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function move(
        int $produtoId,
        string $tipo,
        float $quantidade,
        ?string $observacoes,
        int $userId,
        ?string $origemTipo = null,
        ?int $origemId = null
    ): array {
        $tiposPermitidos = [
            'entrada',
            'saida',
            'ajuste_entrada',
            'ajuste_saida',
        ];

        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new RuntimeException(
                'Tipo de movimentação inválido.'
            );
        }

        if ($quantidade <= 0) {
            throw new RuntimeException(
                'A quantidade deve ser superior a zero.'
            );
        }

        try {
            $this->pdo->beginTransaction();

            /*
             * FOR UPDATE impede duas operações concorrentes
             * de alterarem o mesmo stock simultaneamente.
             */
            $statement = $this->pdo->prepare(
                '
                SELECT
                    s.id,
                    s.produto_id,
                    s.quantidade,
                    s.quantidade_reservada
                FROM stock s
                WHERE s.produto_id = :produto_id
                FOR UPDATE
                '
            );

            $statement->execute([
                'produto_id' => $produtoId,
            ]);

            $stock = $statement->fetch();

            if (!$stock) {
                throw new RuntimeException(
                    'Registo de stock não encontrado para o produto.'
                );
            }

            $stockAnterior = (float) $stock['quantidade'];
            $reservado = (float) $stock['quantidade_reservada'];

            $tiposEntrada = [
                'entrada',
                'ajuste_entrada',
            ];

            if (in_array($tipo, $tiposEntrada, true)) {
                $stockPosterior =
                    $stockAnterior + $quantidade;
            } else {
                $disponivel =
                    $stockAnterior - $reservado;

                if ($quantidade > $disponivel) {
                    throw new RuntimeException(
                        'Stock disponível insuficiente para esta operação.'
                    );
                }

                $stockPosterior =
                    $stockAnterior - $quantidade;
            }

            $update = $this->pdo->prepare(
                '
                UPDATE stock
                SET quantidade = :quantidade
                WHERE produto_id = :produto_id
                '
            );

            $update->execute([
                'quantidade' => $stockPosterior,
                'produto_id' => $produtoId,
            ]);

            $movement = $this->pdo->prepare(
                '
                INSERT INTO movimentacoes_stock (
                    produto_id,
                    tipo,
                    quantidade,
                    stock_anterior,
                    stock_posterior,
                    origem_tipo,
                    origem_id,
                    observacoes,
                    created_by
                )
                VALUES (
                    :produto_id,
                    :tipo,
                    :quantidade,
                    :stock_anterior,
                    :stock_posterior,
                    :origem_tipo,
                    :origem_id,
                    :observacoes,
                    :created_by
                )
                '
            );

            $movement->execute([
                'produto_id' => $produtoId,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockPosterior,
                'origem_tipo' => $origemTipo,
                'origem_id' => $origemId,
                'observacoes' => $observacoes,
                'created_by' => $userId,
            ]);

            $movementId =
                (int) $this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'movimentacao_id' => $movementId,
                'produto_id' => $produtoId,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockPosterior,
            ];
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $error;
        }
    }
}