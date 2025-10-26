import { Entity, EntityExtractor } from './types';

// Date patterns
const TODAY_PATTERN = /\b(today|aujourd'?hui|auj)\b/i;
const TOMORROW_PATTERN = /\b(tomorrow|demain)\b/i;
const NEXT_WEEK_PATTERN = /\b(next week|semaine prochaine)\b/i;
const DATE_PATTERN = /\b(\d{1,2}[/-]\d{1,2}(?:[/-]\d{2,4})?)\b/;

// Time patterns
const TIME_PATTERN = /\b(\d{1,2}h(?:\d{2})?|\d{1,2}:\d{2})\b/i;
const DURATION_PATTERN = /\b(\d+)\s*(min(?:ute)?s?|hour?s?|heure?s?)\b/i;

// Priority patterns
const PRIORITY_PATTERN = /\b(p[1-4]|priority\s*[1-4]|high|medium|low|haute?|moyen(?:ne)?|basse?)\b/i;

// Service patterns
const SERVICE_PATTERN = /\b(todoist|notion|jira|gmail|calendar|mail)\b/i;

// Action patterns
const ACTION_PATTERN =
    /\b(create|add|new|update|edit|delete|remove|list|show|find|search|créer|ajouter|nouveau|modifier|supprimer|lister|afficher|chercher|rechercher)\b/i;

export const dateExtractor: EntityExtractor = {
    type: 'date',
    pattern: (text: string): Entity | null => {
        const lowerText = text.toLowerCase();

        if (TODAY_PATTERN.test(lowerText)) {
            const match = lowerText.match(TODAY_PATTERN)!;
            return {
                type: 'date',
                value: 'today',
                confidence: 1.0,
                position: [match.index!, match.index! + match[0].length],
            };
        }

        if (TOMORROW_PATTERN.test(lowerText)) {
            const match = lowerText.match(TOMORROW_PATTERN)!;
            return {
                type: 'date',
                value: 'tomorrow',
                confidence: 1.0,
                position: [match.index!, match.index! + match[0].length],
            };
        }

        if (NEXT_WEEK_PATTERN.test(lowerText)) {
            const match = lowerText.match(NEXT_WEEK_PATTERN)!;
            return {
                type: 'date',
                value: 'next_week',
                confidence: 0.9,
                position: [match.index!, match.index! + match[0].length],
            };
        }

        const dateMatch = text.match(DATE_PATTERN);
        if (dateMatch) {
            return {
                type: 'date',
                value: dateMatch[1],
                confidence: 0.8,
                position: [dateMatch.index!, dateMatch.index! + dateMatch[0].length],
            };
        }

        return null;
    },
};

export const timeExtractor: EntityExtractor = {
    type: 'time',
    pattern: (text: string): Entity | null => {
        const timeMatch = text.match(TIME_PATTERN);
        if (timeMatch) {
            return {
                type: 'time',
                value: timeMatch[1],
                confidence: 0.9,
                position: [timeMatch.index!, timeMatch.index! + timeMatch[0].length],
            };
        }

        const durationMatch = text.match(DURATION_PATTERN);
        if (durationMatch) {
            const value = durationMatch[1];
            const unit = durationMatch[2].toLowerCase();
            const minutes = unit.includes('hour') || unit.includes('heure') ? parseInt(value) * 60 : parseInt(value);

            return {
                type: 'time',
                value: `${minutes}min`,
                confidence: 0.9,
                position: [durationMatch.index!, durationMatch.index! + durationMatch[0].length],
            };
        }

        return null;
    },
};

export const priorityExtractor: EntityExtractor = {
    type: 'priority',
    pattern: (text: string): Entity | null => {
        const match = text.match(PRIORITY_PATTERN);
        if (!match) return null;

        const value = match[1].toLowerCase();
        let normalizedValue: string;

        if (value.startsWith('p')) {
            normalizedValue = value.toUpperCase();
        } else if (value.includes('1') || value === 'high' || value.includes('haut')) {
            normalizedValue = 'P1';
        } else if (value.includes('2') || value === 'medium' || value.includes('moyen')) {
            normalizedValue = 'P2';
        } else if (value.includes('3') || value === 'low' || value.includes('bas')) {
            normalizedValue = 'P3';
        } else {
            normalizedValue = 'P4';
        }

        return {
            type: 'priority',
            value: normalizedValue,
            confidence: 0.9,
            position: [match.index!, match.index! + match[0].length],
        };
    },
};

export const serviceExtractor: EntityExtractor = {
    type: 'service',
    pattern: (text: string): Entity | null => {
        const match = text.match(SERVICE_PATTERN);
        if (!match) return null;

        const value = match[1].toLowerCase();
        const normalizedValue = value === 'mail' ? 'gmail' : value;

        return {
            type: 'service',
            value: normalizedValue as 'todoist' | 'notion' | 'jira' | 'gmail' | 'calendar',
            confidence: 1.0,
            position: [match.index!, match.index! + match[0].length],
        };
    },
};

export const actionExtractor: EntityExtractor = {
    type: 'action',
    pattern: (text: string): Entity | null => {
        const match = text.match(ACTION_PATTERN);
        if (!match) return null;

        const value = match[1].toLowerCase();
        // Normalize French to English
        const frenchToEnglish: Record<string, string> = {
            créer: 'create',
            ajouter: 'add',
            nouveau: 'new',
            modifier: 'update',
            supprimer: 'delete',
            lister: 'list',
            afficher: 'show',
            chercher: 'search',
            rechercher: 'search',
        };

        const normalizedValue = frenchToEnglish[value] || value;

        return {
            type: 'action',
            value: normalizedValue,
            confidence: 0.95,
            position: [match.index!, match.index! + match[0].length],
        };
    },
};

export const projectExtractor: EntityExtractor = {
    type: 'project',
    pattern: (text: string): Entity | null => {
        // Match "project ProjectName" or "projet ProjectName" or "#ProjectName"
        const patterns = [/\b(?:project|projet)\s+([A-Za-z0-9_-]+)/i, /#([A-Za-z0-9_-]+)/];

        for (const pattern of patterns) {
            const match = text.match(pattern);
            if (match) {
                return {
                    type: 'project',
                    value: match[1],
                    confidence: 0.9,
                    position: [match.index!, match.index! + match[0].length],
                };
            }
        }

        return null;
    },
};

export const labelExtractor: EntityExtractor = {
    type: 'label',
    pattern: (text: string): Entity | null => {
        // Match "@label" pattern
        const match = text.match(/@([A-Za-z0-9_-]+)/);
        if (!match) return null;

        return {
            type: 'label',
            value: match[1],
            confidence: 0.95,
            position: [match.index!, match.index! + match[0].length],
        };
    },
};

// Extract the main object/content from the text
export const objectExtractor: EntityExtractor = {
    type: 'object',
    pattern: (text: string): Entity | null => {
        // Remove already identified entities and actions
        let cleanedText = text;

        // Remove common patterns
        cleanedText = cleanedText.replace(SERVICE_PATTERN, '');
        cleanedText = cleanedText.replace(ACTION_PATTERN, '');
        cleanedText = cleanedText.replace(DATE_PATTERN, '');
        cleanedText = cleanedText.replace(TIME_PATTERN, '');
        cleanedText = cleanedText.replace(PRIORITY_PATTERN, '');
        cleanedText = cleanedText.replace(/@[A-Za-z0-9_-]+/g, '');
        cleanedText = cleanedText.replace(/#[A-Za-z0-9_-]+/g, '');

        // Clean up quotes and extra spaces
        cleanedText = cleanedText.replace(/["']/g, '').trim();
        cleanedText = cleanedText.replace(/\s+/g, ' ');

        // Skip if too short
        if (cleanedText.length < 3) return null;

        return {
            type: 'object',
            value: cleanedText,
            confidence: 0.7,
            position: [0, text.length], // Approximate
        };
    },
};

export const defaultExtractors: EntityExtractor[] = [
    dateExtractor,
    timeExtractor,
    priorityExtractor,
    serviceExtractor,
    actionExtractor,
    projectExtractor,
    labelExtractor,
    objectExtractor,
];
