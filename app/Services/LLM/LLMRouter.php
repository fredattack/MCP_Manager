<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Enums\LLMProvider;
use App\Exceptions\LLMException;
use Illuminate\Support\Facades\Log;

class LLMRouter
{
    /**
     * @var array<LLMProvider, OpenAIService|MistralService>
     */
    private array $providers = [];

    /**
     * @var array<int, LLMProvider>
     */
    private array $fallbackOrder;

    public function __construct(
        private readonly OpenAIService $openAIService,
        private readonly MistralService $mistralService
    ) {
        $this->providers = [
            LLMProvider::OpenAI => $this->openAIService,
            LLMProvider::Mistral => $this->mistralService,
        ];

        $this->fallbackOrder = [
            LLMProvider::OpenAI,
            LLMProvider::Mistral,
        ];
    }

    /**
     * Send a chat completion request with automatic fallback
     *
     * @param  array<int, array<string, string>>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws LLMException
     */
    public function chat(array $messages, array $options = []): array
    {
        $preferredProvider = isset($options['provider'])
            ? LLMProvider::from($options['provider'])
            : null;

        $order = $preferredProvider
            ? $this->getOrderWithPreferred($preferredProvider)
            : $this->fallbackOrder;

        $errors = [];

        foreach ($order as $provider) {
            try {
                Log::info('Attempting LLM request', [
                    'provider' => $provider->value,
                    'preferred' => $preferredProvider?->value,
                ]);

                $service = $this->providers[$provider];
                $result = $service->chat($messages, $options);

                // Log successful routing
                if ($provider !== $order[0]) {
                    Log::warning('LLM fallback used', [
                        'original_provider' => $order[0]->value,
                        'fallback_provider' => $provider->value,
                        'failed_providers' => array_map(
                            fn ($p) => $p->value,
                            array_slice($order, 0, array_search($provider, $order))
                        ),
                    ]);
                }

                return $result;

            } catch (LLMException $e) {
                $errors[$provider->value] = $e->getMessage();

                Log::warning('LLM provider failed, trying fallback', [
                    'provider' => $provider->value,
                    'error' => $e->getMessage(),
                    'remaining_providers' => array_map(
                        fn ($p) => $p->value,
                        array_slice($order, array_search($provider, $order) + 1)
                    ),
                ]);

                continue;
            }
        }

        Log::error('All LLM providers failed', [
            'providers_tried' => array_map(fn ($p) => $p->value, $order),
            'errors' => $errors,
        ]);

        throw LLMException::allProvidersFailed();
    }

    /**
     * Check health of all LLM providers
     *
     * @return array<string, array<string, mixed>>
     */
    public function healthCheck(): array
    {
        $results = [];

        foreach ($this->providers as $provider => $service) {
            $startTime = microtime(true);

            try {
                $isAvailable = $service->isAvailable();
                $duration = microtime(true) - $startTime;

                $results[$provider->value] = [
                    'available' => $isAvailable,
                    'response_time' => $duration,
                    'status' => $isAvailable ? 'healthy' : 'unhealthy',
                ];
            } catch (\Exception $e) {
                $results[$provider->value] = [
                    'available' => false,
                    'response_time' => null,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get the fallback order with preferred provider first
     *
     * @return array<int, LLMProvider>
     */
    private function getOrderWithPreferred(LLMProvider $preferred): array
    {
        $order = [$preferred];

        foreach ($this->fallbackOrder as $provider) {
            if ($provider !== $preferred) {
                $order[] = $provider;
            }
        }

        return $order;
    }

    /**
     * Set custom fallback order
     *
     * @param  array<int, LLMProvider>  $order
     */
    public function setFallbackOrder(array $order): self
    {
        $this->fallbackOrder = $order;

        return $this;
    }
}
