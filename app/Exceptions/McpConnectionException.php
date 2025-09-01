<?php

namespace App\Exceptions;

use Exception;

class McpConnectionException extends Exception
{
    /**
     * Create a new MCP connection exception
     */
    public function __construct(string $message = 'MCP connection failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        // Log the exception if needed
        logger()->error('MCP Connection Exception', [
            'message' => $this->getMessage(),
            'trace' => $this->getTraceAsString(),
        ]);
    }

    /**
     * Render the exception as an HTTP response
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'MCP Connection Error',
                'message' => $this->getMessage(),
            ], 503);
        }

        return redirect()->back()
            ->withErrors(['mcp_error' => $this->getMessage()])
            ->withInput();
    }
}