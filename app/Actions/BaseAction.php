<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseAction
{
    /**
     * Execute the action with transaction handling
     */
    /**
     * @param mixed ...$parameters
     */
    public function handle(mixed ...$parameters): ActionResult
    {
        try {
            // Validation
            $validated = $this->validate(...$parameters);

            // Authorization
            if (! $this->authorize(...$parameters)) {
                return ActionResult::unauthorized('You are not authorized to perform this action');
            }

            // Execute in transaction
            $result = DB::transaction(function () use ($validated, $parameters) {
                // Before hook
                $this->beforeExecute($validated);

                // Main logic
                $data = $this->execute($validated, ...$parameters);

                // After hook
                $this->afterExecute($data, $validated);

                return $data;
            });

            // Log success
            $this->logSuccess($result);

            return ActionResult::success($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            /** @var array<string, mixed> $errors */
            $errors = $e->errors();
            return ActionResult::validationError($errors);
        } catch (\Exception $e) {
            // Log error
            $this->logError($e);

            // Handle error
            return $this->handleError($e);
        }
    }

    /**
     * Validate input data
     */
    /**
     * @param mixed ...$parameters
     * @return array<string, mixed>
     */
    abstract protected function validate(mixed ...$parameters): array;

    /**
     * Check authorization
     */
    /**
     * @param mixed ...$parameters
     */
    abstract protected function authorize(mixed ...$parameters): bool;

    /**
     * Main business logic
     */
    /**
     * @param array<string, mixed> $validated
     * @param mixed ...$parameters
     * @return mixed
     */
    abstract protected function execute(array $validated, mixed ...$parameters): mixed;

    /**
     * Hook before execution (optional)
     */
    /**
     * @param array<string, mixed> $validated
     */
    protected function beforeExecute(array $validated): void {}

    /**
     * Hook after execution (optional)
     */
    /**
     * @param mixed $result
     * @param array<string, mixed> $validated
     */
    protected function afterExecute(mixed $result, array $validated): void {}

    /**
     * Custom error handling
     */
    protected function handleError(\Throwable $throwable): ActionResult
    {
        return ActionResult::error('An error occurred', [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
        ]);
    }

    /**
     * Log successful execution
     */
    /**
     * @param mixed $result
     */
    protected function logSuccess(mixed $result): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        Log::info(static::class.' executed successfully', [
            'user_id' => $user?->id,
            'result' => $result,
        ]);
    }

    /**
     * Log error
     */
    protected function logError(\Exception $exception): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        Log::error(static::class.' failed', [
            'user_id' => $user?->id,
            'exception' => $exception,
        ]);
    }
}
