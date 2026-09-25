<?php

namespace Wortek\Store\Controllers;

use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\ClientRepository;

class ClientController
{
    public function __construct(
        private ClientRepository $clients
    ) {
    }

    public function index(): never
    {
        $search = isset($_GET['search'])
            ? trim((string) $_GET['search'])
            : null;

        $clients = $this->clients->all($search);

        Response::success([
            'clientes' => $clients,
        ]);
    }

    public function show(int $id): never
    {
        $client = $this->clients->findById($id);

        if (!$client) {
            Response::error(
                'Cliente não encontrado.',
                404
            );
        }

        Response::success([
            'cliente' => $client,
        ]);
    }

    public function store(int $userId): never
    {
        $input = $this->getJsonBody();

        $data = $this->validate($input);

        $data['created_by'] = $userId;

        $id = $this->clients->create($data);

        $client = $this->clients->findById($id);

        Response::success([
            'message' => 'Cliente registado com sucesso.',
            'cliente' => $client,
        ], 201);
    }

    public function update(int $id): never
    {
        $client = $this->clients->findById($id);

        if (!$client) {
            Response::error(
                'Cliente não encontrado.',
                404
            );
        }

        $input = $this->getJsonBody();

        $data = $this->validate($input);

        $this->clients->update($id, $data);

        Response::success([
            'message' => 'Cliente actualizado com sucesso.',
            'cliente' => $this->clients->findById($id),
        ]);
    }

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            Response::error(
                'Corpo da requisição inválido.',
                400
            );
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            Response::error(
                'Corpo da requisição inválido.',
                400
            );
        }

        return $data;
    }

    private function validate(array $input): array
    {
        $tipo = trim((string) ($input['tipo'] ?? 'particular'));
        $nome = trim((string) ($input['nome'] ?? ''));
        $telefone = trim((string) ($input['telefone'] ?? ''));
        $estado = trim((string) ($input['estado'] ?? 'ativo'));

        if (!in_array($tipo, ['particular', 'empresa'], true)) {
            Response::error(
                'Tipo de cliente inválido.',
                422
            );
        }

        if ($nome === '') {
            Response::error(
                'O nome do cliente é obrigatório.',
                422
            );
        }

        if (mb_strlen($nome) > 180) {
            Response::error(
                'O nome do cliente não pode exceder 180 caracteres.',
                422
            );
        }

        if ($telefone === '') {
            Response::error(
                'O telefone do cliente é obrigatório.',
                422
            );
        }

        if (mb_strlen($telefone) > 30) {
            Response::error(
                'O telefone não pode exceder 30 caracteres.',
                422
            );
        }

        if (!in_array($estado, ['ativo', 'inativo'], true)) {
            Response::error(
                'Estado do cliente inválido.',
                422
            );
        }

        $email = $this->nullableString(
            $input['email'] ?? null
        );

        if (
            $email !== null &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            Response::error(
                'O endereço de email é inválido.',
                422
            );
        }

        return [
            'tipo' => $tipo,
            'nome' => $nome,
            'nuit' => $this->nullableString(
                $input['nuit'] ?? null
            ),
            'email' => $email,
            'telefone' => $telefone,
            'telefone_alternativo' => $this->nullableString(
                $input['telefone_alternativo'] ?? null
            ),
            'endereco' => $this->nullableString(
                $input['endereco'] ?? null
            ),
            'cidade' => $this->nullableString(
                $input['cidade'] ?? null
            ),
            'observacoes' => $this->nullableString(
                $input['observacoes'] ?? null
            ),
            'estado' => $estado,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }
}