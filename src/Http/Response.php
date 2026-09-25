<?php

namespace Wortek\Store\Http;

class Response
{
    public static function json(
        array $data,
        int $status = 200
    ): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    public static function success(
        array $data = [],
        int $status = 200
    ): never {
        self::json([
            'success' => true,
            ...$data
        ], $status);
    }

    public static function error(
        string $message,
        int $status = 400
    ): never {
        self::json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}