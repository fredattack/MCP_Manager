<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM;

use App\Services\LLM\OpenAIService;
use Tests\TestCase;

/**
 * @group llm
 * @group unit
 * @group sprint2
 */
class OpenAIServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.api_key' => 'test-key-for-testing']);
    }

    public function test_service_instantiates_correctly(): void
    {
        $service = new OpenAIService;

        $this->assertInstanceOf(OpenAIService::class, $service);
    }

    public function test_set_max_retries_returns_self(): void
    {
        $service = new OpenAIService;
        $result = $service->setMaxRetries(5);

        $this->assertSame($service, $result);
    }

    public function test_set_timeout_returns_self(): void
    {
        $service = new OpenAIService;
        $result = $service->setTimeout(60.0);

        $this->assertSame($service, $result);
    }
}
