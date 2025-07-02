<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class GmailController extends Controller
{
    protected GoogleService $googleService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('has.integration:'.IntegrationType::GMAIL->value);
    }

    private function getGmailIntegration(): IntegrationAccount
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        /** @var User $user */
        $user = $auth->user();
        
        return $user->integrationAccounts()
            ->where('type', IntegrationType::GMAIL)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public function index(): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $integration = $this->getGmailIntegration();

        $this->googleService = new GoogleService($integration);

        try {
            $status = $this->googleService->getGmailStatus();
            $messages = $this->googleService->listGmailMessages(['max_results' => 20]);
            $labels = $this->googleService->listGmailLabels();

            return Inertia::render('gmail/Index', [
                'status' => $status,
                'messages' => $messages,
                'labels' => $labels,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to load Gmail data: '.$e->getMessage()]);
        }
    }

    public function show(string $messageId): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $integration = $this->getGmailIntegration();

        $this->googleService = new GoogleService($integration);

        try {
            $message = $this->googleService->getGmailMessage($messageId);

            return Inertia::render('gmail/Show', [
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to load message: '.$e->getMessage()]);
        }
    }

    public function send(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'body_type' => 'in:text,html',
        ]);

        $integration = $this->getGmailIntegration();

        $this->googleService = new GoogleService($integration);

        try {
            $result = $this->googleService->sendGmailMessage([
                'to' => $request->input('to'),
                'subject' => $request->input('subject'),
                'body' => $request->input('body'),
                'body_type' => $request->input('body_type', 'text'),
            ]);

            return back()->with('success', 'Email sent successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send email: '.$e->getMessage()]);
        }
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
            'max_results' => 'integer|min:1|max:100',
        ]);

        $integration = $this->getGmailIntegration();

        $this->googleService = new GoogleService($integration);

        try {
            $query = (string) $request->input('query', '');
            $results = $this->googleService->searchGmailMessages(
                $query,
                ['max_results' => (int) $request->input('max_results', 20)]
            );

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed: '.$e->getMessage()], 500);
        }
    }

    public function modifyLabels(Request $request, string $messageId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'add_labels' => 'array',
            'remove_labels' => 'array',
        ]);

        $integration = $this->getGmailIntegration();

        $this->googleService = new GoogleService($integration);

        try {
            $result = $this->googleService->modifyGmailLabels($messageId, [
                'add_labels' => $request->add_labels ?? [],
                'remove_labels' => $request->remove_labels ?? [],
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to modify labels: '.$e->getMessage()], 500);
        }
    }
}
