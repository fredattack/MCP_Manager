import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { Clock, Coffee, RefreshCw, Utensils } from 'lucide-react';

interface TimeBlock {
    start: string;
    end: string;
    duration: number;
    title: string;
    task_id?: string;
    period: 'morning' | 'afternoon';
}

interface TimeBlocksDisplayProps {
    timeBlocks: TimeBlock[];
}

export function TimeBlocksDisplay({ timeBlocks }: TimeBlocksDisplayProps) {
    const morningBlocks = timeBlocks.filter((block) => block.period === 'morning');
    const afternoonBlocks = timeBlocks.filter((block) => block.period === 'afternoon');

    const getBlockIcon = (title: string) => {
        if (title.includes('☕') || title.includes('Pause')) return <Coffee className="h-4 w-4" />;
        if (title.includes('🍽️') || title.includes('déjeuner')) return <Utensils className="h-4 w-4" />;
        if (title.includes('🔄') || title.includes('Buffer')) return <RefreshCw className="h-4 w-4" />;
        if (title.includes('🎯')) return <Clock className="text-primary h-4 w-4" />;
        return <Clock className="h-4 w-4" />;
    };

    const getBlockStyle = (title: string) => {
        if (title.includes('🎯')) return 'bg-primary/10 border-primary/30';
        if (title.includes('☕') || title.includes('🍽️')) return 'bg-green-50 border-green-200';
        if (title.includes('🔄')) return 'bg-gray-50 border-gray-200';
        return 'bg-blue-50 border-blue-200';
    };

    const renderTimeBlock = (block: TimeBlock, index: number) => (
        <div key={index} className={cn('flex gap-4 rounded-lg border p-3 transition-colors hover:shadow-sm', getBlockStyle(block.title))}>
            <div className="flex min-w-[100px] items-center gap-2">
                {getBlockIcon(block.title)}
                <span className="text-sm font-medium">
                    {block.start} - {block.end}
                </span>
            </div>
            <div className="flex-1">
                <p className="font-medium">{block.title}</p>
                <p className="text-muted-foreground text-xs">{block.duration} minutes</p>
            </div>
        </div>
    );

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Clock className="h-5 w-5" />
                    Planning Time-Blocked
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                {morningBlocks.length > 0 && (
                    <div>
                        <h3 className="mb-3 font-semibold">Matin</h3>
                        <div className="space-y-2">{morningBlocks.map((block, index) => renderTimeBlock(block, index))}</div>
                    </div>
                )}

                {afternoonBlocks.length > 0 && (
                    <div>
                        <h3 className="mb-3 font-semibold">Après-midi</h3>
                        <div className="space-y-2">{afternoonBlocks.map((block, index) => renderTimeBlock(block, index))}</div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
