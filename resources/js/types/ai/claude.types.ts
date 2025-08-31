export type AIModel = 'gpt-4' | 'claude-3-opus' | 'mistral-large';
export type ModelType = AIModel; // Alias for backward compatibility
export type MessageRole = 'user' | 'assistant' | 'system';
export type MessageStatus = 'sending' | 'sent' | 'error' | 'streaming';

export interface Message {
  id: string;
  role: MessageRole;
  content: string;
  timestamp: Date;
  status: MessageStatus;
  metadata?: {
    model?: AIModel;
    tokens?: number;
    processingTime?: number;
    temperature?: number;
    error?: string;
  };
}

export interface Conversation {
  id: string;
  title: string;
  messages: Message[];
  createdAt: Date;
  updatedAt: Date;
  metadata?: {
    model?: string;
    totalTokens?: number;
    totalMessages?: number;
  };
}

export interface ChatRequest {
  messages: Array<{
    role: MessageRole;
    content: string;
  }>;
  model?: AIModel;
  temperature?: number;
  max_tokens?: number;
  stream?: boolean;
}

export interface ChatResponse {
  id?: string;
  object?: string;
  created?: number;
  model?: string;
  choices?: Array<{
    index: number;
    message?: {
      role: MessageRole;
      content: string;
    };
    delta?: {
      role?: MessageRole;
      content?: string;
    };
    finish_reason?: string;
  }>;
  usage?: {
    prompt_tokens: number;
    completion_tokens: number;
    total_tokens: number;
  };
  error?: {
    message: string;
    type?: string;
    code?: string;
    status?: number;
  };
}

export interface StreamChunk {
  id?: string;
  object?: string;
  created?: number;
  model?: string;
  choices?: Array<{
    index: number;
    delta?: {
      role?: MessageRole;
      content?: string;
    };
    finish_reason?: string | null;
  }>;
}

export interface ChatStore {
  conversations: Map<string, Conversation>;
  activeConversationId: string | null;
  currentMessages: Message[];
  isLoading: boolean;
  streamingMessageId: string | null;
  selectedMessageId: string | null;
  
  // Actions
  sendMessage: (content: string, model?: string) => Promise<void>;
  regenerateMessage: (messageId: string) => Promise<void>;
  selectMessage: (messageId: string) => void;
  clearConversation: () => void;
  newConversation: () => void;
  deleteConversation: (conversationId: string) => void;
  exportConversation: (format: 'pdf' | 'md' | 'html') => Promise<void>;
  loadConversation: (conversationId: string) => void;
  updateMessage: (messageId: string, updates: Partial<Message>) => void;
}

export interface ChatConfig {
  defaultModel: string;
  defaultTemperature: number;
  defaultMaxTokens: number;
  enableStreaming: boolean;
  autoSave: boolean;
  historyDays: number;
}

export interface CanvasContent {
  type: 'code' | 'markdown' | 'table' | 'chart' | 'mixed';
  content: string;
  metadata?: {
    language?: string;
    fileName?: string;
    lineNumbers?: boolean;
    theme?: string;
    fullContent?: string;
    codeBlocks?: Array<{ language: string; code: string }>;
    rawData?: string;
    chartType?: string;
  };
}

export interface ExportOptions {
  format: 'pdf' | 'md' | 'html' | 'json';
  includeMetadata: boolean;
  includeTimestamps: boolean;
  template?: string;
}

export interface KeyboardShortcut {
  key: string;
  ctrlKey?: boolean;
  shiftKey?: boolean;
  altKey?: boolean;
  metaKey?: boolean;
  action: string;
  description: string;
}

export interface ChatActions {
  copy: (messageId: string) => void;
  edit: (messageId: string) => void;
  delete: (messageId: string) => void;
  regenerate: (messageId: string) => void;
  share: (messageId: string) => void;
  bookmark: (messageId: string) => void;
}

export interface SearchOptions {
  query: string;
  conversationId?: string;
  dateRange?: {
    start: Date;
    end: Date;
  };
  messageRole?: 'user' | 'assistant';
  includeContent: boolean;
  includeMetadata: boolean;
}

export interface SearchResult {
  messageId: string;
  conversationId: string;
  content: string;
  timestamp: Date;
  relevanceScore: number;
  highlights: string[];
}

// Component Props
export interface ChatPanelProps {
  className?: string;
  onMessageSelect?: (messageId: string) => void;
}

export interface CanvasPanelProps {
  className?: string;
  selectedMessage?: Message;
  content?: CanvasContent;
}

export interface ChatInputProps {
  onSend: (content: string) => void;
  disabled?: boolean;
  placeholder?: string;
  className?: string;
}

export interface MessageItemProps {
  message: Message;
  isSelected?: boolean;
  onSelect?: () => void;
  onRegenerate?: () => void;
  onCopy?: () => void;
  onEdit?: () => void;
}

export interface MessageListProps {
  messages: Message[];
  selectedMessageId?: string;
  onMessageSelect?: (messageId: string) => void;
  className?: string;
}

export interface CodeBlockProps {
  code: string;
  language?: string;
  showLineNumbers?: boolean;
  className?: string;
}

export interface MarkdownRendererProps {
  content: string;
  className?: string;
}

export interface TableRendererProps {
  data: unknown[][];
  headers?: string[];
  className?: string;
}

export interface TypingIndicatorProps {
  className?: string;
}

export interface ModelSelectorProps {
  value: AIModel;
  onChange: (model: AIModel) => void;
  className?: string;
}

export interface ChatActionsProps {
  onNewChat: () => void;
  onClearChat: () => void;
  onExport: (format: ExportFormat) => void;
  className?: string;
}

export interface ExportOptionsProps {
  onExport: (format: ExportFormat) => void;
  className?: string;
}

// Utility Types
export type ExportFormat = 'pdf' | 'md' | 'html' | 'json';
export type ConversationSortOrder = 'newest' | 'oldest' | 'mostActive' | 'alphabetical';
export type ThemeMode = 'light' | 'dark' | 'system';
export type CanvasLayout = 'side' | 'bottom' | 'overlay' | 'fullscreen';

// Re-export NLP types
export type { ParsedCommand, ParsedIntent, Entity } from '@/lib/nlp';