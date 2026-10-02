<?php

namespace Wortek\Store\Controllers;

use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\ProductRepository;

class ProductController
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function index(): never
    {
        $search = isset($_GET['search'])
            ? trim((string) $_GET['search'])
            : null;

        $products = $this->productRepository->all($search);

        Response::success([
            'produtos' => $products,
        ]);
    }

    public function categories(): never
{
    Response::success([
        'categorias' => $this->productRepository->categories(),
    ]);
}

    public function show(int $id): never
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            Response::error(
                'Produto não encontrado.',
                404
            );
        }

        Response::success([
            'produto' => $product,
        ]);
    }

    public function store(int $userId): never
    {
        $data = $this->getJsonBody();

        $validated = $this->validate($data);

        $existing = $this->productRepository->findBySku(
            $validated['sku']
        );

        if ($existing) {
            Response::error(
                'Já existe um produto com este SKU.',
                422
            );
        }

        if (
            $validated['categoria_id'] !== null &&
            !$this->productRepository->categoryExists(
                $validated['categoria_id']
            )
        ) {
            Response::error(
                'A categoria seleccionada não existe.',
                422
            );
        }

        $validated['created_by'] = $userId;

        $id = $this->productRepository->create($validated);

        $product = $this->productRepository->findById($id);

        Response::success([
            'message' => 'Produto registado com sucesso.',
            'produto' => $product,
        ], 201);
    }

    public function update(int $id): never
    {
        $current = $this->productRepository->findById($id);

        if (!$current) {
            Response::error(
                'Produto não encontrado.',
                404
            );
        }

        $data = $this->getJsonBody();

        $validated = $this->validate($data);

        $existing = $this->productRepository->findBySku(
            $validated['sku']
        );

        if (
            $existing &&
            (int) $existing['id'] !== $id
        ) {
            Response::error(
                'Já existe outro produto com este SKU.',
                422
            );
        }

        if (
            $validated['categoria_id'] !== null &&
            !$this->productRepository->categoryExists(
                $validated['categoria_id']
            )
        ) {
            Response::error(
                'A categoria seleccionada não existe.',
                422
            );
        }

        $this->productRepository->update(
            $id,
            $validated
        );

        $product = $this->productRepository->findById($id);

        Response::success([
            'message' => 'Produto actualizado com sucesso.',
            'produto' => $product,
        ]);
    }

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');

        $data = json_decode(
            $raw ?: '',
            true
        );

        if (!is_array($data)) {
            Response::error(
                'Corpo da requisição inválido.',
                400
            );
        }

        return $data;
    }

    private function validate(array $data): array
    {
        $sku = trim((string) ($data['sku'] ?? ''));
        $nome = trim((string) ($data['nome'] ?? ''));
        $unidade = trim(
            (string) ($data['unidade'] ?? 'unidade')
        );

        if ($sku === '') {
            Response::error(
                'O SKU é obrigatório.',
                422
            );
        }

        if ($nome === '') {
            Response::error(
                'O nome do produto é obrigatório.',
                422
            );
        }

        if ($unidade === '') {
            Response::error(
                'A unidade é obrigatória.',
                422
            );
        }

        if (mb_strlen($sku) > 60) {
            Response::error(
                'O SKU não pode exceder 60 caracteres.',
                422
            );
        }

        if (mb_strlen($nome) > 180) {
            Response::error(
                'O nome não pode exceder 180 caracteres.',
                422
            );
        }

        if (mb_strlen($unidade) > 30) {
            Response::error(
                'A unidade não pode exceder 30 caracteres.',
                422
            );
        }

        $categoriaId = null;

        if (
            isset($data['categoria_id']) &&
            $data['categoria_id'] !== '' &&
            $data['categoria_id'] !== null
        ) {
            if (
                filter_var(
                    $data['categoria_id'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int) $data['categoria_id'] <= 0
            ) {
                Response::error(
                    'A categoria informada é inválida.',
                    422
                );
            }

            $categoriaId = (int) $data['categoria_id'];
        }

        $precoCompra = $this->validateMoney(
            $data['preco_compra'] ?? null,
            'O preço de compra'
        );

        $precoVenda = $this->validateMoney(
            $data['preco_venda'] ?? 0,
            'O preço de venda',
            false
        );

        $stockMinimo = $this->validateQuantity(
            $data['stock_minimo'] ?? 0
        );

        $estado = $data['estado'] ?? 'ativo';

        if (!in_array(
            $estado,
            ['ativo', 'inativo'],
            true
        )) {
            Response::error(
                'O estado informado é inválido.',
                422
            );
        }

        return [
            'categoria_id' => $categoriaId,
            'sku' => $sku,
            'nome' => $nome,
            'descricao' => $this->nullableString(
                $data['descricao'] ?? null
            ),
            'marca' => $this->nullableString(
                $data['marca'] ?? null,
                120
            ),
            'modelo' => $this->nullableString(
                $data['modelo'] ?? null,
                120
            ),
            'unidade' => $unidade,
            'preco_compra' => $precoCompra,
            'preco_venda' => $precoVenda,
            'stock_minimo' => $stockMinimo,
            'estado' => $estado,
        ];
    }

    private function validateMoney(
        mixed $value,
        string $field,
        bool $nullable = true
    ): ?float {
        if (
            $nullable &&
            ($value === null || $value === '')
        ) {
            return null;
        }

        if (!is_numeric($value)) {
            Response::error(
                "{$field} deve ser um valor numérico.",
                422
            );
        }

        $number = (float) $value;

        if ($number < 0) {
            Response::error(
                "{$field} não pode ser negativo.",
                422
            );
        }

        return $number;
    }

    private function validateQuantity(mixed $value): float
    {
        if (!is_numeric($value)) {
            Response::error(
                'O stock mínimo deve ser um valor numérico.',
                422
            );
        }

        $number = (float) $value;

        if ($number < 0) {
            Response::error(
                'O stock mínimo não pode ser negativo.',
                422
            );
        }

        return $number;
    }

    private function nullableString(
        mixed $value,
        ?int $maxLength = null
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (
            $maxLength !== null &&
            mb_strlen($value) > $maxLength
        ) {
            Response::error(
                "O campo não pode exceder {$maxLength} caracteres.",
                422
            );
        }

        return $value;
    }
}