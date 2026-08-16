<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Shared `{"success": ..., "message": ..., "data": ...}` envelope used by the API controllers.
 */
trait ApiResponses
{
    protected function ok(mixed $data = null, ?string $message = null, array $extra = []): JsonResponse
    {
        return response()->json($this->envelope(true, $message, $data, $extra));
    }

    protected function created(mixed $data = null, ?string $message = null, array $extra = []): JsonResponse
    {
        return response()->json($this->envelope(true, $message, $data, $extra), 201);
    }

    protected function message(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    protected function failure(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message] + $extra, $status);
    }

    protected function notFound(string $message): JsonResponse
    {
        return $this->failure($message, 404);
    }

    protected function forbidden(string $message = 'Access denied.'): JsonResponse
    {
        return $this->failure($message, 403);
    }

    protected function unauthenticated(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->failure($message, 401);
    }

    private function envelope(bool $success, ?string $message, mixed $data, array $extra): array
    {
        $payload = ['success' => $success];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return $payload + $extra;
    }
}
