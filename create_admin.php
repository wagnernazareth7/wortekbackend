<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Wortek\Store\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function perguntar(string $mensagem): string
{
    echo $mensagem;
    return trim((string) fgets(STDIN));
}

try {
    $pdo = Database::getConnection();

    echo "=== Criar Administrador Wortek Store ===" . PHP_EOL;

    $nome = perguntar("Nome: ");
    $email = strtolower(perguntar("Email: "));
    $telefone = perguntar("Telefone (opcional): ");
    $senha = perguntar("Senha: ");

    if ($nome === '' || $email === '' || $senha === '') {
        throw new RuntimeException(
            'Nome, email e senha são obrigatórios.'
        );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Email inválido.');
    }

    if (strlen($senha) < 8) {
        throw new RuntimeException(
            'A senha deve possuir pelo menos 8 caracteres.'
        );
    }

    $stmt = $pdo->prepare(
        "SELECT id
         FROM perfis
         WHERE nome = 'Administrador'
           AND estado = 'ativo'
         LIMIT 1"
    );

    $stmt->execute();

    $perfilId = $stmt->fetchColumn();

    if (!$perfilId) {
        throw new RuntimeException(
            'Perfil Administrador não encontrado.'
        );
    }

    $stmt = $pdo->prepare(
        'SELECT id
         FROM utilizadores
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute([
        'email' => $email
    ]);

    if ($stmt->fetch()) {
        throw new RuntimeException(
            'Já existe um utilizador com este email.'
        );
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO utilizadores
            (nome, email, telefone, senha, perfil_id, estado)
         VALUES
            (:nome, :email, :telefone, :senha, :perfil_id, :estado)'
    );

    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone !== '' ? $telefone : null,
        'senha' => $hash,
        'perfil_id' => $perfilId,
        'estado' => 'ativo'
    ]);

    echo PHP_EOL;
    echo "Administrador criado com sucesso." . PHP_EOL;
    echo "ID: " . $pdo->lastInsertId() . PHP_EOL;
    echo "Email: {$email}" . PHP_EOL;

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "ERRO: {$e->getMessage()}" . PHP_EOL
    );

    exit(1);
}