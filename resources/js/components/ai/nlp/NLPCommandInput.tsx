import React, { useState } from 'react';
import { useNLPEngine } from '@/hooks/use-nlp-engine';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Sparkles, 
    ArrowRight, 
    Loader2,
    AlertCircle,
    CheckCircle,
} from 'lucide-react';
import { ParsedCommand } from '@/lib/nlp';

interface NLPCommandInputProps {
    onCommand?: (command: ParsedCommand) => void;
    placeholder?: string;
    className?: string;
}

export function NLPCommandInput({ 
    onCommand, 
    placeholder = "Type a natural language command...",
    className 
}: NLPCommandInputProps) {
    const [input, setInput] = useState('');
    const { parseCommand, executeCommand, isProcessing, lastCommand } = useNLPEngine({
        onCommandParsed: onCommand,
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!input.trim() || isProcessing) return;

        const command = parseCommand(input);
        if (command && command.confidence >= 0.7) {
            await executeCommand(command);
            setInput('');
        }
    };

    const handleSuggestionClick = async (suggestion: string) => {
        setInput(suggestion);
        const command = parseCommand(suggestion);
        if (command) {
            await executeCommand(command);
            setInput('');
        }
    };

    return (
        <div className={className}>
            <form onSubmit={handleSubmit} className="relative">
                <div className="relative">
                    <Sparkles className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                    <Input
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder={placeholder}
                        className="pl-10 pr-24"
                        disabled={isProcessing}
                    />
                    <Button
                        type="submit"
                        size="sm"
                        className="absolute right-1 top-1/2 transform -translate-y-1/2"
                        disabled={!input.trim() || isProcessing}
                    >
                        {isProcessing ? (
                            <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                            <ArrowRight className="h-4 w-4" />
                        )}
                    </Button>
                </div>
            </form>

            {/* Command Preview */}
            {input && !isProcessing && (() => {
                const command = parseCommand(input);
                if (!command) return null;

                return (
                    <Card className="mt-2">
                        <CardContent className="pt-4">
                            <div className="flex items-start justify-between">
                                <div className="space-y-2 flex-1">
                                    <div className="flex items-center gap-2">
                                        {command.confidence >= 0.7 ? (
                                            <CheckCircle className="h-4 w-4 text-green-600" />
                                        ) : (
                                            <AlertCircle className="h-4 w-4 text-yellow-600" />
                                        )}
                                        <span className="text-sm font-medium">
                                            {command.intent.intent.replace(/_/g, ' ')}
                                        </span>
                                        {command.intent.service && (
                                            <Badge variant="secondary" className="text-xs">
                                                {command.intent.service}
                                            </Badge>
                                        )}
                                    </div>

                                    {/* Extracted Entities */}
                                    {command.entities.length > 0 && (
                                        <div className="flex flex-wrap gap-1">
                                            {command.entities.map((entity, idx) => (
                                                <Badge
                                                    key={idx}
                                                    variant="outline"
                                                    className="text-xs"
                                                >
                                                    {entity.type}: {entity.value}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}

                                    {/* Suggestions */}
                                    {command.suggestions && command.suggestions.length > 0 && (
                                        <div className="space-y-1 mt-2">
                                            <p className="text-xs text-muted-foreground">
                                                Did you mean:
                                            </p>
                                            {command.suggestions.map((suggestion, idx) => (
                                                <button
                                                    key={idx}
                                                    onClick={() => handleSuggestionClick(suggestion.text)}
                                                    className="block text-sm text-primary hover:underline text-left"
                                                >
                                                    {suggestion.text}
                                                </button>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                <div className="text-right">
                                    <span className="text-xs text-muted-foreground">
                                        Confidence
                                    </span>
                                    <div className="text-sm font-medium">
                                        {Math.round(command.confidence * 100)}%
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                );
            })()}

            {/* Last Command Result */}
            {lastCommand && !input && (
                <div className="mt-2 text-sm text-muted-foreground">
                    Last: {lastCommand.originalText}
                </div>
            )}
        </div>
    );
}