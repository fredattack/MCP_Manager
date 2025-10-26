import type { CanvasContent, CanvasLayout, Message } from '@/types/ai/claude.types';
import { useCallback, useEffect, useState } from 'react';

interface UseCanvasSyncOptions {
    autoDetectContent?: boolean;
    defaultLayout?: CanvasLayout;
}

export function useCanvasSync(options: UseCanvasSyncOptions = {}) {
    const { autoDetectContent = true, defaultLayout = 'side' } = options;

    const [selectedMessageId, setSelectedMessageId] = useState<string | null>(null);
    const [canvasContent, setCanvasContent] = useState<CanvasContent | null>(null);
    const [layout, setLayout] = useState<CanvasLayout>(defaultLayout);
    const [isCanvasVisible, setIsCanvasVisible] = useState(true);
    const [isFullscreen, setIsFullscreen] = useState(false);

    const detectContentType = useCallback((content: string): CanvasContent['type'] => {
        // Code blocks detection
        if (content.includes('```') || content.includes('`')) {
            return 'code';
        }

        // Table detection (markdown tables)
        if (content.includes('|') && content.includes('---')) {
            return 'table';
        }

        // Chart/visualization keywords
        const chartKeywords = ['chart', 'graph', 'plot', 'visualization', 'data:', 'statistics'];
        if (chartKeywords.some((keyword) => content.toLowerCase().includes(keyword))) {
            return 'chart';
        }

        // Markdown detection (headers, lists, etc.)
        if (content.includes('#') || content.includes('*') || content.includes('-')) {
            return 'markdown';
        }

        // Default to mixed content
        return 'mixed';
    }, []);

    const extractCodeBlocks = useCallback((content: string) => {
        const codeBlockRegex = /```(\w+)?\n([\s\S]*?)```/g;
        const blocks = [];
        let match;

        while ((match = codeBlockRegex.exec(content)) !== null) {
            blocks.push({
                language: match[1] || 'text',
                code: match[2].trim(),
            });
        }

        return blocks;
    }, []);

    const extractTables = useCallback((content: string) => {
        const lines = content.split('\n');
        const tables = [];
        let currentTable = [];
        let inTable = false;

        for (const line of lines) {
            if (line.includes('|')) {
                inTable = true;
                currentTable.push(line);
            } else if (inTable && line.trim() === '') {
                if (currentTable.length > 0) {
                    tables.push(currentTable.join('\n'));
                    currentTable = [];
                }
                inTable = false;
            } else if (inTable) {
                currentTable.push(line);
            }
        }

        if (currentTable.length > 0) {
            tables.push(currentTable.join('\n'));
        }

        return tables;
    }, []);

    const parseCanvasContent = useCallback(
        (message: Message): CanvasContent => {
            const contentType = autoDetectContent ? detectContentType(message.content) : 'mixed';

            let metadata: CanvasContent['metadata'] = {};

            switch (contentType) {
                case 'code': {
                    const codeBlocks = extractCodeBlocks(message.content);
                    if (codeBlocks.length > 0) {
                        metadata = {
                            language: codeBlocks[0].language,
                            lineNumbers: true,
                            theme: 'dark',
                        };
                    }
                    break;
                }

                case 'table': {
                    const tables = extractTables(message.content);
                    if (tables.length > 0) {
                        metadata = {
                            lineNumbers: false,
                        };
                    }
                    break;
                }

                default:
                    metadata = {
                        lineNumbers: false,
                    };
            }

            return {
                type: contentType,
                content: message.content,
                metadata,
            };
        },
        [autoDetectContent, detectContentType, extractCodeBlocks, extractTables],
    );

    const selectMessage = useCallback(
        (messageId: string, messages: Message[]) => {
            const message = messages.find((msg) => msg.id === messageId);
            if (!message) return;

            setSelectedMessageId(messageId);

            if (message.role === 'assistant') {
                const content = parseCanvasContent(message);
                setCanvasContent(content);
                setIsCanvasVisible(true);
            } else {
                setCanvasContent(null);
            }
        },
        [parseCanvasContent],
    );

    const clearSelection = useCallback(() => {
        setSelectedMessageId(null);
        setCanvasContent(null);
    }, []);

    const toggleCanvas = useCallback(() => {
        setIsCanvasVisible((prev) => !prev);
    }, []);

    const toggleFullscreen = useCallback(() => {
        setIsFullscreen((prev) => !prev);
        if (!isFullscreen) {
            setLayout('fullscreen');
        } else {
            setLayout(defaultLayout);
        }
    }, [isFullscreen, defaultLayout]);

    const updateLayout = useCallback((newLayout: CanvasLayout) => {
        setLayout(newLayout);
        setIsFullscreen(newLayout === 'fullscreen');
    }, []);

    const copyCanvasContent = useCallback(() => {
        if (canvasContent) {
            navigator.clipboard
                .writeText(canvasContent.content)
                .then(() => {
                    // Could add a toast notification here
                    console.log('Canvas content copied to clipboard');
                })
                .catch((err) => {
                    console.error('Failed to copy canvas content:', err);
                });
        }
    }, [canvasContent]);

    const downloadCanvasContent = useCallback(
        (format: 'txt' | 'md' | 'html' = 'txt') => {
            if (!canvasContent) return;

            const timestamp = new Date().toISOString().split('T')[0];
            const filename = `canvas-content-${timestamp}.${format}`;

            let content = canvasContent.content;
            let mimeType = 'text/plain';

            switch (format) {
                case 'md':
                    mimeType = 'text/markdown';
                    break;
                case 'html':
                    content = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Canvas Content</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; }
        pre { background: #f5f5f5; padding: 20px; border-radius: 8px; overflow-x: auto; }
        code { background: #f5f5f5; padding: 2px 4px; border-radius: 4px; }
    </style>
</head>
<body>
    <pre><code>${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
</body>
</html>`;
                    mimeType = 'text/html';
                    break;
            }

            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
        [canvasContent],
    );

    // Auto-sync with latest assistant message
    useEffect(() => {
        const savedLayout = localStorage.getItem('claude-canvas-layout');
        if (savedLayout && savedLayout !== layout) {
            setLayout(savedLayout as CanvasLayout);
        }
    }, [layout]);

    // Save layout preference
    useEffect(() => {
        localStorage.setItem('claude-canvas-layout', layout);
    }, [layout]);

    return {
        selectedMessageId,
        canvasContent,
        layout,
        isCanvasVisible,
        isFullscreen,
        selectMessage,
        clearSelection,
        toggleCanvas,
        toggleFullscreen,
        updateLayout,
        copyCanvasContent,
        downloadCanvasContent,
    };
}
