<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM;

use App\Services\LLM\MistralService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase as BaseTestCase;

/**
 * @group llm
 * @group unit
 * @group sprint2
 */
class MistralServiceTest extends BaseTestCase
{
    public function test_service_instantiates_correctly(): void
    {
        config(['services.mistral.api_key' => 'test-key']);
        config(['services.mistral.model' => 'mistral-large-latest']);

        $service = new MistralService;

        $this->assertInstanceOf(MistralService::class, $service);
    }

    public function test_set_max_retries_returns_self(): void
    {
        config(['services.mistral.api_key' => 'test-key']);

        $service = new MistralService;
        $result = $service->setMaxRetries(5);

        $this->assertSame($service, $result);
    }

    public function test_set_timeout_returns_self(): void
    {
        config(['services.mistral.api_key' => 'test-key']);

        $service = new MistralService;
        $result = $service->setTimeout(60.0);

        $this->assertSame($service, $result);
    }

    public function test_chat_sends_correct_request_structure(): void
    {
        config(['services.mistral.api_key' => 'test-key']);
        config(['services.mistral.model' => 'mistral-large-latest']);

        Http::fake([
            'api.mistral.ai/*' => Http::response([
                'id' => 'chatcmpl-123',
                'object' => 'chat.completion',
                'created' => time(),
                'model' => 'mistral-large-latest',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Test response',
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                    'total_tokens' => 30,
                ],
            ], 200),
        ]);

        $service = new MistralService;
        $result = $service->chat([
            ['role' => 'user', 'content' => 'Hello'],
        ]);

        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals('Test response', $result['content']);
    }
}
