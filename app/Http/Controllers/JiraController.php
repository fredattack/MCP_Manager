<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\JiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JiraController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.integration:jira');
    }

    /**
     * Get the active JIRA integration account for the authenticated user.
     */
    private function getJiraIntegration(Request $request): IntegrationAccount
    {
        /** @var User $user */
        $user = $request->user();

        /** @var IntegrationAccount $integrationAccount */
        $integrationAccount = $user->integrationAccounts()
            ->where('type', IntegrationType::JIRA)
            ->where('status', \App\Enums\IntegrationStatus::ACTIVE)
            ->firstOrFail();

        return $integrationAccount;
    }

    /**
     * List all JIRA projects.
     */
    public function listProjects(Request $request): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $projects = $jiraService->listProjects();

            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific project.
     */
    public function getProject(Request $request, string $projectKey): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $project = $jiraService->getProject($projectKey);

            return response()->json([
                'success' => true,
                'data' => $project,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all boards.
     */
    public function listBoards(Request $request): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $filters */
            $filters = $request->only(['project_key', 'type']);
            $boards = $jiraService->listBoards($filters);

            return response()->json([
                'success' => true,
                'data' => $boards,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific board.
     */
    public function getBoard(Request $request, string $boardId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $board = $jiraService->getBoard($boardId);

            return response()->json([
                'success' => true,
                'data' => $board,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List issues for a board.
     */
    public function listBoardIssues(Request $request, string $boardId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $filters */
            $filters = $request->only(['epic', 'sprint_id']);
            $issues = $jiraService->listBoardIssues($boardId, $filters);

            return response()->json([
                'success' => true,
                'data' => $issues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search issues using JQL.
     */
    public function searchIssues(Request $request): JsonResponse
    {
        $request->validate([
            'jql' => 'required|string',
            'max_results' => 'integer|min:1|max:100',
            'start_at' => 'integer|min:0',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $options */
            $options = $request->only(['max_results', 'start_at']);
            /** @var string $jql */
            $jql = $request->input('jql');
            $issues = $jiraService->searchIssues($jql, $options);

            return response()->json([
                'success' => true,
                'data' => $issues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific issue.
     */
    public function getIssue(Request $request, string $issueKey): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $options */
            $options = $request->only(['expand']);
            $issue = $jiraService->getIssue($issueKey, $options);

            return response()->json([
                'success' => true,
                'data' => $issue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new issue.
     */
    public function createIssue(Request $request): JsonResponse
    {
        $request->validate([
            'project_key' => 'required|string',
            'issue_type' => 'required|string',
            'summary' => 'required|string|max:255',
            'description' => 'string|nullable',
            'assignee' => 'string|nullable',
            'priority' => 'string|nullable',
            'epic_link' => 'string|nullable',
            'sprint_id' => 'string|nullable',
            'story_points' => 'integer|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $data */
            $data = $request->only([
                'project_key', 'issue_type', 'summary', 'description',
                'assignee', 'priority', 'epic_link', 'sprint_id', 'story_points',
            ]);
            $issue = $jiraService->createIssue($data);

            return response()->json([
                'success' => true,
                'data' => $issue,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an issue.
     */
    public function updateIssue(Request $request, string $issueKey): JsonResponse
    {
        $request->validate([
            'summary' => 'string|max:255|nullable',
            'description' => 'string|nullable',
            'assignee' => 'string|nullable',
            'priority' => 'string|nullable',
            'story_points' => 'integer|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $data */
            $data = $request->only([
                'summary', 'description', 'assignee', 'priority', 'story_points',
            ]);
            $issue = $jiraService->updateIssue($issueKey, $data);

            return response()->json([
                'success' => true,
                'data' => $issue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transition an issue to a different status.
     */
    public function transitionIssue(Request $request, string $issueKey): JsonResponse
    {
        $request->validate([
            'transition_id' => 'required|string',
            'comment' => 'string|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $options */
            $options = $request->only(['comment']);
            /** @var string $transitionId */
            $transitionId = $request->input('transition_id');
            $result = $jiraService->transitionIssue(
                $issueKey,
                $transitionId,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available transitions for an issue.
     */
    public function getTransitions(Request $request, string $issueKey): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $transitions = $jiraService->getTransitions($issueKey);

            return response()->json([
                'success' => true,
                'data' => $transitions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign an issue to a user.
     */
    public function assignIssue(Request $request, string $issueKey): JsonResponse
    {
        $request->validate([
            'assignee' => 'required|string',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var string $assignee */
            $assignee = $request->input('assignee');
            $result = $jiraService->assignIssue($issueKey, $assignee);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create an epic.
     */
    public function createEpic(Request $request): JsonResponse
    {
        $request->validate([
            'project_key' => 'required|string',
            'summary' => 'required|string|max:255',
            'description' => 'string|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $options */
            $options = $request->only(['description']);
            /** @var string $projectKey */
            $projectKey = $request->input('project_key');
            /** @var string $summary */
            $summary = $request->input('summary');
            $epic = $jiraService->createEpic(
                $projectKey,
                $summary,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $epic,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get epic progress.
     */
    public function getEpicProgress(Request $request, string $epicKey): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $progress = $jiraService->getEpicProgress($epicKey);

            return response()->json([
                'success' => true,
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get issues in an epic.
     */
    public function getEpicIssues(Request $request, string $epicKey): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $issues = $jiraService->getEpicIssues($epicKey);

            return response()->json([
                'success' => true,
                'data' => $issues,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List sprints for a board.
     */
    public function listSprints(Request $request, string $boardId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $filters */
            $filters = $request->only(['state']);
            $sprints = $jiraService->listSprints($boardId, $filters);

            return response()->json([
                'success' => true,
                'data' => $sprints,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific sprint.
     */
    public function getSprint(Request $request, string $sprintId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $sprint = $jiraService->getSprint($sprintId);

            return response()->json([
                'success' => true,
                'data' => $sprint,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a sprint.
     */
    public function startSprint(Request $request, string $sprintId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'goal' => 'string|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $data */
            $data = $request->only(['name', 'startDate', 'endDate', 'goal']);
            $result = $jiraService->startSprint($sprintId, $data);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a sprint.
     */
    public function completeSprint(Request $request, string $sprintId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $result = $jiraService->completeSprint($sprintId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sprint velocity.
     */
    public function getSprintVelocity(Request $request, string $sprintId): JsonResponse
    {
        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            $velocity = $jiraService->getSprintVelocity($sprintId);

            return response()->json([
                'success' => true,
                'data' => $velocity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a JIRA issue from a Sentry error.
     */
    public function createFromSentry(Request $request): JsonResponse
    {
        $request->validate([
            'sentry_issue_id' => 'required|string',
            'project_key' => 'required|string',
            'issue_type' => 'string|nullable',
        ]);

        try {
            $integrationAccount = $this->getJiraIntegration($request);
            $jiraService = new JiraService($integrationAccount);
            /** @var array<string, mixed> $options */
            $options = $request->only(['issue_type']);
            /** @var string $sentryIssueId */
            $sentryIssueId = $request->input('sentry_issue_id');
            /** @var string $projectKey */
            $projectKey = $request->input('project_key');
            $issue = $jiraService->createFromSentry(
                $sentryIssueId,
                $projectKey,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $issue,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
