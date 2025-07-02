import React, { useState } from 'react';

interface CodeBlockProps {
  code: string;
  language?: string;
  fileName?: string;
  showLineNumbers?: boolean;
  theme?: 'light' | 'dark';
  className?: string;
}

export function CodeBlock({
  code,
  language = 'text',
  fileName,
  showLineNumbers = true,
  theme = 'dark',
  className = ''
}: CodeBlockProps) {
  const [copied, setCopied] = useState(false);

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(code);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (error) {
      console.error('Failed to copy code:', error);
    }
  };

  const lines = code.split('\n');

  const getLanguageIcon = (lang: string) => {
    const icons: Record<string, string> = {
      javascript: '📄',
      typescript: '📘',
      python: '🐍',
      java: '☕',
      cpp: '⚡',
      c: '⚡',
      php: '🐘',
      html: '🌐',
      css: '🎨',
      json: '📋',
      xml: '📄',
      yaml: '📄',
      bash: '💻',
      shell: '💻',
      sql: '🗃️',
      markdown: '📝',
    };
    return icons[lang.toLowerCase()] || '📄';
  };

  return (
    <div className={`relative ${className}`}>
      {/* Header */}
      <div className="flex items-center justify-between bg-gray-800 dark:bg-gray-900 px-4 py-2 rounded-t-lg border-b border-gray-700">
        <div className="flex items-center gap-2">
          <span className="text-lg">{getLanguageIcon(language)}</span>
          <span className="text-sm text-gray-300">
            {fileName || `${language} code`}
          </span>
        </div>
        
        <div className="flex items-center gap-2">
          <span className="text-xs text-gray-400">
            {lines.length} lines
          </span>
          <button
            onClick={handleCopy}
            className="flex items-center gap-1 px-2 py-1 text-xs text-gray-300 hover:text-white hover:bg-gray-700 rounded transition-colors"
            title="Copy code"
          >
            {copied ? (
              <>
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                Copied
              </>
            ) : (
              <>
                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2h8z" />
                </svg>
                Copy
              </>
            )}
          </button>
        </div>
      </div>

      {/* Code content */}
      <div className={`
        relative overflow-x-auto
        ${theme === 'dark' ? 'bg-gray-900 text-gray-100' : 'bg-gray-50 text-gray-900'}
      `}>
        <pre className="p-4 text-sm leading-relaxed">
          <code className="block">
            {lines.map((line, index) => (
              <div key={index} className="flex">
                {showLineNumbers && (
                  <span className="inline-block w-8 text-right text-gray-500 select-none mr-4 flex-shrink-0">
                    {index + 1}
                  </span>
                )}
                <span className="flex-1">
                  {line || '\u00A0'} {/* Non-breaking space for empty lines */}
                </span>
              </div>
            ))}
          </code>
        </pre>
      </div>

      {/* Actions bar */}
      <div className="flex items-center justify-between bg-gray-800 dark:bg-gray-900 px-4 py-2 rounded-b-lg border-t border-gray-700">
        <div className="flex items-center gap-2 text-xs text-gray-400">
          <span>Language: {language}</span>
          {fileName && <span>• {fileName}</span>}
        </div>
        
        <div className="flex items-center gap-1">
          <button
            onClick={() => {
              const blob = new Blob([code], { type: 'text/plain' });
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = fileName || `code.${language}`;
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              URL.revokeObjectURL(url);
            }}
            className="p-1 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors"
            title="Download file"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </button>
          
          <button
            onClick={() => window.print()}
            className="p-1 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors"
            title="Print"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  );
}