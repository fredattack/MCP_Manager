<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CalendarController extends Controller
{
    protected GoogleService $googleService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('has.integration:'.IntegrationType::CALENDAR->value);
    }

    private function getGoogleIntegration(): IntegrationAccount
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        /** @var User $user */
        $user = $auth->user();

        return $user->integrationAccounts()
            ->where('type', IntegrationType::CALENDAR)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function index(): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $status = $this->googleService->getCalendarStatus();
            $calendars = $this->googleService->listCalendars();
            $todayEvents = $this->googleService->getTodayEvents();

            return Inertia::render('calendar/Index', [
                'status' => $status,
                'calendars' => $calendars,
                'todayEvents' => $todayEvents,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to load Calendar data: '.$e->getMessage()]);
        }
    }

    public function events(Request $request): \Illuminate\Http\JsonResponse
    {
        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $params = [];

            if ($request->has('calendar_id')) {
                $params['calendar_id'] = $request->input('calendar_id');
            }

            if ($request->has('time_min')) {
                $params['time_min'] = $request->input('time_min');
            }

            if ($request->has('time_max')) {
                $params['time_max'] = $request->input('time_max');
            }

            $events = $this->googleService->listCalendarEvents($params);

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load events: '.$e->getMessage()], 500);
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'summary' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|array',
            'start.dateTime' => 'required|date',
            'start.timeZone' => 'nullable|string',
            'end' => 'required|array',
            'end.dateTime' => 'required|date|after:start.dateTime',
            'end.timeZone' => 'nullable|string',
            'attendees' => 'nullable|array',
            'attendees.*.email' => 'email',
        ]);

        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $result = $this->googleService->createCalendarEvent($request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create event: '.$e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $eventId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'summary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start' => 'nullable|array',
            'start.dateTime' => 'nullable|date',
            'start.timeZone' => 'nullable|string',
            'end' => 'nullable|array',
            'end.dateTime' => 'nullable|date',
            'end.timeZone' => 'nullable|string',
            'attendees' => 'nullable|array',
            'attendees.*.email' => 'email',
        ]);

        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $result = $this->googleService->updateCalendarEvent($eventId, $request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update event: '.$e->getMessage()], 500);
        }
    }

    public function destroy(string $eventId): \Illuminate\Http\JsonResponse
    {
        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $result = $this->googleService->deleteCalendarEvent($eventId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete event: '.$e->getMessage()], 500);
        }
    }

    public function checkConflicts(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'start' => 'required|array',
            'start.dateTime' => 'required|date',
            'end' => 'required|array',
            'end.dateTime' => 'required|date|after:start.dateTime',
        ]);

        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $result = $this->googleService->checkCalendarConflicts($request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to check conflicts: '.$e->getMessage()], 500);
        }
    }

    public function weekEvents(): \Illuminate\Http\JsonResponse
    {
        $integrationAccount = $this->getGoogleIntegration();

        $this->googleService = new GoogleService($integrationAccount);

        try {
            $events = $this->googleService->getWeekEvents();

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load week events: '.$e->getMessage()], 500);
        }
    }
}
