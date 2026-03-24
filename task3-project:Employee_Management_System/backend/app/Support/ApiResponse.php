<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        string $message,
        array|object|null $data = null,
        array $meta = [],
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
            'meta' => empty($meta) ? (object) [] : $meta,
        ], $status);
    }

    public static function error(
        string $message,
        array $errors = [],
        string $code = 'ERROR',
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => empty($errors) ? (object) [] : $errors,
            'code' => $code,
        ], $status);
    }
}
