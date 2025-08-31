import { NLPEngine } from '../nlp-engine';

describe('NLPEngine', () => {
    let engine: NLPEngine;

    beforeEach(() => {
        engine = new NLPEngine();
    });

    describe('Todoist Commands', () => {
        test('parses create task command', () => {
            const command = engine.parse('Create task "Review PR" for tomorrow with priority P1');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('create_task');
            expect(command!.intent.service).toBe('todoist');
            expect(command!.params!.content).toBe('Review PR');
            expect(command!.params!.date).toBe('tomorrow');
            expect(command!.params!.priority).toBe('P1');
        });

        test('parses simple task creation', () => {
            const command = engine.parse('Task: Fix the login bug');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('create_task');
            expect(command!.params!.content).toBe('Fix the login bug');
        });

        test('parses list tasks command', () => {
            const command = engine.parse('Show my tasks for today');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('list_tasks');
            expect(command!.params!.date).toBe('today');
        });

        test('parses generate planning command', () => {
            const command = engine.parse('Generate daily planning');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('generate_planning');
            expect(command!.intent.service).toBe('todoist');
        });
    });

    describe('Notion Commands', () => {
        test('parses search command', () => {
            const command = engine.parse('Search in Notion for "API documentation"');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('search_notion');
            expect(command!.intent.service).toBe('notion');
            expect(command!.params!.content).toBe('API documentation');
        });

        test('parses create page command', () => {
            const command = engine.parse('Create Notion page "Meeting Notes"');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('create_page');
            expect(command!.intent.service).toBe('notion');
            expect(command!.params!.content).toBe('Meeting Notes');
        });
    });

    describe('JIRA Commands', () => {
        test('parses create issue command', () => {
            const command = engine.parse('Create JIRA issue "Fix payment bug" in project PROJ');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('create_issue');
            expect(command!.intent.service).toBe('jira');
            expect(command!.params!.content).toBe('Fix payment bug in PROJ');
            expect(command!.params!.project).toBe('PROJ');
        });

        test('parses show sprint command', () => {
            const command = engine.parse('Show current sprint');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('show_sprint');
            expect(command!.intent.service).toBe('jira');
        });
    });

    describe('Entity Extraction', () => {
        test('extracts multiple entities', () => {
            const command = engine.parse('Add task "Call client" tomorrow at 2pm with high priority @work');
            
            expect(command).not.toBeNull();
            
            const entities = command!.entities;
            const types = entities.map(e => e.type);
            
            expect(types).toContain('action');
            expect(types).toContain('object');
            expect(types).toContain('date');
            expect(types).toContain('time');
            expect(types).toContain('priority');
            expect(types).toContain('label');
        });

        test('normalizes French commands', () => {
            const command = engine.parse('Créer tâche "Réviser le code" pour demain');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('create_task');
            
            const actionEntity = command!.entities.find(e => e.type === 'action');
            expect(actionEntity?.value).toBe('create');
        });
    });

    describe('Confidence and Suggestions', () => {
        test('provides suggestions for unclear commands', () => {
            const command = engine.parse('task something');
            
            expect(command).not.toBeNull();
            expect(command!.confidence).toBeLessThan(0.7);
            expect(command!.suggestions).toBeDefined();
            expect(command!.suggestions!.length).toBeGreaterThan(0);
        });

        test('high confidence for well-formed commands', () => {
            const command = engine.parse('Create task "Important work"');
            
            expect(command).not.toBeNull();
            expect(command!.confidence).toBeGreaterThanOrEqual(0.7);
            expect(engine.canExecute(command!)).toBe(true);
        });

        test('low confidence for ambiguous commands', () => {
            const command = engine.parse('do something');
            
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('unknown');
            expect(command!.confidence).toBe(0);
            expect(engine.canExecute(command!)).toBe(false);
        });
    });

    describe('Pattern Management', () => {
        test('can add custom patterns', () => {
            const initialCount = engine.getPatterns().length;
            
            engine.addPatterns([{
                id: 'custom.test',
                pattern: /test pattern/i,
                intent: 'test_intent',
                service: 'custom',
            }]);
            
            expect(engine.getPatterns().length).toBe(initialCount + 1);
            
            const command = engine.parse('test pattern');
            expect(command).not.toBeNull();
            expect(command!.intent.intent).toBe('test_intent');
        });

        test('can remove patterns', () => {
            const initialCount = engine.getPatterns().length;
            
            engine.removePatterns(['todoist.task.create']);
            
            expect(engine.getPatterns().length).toBe(initialCount - 1);
            
            const command = engine.parse('Create task "Test"');
            // Should still match the simple pattern
            expect(command).not.toBeNull();
        });
    });
});