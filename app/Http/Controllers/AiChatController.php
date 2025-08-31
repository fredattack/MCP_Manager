<?php

namespace App\Http\Controllers;

use App\Services\McpAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiChatController extends Controller
{
    public function __construct(private readonly McpAuthService $mcpAuthService) {}

    public function chat(Request $request): JsonResponse|StreamedResponse
    {
        $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|string|in:user,assistant,system',
            'messages.*.content' => 'required|string',
            'model' => 'string|in:gpt-4,claude-3-opus,mistral-large',
            'temperature' => 'numeric|between:0,2',
            'max_tokens' => 'integer|between:1,8000',
            'stream' => 'boolean',
        ]);

        $mcpServerUrl = config('services.mcp.server_url', env('MCP_SERVER_URL'));
        if (! is_string($mcpServerUrl)) {
            return response()->json(['error' => 'MCP server URL not configured'], 500);
        }
        $endpoint = $mcpServerUrl.'/claude/chat';

        // Convert messages array to single message string for MCP server
        $messages = $request->input('messages');
        if (! is_array($messages) || $messages === []) {
            return response()->json(['error' => 'Invalid messages format'], 400);
        }
        $lastMessage = end($messages);
        if (! is_array($lastMessage)) {
            return response()->json(['error' => 'Invalid message format'], 400);
        }
        $messageContent = $lastMessage['content'] ?? '';

        $payload = [
            'message' => $messageContent,
            'model' => 'claude-3-opus-20240229',
            'temperature' => $request->input('temperature', 0.7),
            'max_tokens' => $request->input('max_tokens', 4096),
            'stream' => $request->input('stream', false),
        ];

        try {
            $accessToken = $this->mcpAuthService->getAccessToken();
            if ($accessToken === '' || $accessToken === '0') {
                return response()->json([
                    'error' => 'Failed to authenticate with AI service',
                ], 500);
            }

            if ($request->input('stream', false)) {
                return $this->streamResponse($endpoint, $payload, $accessToken);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $payload);

            if ($response->failed()) {
                Log::error('AI Chat API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Failed to communicate with AI service',
                    'status' => $response->status(),
                ], $response->status());
            }

            $responseData = $response->json();
            Log::info('AI Chat API response', ['data' => $responseData]);

            // Standardize response format
            $standardizedResponse = [
                'content' => $responseData['content'] ?? 
                            $responseData['response'] ?? 
                            $responseData['choices'][0]['message']['content'] ?? 
                            'No response received',
                'model' => $request->input('model', 'claude-3-opus'),
                'usage' => $responseData['usage'] ?? null,
                'metadata' => [
                    'timestamp' => now()->toIso8601String(),
                    'request_id' => $responseData['id'] ?? null,
                ],
            ];

            return response()->json($standardizedResponse);

        } catch (\Exception $e) {
            Log::error('AI Chat exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => app()->environment('local') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function streamResponse(string $endpoint, array $payload, string $accessToken): StreamedResponse
    {
        return new StreamedResponse(function () use ($endpoint, $payload, $accessToken): void {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Bearer '.$accessToken,
                        'Content-Type: application/json',
                        'Accept: text/event-stream',
                    ],
                    'content' => json_encode($payload),
                ],
            ]);

            $stream = fopen($endpoint, 'r', false, $context);

            if (! $stream) {
                echo 'data: '.json_encode(['error' => 'Failed to connect to AI service'])."\n\n";

                return;
            }

            while (! feof($stream)) {
                $line = fgets($stream);
                if ($line !== false) {
                    // Parse the SSE line and standardize format
                    if (str_starts_with($line, 'data: ')) {
                        $data = substr($line, 6);
                        $data = trim($data);
                        
                        if ($data === '[DONE]') {
                            echo $line;
                        } else {
                            try {
                                $parsed = json_decode($data, true);
                                if ($parsed !== null) {
                                    // Standardize the chunk format
                                    $standardizedChunk = [
                                        'content' => $parsed['content'] ?? 
                                                   $parsed['choices'][0]['delta']['content'] ?? 
                                                   $parsed['delta']['content'] ?? 
                                                   '',
                                        'type' => 'delta',
                                    ];
                                    echo 'data: '.json_encode($standardizedChunk)."\n\n";
                                } else {
                                    echo $line;
                                }
                            } catch (\Exception $e) {
                                // If parsing fails, pass through original line
                                echo $line;
                            }
                        }
                    } else {
                        echo $line;
                    }
                    ob_flush();
                    flush();
                }
            }

            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
