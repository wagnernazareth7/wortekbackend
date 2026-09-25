<?php

namespace Wortek\Store\Controllers;

use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\UserRepository;

class AuthController
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    public function login(): never
    {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($data)) {
            Response::error('Corpo da requisição inválido.', 400);
        }

        $email = strtolower(trim($data['email'] ?? ''));
        $senha = $data['senha'] ?? '';

        if ($email === '' || $senha === '') {
            Response::error(
                'Email e senha são obrigatórios.',
                422
            );
        }

        $user = $this->users->findByEmail($email);

        if (
            !$user ||
            !password_verify($senha, $user['senha'])
        ) {
            Response::error(
                'Email ou senha inválidos.',
                401
            );
        }

        if ($user['estado'] !== 'ativo') {
            Response::error(
                'Utilizador inativo.',
                403
            );
        }

        session_regenerate_id(true);

        $_SESSION['utilizador_id'] = (int) $user['id'];
        $_SESSION['perfil_id'] = (int) $user['perfil_id'];
        $_SESSION['perfil_nome'] = $user['perfil_nome'];

        $this->users->updateLastLogin((int) $user['id']);

        $permissions = $this->users->getPermissions(
            (int) $user['perfil_id']
        );

        Response::success([
            'message' => 'Login efectuado com sucesso.',
            'user' => [
                'id' => (int) $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'telefone' => $user['telefone'],
                'perfil' => [
                    'id' => (int) $user['perfil_id'],
                    'nome' => $user['perfil_nome']
                ],
                'permissoes' => $permissions
            ]
        ]);
    }

    public function me(): never
    {
        if (empty($_SESSION['utilizador_id'])) {
            Response::error(
                'Não autenticado.',
                401
            );
        }

        $user = $this->users->findById(
            (int) $_SESSION['utilizador_id']
        );

        if (!$user || $user['estado'] !== 'ativo') {
            $this->destroySession();

            Response::error(
                'Sessão inválida.',
                401
            );
        }

        Response::success([
            'user' => [
                'id' => (int) $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'telefone' => $user['telefone'],
                'perfil' => [
                    'id' => (int) $user['perfil_id'],
                    'nome' => $user['perfil_nome']
                ],
                'permissoes' => $user['permissoes']
            ]
        ]);
    }

    public function logout(): never
    {
        $this->destroySession();

        Response::success([
            'message' => 'Sessão terminada com sucesso.'
        ]);
    }

    private function destroySession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}