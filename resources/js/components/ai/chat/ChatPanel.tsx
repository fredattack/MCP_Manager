import React, { useState, useCallback } from 'react';
import { MessageList } from './MessageList';
import { ChatInput } from './ChatInput';
import { TypingIndicator } from './TypingIndicator';
import { useClaudeChat } from '@/hooks/ai/use-claude-chat';
import type { ModelType } from '@/types/ai/claude.types';

interface ChatPanelProps {
  onSelectMessage?: (messageId: string) => void;
  selectedMessageId?: string | null;
  className?: string;
}

export function ChatPanel({ 
  onSelectMessage, 
  selectedMessageId,
  className = '' 
}: ChatPanelProps) {
  const {
    messages,
    isLoading,
    error,
    streamingMessageId,
    currentModel,
    setCurrentModel,
    sendMessage,
    regenerateMessage,
    clearMessages,
    cancelGeneration,
  } = useClaudeChat({
    enableStreaming: true,
    autoSave: true,
  });

  const [showClearConfirm, setShowClearConfirm] = useState(false);

  const handleSendMessage = useCallback(async (content: string, model?: ModelType) => {
    await sendMessage(content, model);
  }, [sendMessage]);

  const handleRegenerateMessage = useCallback(async (messageId: string) => {
    await regenerateMessage(messageId);
  }, [regenerateMessage]);

  const handleClearMessages = useCallback(() => {
    clearMessages();
    setShowClearConfirm(false);
  }, [clearMessages]);

  const handleEditMessage = useCallback((messageId: string) => {
    // TODO: Implement edit functionality
    console.log('Edit message:', messageId);
  }, []);

  const handleCopyMessage = useCallback(() => {
    // Optional: Show toast notification
    console.log('Message copied to clipboard');
  }, []);

  return (
    <div className={`flex flex-col h-full bg-white dark:bg-gray-900 ${className}`}>
      {/* Header */}
      <div className="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div>
          <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Claude Assistant
          </h2>
          <p className="text-sm text-gray-600 dark:text-gray-400">
            Model: {currentModel} • {messages.length} messages
          </p>
        </div>

        <div className="flex items-center gap-2">
          {/* New conversation */}
          <button
            onClick={() => window.location.reload()} // Simple refresh for now
            className="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
            title="New conversation"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
          </button>

          {/* Clear conversation */}
          {messages.length > 0 && (
            <button
              onClick={() => setShowClearConfirm(true)}
              className="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
              title="Clear conversation"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          )}
        </div>
      </div>

      {/* Error banner */}
      {error && (
        <div className="bg-red-50 dark:bg-red-950/20 border-b border-red-200 dark:border-red-800 px-6 py-3">
          <div className="flex items-center gap-2">
            <svg className="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span className="text-sm text-red-700 dark:text-red-300">{error}</span>
            <button
              onClick={() => window.location.reload()}
              className="ml-auto text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200"
            >
              Retry
            </button>
          </div>
        </div>
      )}

      {/* Messages */}
      <div className="flex-1 overflow-hidden">
        <MessageList
          messages={messages}
          selectedMessageId={selectedMessageId}
          onSelectMessage={onSelectMessage}
          onRegenerateMessage={handleRegenerateMessage}
          onEditMessage={handleEditMessage}
          onCopyMessage={handleCopyMessage}
          className="h-full"
        />
        
        {/* Typing indicator */}
        {isLoading && streamingMessageId && (
          <TypingIndicator className="border-t border-gray-200 dark:border-gray-700" />
        )}
      </div>

      {/* Input */}
      <div className="border-t border-gray-200 dark:border-gray-700 p-4">
        <ChatInput
          onSend={handleSendMessage}
          onCancel={cancelGeneration}
          disabled={isLoading}
          currentModel={currentModel}
          onModelChange={setCurrentModel}
          showModelSelector={true}
        />
      </div>

      {/* Clear confirmation dialog */}
      {showClearConfirm && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md mx-4">
            <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
              Clear Conversation
            </h3>
            <p className="text-gray-600 dark:text-gray-400 mb-6">
              This will permanently delete all messages in this conversation. This action cannot be undone.
            </p>
            <div className="flex justify-end gap-3">
              <button
                onClick={() => setShowClearConfirm(false)}
                className="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={handleClearMessages}
                className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}