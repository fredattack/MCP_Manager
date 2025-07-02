import React, { useEffect, useRef } from 'react';
import { MessageItem } from './MessageItem';
import type { Message } from '@/types/ai/claude.types';

interface MessageListProps {
  messages: Message[];
  selectedMessageId?: string | null;
  onSelectMessage?: (messageId: string) => void;
  onRegenerateMessage?: (messageId: string) => void;
  onEditMessage?: (messageId: string) => void;
  onCopyMessage?: (content: string) => void;
  autoScroll?: boolean;
  className?: string;
}

export function MessageList({
  messages,
  selectedMessageId,
  onSelectMessage,
  onRegenerateMessage,
  onEditMessage,
  onCopyMessage,
  autoScroll = true,
  className = ''
}: MessageListProps) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Auto-scroll to bottom when new messages arrive
  useEffect(() => {
    if (autoScroll && messagesEndRef.current) {
      messagesEndRef.current.scrollIntoView({ 
        behavior: 'smooth',
        block: 'end'
      });
    }
  }, [messages, autoScroll]);

  const handleCopyMessage = async (content: string) => {
    try {
      await navigator.clipboard.writeText(content);
      if (onCopyMessage) {
        onCopyMessage(content);
      }
    } catch (error) {
      console.error('Failed to copy message:', error);
    }
  };

  if (messages.length === 0) {
    return (
      <div className={`flex items-center justify-center h-full ${className}`}>
        <div className="text-center">
          <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
            Start a conversation
          </h3>
          <p className="text-gray-600 dark:text-gray-400 max-w-sm mx-auto">
            Ask Claude anything. You can request code, explanations, creative writing, analysis, and more.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div 
      ref={scrollRef}
      className={`flex-1 overflow-y-auto ${className}`}
    >
      <div className="divide-y divide-gray-200 dark:divide-gray-700">
        {messages.map((message) => (
          <MessageItem
            key={message.id}
            message={message}
            isSelected={selectedMessageId === message.id}
            onSelect={onSelectMessage}
            onRegenerate={onRegenerateMessage}
            onEdit={onEditMessage}
            onCopy={handleCopyMessage}
          />
        ))}
      </div>
      
      {/* Scroll target */}
      <div ref={messagesEndRef} />
    </div>
  );
}