<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdobeFetchController extends Controller
{
    /**
     * Trigger the adobe-fetch script execution.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function execute()
    {
        // Start the script in the background from the project root
     Process::path(storage_path())
            ->start('npm run fetch:adobe');
        return response()->json(['message' => 'Adobe fetch script started successfully']);
    }

    /**
     * Stream the adobe-fetch logs.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamLogs()
    {
        $logPath = storage_path('logs/adobe-fetch.log');

        // Create the logs directory if it doesn't exist
        $logDir = storage_path('logs');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Create the log file if it doesn't exist
        if (!file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        // Return a streamed response
        return new StreamedResponse(function () use ($logPath) {
            // Open the log file
            $file = fopen($logPath, 'r');

            // Move to the end of the file
            fseek($file, 0, SEEK_END);

            // Keep checking for new content
            $lastSize = filesize($logPath);
            $inactivityCounter = 0;
            $maxInactivity = 20; // Close after 10 seconds of inactivity

            while ($inactivityCounter < $maxInactivity) {
                clearstatcache(true, $logPath);
                $currentSize = filesize($logPath);

                // If file size has changed, read new content
                if ($currentSize > $lastSize) {
                    $inactivityCounter = 0; // Reset inactivity counter

                    // Read new content
                    while (($line = fgets($file)) !== false) {
                        echo "data: " . $line . "\n\n";
                        ob_flush();
                        flush();
                    }

                    $lastSize = $currentSize;
                } else {
                    // No new content, increment inactivity counter
                    $inactivityCounter++;
                }

                // Wait before checking again
                usleep(500000); // 0.5 seconds
            }

            // Send a completion message
            echo "data: [Completed] Adobe fetch process has finished.\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
