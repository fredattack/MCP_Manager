import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useNLPEngine } from '@/hooks/use-nlp-engine';
import { ParsedCommand } from '@/lib/nlp';
import { AlertCircle, ArrowRight, CheckCircle, Loader2, Sparkles } from 'lucide-react';
import React, { useState } from 'react';

interface NLPCommandInputProps {
    onCommand?: (command: ParsedCommand) => void;
    placeholder?: string;
    className?: string;
}

export function NLPCommandInput({ onCommand, placeholder = 'Type a natural language command...', className }: NLPCommandInputProps) {
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
                    <Sparkles className="text-muted-foreground absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform" />
                    <Input
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder={placeholder}
                        className="pr-24 pl-10"
                        disabled={isProcessing}
                    />
                    <Button
                        type="submit"
                        size="sm"
                        className="absolute top-1/2 right-1 -translate-y-1/2 transform"
                        disabled={!input.trim() || isProcessing}
                    >
                        {isProcessing ? <Loader2 className="h-4 w-4 animate-spin" /> : <ArrowRight className="h-4 w-4" />}
                    </Button>
                </div>
            </form>

            {/* Command Preview */}
            {input &&
                !isProcessing &&
                (() => {
                    const command = parseCommand(input);
                    if (!command) return null;

                    return (
                        <Card className="mt-2">
                            <CardContent className="pt-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1 space-y-2">
                                        <div className="flex items-center gap-2">
                                            {command.confidence >= 0.7 ? (
                                                <CheckCircle className="h-4 w-4 text-green-600" />
                                            ) : (
                                                <AlertCircle className="h-4 w-4 text-yellow-600" />
                                            )}
                                            <span className="text-sm font-medium">{command.intent.intent.replace(/_/g, ' ')}</span>
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
                                                    <Badge key={idx} variant="outline" className="text-xs">
                                                        {entity.type}: {entity.value}
                                                    </Badge>
                                                ))}
                                            </div>
                                        )}

                                        {/* Suggestions */}
                                        {command.suggestions && command.suggestions.length > 0 && (
                                            <div className="mt-2 space-y-1">
                                                <p className="text-muted-foreground text-xs">Did you mean:</p>
                                                {command.suggestions.map((suggestion, idx) => (
                                                    <button
                                                        key={idx}
                                                        onClick={() => handleSuggestionClick(suggestion.text)}
                                                        className="text-primary block text-left text-sm hover:underline"
                                                    >
                                                        {suggestion.text}
                                                    </button>
                                                ))}
                                            </div>
                                        )}
                                    </div>

                                    <div className="text-right">
                                        <span className="text-muted-foreground text-xs">Confidence</span>
                                        <div className="text-sm font-medium">{Math.round(command.confidence * 100)}%</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })()}

            {/* Last Command Result */}
            {lastCommand && !input && <div className="text-muted-foreground mt-2 text-sm">Last: {lastCommand.originalText}</div>}
        </div>
    );
}
