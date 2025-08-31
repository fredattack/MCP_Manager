import React, { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2 } from 'lucide-react';

interface JiraProject {
    id: string;
    key: string;
    name: string;
}

interface CreateIssueDialogProps {
    open: boolean;
    onClose: () => void;
    onCreate: (data: {
        project_key: string;
        issue_type: string;
        summary: string;
        description?: string;
        priority?: string;
    }) => Promise<unknown>;
    projects: JiraProject[];
}

export function CreateIssueDialog({ open, onClose, onCreate, projects }: CreateIssueDialogProps) {
    const [creating, setCreating] = useState(false);
    const [formData, setFormData] = useState({
        project_key: '',
        issue_type: 'Task',
        summary: '',
        description: '',
        priority: 'Medium',
    });

    const issueTypes = ['Bug', 'Task', 'Story', 'Epic'];
    const priorities = ['Highest', 'High', 'Medium', 'Low', 'Lowest'];

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.project_key || !formData.summary) return;

        try {
            setCreating(true);
            await onCreate(formData);
            onClose();
            // Reset form
            setFormData({
                project_key: '',
                issue_type: 'Task',
                summary: '',
                description: '',
                priority: 'Medium',
            });
        } catch {
            // Error is handled by the hook
        } finally {
            setCreating(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[525px]">
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create New Issue</DialogTitle>
                        <DialogDescription>
                            Create a new JIRA issue. Fill in the required fields below.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        {/* Project */}
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="project" className="text-right">
                                Project*
                            </Label>
                            <Select
                                value={formData.project_key}
                                onValueChange={(value) => setFormData({ ...formData, project_key: value })}
                            >
                                <SelectTrigger className="col-span-3">
                                    <SelectValue placeholder="Select a project" />
                                </SelectTrigger>
                                <SelectContent>
                                    {projects.map((project) => (
                                        <SelectItem key={project.id} value={project.key}>
                                            {project.name} ({project.key})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Issue Type */}
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="issue_type" className="text-right">
                                Type*
                            </Label>
                            <Select
                                value={formData.issue_type}
                                onValueChange={(value) => setFormData({ ...formData, issue_type: value })}
                            >
                                <SelectTrigger className="col-span-3">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {issueTypes.map((type) => (
                                        <SelectItem key={type} value={type}>
                                            {type}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Summary */}
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="summary" className="text-right">
                                Summary*
                            </Label>
                            <Input
                                id="summary"
                                value={formData.summary}
                                onChange={(e) => setFormData({ ...formData, summary: e.target.value })}
                                className="col-span-3"
                                placeholder="Brief description of the issue"
                                required
                            />
                        </div>

                        {/* Description */}
                        <div className="grid grid-cols-4 items-start gap-4">
                            <Label htmlFor="description" className="text-right pt-2">
                                Description
                            </Label>
                            <Textarea
                                id="description"
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                className="col-span-3"
                                placeholder="Detailed description of the issue"
                                rows={4}
                            />
                        </div>

                        {/* Priority */}
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="priority" className="text-right">
                                Priority
                            </Label>
                            <Select
                                value={formData.priority}
                                onValueChange={(value) => setFormData({ ...formData, priority: value })}
                            >
                                <SelectTrigger className="col-span-3">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {priorities.map((priority) => (
                                        <SelectItem key={priority} value={priority}>
                                            {priority}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={creating}>
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={creating || !formData.project_key || !formData.summary}
                        >
                            {creating ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Creating...
                                </>
                            ) : (
                                'Create Issue'
                            )}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}