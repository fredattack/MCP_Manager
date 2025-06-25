<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Services\NotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NotionIntegrationController extends Controller
{
    /**
     * Get the user's active Notion integration account.
     */
    protected function getNotionIntegration(): ?IntegrationAccount
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        /** @var IntegrationAccount|null $integration */
        $integration = $user->integrationAccounts()
            ->where('type', IntegrationType::NOTION)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        return $integration;
    }

    /**
     * Fetch the Notion pages tree.
     */
    public function pagesTree(Request $request): JsonResponse
    {
        $integration = $this->getNotionIntegration();

        if (! $integration instanceof \App\Models\IntegrationAccount) {
            return response()->json(['message' => 'No active Notion integration found'], 403);
        }

        $notionService = new NotionService($integration);

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

    /**
     * Fetch Notion databases.
     */
    public function databases(): JsonResponse
    {
        $integration = $this->getNotionIntegration();

        if (! $integration instanceof \App\Models\IntegrationAccount) {
            return response()->json(['message' => 'No active Notion integration found'], 403);
        }

        $notionService = new NotionService($integration);

        try {
            $databases = $notionService->fetchNotionDatabases();

            return response()->json($databases);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch a specific Notion page.
     */
    public function page(string $pageId): JsonResponse
    {
        $integration = $this->getNotionIntegration();

        if (! $integration instanceof \App\Models\IntegrationAccount) {
            return response()->json(['message' => 'No active Notion integration found'], 403);
        }

        $notionService = new NotionService($integration);

        try {
            $page = $notionService->fetchNotionPage($pageId);

            return response()->json($page);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch blocks for a specific Notion page.
     */
    public function blocks(string $pageId): JsonResponse
    {
        $integration = $this->getNotionIntegration();

        if (! $integration instanceof \App\Models\IntegrationAccount) {
            return response()->json(['message' => 'No active Notion integration found'], 403);
        }

        $notionService = new NotionService($integration);

        try {
            $blocks = $notionService->fetchNotionBlocks($pageId);

            return response()->json($blocks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
