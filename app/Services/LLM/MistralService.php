<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Enums\LLMProvider;
use App\Exceptions\LLMException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MistralService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.mistral.ai/v1';

    private int $maxRetries = 3;

    private int $retryDelay = 1000; // milliseconds

    private float $timeout = 30.0; // seconds

    public function __construct()
    {
        $this->apiKey = config('services.mistral.api_key');
    }

    /**
     * Send a chat completion request to Mistral
     *
     * @param  array<int, array<string, string>>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws LLMException
     */
    public function chat(array $messages, array $options = []): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $startTime = microtime(true);

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->baseUrl.'/chat/completions', [
                        'model' => $options['model'] ?? config('services.mistral.model', 'mistral-large-latest'),
                        'messages' => $messages,
                        'temperature' => $options['temperature'] ?? 0.7,
                        'max_tokens' => $options['max_tokens'] ?? 4000,
                    ]);

                if (! $response->successful()) {
                    $attempt++;
                    $error = $response->json('error.message', $response->body());

                    if ($response->status() === 429) {
                        Log::warning('Mistral rate limit exceeded', [
                            'attempt' => $attempt,
                            'max_retries' => $this->maxRetries,
                        ]);

                        if ($attempt >= $this->maxRetries) {
                            throw LLMException::rateLimitExceeded(LLMProvider::Mistral->value);
                        }

                        // Exponential backoff
                        usleep($this->retryDelay * (2 ** ($attempt - 1)) * 1000);

                        continue;
                    }

                    Log::error('Mistral API error', [
                        'status' => $response->status(),
                        'error' => $error,
                        'attempt' => $attempt,
                    ]);

                    if ($attempt >= $this->maxRetries) {
                        throw LLMException::apiError(LLMProvider::Mistral->value, $error);
                    }

                    usleep($this->retryDelay * 1000);

                    continue;
                }

                $duration = microtime(true) - $startTime;
                $data = $response->json();

                if (! isset($data['choices'][0]['message']['content'])) {
                    throw LLMException::invalidResponse(
                        LLMProvider::Mistral->value,
                        'Missing content in response'
                    );
                }

                $result = [
                    'provider' => LLMProvider::Mistral->value,
                    'content' => $data['choices'][0]['message']['content'],
                    'model' => $data['model'],
                    'usage' => [
                        'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                        'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                        'total_tokens' => $data['usage']['total_tokens'] ?? 0,
                    ],
                    'finish_reason' => $data['choices'][0]['finish_reason'] ?? 'unknown',
                    'duration' => $duration,
                ];

                Log::info('Mistral chat completion successful', [
                    'model' => $result['model'],
                    'tokens' => $result['usage']['total_tokens'],
                    'duration' => $duration,
                    'attempt' => $attempt + 1,
                ]);

                return $result;

            } catch (\Exception $e) {
                if ($e instanceof LLMException) {
                    throw $e;
                }

                $lastException = $e;
                $attempt++;

                Log::warning('Mistral connection error', [
                    'message' => $e->getMessage(),
                    'attempt' => $attempt,
                    'max_retries' => $this->maxRetries,
                ]);

                if ($attempt >= $this->maxRetries) {
                    throw LLMException::timeout(LLMProvider::Mistral->value, $this->timeout);
                }

                usleep($this->retryDelay * 1000);
            }
        }

        throw LLMException::apiError(
            LLMProvider::Mistral->value,
            $lastException?->getMessage() ?? 'Unknown error after retries'
        );
    }

    /**
     * Check if Mistral service is available
     */
    public function isAvailable(): bool
    {
        try {
            $this->chat([
                ['role' => 'user', 'content' => 'ping'],
            ], ['max_tokens' => 5]);

            return true;
        } catch (LLMException) {
            return false;
        }
    }

    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = $retries;

        return $this;
    }

    public function setTimeout(float $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }
}
