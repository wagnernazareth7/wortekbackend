<?php

namespace Wortek\Store\Controllers;

use RuntimeException;
use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\StockRepository;

class StockController
{
    public function __construct(
        private StockRepository $stock
    ) {
    }

    public function index(): never
    {
        $search = isset($_GET['search'])
            ? trim((string) $_GET['search'])
            : null;

        Response::success([
            'stock' => $this->stock->all($search),
        ]);
    }

    public function show(int $produtoId): never
    {
        $stock = $this->stock->findByProductId($produtoId);

        if (!$stock) {
            Response::error(
                'Stock do produto não encontrado.',
                404
            );
        }

        Response::success([
            'stock' => $stock,
        ]);
    }

    public function movements(): never
    {
        $produtoId = null;

        if (
            isset($_GET['produto_id'])
            && $_GET['produto_id'] !== ''
        ) {
            if (
                !ctype_digit(
                    (string) $_GET['produto_id']
                )
            ) {
                Response::error(
                    'Produto inválido.',
                    422
                );
            }

            $produtoId = (int) $_GET['produto_id'];
        }

        Response::success([
            'movimentacoes' =>
                $this->stock->movements($produtoId),
        ]);
    }

    public function move(int $userId): never
    {
        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($input)) {
            Response::error(
                'Corpo da requisição inválido.',
                400
            );
        }

        $produtoId = (int) ($input['produto_id'] ?? 0);

        $tipo = trim(
            (string) ($input['tipo'] ?? '')
        );

        $quantidade = (float) (
            $input['quantidade'] ?? 0
        );

        $observacoes = isset($input['observacoes'])
            ? trim((string) $input['observacoes'])
            : null;

        $origemTipo = isset($input['origem_tipo'])
            ? trim((string) $input['origem_tipo'])
            : null;

        $origemId = isset($input['origem_id'])
            && $input['origem_id'] !== ''
                ? (int) $input['origem_id']
                : null;

        if ($produtoId <= 0) {
            Response::error(
                'Produto é obrigatório.',
                422
            );
        }

        if ($tipo === '') {
            Response::error(
                'Tipo de movimentação é obrigatório.',
                422
            );
        }

        if ($quantidade <= 0) {
            Response::error(
                'Quantidade deve ser superior a zero.',
                422
            );
        }

        $tiposPermitidos = [
            'entrada',
            'saida',
            'ajuste_entrada',
            'ajuste_saida',
        ];

        if (!in_array($tipo, $tiposPermitidos, true)) {
            Response::error(
                'Tipo de movimentação inválido.',
                422
            );
        }

        try {
            $movimentacao = $this->stock->move(
                $produtoId,
                $tipo,
                $quantidade,
                $observacoes !== ''
                    ? $observacoes
                    : null,
                $userId,
                $origemTipo !== ''
                    ? $origemTipo
                    : null,
                $origemId
            );

            $stock = $this->stock
                ->findByProductId($produtoId);

            Response::success([
                'message' =>
                    'Movimentação de stock registada com sucesso.',
                'movimentacao' => $movimentacao,
                'stock' => $stock,
            ], 201);
        } catch (RuntimeException $error) {
            Response::error(
                $error->getMessage(),
                422
            );
        }
    }
}