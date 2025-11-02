<?php

namespace App\Http\Controllers;

use App\Exceptions\McpServerException;
use App\Services\McpServer\McpServiceProxy;
use App\Services\NotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class NotionController extends Controller
{
    public function __construct(
        private readonly McpServiceProxy $mcpProxy
    ) {}

    /**
     * Display Notion integration page.
     */
    public function index(Request $request): Response
    {
        try {
            $databases = $this->mcpProxy->notion($request->user())->listDatabases();

            return Inertia::render('Notion/Index', [
                'databases' => $databases,
            ]);
        } catch (McpServerException $e) {
            return Inertia::render('Notion/Index', [
                'databases' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get a specific database.
     */
    public function getDatabase(Request $request, string $databaseId): JsonResponse
    {
        try {
            $database = $this->mcpProxy->notion($request->user())->getDatabase($databaseId);

            return response()->json($database);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Query a database.
     */
    public function queryDatabase(Request $request, string $databaseId): JsonResponse
    {
        try {
            $filter = $request->input('filter', []);
            $sorts = $request->input('sorts', []);
            $pageSize = $request->input('page_size');

            $results = $this->mcpProxy->notion($request->user())
                ->queryDatabase($databaseId, $filter, $sorts, $pageSize);

            return response()->json($results);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific page.
     */
    public function getPage(Request $request, string $pageId): JsonResponse
    {
        try {
            $page = $this->mcpProxy->notion($request->user())->getPage($pageId);

            return response()->json($page);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new page.
     */
    public function createPage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'properties' => 'required|array',
                'parent_id' => 'nullable|string',
                'parent_type' => 'nullable|string|in:database_id,page_id',
            ]);

            $page = $this->mcpProxy->notion($request->user())->createPage(
                $validated['properties'],
                $validated['parent_id'] ?? null,
                $validated['parent_type'] ?? 'database_id'
            );

            return response()->json($page, 201);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a page.
     */
    public function updatePage(Request $request, string $pageId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'properties' => 'required|array',
            ]);

            $page = $this->mcpProxy->notion($request->user())->updatePage(
                $pageId,
                $validated['properties']
            );

            return response()->json($page);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a page.
     */
    public function deletePage(Request $request, string $pageId): JsonResponse
    {
        try {
            $result = $this->mcpProxy->notion($request->user())->deletePage($pageId);

            return response()->json($result);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search Notion.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string',
                'filter' => 'nullable|string',
            ]);

            $results = $this->mcpProxy->notion($request->user())->search(
                $validated['query'],
                $validated['filter'] ?? null
            );

            return response()->json($results);
        } catch (McpServerException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * LEGACY: Fetch Notion pages from the MCP Server (old endpoint).
     */
    public function fetch(NotionService $notionService, Request $request): JsonResponse
    {
        try {
            $pageId = $request->query('page_id');
            $pageId = is_array($pageId) ? null : $pageId;
            $notionPages = $notionService->fetchNotionPages($pageId);

            return response()->json($notionPages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
