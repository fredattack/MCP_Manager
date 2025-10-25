<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\GitProvider;
use App\Http\Controllers\Controller;
use App\Jobs\CloneRepositoryJob;
use App\Models\GitClone;
use App\Models\GitConnection;
use App\Services\Git\GitCloneService;
use App\Services\Git\GitRepositoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class GitCloneController extends Controller
{
    public function __construct(
        private readonly GitCloneService $cloneService,
        private readonly GitRepositoryService $repositoryService
    ) {}

    /**
     * Clone a repository.
     *
     * POST /api/git/{provider}/repos/{externalId}/clone
     */
    public function clone(Request $request, string $provider, string $externalId): JsonResponse
    {
        $validator = Validator::make(
            array_merge(['provider' => $provider], $request->all()),
            [
                'provider' => ['required', new Enum(GitProvider::class)],
                'ref' => 'nullable|string|max:255',
                'storage' => 'nullable|in:local,s3',
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

            // Get repository
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

            // Get active connection
            $connection = GitConnection::where('user_id', $request->user()->id)
                ->where('provider', $providerEnum)
                ->active()
                ->firstOrFail();

            $ref = $request->input('ref', $repository->default_branch);
            $storage = $request->input('storage', config('services.git.clone_storage', 'local'));

            // Initialize clone
            $clone = $this->cloneService->initializeClone($repository, $ref, $storage);

            // Dispatch job
            CloneRepositoryJob::dispatch($clone, $connection);

            Log::info('Clone job dispatched', [
                'clone_id' => $clone->id,
                'repository' => $repository->full_name,
                'ref' => $ref,
                'storage' => $storage,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clone job dispatched',
                'clone' => [
                    'id' => $clone->id,
                    'repository' => $repository->full_name,
                    'ref' => $clone->ref,
                    'storage' => $clone->storage_driver,
                    'status' => $clone->status->value,
                    'created_at' => $clone->created_at->toIso8601String(),
                ],
            ], 202);
        } catch (\Exception $e) {
            Log::error('Clone initiation failed', [
                'provider' => $provider,
                'external_id' => $externalId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to initiate clone',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List clones for a repository.
     *
     * GET /api/git/{provider}/repos/{externalId}/clones
     */
    public function index(Request $request, string $provider, string $externalId): JsonResponse
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
                ], 404);
            }

            $clones = GitClone::where('repository_id', $repository->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'repository' => $repository->full_name,
                'data' => $clones->items(),
                'pagination' => [
                    'current_page' => $clones->currentPage(),
                    'per_page' => $clones->perPage(),
                    'total' => $clones->total(),
                    'last_page' => $clones->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Clone listing failed', [
                'provider' => $provider,
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to list clones',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single clone.
     *
     * GET /api/git/clones/{cloneId}
     */
    public function show(Request $request, int $cloneId): JsonResponse
    {
        try {
            $clone = GitClone::with('repository')
                ->whereHas('repository', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->findOrFail($cloneId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $clone->id,
                    'repository' => $clone->repository->full_name,
                    'ref' => $clone->ref,
                    'storage_driver' => $clone->storage_driver,
                    'artifact_path' => $clone->artifact_path,
                    'size_bytes' => $clone->size_bytes,
                    'size_formatted' => $clone->getFormattedSize(),
                    'duration_ms' => $clone->duration_ms,
                    'duration_formatted' => $clone->getFormattedDuration(),
                    'status' => $clone->status->value,
                    'error' => $clone->error,
                    'created_at' => $clone->created_at->toIso8601String(),
                    'updated_at' => $clone->updated_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Clone fetch failed', [
                'clone_id' => $cloneId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Clone not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
