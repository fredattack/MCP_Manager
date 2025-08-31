<?php

namespace App\Http\Controllers;

use App\Services\NaturalLanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class NaturalLanguageController extends BaseController
{
    public function __construct(private readonly NaturalLanguageService $naturalLanguageService) {}

    public function processCommand(Request $request): JsonResponse
    {
        $request->validate([
            'command' => 'required|string|max:500',
        ]);

        $command = $request->input('command');
        $userId = Auth::id();

        $result = $this->naturalLanguageService->processCommand($command, $userId);

        return response()->json($result);
    }

    public function getSuggestions(): JsonResponse
    {
        $suggestions = [
            'Todoist' => [
                'Affiche mes tâches du jour',
                'Montre mes tâches de demain',
                'Liste mes tâches de la semaine',
                'Affiche mes projets',
                'Ajoute une tâche "Faire les courses"',
                'Crée une tâche "Appeler le médecin"',
            ],
            'Notion' => [
                'Affiche mes pages',
                'Montre mes pages Notion',
                'Liste mes bases de données',
                'Ouvre la page "Mes notes"',
                'Affiche la page "Journal"',
                'Recherche "réunion" dans Notion',
                'Cherche "projet" dans mes pages',
            ],
        ];

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    public function getCommandHistory(): JsonResponse
    {
        $user = Auth::user();

        $history = cache()->get("nl_history_{$user->id}", []);

        return response()->json([
            'success' => true,
            'history' => array_reverse($history),
        ]);
    }

    private function saveToHistory(string $command, array $result): void
    {
        $user = Auth::user();
        $historyKey = "nl_history_{$user->id}";

        $history = cache()->get($historyKey, []);

        $history[] = [
            'command' => $command,
            'result' => $result,
            'timestamp' => now()->toISOString(),
        ];

        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        cache()->put($historyKey, $history, now()->addDays(7));
    }
}
