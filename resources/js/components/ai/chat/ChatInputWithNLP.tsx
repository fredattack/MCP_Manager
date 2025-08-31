import React, { useState } from 'react';
import { ChatInput } from './ChatInput';
import { useNLPEngine } from '@/hooks/use-nlp-engine';
import { NLPCommandInput } from '@/components/ai/nlp/NLPCommandInput';
import { Button } from '@/components/ui/button';
import { Sparkles, MessageSquare } from 'lucide-react';
import type { ModelType, ParsedCommand } from '@/types/ai/claude.types';

interface ChatInputWithNLPProps {
  onSend: (message: string, model?: ModelType) => void;
  onCommand?: (command: ParsedCommand) => void;
  onCancel?: () => void;
  disabled?: boolean;
  placeholder?: string;
  currentModel?: ModelType;
  onModelChange?: (model: ModelType) => void;
  showModelSelector?: boolean;
  maxLength?: number;
  className?: string;
  defaultMode?: 'chat' | 'nlp';
}

export function ChatInputWithNLP({
  onSend,
  onCommand,
  onCancel,
  disabled = false,
  placeholder,
  currentModel = 'gpt-4',
  onModelChange,
  showModelSelector = true,
  maxLength = 8000,
  className = '',
  defaultMode = 'chat'
}: ChatInputWithNLPProps) {
  const [mode, setMode] = useState<'chat' | 'nlp'>(defaultMode);
  const { parseCommand, executeCommand } = useNLPEngine({
    onCommandParsed: onCommand,
  });

  const handleNLPCommand = async (command: ParsedCommand) => {
    // If we have a custom command handler, use it
    if (onCommand) {
      onCommand(command);
      return;
    }

    // Otherwise, execute the command directly
    await executeCommand(command);
  };

  const handleChatMessage = (message: string) => {
    // Check if this looks like a natural language command
    const command = parseCommand(message);
    
    if (command && command.confidence > 0.8) {
      // High confidence command detected, suggest switching to NLP mode
      const confirmSwitch = window.confirm(
        'This looks like a command. Would you like to switch to command mode?'
      );
      
      if (confirmSwitch) {
        setMode('nlp');
        // Re-parse and execute in NLP mode
        handleNLPCommand(command);
        return;
      }
    }

    // Send as regular chat message
    onSend(message, currentModel);
  };

  return (
    <div className={className}>
      {/* Mode toggle */}
      <div className="flex justify-end mb-2">
        <div className="inline-flex rounded-md shadow-sm" role="group">
          <Button
            type="button"
            size="sm"
            variant={mode === 'chat' ? 'default' : 'outline'}
            className="rounded-r-none"
            onClick={() => setMode('chat')}
          >
            <MessageSquare className="w-4 h-4 mr-1" />
            Chat
          </Button>
          <Button
            type="button"
            size="sm"
            variant={mode === 'nlp' ? 'default' : 'outline'}
            className="rounded-l-none"
            onClick={() => setMode('nlp')}
          >
            <Sparkles className="w-4 h-4 mr-1" />
            Command
          </Button>
        </div>
      </div>

      {/* Input component based on mode */}
      {mode === 'chat' ? (
        <ChatInput
          onSend={handleChatMessage}
          onCancel={onCancel}
          disabled={disabled}
          placeholder={placeholder || "Message Claude..."}
          currentModel={currentModel}
          onModelChange={onModelChange}
          showModelSelector={showModelSelector}
          maxLength={maxLength}
        />
      ) : (
        <NLPCommandInput
          onCommand={handleNLPCommand}
          placeholder="Type a command (e.g., 'Create task Review PR for tomorrow')"
          className="w-full"
        />
      )}

      {/* Mode description */}
      <div className="mt-2 text-xs text-gray-500 dark:text-gray-400">
        {mode === 'chat' ? (
          <>Chat mode: Have a conversation with AI models</>
        ) : (
          <>Command mode: Execute actions using natural language (e.g., "Create task", "Search Notion", "Show sprint")</>
        )}
      </div>
    </div>
  );
}