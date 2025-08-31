<?php

namespace App\Services;

use App\Exceptions\IntegrationException;
use App\Models\IntegrationAccount;

class JiraService
{
    protected McpProxyService $mcpProxy;

    protected IntegrationAccount $integrationAccount;

    /**
     * Create a new JiraService instance.
     */
    public function __construct(IntegrationAccount $integrationAccount)
    {
        if ($integrationAccount->type->value !== 'jira') {
            throw new IntegrationException('Invalid integration type. Expected JIRA integration.');
        }

        $this->integrationAccount = $integrationAccount;
        $this->mcpProxy = new McpProxyService;
    }

    /**
     * Call MCP tool and ensure array return.
     *
     * @param  array<string, mixed>  $params
     * @return array<mixed>
     */
    protected function callTool(string $tool, array $params = []): array
    {
        $result = $this->mcpProxy->callMcpTool(
            $this->integrationAccount,
            $tool,
            $params
        );

        return is_array($result) ? $result : [];
    }

    /**
     * List all JIRA projects.
     *
     * @return array<mixed>
     */
    public function listProjects(): array
    {
        return $this->callTool('jira_list_projects', []);
    }

    /**
     * Get a specific project.
     *
     * @return array<mixed>
     */
    public function getProject(string $projectKey): array
    {
        return $this->callTool('jira_get_project', ['project_key' => $projectKey]);
    }

    /**
     * List all boards.
     *
     * @param  array<string, mixed>  $filters
     * @return array<mixed>
     */
    public function listBoards(array $filters = []): array
    {
        return $this->callTool('jira_list_boards', $filters);
    }

    /**
     * Get a specific board.
     *
     * @return array<mixed>
     */
    public function getBoard(string $boardId): array
    {
        return $this->callTool('jira_get_board', ['board_id' => $boardId]);
    }

    /**
     * List issues for a board.
     *
     * @param  array<string, mixed>  $filters
     * @return array<mixed>
     */
    public function listBoardIssues(string $boardId, array $filters = []): array
    {
        return $this->callTool(
            'jira_list_board_issues',
            array_merge(['board_id' => $boardId], $filters)
        );
    }

    /**
     * Search issues using JQL.
     *
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function searchIssues(string $jql, array $options = []): array
    {
        return $this->callTool(
            'jira_search_issues',
            array_merge(['jql' => $jql], $options)
        );
    }

    /**
     * Get a specific issue.
     *
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function getIssue(string $issueKey, array $options = []): array
    {
        return $this->callTool(
            'jira_get_issue',
            array_merge(['issue_key' => $issueKey], $options)
        );
    }

    /**
     * Create a new issue.
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function createIssue(array $data): array
    {
        $requiredFields = ['project_key', 'issue_type', 'summary'];
        foreach ($requiredFields as $requiredField) {
            if (! isset($data[$requiredField])) {
                throw new IntegrationException("Missing required field: {$requiredField}");
            }
        }

        return $this->callTool('jira_create_issue', $data);
    }

    /**
     * Update an issue.
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function updateIssue(string $issueKey, array $data): array
    {
        return $this->callTool(
            'jira_update_issue',
            array_merge(['issue_key' => $issueKey], $data)
        );
    }

    /**
     * Transition an issue to a different status.
     *
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function transitionIssue(string $issueKey, string $transitionId, array $options = []): array
    {
        return $this->callTool(
            'jira_transition_issue',
            array_merge([
                'issue_key' => $issueKey,
                'transition_id' => $transitionId,
            ], $options)
        );
    }

    /**
     * Get available transitions for an issue.
     *
     * @return array<mixed>
     */
    public function getTransitions(string $issueKey): array
    {
        return $this->callTool('jira_get_transitions', ['issue_key' => $issueKey]);
    }

    /**
     * Assign an issue to a user.
     *
     * @return array<mixed>
     */
    public function assignIssue(string $issueKey, string $assignee): array
    {
        return $this->callTool(
            'jira_assign_issue',
            [
                'issue_key' => $issueKey,
                'assignee' => $assignee,
            ]
        );
    }

    /**
     * Create an epic.
     *
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function createEpic(string $projectKey, string $summary, array $options = []): array
    {
        return $this->callTool(
            'jira_create_epic',
            array_merge([
                'project_key' => $projectKey,
                'summary' => $summary,
            ], $options)
        );
    }

    /**
     * Get epic progress.
     *
     * @return array<mixed>
     */
    public function getEpicProgress(string $epicKey): array
    {
        return $this->callTool('jira_get_epic_progress', ['epic_key' => $epicKey]);
    }

    /**
     * Get issues in an epic.
     *
     * @return array<mixed>
     */
    public function getEpicIssues(string $epicKey): array
    {
        return $this->callTool('jira_get_epic_issues', ['epic_key' => $epicKey]);
    }

    /**
     * List sprints for a board.
     *
     * @param  array<string, mixed>  $filters
     * @return array<mixed>
     */
    public function listSprints(string $boardId, array $filters = []): array
    {
        return $this->callTool(
            'jira_list_sprints',
            array_merge(['board_id' => $boardId], $filters)
        );
    }

    /**
     * Get a specific sprint.
     *
     * @return array<mixed>
     */
    public function getSprint(string $sprintId): array
    {
        return $this->callTool('jira_get_sprint', ['sprint_id' => $sprintId]);
    }

    /**
     * Start a sprint.
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function startSprint(string $sprintId, array $data): array
    {
        $requiredFields = ['name', 'startDate', 'endDate'];
        foreach ($requiredFields as $requiredField) {
            if (! isset($data[$requiredField])) {
                throw new IntegrationException("Missing required field: {$requiredField}");
            }
        }

        return $this->callTool(
            'jira_start_sprint',
            array_merge(['sprint_id' => $sprintId], $data)
        );
    }

    /**
     * Complete a sprint.
     *
     * @return array<mixed>
     */
    public function completeSprint(string $sprintId): array
    {
        return $this->callTool('jira_complete_sprint', ['sprint_id' => $sprintId]);
    }

    /**
     * Get sprint velocity.
     *
     * @return array<mixed>
     */
    public function getSprintVelocity(string $sprintId): array
    {
        return $this->callTool('jira_get_sprint_velocity', ['sprint_id' => $sprintId]);
    }

    /**
     * Create a JIRA issue from a Sentry error.
     *
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function createFromSentry(string $sentryIssueId, string $projectKey, array $options = []): array
    {
        return $this->callTool(
            'jira_create_from_sentry',
            array_merge([
                'sentry_issue_id' => $sentryIssueId,
                'project_key' => $projectKey,
            ], $options)
        );
    }
}
