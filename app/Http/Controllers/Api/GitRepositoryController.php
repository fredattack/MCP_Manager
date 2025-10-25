<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\GitProvider;
use App\Http\Controllers\Controller;
use App\Services\Git\GitRepositoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class GitRepositoryController extends Controller
{
    public function __construct(
        private readonly GitRepositoryService $repositoryService
    ) {}

    /**
     * Sync repositories from provider.
     *
     * POST /api/git/{provider}/repos/sync
     */
    public function sync(Request $request, string $provider): JsonResponse
    {
        $startTime = microtime(true);

        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $providerEnum = GitProvider::from($provider);

            $result = $this->repositoryService->syncRepositories(
                $request->user(),
                $providerEnum
            );

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'synced' => $result['synced'],
                'created' => $result['created'],
                'updated' => $result['updated'],
                'duration_ms' => $duration,
            ]);
        } catch (\Exception $e) {
            Log::error('Repository sync failed', [
                'provider' => $provider,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to sync repositories',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List repositories from database.
     *
     * GET /api/git/{provider}/repos
     */
    public function index(Request $request, string $provider): JsonResponse
    {
        $validator = Validator::make(
            array_merge(['provider' => $provider], $request->all()),
            [
                'provider' => ['required', new Enum(GitProvider::class)],
                'visibility' => 'nullable|in:public,private,internal',
                'archived' => 'nullable|boolean',
                'search' => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $providerEnum = GitProvider::from($provider);

            $filters = [
                'visibility' => $request->input('visibility'),
                'archived' => $request->input('archived'),
                'search' => $request->input('search'),
            ];

            $perPage = (int) $request->input('per_page', 50);

            $repositories = $this->repositoryService->listRepositories(
                $request->user(),
                $providerEnum,
                array_filter($filters, fn ($v) => $v !== null),
                $perPage
            );

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'data' => $repositories->items(),
                'pagination' => [
                    'current_page' => $repositories->currentPage(),
                    'per_page' => $repositories->perPage(),
                    'total' => $repositories->total(),
                    'last_page' => $repositories->lastPage(),
                    'from' => $repositories->firstItem(),
                    'to' => $repositories->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Repository listing failed', [
                'provider' => $provider,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to list repositories',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single repository.
     *
     * GET /api/git/{provider}/repos/{externalId}
     */
    public function show(Request $request, string $provider, string $externalId): JsonResponse
    {
        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $providerEnum = GitProvider::from($provider);

            $repository = $this->repositoryService->getRepository(
                $request->user(),
                $providerEnum,
                $externalId
            );

            if ($repository === null) {
                return response()->json([
                    'error' => 'Repository not found',
                    'message' => "Repository with external_id {$externalId} not found",
                ], 404);
            }

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'data' => $repository,
            ]);
        } catch (\Exception $e) {
            Log::error('Repository fetch failed', [
                'provider' => $provider,
                'external_id' => $externalId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch repository',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh a single repository from provider.
     *
     * POST /api/git/{provider}/repos/{externalId}/refresh
     */
    public function refresh(Request $request, string $provider, string $externalId): JsonResponse
    {
        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $providerEnum = GitProvider::from($provider);

            $repository = $this->repositoryService->getRepository(
                $request->user(),
                $providerEnum,
                $externalId
            );

            if ($repository === null) {
                return response()->json([
                    'error' => 'Repository not found',
                    'message' => "Repository with external_id {$externalId} not found",
                ], 404);
            }

            $repository = $this->repositoryService->refreshRepository(
                $request->user(),
                $repository
            );

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'data' => $repository,
            ]);
        } catch (\Exception $e) {
            Log::error('Repository refresh failed', [
                'provider' => $provider,
                'external_id' => $externalId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to refresh repository',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get repository statistics.
     *
     * GET /api/git/{provider}/repos/stats
     */
    public function stats(Request $request, string $provider): JsonResponse
    {
        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $providerEnum = GitProvider::from($provider);

            $stats = $this->repositoryService->getStatistics(
                $request->user(),
                $providerEnum
            );

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Repository stats failed', [
                'provider' => $provider,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
