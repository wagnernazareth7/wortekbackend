<?php

namespace Wortek\Store\Repositories;

use PDO;

class ClientRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function all(?string $search = null): array
    {
        $sql = '
            SELECT
                c.id,
                c.tipo,
                c.nome,
                c.nuit,
                c.email,
                c.telefone,
                c.telefone_alternativo,
                c.endereco,
                c.cidade,
                c.observacoes,
                c.estado,
                c.created_by,
                c.created_at,
                c.updated_at,
                u.nome AS criado_por
            FROM clientes c
            LEFT JOIN utilizadores u
                ON u.id = c.created_by
        ';

        $params = [];

        if ($search !== null && trim($search) !== '') {
    $searchTerm = '%' . trim($search) . '%';

    $sql .= '
        WHERE
            c.nome LIKE :search_nome
            OR c.telefone LIKE :search_telefone
            OR c.email LIKE :search_email
            OR c.nuit LIKE :search_nuit
    ';

    $params = [
        'search_nome' => $searchTerm,
        'search_telefone' => $searchTerm,
        'search_email' => $searchTerm,
        'search_nuit' => $searchTerm,
    ];
}

        $sql .= ' ORDER BY c.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            '
            SELECT
                c.id,
                c.tipo,
                c.nome,
                c.nuit,
                c.email,
                c.telefone,
                c.telefone_alternativo,
                c.endereco,
                c.cidade,
                c.observacoes,
                c.estado,
                c.created_by,
                c.created_at,
                c.updated_at,
                u.nome AS criado_por
            FROM clientes c
            LEFT JOIN utilizadores u
                ON u.id = c.created_by
            WHERE c.id = :id
            LIMIT 1
            '
        );

        $statement->execute([
            'id' => $id,
        ]);

        $client = $statement->fetch();

        return $client ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            '
            INSERT INTO clientes (
                tipo,
                nome,
                nuit,
                email,
                telefone,
                telefone_alternativo,
                endereco,
                cidade,
                observacoes,
                estado,
                created_by
            )
            VALUES (
                :tipo,
                :nome,
                :nuit,
                :email,
                :telefone,
                :telefone_alternativo,
                :endereco,
                :cidade,
                :observacoes,
                :estado,
                :created_by
            )
            '
        );

        $statement->execute([
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'nuit' => $data['nuit'],
            'email' => $data['email'],
            'telefone' => $data['telefone'],
            'telefone_alternativo' => $data['telefone_alternativo'],
            'endereco' => $data['endereco'],
            'cidade' => $data['cidade'],
            'observacoes' => $data['observacoes'],
            'estado' => $data['estado'],
            'created_by' => $data['created_by'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            '
            UPDATE clientes
            SET
                tipo = :tipo,
                nome = :nome,
                nuit = :nuit,
                email = :email,
                telefone = :telefone,
                telefone_alternativo = :telefone_alternativo,
                endereco = :endereco,
                cidade = :cidade,
                observacoes = :observacoes,
                estado = :estado
            WHERE id = :id
            '
        );

        return $statement->execute([
            'id' => $id,
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'nuit' => $data['nuit'],
            'email' => $data['email'],
            'telefone' => $data['telefone'],
            'telefone_alternativo' => $data['telefone_alternativo'],
            'endereco' => $data['endereco'],
            'cidade' => $data['cidade'],
            'observacoes' => $data['observacoes'],
            'estado' => $data['estado'],
        ]);
    }
}