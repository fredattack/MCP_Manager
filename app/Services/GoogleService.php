<?php

namespace App\Services;

use App\Models\IntegrationAccount;
use Illuminate\Support\Facades\Http;

class GoogleService
{
    protected string $accessToken;

    protected ?string $refreshToken;

    public function __construct(?IntegrationAccount $integrationAccount = null)
    {
        if (! $integrationAccount instanceof \App\Models\IntegrationAccount) {
            throw new \Exception('Integration account required');
        }

        $this->accessToken = decrypt($integrationAccount->access_token);
        $this->refreshToken = $integrationAccount->refresh_token ? decrypt($integrationAccount->refresh_token) : null;
    }

    /**
     * Make a request to Google API
     */
    protected function makeGoogleApiRequest(string $url, array $params = [], string $method = 'GET'): array
    {
        $response = Http::withToken($this->accessToken)
            ->when($method === 'POST', fn ($http) => $http->post($url, $params))
            ->when($method === 'PUT', fn ($http) => $http->put($url, $params))
            ->when($method === 'DELETE', fn ($http) => $http->delete($url, $params), fn ($http) => $http->get($url, $params));

        if ($response->failed()) {
            throw new \Exception('Google API request failed: '.$response->body());
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    // Gmail Methods
    public function getGmailStatus(): array
    {
        try {
            $profile = $this->makeGoogleApiRequest('https://gmail.googleapis.com/gmail/v1/users/me/profile');

            return [
                'status' => 'connected',
                'email' => $profile['emailAddress'] ?? null,
                'messages_total' => $profile['messagesTotal'] ?? 0,
                'threads_total' => $profile['threadsTotal'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function listGmailMessages(array $params = []): array
    {
        $queryParams = array_filter([
            'maxResults' => $params['max_results'] ?? 10,
            'q' => $params['query'] ?? null,
            'labelIds' => $params['label_ids'] ?? null,
        ]);

        $url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages?'.http_build_query($queryParams);

        $response = $this->makeGoogleApiRequest($url);

        // Get message details for each message
        $messages = [];
        if (isset($response['messages'])) {
            foreach (array_slice($response['messages'], 0, 20) as $message) {
                try {
                    $messageDetail = $this->getGmailMessage($message['id']);
                    $messages[] = $messageDetail;
                } catch (\Exception) {
                    // Skip messages that can't be fetched
                    continue;
                }
            }
        }

        return [
            'messages' => $messages,
            'nextPageToken' => $response['nextPageToken'] ?? null,
            'resultSizeEstimate' => $response['resultSizeEstimate'] ?? 0,
        ];
    }

    public function getGmailMessage(string $messageId): array
    {
        $url = "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}";
        $message = $this->makeGoogleApiRequest($url);

        // Parse message headers
        $headers = [];
        if (isset($message['payload']['headers'])) {
            foreach ($message['payload']['headers'] as $header) {
                $headers[strtolower((string) $header['name'])] = $header['value'];
            }
        }

        return [
            'id' => $message['id'],
            'threadId' => $message['threadId'],
            'labelIds' => $message['labelIds'] ?? [],
            'snippet' => $message['snippet'] ?? '',
            'subject' => $headers['subject'] ?? 'No Subject',
            'from' => $headers['from'] ?? 'Unknown',
            'to' => $headers['to'] ?? '',
            'date' => $headers['date'] ?? '',
            'internalDate' => $message['internalDate'] ?? null,
        ];
    }

    public function sendGmailMessage(array $messageData): array
    {
        $to = $messageData['to'];
        $subject = $messageData['subject'];
        $body = $messageData['body'];
        $bodyType = $messageData['body_type'] ?? 'text';

        $rawMessage = "To: {$to}\r\n";
        $rawMessage .= "Subject: {$subject}\r\n";
        $rawMessage .= 'Content-Type: text/'.($bodyType === 'html' ? 'html' : 'plain')."; charset=utf-8\r\n\r\n";
        $rawMessage .= $body;

        $encodedMessage = base64_encode($rawMessage);
        $encodedMessage = str_replace(['+', '/', '='], ['-', '_', ''], $encodedMessage);

        return $this->makeGoogleApiRequest(
            'https://gmail.googleapis.com/gmail/v1/users/me/messages/send',
            ['raw' => $encodedMessage],
            'POST'
        );
    }

    public function listGmailLabels(): array
    {
        return $this->makeGoogleApiRequest('https://gmail.googleapis.com/gmail/v1/users/me/labels');
    }

    public function modifyGmailLabels(string $messageId, array $labels): array
    {
        $url = "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}/modify";

        return $this->makeGoogleApiRequest($url, [
            'addLabelIds' => $labels['add_labels'] ?? [],
            'removeLabelIds' => $labels['remove_labels'] ?? [],
        ], 'POST');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchGmailMessages(string $query, array $params = []): array
    {
        return $this->listGmailMessages(array_merge($params, ['query' => $query]));
    }

    // Calendar Methods
    /**
     * @return array<string, mixed>
     */
    public function getCalendarStatus(): array
    {
        try {
            $calendars = $this->makeGoogleApiRequest('https://www.googleapis.com/calendar/v3/users/me/calendarList');

            return [
                'status' => 'connected',
                'calendars_count' => count($calendars['items'] ?? []),
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listCalendars(): array
    {
        return $this->makeGoogleApiRequest('https://www.googleapis.com/calendar/v3/users/me/calendarList');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function listCalendarEvents(array $params = []): array
    {
        $calendarId = $params['calendar_id'] ?? 'primary';
        $queryParams = array_filter([
            'timeMin' => $params['time_min'] ?? null,
            'timeMax' => $params['time_max'] ?? null,
            'maxResults' => $params['max_results'] ?? 10,
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ]);

        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events?".http_build_query($queryParams);

        return $this->makeGoogleApiRequest($url);
    }

    /**
     * @param  array<string, mixed>  $eventData
     * @return array<string, mixed>
     */
    public function createCalendarEvent(array $eventData): array
    {
        $calendarId = $eventData['calendar_id'] ?? 'primary';
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events";

        return $this->makeGoogleApiRequest($url, $eventData, 'POST');
    }

    /**
     * @param  array<string, mixed>  $eventData
     * @return array<string, mixed>
     */
    public function updateCalendarEvent(string $eventId, array $eventData): array
    {
        $calendarId = $eventData['calendar_id'] ?? 'primary';
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}";

        return $this->makeGoogleApiRequest($url, $eventData, 'PUT');
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteCalendarEvent(string $eventId): array
    {
        $calendarId = 'primary';
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$eventId}";

        $this->makeGoogleApiRequest($url, [], 'DELETE');

        return ['success' => true];
    }

    /**
     * @param  array<string, mixed>  $eventData
     * @return array<string, mixed>
     */
    public function checkCalendarConflicts(array $eventData): array
    {
        // Check for conflicts by querying events in the same time range
        $params = [
            'time_min' => $eventData['start']['dateTime'],
            'time_max' => $eventData['end']['dateTime'],
        ];

        $events = $this->listCalendarEvents($params);

        return [
            'conflicts' => count($events['items'] ?? []) > 0,
            'conflicting_events' => $events['items'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTodayEvents(): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        return $this->listCalendarEvents([
            'time_min' => $today->toISOString(),
            'time_max' => $tomorrow->toISOString(),
            'max_results' => 50,
        ]);
    }

    public function getWeekEvents(): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return $this->listCalendarEvents([
            'time_min' => $weekStart->toISOString(),
            'time_max' => $weekEnd->toISOString(),
            'max_results' => 100,
        ]);
    }
}
