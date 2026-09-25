<?php

namespace Wortek\Store\Repositories;

use PDO;

class UserRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id,
                u.nome,
                u.email,
                u.telefone,
                u.senha,
                u.estado,
                u.ultimo_login,
                u.perfil_id,
                p.nome AS perfil_nome
             FROM utilizadores u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id,
                u.nome,
                u.email,
                u.telefone,
                u.estado,
                u.ultimo_login,
                u.perfil_id,
                p.nome AS perfil_nome
             FROM utilizadores u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $user['permissoes'] = $this->getPermissions(
            (int) $user['perfil_id']
        );

        return $user;
    }

    public function getPermissions(int $perfilId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pm.codigo
             FROM permissoes pm
             INNER JOIN perfil_permissoes pp
                ON pp.permissao_id = pm.id
             WHERE pp.perfil_id = :perfil_id
             ORDER BY pm.codigo'
        );

        $stmt->execute([
            'perfil_id' => $perfilId
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilizadores
             SET ultimo_login = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id
        ]);
    }
}