<?php

namespace Wortek\Store\Repositories;

use PDO;

class ProductRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function all(?string $search = null): array
    {
        $sql = '
            SELECT
                p.id,
                p.categoria_id,
                c.nome AS categoria_nome,
                p.sku,
                p.nome,
                p.descricao,
                p.marca,
                p.modelo,
                p.unidade,
                p.preco_compra,
                p.preco_venda,
                p.stock_minimo,
                p.estado,
                p.created_by,
                p.created_at,
                p.updated_at,
                u.nome AS criado_por,
                COALESCE(s.quantidade, 0) AS quantidade,
                COALESCE(s.quantidade_reservada, 0) AS quantidade_reservada,
                (
                    COALESCE(s.quantidade, 0) -
                    COALESCE(s.quantidade_reservada, 0)
                ) AS quantidade_disponivel
            FROM produtos p
            LEFT JOIN categorias c
                ON c.id = p.categoria_id
            LEFT JOIN utilizadores u
                ON u.id = p.created_by
            LEFT JOIN stock s
                ON s.produto_id = p.id
        ';

        $params = [];

        if ($search !== null && trim($search) !== '') {
            $searchTerm = '%' . trim($search) . '%';

            $sql .= '
                WHERE
                    p.nome LIKE :search_nome
                    OR p.sku LIKE :search_sku
                    OR p.marca LIKE :search_marca
                    OR p.modelo LIKE :search_modelo
                    OR c.nome LIKE :search_categoria
            ';

            $params = [
                'search_nome' => $searchTerm,
                'search_sku' => $searchTerm,
                'search_marca' => $searchTerm,
                'search_modelo' => $searchTerm,
                'search_categoria' => $searchTerm,
            ];
        }

        $sql .= ' ORDER BY p.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            '
            SELECT
                p.id,
                p.categoria_id,
                c.nome AS categoria_nome,
                p.sku,
                p.nome,
                p.descricao,
                p.marca,
                p.modelo,
                p.unidade,
                p.preco_compra,
                p.preco_venda,
                p.stock_minimo,
                p.estado,
                p.created_by,
                p.created_at,
                p.updated_at,
                u.nome AS criado_por,
                COALESCE(s.quantidade, 0) AS quantidade,
                COALESCE(s.quantidade_reservada, 0)
                    AS quantidade_reservada,
                (
                    COALESCE(s.quantidade, 0) -
                    COALESCE(s.quantidade_reservada, 0)
                ) AS quantidade_disponivel
            FROM produtos p
            LEFT JOIN categorias c
                ON c.id = p.categoria_id
            LEFT JOIN utilizadores u
                ON u.id = p.created_by
            LEFT JOIN stock s
                ON s.produto_id = p.id
            WHERE p.id = :id
            LIMIT 1
            '
        );

        $statement->execute([
            'id' => $id,
        ]);

        $product = $statement->fetch();

        return $product ?: null;
    }

    public function findBySku(string $sku): ?array
    {
        $statement = $this->pdo->prepare(
            '
            SELECT id, sku
            FROM produtos
            WHERE sku = :sku
            LIMIT 1
            '
        );

        $statement->execute([
            'sku' => $sku,
        ]);

        $product = $statement->fetch();

        return $product ?: null;
    }

    public function categoryExists(int $id): bool
    {
        $statement = $this->pdo->prepare(
            '
            SELECT 1
            FROM categorias
            WHERE id = :id
            LIMIT 1
            '
        );

        $statement->execute([
            'id' => $id,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function categories(): array
{
    return $this->pdo
        ->query("SELECT id, nome, descricao FROM categorias WHERE estado = 'ativo' ORDER BY nome")
        ->fetchAll();
}

    public function create(array $data): int
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                '
                INSERT INTO produtos (
                    categoria_id,
                    sku,
                    nome,
                    descricao,
                    marca,
                    modelo,
                    unidade,
                    preco_compra,
                    preco_venda,
                    stock_minimo,
                    estado,
                    created_by
                )
                VALUES (
                    :categoria_id,
                    :sku,
                    :nome,
                    :descricao,
                    :marca,
                    :modelo,
                    :unidade,
                    :preco_compra,
                    :preco_venda,
                    :stock_minimo,
                    :estado,
                    :created_by
                )
                '
            );

            $statement->execute([
                'categoria_id' => $data['categoria_id'],
                'sku' => $data['sku'],
                'nome' => $data['nome'],
                'descricao' => $data['descricao'],
                'marca' => $data['marca'],
                'modelo' => $data['modelo'],
                'unidade' => $data['unidade'],
                'preco_compra' => $data['preco_compra'],
                'preco_venda' => $data['preco_venda'],
                'stock_minimo' => $data['stock_minimo'],
                'estado' => $data['estado'],
                'created_by' => $data['created_by'],
            ]);

            $productId = (int) $this->pdo->lastInsertId();

            $stockStatement = $this->pdo->prepare(
                '
                INSERT INTO stock (
                    produto_id,
                    quantidade,
                    quantidade_reservada
                )
                VALUES (
                    :produto_id,
                    0,
                    0
                )
                '
            );

            $stockStatement->execute([
                'produto_id' => $productId,
            ]);

            $this->pdo->commit();

            return $productId;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            '
            UPDATE produtos
            SET
                categoria_id = :categoria_id,
                sku = :sku,
                nome = :nome,
                descricao = :descricao,
                marca = :marca,
                modelo = :modelo,
                unidade = :unidade,
                preco_compra = :preco_compra,
                preco_venda = :preco_venda,
                stock_minimo = :stock_minimo,
                estado = :estado
            WHERE id = :id
            '
        );

        return $statement->execute([
            'id' => $id,
            'categoria_id' => $data['categoria_id'],
            'sku' => $data['sku'],
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
            'marca' => $data['marca'],
            'modelo' => $data['modelo'],
            'unidade' => $data['unidade'],
            'preco_compra' => $data['preco_compra'],
            'preco_venda' => $data['preco_venda'],
            'stock_minimo' => $data['stock_minimo'],
            'estado' => $data['estado'],
        ]);
    }
}