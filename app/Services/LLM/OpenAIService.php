<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Enums\LLMProvider;
use App\Exceptions\LLMException;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;

class OpenAIService
{
    private Client $client;

    private int $maxRetries = 3;

    private int $retryDelay = 1000; // milliseconds

    private float $timeout = 30.0; // seconds

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    /**
     * Send a chat completion request to OpenAI
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

                $response = $this->client->chat()->create([
                    'model' => $options['model'] ?? config('services.openai.model', 'gpt-4'),
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 4000,
                    'timeout' => $this->timeout,
                ]);

                $duration = microtime(true) - $startTime;

                $result = [
                    'provider' => LLMProvider::OpenAI->value,
                    'content' => $response->choices[0]->message->content,
                    'model' => $response->model,
                    'usage' => [
                        'prompt_tokens' => $response->usage->promptTokens,
                        'completion_tokens' => $response->usage->completionTokens,
                        'total_tokens' => $response->usage->totalTokens,
                    ],
                    'finish_reason' => $response->choices[0]->finishReason,
                    'duration' => $duration,
                ];

                Log::info('OpenAI chat completion successful', [
                    'model' => $result['model'],
                    'tokens' => $result['usage']['total_tokens'],
                    'duration' => $duration,
                    'attempt' => $attempt + 1,
                ]);

                return $result;

            } catch (ErrorException $e) {
                $lastException = $e;
                $attempt++;

                if (str_contains($e->getMessage(), 'rate_limit')) {
                    Log::warning('OpenAI rate limit exceeded', [
                        'attempt' => $attempt,
                        'max_retries' => $this->maxRetries,
                    ]);

                    if ($attempt >= $this->maxRetries) {
                        throw LLMException::rateLimitExceeded(LLMProvider::OpenAI->value);
                    }

                    // Exponential backoff
                    usleep($this->retryDelay * (2 ** ($attempt - 1)) * 1000);

                    continue;
                }

                Log::error('OpenAI API error', [
                    'message' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);

                if ($attempt >= $this->maxRetries) {
                    throw LLMException::apiError(LLMProvider::OpenAI->value, $e->getMessage());
                }

                usleep($this->retryDelay * 1000);

            } catch (TransporterException $e) {
                $lastException = $e;
                $attempt++;

                Log::warning('OpenAI connection error', [
                    'message' => $e->getMessage(),
                    'attempt' => $attempt,
                    'max_retries' => $this->maxRetries,
                ]);

                if ($attempt >= $this->maxRetries) {
                    throw LLMException::timeout(LLMProvider::OpenAI->value, $this->timeout);
                }

                usleep($this->retryDelay * 1000);
            }
        }

        throw LLMException::apiError(
            LLMProvider::OpenAI->value,
            $lastException?->getMessage() ?? 'Unknown error after retries'
        );
    }

    /**
     * Check if OpenAI service is available
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
