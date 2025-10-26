import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/ui/use-toast';
import { Copy, Download } from 'lucide-react';

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
                <div className="flex items-center justify-between">
                    <CardTitle>Markdown View</CardTitle>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" onClick={handleCopy}>
                            <Copy className="mr-2 h-4 w-4" />
                            Copy
                        </Button>
                        <Button variant="outline" size="sm" onClick={handleDownload}>
                            <Download className="mr-2 h-4 w-4" />
                            Download
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <pre className="bg-muted overflow-x-auto rounded-lg p-4">
                    <code className="text-sm">{markdown}</code>
                </pre>
            </CardContent>
        </Card>
    );
}
