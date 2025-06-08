<?php

namespace App\Http\Controllers;

use App\Services\NotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class NotionController extends Controller
{
    /**
     * Fetch Notion pages from the MCP Server.
     */
    public function fetch(NotionService $notionService, \Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $pageId = $request->query('page_id');
            // Ensure we're passing a string or null to the fetchNotionPages method
            $pageId = is_array($pageId) ? null : $pageId;
            $notionPages = $notionService->fetchNotionPages($pageId);

            return response()->json($notionPages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
