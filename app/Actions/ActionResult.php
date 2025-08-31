<?php

declare(strict_types=1);

namespace App\Actions;

class ActionResult
{
    /**
     * @param array<string, mixed> $errors
     */
    private function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $message = null,
        public readonly array $errors = [],
        public readonly int $statusCode = 200
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(
            success: true,
            data: $data,
            message: $message,
            statusCode: 200
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    public static function error(string $message, array $errors = [], int $statusCode = 400): self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            statusCode: $statusCode
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    public static function validationError(array $errors): self
    {
        return new self(
            success: false,
            message: 'Validation failed',
            errors: $errors,
            statusCode: 422
        );
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(
            success: false,
            message: $message,
            statusCode: 403
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }

    public function toResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->toArray(), $this->statusCode);
    }
}
