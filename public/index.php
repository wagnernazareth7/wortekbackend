<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Wortek\Store\Config\Database;
use Wortek\Store\Controllers\AuthController;
use Wortek\Store\Controllers\ClientController;
use Wortek\Store\Http\Authorization;
use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\ClientRepository;
use Wortek\Store\Repositories\UserRepository;

$root = dirname(__DIR__);

$dotenv = Dotenv::createImmutable($root);
$dotenv->load();

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
*/

$frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';

header("Access-Control-Allow-Origin: {$frontendUrl}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
|--------------------------------------------------------------------------
| Sessão
|--------------------------------------------------------------------------
*/

session_name('wortek_session');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

/*
|--------------------------------------------------------------------------
| Dependências
|--------------------------------------------------------------------------
*/

$pdo = Database::getConnection();

$userRepository = new UserRepository($pdo);
$clientRepository = new ClientRepository($pdo);

$authorization = new Authorization($userRepository);

$authController = new AuthController($userRepository);
$clientController = new ClientController($clientRepository);

/*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/

$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
}

/*
|--------------------------------------------------------------------------
| Rotas
|--------------------------------------------------------------------------
*/

if ($path === '/health' && $method === 'GET') {
    Response::success([
        'message' => 'Wortek Store API funcionando.',
        'database' => 'connected'
    ]);
}

if ($path === '/login' && $method === 'POST') {
    $authController->login();
}

if ($path === '/me' && $method === 'GET') {
    $authController->me();
}

if ($path === '/logout' && $method === 'POST') {
    $authController->logout();
}

/*
|--------------------------------------------------------------------------
| Clientes
|--------------------------------------------------------------------------
*/

if ($path === '/clientes' && $method === 'GET') {
    $authorization->requirePermission(
        'cliente.visualizar'
    );

    $clientController->index();
}

if (
    preg_match('#^/clientes/(\d+)$#', $path, $matches)
    && $method === 'GET'
) {
    $authorization->requirePermission(
        'cliente.visualizar'
    );

    $clientController->show(
        (int) $matches[1]
    );
}

if ($path === '/clientes' && $method === 'POST') {
    $user = $authorization->requirePermission(
        'cliente.criar'
    );

    $clientController->store(
        (int) $user['id']
    );
}

if (
    preg_match('#^/clientes/(\d+)$#', $path, $matches)
    && $method === 'PUT'
) {
    $authorization->requirePermission(
        'cliente.editar'
    );

    $clientController->update(
        (int) $matches[1]
    );
}

Response::error(
    'Rota não encontrada.',
    404
);