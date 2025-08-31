import { FC, useState, useEffect, useRef } from 'react';
import { CodeBlock } from './CodeBlock';
import { MarkdownRenderer } from './MarkdownRenderer';
import { TableRenderer } from './TableRenderer';
import { 
  CanvasPanelProps, 
  CanvasContent,
  ExportFormat 
} from '@/types/ai/claude.types';
import { cn } from '@/lib/utils';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

export const CanvasPanel: FC<CanvasPanelProps> = ({
  className,
  selectedMessage,
  content,
}) => {
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [zoom, setZoom] = useState(100);
  const [extractedContent, setExtractedContent] = useState<CanvasContent | null>(null);
  const contentRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (selectedMessage && selectedMessage.role === 'assistant') {
      const extracted = extractCanvasContent(selectedMessage.content);
      setExtractedContent(extracted);
    } else {
      setExtractedContent(null);
    }
  }, [selectedMessage]);

  const extractCanvasContent = (content: string): CanvasContent => {
    // Check for code blocks
    const codeBlockRegex = /```(\w+)?\n([\s\S]*?)```/g;
    const codeMatches = Array.from(content.matchAll(codeBlockRegex));
    
    if (codeMatches.length > 0) {
      // If multiple code blocks, combine them
      if (codeMatches.length > 1) {
        const blocks = codeMatches.map(match => ({
          language: match[1] || 'plaintext',
          code: match[2].trim()
        }));
        
        return {
          type: 'mixed',
          content: content,
          metadata: { codeBlocks: blocks }
        };
      }
      
      // Single code block
      const [, language, code] = codeMatches[0];
      return {
        type: 'code',
        content: code.trim(),
        metadata: { 
          language: language || 'plaintext',
          fullContent: content 
        }
      };
    }

    // Check for tables
    const tableRegex = /\|.*\|[\r\n]+\|[-:\s|]+\|[\r\n]+(\|.*\|[\r\n]+)+/g;
    const tableMatch = content.match(tableRegex);
    
    if (tableMatch) {
      return {
        type: 'table',
        content: tableMatch[0],
        metadata: { fullContent: content }
      };
    }

    // Check for charts/data visualization hints
    const chartKeywords = /\b(chart|graph|plot|diagram|visualization)\b/i;
    if (chartKeywords.test(content)) {
      // Extract potential data
      const dataRegex = /\[[\s\S]*?\]|\{[\s\S]*?\}/g;
      const dataMatches = content.match(dataRegex);
      
      if (dataMatches) {
        return {
          type: 'chart',
          content: content,
          metadata: { 
            rawData: dataMatches[0],
            chartType: 'auto' 
          }
        };
      }
    }

    // Default to markdown
    return {
      type: 'markdown',
      content: content,
    };
  };

  const handleExport = async (format: ExportFormat) => {
    if (!extractedContent) return;

    let exportContent = '';
    let filename = `export_${new Date().toISOString().slice(0, 10)}`;
    let mimeType = 'text/plain';

    switch (format) {
      case 'md':
        exportContent = selectedMessage?.content || '';
        filename += '.md';
        mimeType = 'text/markdown';
        break;
      
      case 'html':
        exportContent = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Claude Assistant Export</title>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; }
    pre { background: #f4f4f4; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; }
    code { background: #f4f4f4; padding: 0.2rem 0.4rem; border-radius: 0.25rem; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    th { background-color: #f4f4f4; }
  </style>
</head>
<body>
  ${convertToHtml(extractedContent)}
</body>
</html>`;
        filename += '.html';
        mimeType = 'text/html';
        break;
      
      case 'pdf':
        await exportToPDF();
        return;
      
      default:
        exportContent = JSON.stringify({
          message: selectedMessage,
          content: extractedContent,
          timestamp: new Date().toISOString()
        }, null, 2);
        filename += '.json';
        mimeType = 'application/json';
    }

    // Create and download file
    const blob = new Blob([exportContent], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  const exportToPDF = async () => {
    if (!contentRef.current || !selectedMessage) return;

    try {
      // Show loading state
      const loadingDiv = document.createElement('div');
      loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
      loadingDiv.innerHTML = '<div class="bg-white p-4 rounded-lg">Generating PDF...</div>';
      document.body.appendChild(loadingDiv);

      // Capture the content as canvas
      const canvas = await html2canvas(contentRef.current, {
        scale: 2,
        logging: false,
        backgroundColor: '#ffffff',
        windowWidth: contentRef.current.scrollWidth,
        windowHeight: contentRef.current.scrollHeight,
      });

      // Calculate PDF dimensions
      const imgWidth = 210; // A4 width in mm
      const pageHeight = 297; // A4 height in mm
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      let heightLeft = imgHeight;

      // Create PDF
      const pdf = new jsPDF('p', 'mm', 'a4');
      let position = 0;

      // Add content to PDF
      const imgData = canvas.toDataURL('image/png');
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;

      // Add additional pages if needed
      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      }

      // Add metadata
      pdf.setProperties({
        title: 'Claude Assistant Export',
        subject: 'Canvas Content',
        author: 'Claude Assistant',
        keywords: 'ai, assistant, export',
        creator: 'MCP Manager',
      });

      // Save the PDF
      const filename = `canvas_export_${new Date().toISOString().slice(0, 10)}.pdf`;
      pdf.save(filename);

      // Remove loading state
      document.body.removeChild(loadingDiv);
    } catch (error) {
      console.error('Error exporting to PDF:', error);
      alert('Failed to export PDF. Please try again.');
    }
  };

  const convertToHtml = (content: CanvasContent): string => {
    switch (content.type) {
      case 'code':
        return `<pre><code class="language-${content.metadata?.language}">${escapeHtml(content.content)}</code></pre>`;
      case 'markdown':
        // Simple markdown to HTML conversion
        return content.content
          .replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
          .replace(/`([^`]+)`/g, '<code>$1</code>')
          .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
          .replace(/\*([^*]+)\*/g, '<em>$1</em>')
          .replace(/^### (.+)$/gm, '<h3>$1</h3>')
          .replace(/^## (.+)$/gm, '<h2>$1</h2>')
          .replace(/^# (.+)$/gm, '<h1>$1</h1>')
          .replace(/\n\n/g, '</p><p>')
          .replace(/^/, '<p>')
          .replace(/$/, '</p>');
      case 'table':
        return `<div class="table-wrapper">${content.content}</div>`;
      default:
        return `<div>${escapeHtml(content.content)}</div>`;
    }
  };

  const escapeHtml = (text: string): string => {
    const map: Record<string, string> = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  };

  const renderContent = () => {
    const canvasContent = content || extractedContent;
    
    if (!canvasContent) {
      return <EmptyCanvasPlaceholder />;
    }

    switch (canvasContent.type) {
      case 'code':
        return (
          <CodeBlock
            code={canvasContent.content}
            language={canvasContent.metadata?.language}
            showLineNumbers={true}
            className="h-full"
          />
        );
      
      case 'markdown':
        return (
          <MarkdownRenderer
            content={canvasContent.content}
            className="prose prose-sm dark:prose-invert max-w-none"
          />
        );
      
      case 'table':
        return (
          <TableRenderer
            content={canvasContent.content}
            className="w-full"
          />
        );
      
      case 'mixed':
        return (
          <div className="space-y-4">
            <MarkdownRenderer
              content={canvasContent.content}
              className="prose prose-sm dark:prose-invert max-w-none"
            />
          </div>
        );
      
      default:
        return (
          <div className="p-4">
            <pre className="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">
              {canvasContent.content}
            </pre>
          </div>
        );
    }
  };

  return (
    <div className={cn(
      "flex flex-col h-full bg-gray-50 dark:bg-gray-800",
      isFullscreen && "fixed inset-0 z-50",
      className
    )}>
      {/* Header */}
      <div className="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Canvas
          </h2>
          <div className="flex items-center gap-2">
            {/* Zoom controls */}
            <div className="flex items-center gap-1 border-r border-gray-200 dark:border-gray-700 pr-2 mr-2">
              <button
                onClick={() => setZoom(Math.max(50, zoom - 10))}
                className="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                title="Zoom out"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                </svg>
              </button>
              <span className="text-xs text-gray-600 dark:text-gray-400 min-w-[3rem] text-center">
                {zoom}%
              </span>
              <button
                onClick={() => setZoom(Math.min(200, zoom + 10))}
                className="p-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                title="Zoom in"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>

            {/* Export menu */}
            <div className="relative group">
              <button
                className="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                title="Export"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
              </button>
              <div className="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                <div className="py-1">
                  {(['md', 'html', 'pdf', 'json'] as ExportFormat[]).map(format => (
                    <button
                      key={format}
                      onClick={() => handleExport(format)}
                      className="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      Export as {format.toUpperCase()}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Fullscreen toggle */}
            <button
              onClick={() => setIsFullscreen(!isFullscreen)}
              className="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
              title={isFullscreen ? "Exit fullscreen" : "Enter fullscreen"}
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {isFullscreen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                )}
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* Content */}
      <div 
        ref={contentRef}
        className="flex-1 overflow-auto p-6"
        style={{ fontSize: `${zoom}%` }}
      >
        {renderContent()}
      </div>
    </div>
  );
};

const EmptyCanvasPlaceholder: FC = () => (
  <div className="flex h-full items-center justify-center">
    <div className="text-center max-w-md">
      <div className="mx-auto mb-4 h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
        <svg className="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">
        Canvas View
      </h3>
      <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Select an assistant message to view formatted content
      </p>
      <p className="mt-4 text-xs text-gray-500 dark:text-gray-500">
        The canvas automatically detects and formats:
      </p>
      <ul className="mt-2 text-xs text-gray-500 dark:text-gray-500 space-y-1">
        <li>• Code blocks with syntax highlighting</li>
        <li>• Markdown formatted text</li>
        <li>• Tables and structured data</li>
        <li>• Charts and visualizations</li>
      </ul>
    </div>
  </div>
);