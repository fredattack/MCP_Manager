export interface Entity {
    type: 'service' | 'action' | 'object' | 'date' | 'time' | 'priority' | 'project' | 'label';
    value: string;
    confidence: number;
    position: [number, number]; // [start, end] position in text
}

export interface ParsedIntent {
    intent: string;
    confidence: number;
    service?: 'todoist' | 'notion' | 'jira' | 'gmail' | 'calendar';
}

export interface CommandPattern {
    id: string;
    pattern: RegExp;
    intent: string;
    service?: string;
    extractors?: EntityExtractor[];
    examples?: string[];
}

export interface EntityExtractor {
    type: Entity['type'];
    pattern: RegExp | ((text: string) => Entity | null);
}

export interface ParsedCommand {
    originalText: string;
    intent: ParsedIntent;
    entities: Entity[];
    confidence: number;
    suggestions?: CommandSuggestion[];
    params?: Record<string, unknown>;
}

export interface CommandSuggestion {
    text: string;
    confidence: number;
    description?: string;
}

export interface Context {
    previousCommands?: ParsedCommand[];
    currentService?: string;
    userPreferences?: Record<string, unknown>;
}

export interface NLPEngineConfig {
    patterns: CommandPattern[];
    entityExtractors: EntityExtractor[];
    confidenceThreshold?: number;
    maxSuggestions?: number;
}