import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Target } from 'lucide-react';

interface MITDisplayProps {
    mit: {
        id: string;
        content: string;
        project_name?: string;
        priority: string;
        duration?: number;
        energy?: string;
    };
}

export function MITDisplay({ mit }: MITDisplayProps) {
    return (
        <Card className="border-primary/50 bg-primary/5">
            <CardHeader>
                <div className="flex items-center gap-2">
                    <Target className="text-primary h-5 w-5" />
                    <CardTitle>🎯 MIT du jour (Most Important Task)</CardTitle>
                </div>
                <CardDescription>Cette tâche seule rendrait ma journée réussie</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    <h3 className="text-xl font-semibold">{mit.content}</h3>
                    <div className="flex flex-wrap gap-2">
                        {mit.project_name && <Badge variant="secondary">Projet: {mit.project_name}</Badge>}
                        <Badge variant={mit.priority === 'P1' ? 'destructive' : 'default'}>{mit.priority}</Badge>
                        {mit.duration && <Badge variant="outline">⏱️ {mit.duration} min</Badge>}
                        {mit.energy && <Badge variant="outline">⚡ {mit.energy} energy</Badge>}
                    </div>
                    <p className="text-muted-foreground mt-2 text-sm">À programmer impérativement avant 10h pour maximiser votre productivité</p>
                </div>
            </CardContent>
        </Card>
    );
}
