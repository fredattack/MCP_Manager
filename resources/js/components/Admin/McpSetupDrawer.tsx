import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { BookOpen, Check, Copy, MessageSquare, Terminal } from 'lucide-react';
import { useState } from 'react';

interface McpCredentials {
    username: string;
    token_base64: string;
}

interface McpSetupDrawerProps {
    isOpen: boolean;
    onClose: () => void;
    credentials: McpCredentials;
    userName: string;
    mcpServerUrl?: string;
}

export function McpSetupDrawer({ isOpen, onClose, credentials, userName, mcpServerUrl = 'http://localhost:9978/mcp' }: McpSetupDrawerProps) {
    const [copied, setCopied] = useState<string | null>(null);
    const [selectedPlatform, setSelectedPlatform] = useState<'claude-code' | 'claude-desktop' | 'chatgpt'>('claude-code');

    const copyToClipboard = async (text: string, key: string) => {
        await navigator.clipboard.writeText(text);
        setCopied(key);
        setTimeout(() => setCopied(null), 2000);
    };

    const serverName = 'agentops';

    // Python bridge configuration (recommended - works with existing mcp-server)
    const pythonBridgePath = '/Users/fred/PhpstormProjects/mcp-server/venv/bin/python';
    const pythonScriptPath = '/Users/fred/PhpstormProjects/mcp-server/mcp_remote_client.py';

    const mcpConfig = {
        mcpServers: {
            [serverName]: {
                command: pythonBridgePath,
                args: ['-u', pythonScriptPath],
                env: {
                    MCP_API_URL: mcpServerUrl.replace('/mcp', ''),
                    MCP_USERNAME: credentials.username,
                    MCP_TOKEN: credentials.token_base64,
                },
            },
        },
    };

    const mcpConfigJson = JSON.stringify(mcpConfig, null, 2);

    const platforms = [
        {
            id: 'claude-code' as const,
            name: 'Claude Code',
            icon: Terminal,
            configPath: '~/.claude/claude_desktop_config.json',
            instructions: [
                'Verify that the Python bridge script exists at the path shown in the config',
                'Open your Claude Code configuration file',
                'Add or merge the MCP server configuration (copy the JSON below)',
                'Update the Python paths if they differ on your system',
                'Make sure the Laravel server is running',
                'Restart Claude Code or reload the window',
            ],
        },
        {
            id: 'claude-desktop' as const,
            name: 'Claude Desktop',
            icon: BookOpen,
            configPath: {
                mac: '~/Library/Application Support/Claude/claude_desktop_config.json',
                windows: '%APPDATA%/Claude/claude_desktop_config.json',
                linux: '~/.config/Claude/claude_desktop_config.json',
            },
            instructions: [
                'Verify that the Python bridge script exists at the path shown in the config',
                'Locate your Claude Desktop configuration file (path varies by OS)',
                "If the file doesn't exist, create it",
                'Add or merge the MCP server configuration (copy the JSON below)',
                'Update the Python paths if they differ on your system',
                'Make sure the Laravel server is running',
                'Restart Claude Desktop',
                'Click on the MCP icon to verify the server is connected',
            ],
        },
        {
            id: 'chatgpt' as const,
            name: 'ChatGPT Desktop',
            icon: MessageSquare,
            configPath: 'Configuration may vary',
            instructions: [
                'Verify that the Python bridge script exists',
                'Open ChatGPT Desktop settings',
                'Navigate to the MCP servers section',
                'Add a new server configuration',
                'Update the Python paths if needed',
                'Save and restart ChatGPT Desktop',
            ],
        },
    ];

    const currentPlatform = platforms.find((p) => p.id === selectedPlatform);

    return (
        <Sheet open={isOpen} onOpenChange={onClose}>
            <SheetContent className="w-full overflow-y-auto bg-white sm:max-w-2xl dark:bg-gray-900">
                <SheetHeader>
                    <SheetTitle className="font-monologue-serif text-2xl text-gray-900 dark:text-white">MCP Server Setup</SheetTitle>
                    <SheetDescription className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">
                        Configure this MCP server in your preferred AI tool
                    </SheetDescription>
                </SheetHeader>

                <div className="mt-6 space-y-6">
                    {/* Platform Selection */}
                    <div>
                        <label className="font-monologue-mono mb-3 block text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                            Select Platform
                        </label>
                        <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            {platforms.map((platform) => {
                                const Icon = platform.icon;
                                return (
                                    <button
                                        key={platform.id}
                                        onClick={() => setSelectedPlatform(platform.id)}
                                        className={`flex items-center gap-2 rounded-lg border p-3 transition-colors ${
                                            selectedPlatform === platform.id
                                                ? 'border-cyan-500 bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-400'
                                                : 'dark:hover:bg-gray-750 border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        }`}
                                    >
                                        <Icon className="h-5 w-5" />
                                        <span className="font-monologue-mono text-sm">{platform.name}</span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* MCP Configuration */}
                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <label className="font-monologue-mono text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                Configuration JSON
                            </label>
                            <button
                                onClick={() => copyToClipboard(mcpConfigJson, 'config')}
                                className="flex items-center gap-1 rounded bg-gray-200 px-2 py-1 text-xs hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                {copied === 'config' ? (
                                    <>
                                        <Check className="h-3 w-3" />
                                        Copied!
                                    </>
                                ) : (
                                    <>
                                        <Copy className="h-3 w-3" />
                                        Copy
                                    </>
                                )}
                            </button>
                        </div>
                        <pre className="font-monologue-mono overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-green-400">{mcpConfigJson}</pre>
                    </div>

                    {/* Platform-Specific Instructions */}
                    {currentPlatform && (
                        <div>
                            <h3 className="font-monologue-serif mb-3 text-lg text-gray-900 dark:text-white">
                                Setup Instructions for {currentPlatform.name}
                            </h3>

                            {/* Config Path */}
                            <div className="mb-4 rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                                <p className="font-monologue-mono mb-1 text-xs tracking-wide text-blue-700 uppercase dark:text-blue-400">
                                    Configuration File Location
                                </p>
                                {typeof currentPlatform.configPath === 'string' ? (
                                    <code className="font-monologue-mono text-sm text-blue-900 dark:text-blue-300">{currentPlatform.configPath}</code>
                                ) : (
                                    <div className="space-y-1">
                                        <div>
                                            <span className="font-monologue-mono text-xs text-blue-700 dark:text-blue-400">macOS: </span>
                                            <code className="font-monologue-mono text-sm text-blue-900 dark:text-blue-300">
                                                {currentPlatform.configPath.mac}
                                            </code>
                                        </div>
                                        <div>
                                            <span className="font-monologue-mono text-xs text-blue-700 dark:text-blue-400">Windows: </span>
                                            <code className="font-monologue-mono text-sm text-blue-900 dark:text-blue-300">
                                                {currentPlatform.configPath.windows}
                                            </code>
                                        </div>
                                        <div>
                                            <span className="font-monologue-mono text-xs text-blue-700 dark:text-blue-400">Linux: </span>
                                            <code className="font-monologue-mono text-sm text-blue-900 dark:text-blue-300">
                                                {currentPlatform.configPath.linux}
                                            </code>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Step-by-step Instructions */}
                            <ol className="space-y-3">
                                {currentPlatform.instructions.map((instruction, index) => (
                                    <li key={index} className="flex gap-3">
                                        <span className="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-cyan-100 text-xs font-semibold text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400">
                                            {index + 1}
                                        </span>
                                        <span className="font-monologue-mono flex-1 pt-0.5 text-sm text-gray-700 dark:text-gray-300">
                                            {instruction}
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    )}

                    {/* Server Details */}
                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h4 className="font-monologue-mono mb-3 text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">Server Details</h4>
                        <dl className="space-y-2">
                            <div className="flex justify-between">
                                <dt className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">Server Name:</dt>
                                <dd className="font-monologue-mono text-sm text-gray-900 dark:text-white">{serverName}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">URL:</dt>
                                <dd className="font-monologue-mono text-sm text-gray-900 dark:text-white">{mcpServerUrl}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="font-monologue-mono text-sm text-gray-600 dark:text-gray-400">Auth Type:</dt>
                                <dd className="font-monologue-mono text-sm text-gray-900 dark:text-white">Basic Auth</dd>
                            </div>
                        </dl>
                    </div>

                    {/* Important Notes */}
                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/50 dark:bg-yellow-900/20">
                        <h4 className="font-monologue-serif mb-2 text-sm font-semibold text-yellow-900 dark:text-yellow-400">
                            Important Setup Steps
                        </h4>
                        <ul className="font-monologue-mono list-inside list-disc space-y-1 text-xs text-yellow-800 dark:text-yellow-300">
                            <li>Verify the Python bridge script exists at the path shown in the config</li>
                            <li>Make sure Python 3 is installed with the required dependencies</li>
                            <li>Update the Python and script paths if they differ on your system</li>
                            <li>The Laravel server must be running at {mcpServerUrl.replace('/mcp', '')}</li>
                            <li>Keep your credentials secure and never share them publicly</li>
                            <li>Restart Claude Desktop after configuration changes</li>
                        </ul>
                    </div>

                    {/* Bridge Script Info */}
                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-900/20">
                        <h4 className="font-monologue-serif mb-2 text-sm font-semibold text-blue-900 dark:text-blue-400">About the Python Bridge</h4>
                        <p className="font-monologue-mono text-xs text-blue-800 dark:text-blue-300">
                            The mcp_remote_client.py script converts STDIO communication (used by Claude Desktop) into HTTP requests to your Laravel
                            MCP endpoint. Your credentials are passed as environment variables and used to authenticate with the server.
                        </p>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    );
}
