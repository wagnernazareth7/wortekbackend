<?php

namespace Wortek\Store\Http;

use Wortek\Store\Repositories\UserRepository;

class Authorization
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    public function requireAuthentication(): array
    {
        $userId = $_SESSION['utilizador_id'] ?? null;

        if (!$userId) {
            Response::error(
                'Não autenticado.',
                401
            );
        }

        $user = $this->users->findById((int) $userId);

        if (!$user || $user['estado'] !== 'ativo') {
            $this->destroySession();

            Response::error(
                'Sessão inválida.',
                401
            );
        }

        return $user;
    }

    public function requirePermission(string $permission): array
    {
        $user = $this->requireAuthentication();

        if (!in_array(
            $permission,
            $user['permissoes'] ?? [],
            true
        )) {
            Response::error(
                'Não possui permissão para realizar esta operação.',
                403
            );
        }

        return $user;
    }

    private function destroySession(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}