import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Copy, Download } from 'lucide-react';
import { useToast } from '@/hooks/ui/use-toast';

interface PlanningMarkdownViewProps {
    markdown: string;
}

export function PlanningMarkdownView({ markdown }: PlanningMarkdownViewProps) {
    const { toast } = useToast();

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(markdown);
            toast.success('Copied!', 'Planning copied to clipboard');
        } catch {
            toast.error('Failed to copy', 'Please try again');
        }
    };

    const handleDownload = () => {
        const blob = new Blob([markdown], { type: 'text/markdown' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `daily-planning-${new Date().toISOString().split('T')[0]}.md`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        toast.success('Downloaded!', 'Planning saved as markdown file');
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex justify-between items-center">
                    <CardTitle>Markdown View</CardTitle>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="sm"
                            onClick={handleCopy}
                        >
                            <Copy className="h-4 w-4 mr-2" />
                            Copy
                        </Button>
                        <Button 
                            variant="outline" 
                            size="sm"
                            onClick={handleDownload}
                        >
                            <Download className="h-4 w-4 mr-2" />
                            Download
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <pre className="bg-muted p-4 rounded-lg overflow-x-auto">
                    <code className="text-sm">{markdown}</code>
                </pre>
            </CardContent>
        </Card>
    );
}