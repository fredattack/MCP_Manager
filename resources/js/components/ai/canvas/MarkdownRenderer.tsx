import React from 'react';

interface MarkdownRendererProps {
  content: string;
  className?: string;
}

export function MarkdownRenderer({ content, className = '' }: MarkdownRendererProps) {
  // Simple markdown parser for basic formatting
  const parseMarkdown = (text: string): React.ReactElement => {
    const lines = text.split('\n');
    const elements: React.ReactElement[] = [];
    let currentListItems: React.ReactElement[] = [];
    let listType: 'ul' | 'ol' | null = null;

    const flushList = () => {
      if (currentListItems.length > 0) {
        elements.push(
          listType === 'ol' ? (
            <ol key={elements.length} className="list-decimal list-inside mb-4 space-y-1">
              {currentListItems}
            </ol>
          ) : (
            <ul key={elements.length} className="list-disc list-inside mb-4 space-y-1">
              {currentListItems}
            </ul>
          )
        );
        currentListItems = [];
        listType = null;
      }
    };

    lines.forEach((line, index) => {
      const trimmedLine = line.trim();

      // Headers
      if (trimmedLine.startsWith('# ')) {
        flushList();
        elements.push(
          <h1 key={index} className="text-3xl font-bold mb-4 text-gray-900 dark:text-gray-100">
            {trimmedLine.slice(2)}
          </h1>
        );
      } else if (trimmedLine.startsWith('## ')) {
        flushList();
        elements.push(
          <h2 key={index} className="text-2xl font-semibold mb-3 text-gray-900 dark:text-gray-100">
            {trimmedLine.slice(3)}
          </h2>
        );
      } else if (trimmedLine.startsWith('### ')) {
        flushList();
        elements.push(
          <h3 key={index} className="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
            {trimmedLine.slice(4)}
          </h3>
        );
      } else if (trimmedLine.startsWith('#### ')) {
        flushList();
        elements.push(
          <h4 key={index} className="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100">
            {trimmedLine.slice(5)}
          </h4>
        );
      }
      // Unordered lists
      else if (trimmedLine.startsWith('- ') || trimmedLine.startsWith('* ')) {
        if (listType !== 'ul') {
          flushList();
          listType = 'ul';
        }
        currentListItems.push(
          <li key={index} className="text-gray-700 dark:text-gray-300">
            {parseInlineMarkdown(trimmedLine.slice(2))}
          </li>
        );
      }
      // Ordered lists
      else if (/^\d+\.\s/.test(trimmedLine)) {
        if (listType !== 'ol') {
          flushList();
          listType = 'ol';
        }
        const content = trimmedLine.replace(/^\d+\.\s/, '');
        currentListItems.push(
          <li key={index} className="text-gray-700 dark:text-gray-300">
            {parseInlineMarkdown(content)}
          </li>
        );
      }
      // Code blocks
      else if (trimmedLine.startsWith('```')) {
        flushList();
        // This is handled elsewhere in the parent component
        elements.push(
          <div key={index} className="bg-gray-100 dark:bg-gray-800 p-3 rounded mb-4 text-sm font-mono">
            Code block detected - see code renderer
          </div>
        );
      }
      // Blockquotes
      else if (trimmedLine.startsWith('> ')) {
        flushList();
        elements.push(
          <blockquote key={index} className="border-l-4 border-blue-500 pl-4 py-2 mb-4 italic text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-950/20">
            {parseInlineMarkdown(trimmedLine.slice(2))}
          </blockquote>
        );
      }
      // Horizontal rules
      else if (trimmedLine === '---' || trimmedLine === '***') {
        flushList();
        elements.push(
          <hr key={index} className="my-6 border-gray-300 dark:border-gray-600" />
        );
      }
      // Empty lines
      else if (trimmedLine === '') {
        flushList();
        elements.push(<br key={index} />);
      }
      // Regular paragraphs
      else {
        flushList();
        elements.push(
          <p key={index} className="mb-4 text-gray-700 dark:text-gray-300 leading-relaxed">
            {parseInlineMarkdown(trimmedLine)}
          </p>
        );
      }
    });

    flushList(); // Flush any remaining list items

    return <>{elements}</>;
  };

  const parseInlineMarkdown = (text: string): React.ReactElement => {
    const parts: (string | React.ReactElement)[] = [];
    let remaining = text;
    let key = 0;

    while (remaining.length > 0) {
      // Bold text **text**
      const boldMatch = remaining.match(/\*\*(.*?)\*\*/);
      if (boldMatch) {
        const beforeBold = remaining.slice(0, boldMatch.index);
        if (beforeBold) parts.push(beforeBold);
        
        parts.push(
          <strong key={key++} className="font-semibold">
            {boldMatch[1]}
          </strong>
        );
        remaining = remaining.slice((boldMatch.index || 0) + boldMatch[0].length);
        continue;
      }

      // Italic text *text*
      const italicMatch = remaining.match(/\*(.*?)\*/);
      if (italicMatch) {
        const beforeItalic = remaining.slice(0, italicMatch.index);
        if (beforeItalic) parts.push(beforeItalic);
        
        parts.push(
          <em key={key++} className="italic">
            {italicMatch[1]}
          </em>
        );
        remaining = remaining.slice((italicMatch.index || 0) + italicMatch[0].length);
        continue;
      }

      // Inline code `code`
      const codeMatch = remaining.match(/`(.*?)`/);
      if (codeMatch) {
        const beforeCode = remaining.slice(0, codeMatch.index);
        if (beforeCode) parts.push(beforeCode);
        
        parts.push(
          <code key={key++} className="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-sm font-mono">
            {codeMatch[1]}
          </code>
        );
        remaining = remaining.slice((codeMatch.index || 0) + codeMatch[0].length);
        continue;
      }

      // Links [text](url)
      const linkMatch = remaining.match(/\[([^\]]+)\]\(([^)]+)\)/);
      if (linkMatch) {
        const beforeLink = remaining.slice(0, linkMatch.index);
        if (beforeLink) parts.push(beforeLink);
        
        parts.push(
          <a
            key={key++}
            href={linkMatch[2]}
            target="_blank"
            rel="noopener noreferrer"
            className="text-blue-600 dark:text-blue-400 hover:underline"
          >
            {linkMatch[1]}
          </a>
        );
        remaining = remaining.slice((linkMatch.index || 0) + linkMatch[0].length);
        continue;
      }

      // If no matches found, add the rest as plain text
      parts.push(remaining);
      break;
    }

    return <>{parts}</>;
  };

  return (
    <div className={`prose prose-sm max-w-none dark:prose-invert ${className}`}>
      {parseMarkdown(content)}
    </div>
  );
}