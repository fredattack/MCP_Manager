import {
    Entity,
    ParsedCommand,
    CommandPattern,
    NLPEngineConfig,
    Context,
    CommandSuggestion,
} from './types';
import { defaultExtractors } from './entity-extractors';
import { allPatterns } from './command-patterns';

export class NLPEngine {
    private patterns: CommandPattern[];
    private extractors: typeof defaultExtractors;
    private confidenceThreshold: number;
    private maxSuggestions: number;

    constructor(config?: Partial<NLPEngineConfig>) {
        this.patterns = config?.patterns || allPatterns;
        this.extractors = config?.entityExtractors || defaultExtractors;
        this.confidenceThreshold = config?.confidenceThreshold || 0.7;
        this.maxSuggestions = config?.maxSuggestions || 3;
    }

    /**
     * Parse a natural language command
     */
    parse(text: string, context?: Context): ParsedCommand | null {
        // Context will be used in future enhancements
        void context;
        const normalizedText = this.normalizeText(text);
        
        // Extract entities from the text
        const entities = this.extractEntities(normalizedText);
        
        // Find matching command patterns
        const matchedPattern = this.findBestPattern(normalizedText, entities);
        
        if (!matchedPattern) {
            // No pattern matched, try to provide suggestions
            const suggestions = this.generateSuggestions(normalizedText, entities);
            return {
                originalText: text,
                intent: { intent: 'unknown', confidence: 0 },
                entities,
                confidence: 0,
                suggestions,
            };
        }

        // Calculate overall confidence
        const confidence = this.calculateConfidence(matchedPattern, entities);
        
        // Build the parsed command
        const parsedCommand: ParsedCommand = {
            originalText: text,
            intent: {
                intent: matchedPattern.pattern.intent,
                confidence: matchedPattern.confidence,
                service: matchedPattern.pattern.service as 'todoist' | 'notion' | 'jira' | 'gmail' | 'calendar' | undefined,
            },
            entities,
            confidence,
            params: this.buildParams(entities, matchedPattern.pattern),
        };

        // Add suggestions if confidence is below threshold
        if (confidence < this.confidenceThreshold) {
            parsedCommand.suggestions = this.generateSuggestions(normalizedText, entities);
        }

        return parsedCommand;
    }

    /**
     * Extract all entities from the text
     */
    private extractEntities(text: string): Entity[] {
        const entities: Entity[] = [];
        
        for (const extractor of this.extractors) {
            if (typeof extractor.pattern === 'function') {
                const entity = extractor.pattern(text);
                if (entity) {
                    entities.push(entity);
                }
            }
        }
        
        // Sort entities by position to maintain order
        return entities.sort((a, b) => a.position[0] - b.position[0]);
    }

    /**
     * Find the best matching pattern for the given text
     */
    private findBestPattern(
        text: string, 
        entities: Entity[]
    ): { pattern: CommandPattern; confidence: number } | null {
        const matches: Array<{ pattern: CommandPattern; confidence: number }> = [];
        
        for (const pattern of this.patterns) {
            const match = text.match(pattern.pattern);
            if (match) {
                // Calculate pattern confidence based on match quality
                const matchLength = match[0].length;
                const textLength = text.length;
                const coverageRatio = matchLength / textLength;
                
                // Boost confidence if service entity matches pattern service
                const serviceEntity = entities.find(e => e.type === 'service');
                const serviceBoost = serviceEntity && serviceEntity.value === pattern.service ? 0.2 : 0;
                
                const confidence = Math.min(0.6 + coverageRatio * 0.4 + serviceBoost, 1.0);
                
                matches.push({ pattern, confidence });
            }
        }
        
        // Return the pattern with highest confidence
        if (matches.length === 0) return null;
        
        return matches.sort((a, b) => b.confidence - a.confidence)[0];
    }

    /**
     * Calculate overall confidence for a parsed command
     */
    private calculateConfidence(
        matchedPattern: { pattern: CommandPattern; confidence: number },
        entities: Entity[]
    ): number {
        // Start with pattern confidence
        let confidence = matchedPattern.confidence;
        
        // Average with entity confidences
        if (entities.length > 0) {
            const entityConfidence = entities.reduce((sum, e) => sum + e.confidence, 0) / entities.length;
            confidence = (confidence + entityConfidence) / 2;
        }
        
        // Penalize if missing expected entities for the intent
        const expectedEntities = this.getExpectedEntities(matchedPattern.pattern.intent);
        const missingEntities = expectedEntities.filter(
            expected => !entities.find(e => e.type === expected)
        );
        
        confidence -= missingEntities.length * 0.1;
        
        return Math.max(0, Math.min(1, confidence));
    }

    /**
     * Build parameters from entities for the command
     */
    private buildParams(entities: Entity[], pattern: CommandPattern): Record<string, unknown> {
        const params: Record<string, unknown> = {};
        
        // Add pattern-specific parameters
        if (pattern.service) {
            params.service = pattern.service;
        }
        
        // Convert entities to parameters
        for (const entity of entities) {
            switch (entity.type) {
                case 'object':
                    params.content = entity.value;
                    break;
                case 'date':
                    params.date = entity.value;
                    break;
                case 'time':
                    params.time = entity.value;
                    break;
                case 'priority':
                    params.priority = entity.value;
                    break;
                case 'project':
                    params.project = entity.value;
                    break;
                case 'label':
                    if (!params.labels) params.labels = [];
                    (params.labels as string[]).push(entity.value);
                    break;
                case 'action':
                    params.action = entity.value;
                    break;
                case 'service':
                    params.targetService = entity.value;
                    break;
            }
        }
        
        return params;
    }

    /**
     * Generate suggestions for ambiguous or low-confidence commands
     */
    private generateSuggestions(text: string, entities: Entity[]): CommandSuggestion[] {
        // Entities will be used in future enhancements
        void entities;
        const suggestions: CommandSuggestion[] = [];
        
        // Find patterns that partially match
        const partialMatches: Array<{ pattern: CommandPattern; score: number }> = [];
        
        for (const pattern of this.patterns) {
            // Check if any of the pattern's key words are in the text
            const patternKeywords = this.extractKeywords(pattern.pattern.source);
            const textWords = text.toLowerCase().split(/\s+/);
            
            const matchingKeywords = patternKeywords.filter(keyword => 
                textWords.some(word => word.includes(keyword) || keyword.includes(word))
            );
            
            if (matchingKeywords.length > 0) {
                const score = matchingKeywords.length / patternKeywords.length;
                partialMatches.push({ pattern, score });
            }
        }
        
        // Sort by score and take top suggestions
        partialMatches.sort((a, b) => b.score - a.score);
        
        for (let i = 0; i < Math.min(this.maxSuggestions, partialMatches.length); i++) {
            const { pattern } = partialMatches[i];
            
            // Use the first example as the suggestion
            if (pattern.examples && pattern.examples.length > 0) {
                suggestions.push({
                    text: pattern.examples[0],
                    confidence: partialMatches[i].score,
                    description: `${pattern.intent} for ${pattern.service || 'general'}`,
                });
            }
        }
        
        return suggestions;
    }

    /**
     * Get expected entity types for a given intent
     */
    private getExpectedEntities(intent: string): Entity['type'][] {
        const expectations: Record<string, Entity['type'][]> = {
            create_task: ['object'],
            list_tasks: ['date'],
            complete_task: ['object'],
            create_issue: ['object', 'project'],
            update_issue: ['object'],
            compose_email: ['object'],
            create_event: ['object', 'date', 'time'],
            search_notion: ['object'],
        };
        
        return expectations[intent] || [];
    }

    /**
     * Extract keywords from a regex pattern source
     */
    private extractKeywords(patternSource: string): string[] {
        // Remove regex special characters and extract words
        const cleaned = patternSource
            .replace(/[\\^$.*+?()[\]{}|]/g, ' ')
            .toLowerCase();
        
        const words = cleaned.split(/\s+/).filter(word => 
            word.length > 2 && !['the', 'and', 'for', 'with'].includes(word)
        );
        
        return [...new Set(words)];
    }

    /**
     * Normalize text for better pattern matching
     */
    private normalizeText(text: string): string {
        return text
            .trim()
            .replace(/\s+/g, ' ')
            .replace(/[.,!?;:]+$/g, ''); // Remove trailing punctuation
    }

    /**
     * Check if a command can be executed with current context
     */
    canExecute(command: ParsedCommand): boolean {
        return command.confidence >= this.confidenceThreshold;
    }

    /**
     * Get all available patterns
     */
    getPatterns(): CommandPattern[] {
        return this.patterns;
    }

    /**
     * Add custom patterns
     */
    addPatterns(patterns: CommandPattern[]): void {
        this.patterns.push(...patterns);
    }

    /**
     * Remove patterns by ID
     */
    removePatterns(ids: string[]): void {
        this.patterns = this.patterns.filter(p => !ids.includes(p.id));
    }
}

// Export a default instance
export const nlpEngine = new NLPEngine();