import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { 
    Mail, 
    Send, 
    Plus, 
    Search,
    Clock,
    Users
} from 'lucide-react';

interface Props {
    hasGmail?: boolean;
    hasCalendar?: boolean;
}

export function GoogleQuickActions({ hasGmail = false, hasCalendar = false }: Props) {
    const [showEmailDialog, setShowEmailDialog] = useState(false);
    const [showEventDialog, setShowEventDialog] = useState(false);
    
    const { data: emailData, setData: setEmailData, post: postEmail, processing: emailProcessing } = useForm({
        to: '',
        subject: '',
        body: '',
        body_type: 'text',
    });

    const { data: eventData, setData: setEventData, post: postEvent, processing: eventProcessing } = useForm({
        summary: '',
        description: '',
        start: '',
        end: '',
        location: '',
    });

    const handleSendEmail = (e: React.FormEvent) => {
        e.preventDefault();
        postEmail('/gmail/send', {
            onSuccess: () => {
                setShowEmailDialog(false);
                setEmailData({ to: '', subject: '', body: '', body_type: 'text' });
            },
        });
    };

    const handleCreateEvent = (e: React.FormEvent) => {
        e.preventDefault();
        postEvent('/calendar/events', {
            onSuccess: () => {
                setShowEventDialog(false);
                setEventData({ summary: '', description: '', start: '', end: '', location: '' });
            },
        });
    };

    if (!hasGmail && !hasCalendar) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Mail className="h-5 w-5" />
                        Google Quick Actions
                    </CardTitle>
                    <CardDescription>
                        Connect Gmail or Calendar to access quick actions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Set up your Google integrations to send emails and create events directly from here.
                    </p>
                    <Button variant="outline" disabled>
                        Setup Required
                    </Button>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Mail className="h-5 w-5" />
                    Google Quick Actions
                </CardTitle>
                <CardDescription>
                    Quickly send emails and create calendar events
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    {/* Quick Email */}
                    {hasGmail && (
                        <Dialog open={showEmailDialog} onOpenChange={setShowEmailDialog}>
                            <DialogTrigger asChild>
                                <Button variant="outline" className="h-20 flex-col gap-2">
                                    <Send className="h-6 w-6" />
                                    <span>Send Email</span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle>Quick Send Email</DialogTitle>
                                    <DialogDescription>
                                        Send an email through your connected Gmail account
                                    </DialogDescription>
                                </DialogHeader>
                                <form onSubmit={handleSendEmail} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="to">To</Label>
                                            <Input
                                                id="to"
                                                type="email"
                                                value={emailData.to}
                                                onChange={(e) => setEmailData('to', e.target.value)}
                                                placeholder="recipient@example.com"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="body_type">Format</Label>
                                            <Select value={emailData.body_type} onValueChange={(value) => setEmailData('body_type', value)}>
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="text">Plain Text</SelectItem>
                                                    <SelectItem value="html">HTML</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="subject">Subject</Label>
                                        <Input
                                            id="subject"
                                            value={emailData.subject}
                                            onChange={(e) => setEmailData('subject', e.target.value)}
                                            placeholder="Email subject"
                                            required
                                        />
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="body">Message</Label>
                                        <Textarea
                                            id="body"
                                            value={emailData.body}
                                            onChange={(e) => setEmailData('body', e.target.value)}
                                            placeholder="Type your message here..."
                                            rows={6}
                                            required
                                        />
                                    </div>
                                    
                                    <div className="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setShowEmailDialog(false)}
                                        >
                                            Cancel
                                        </Button>
                                        <Button type="submit" disabled={emailProcessing}>
                                            {emailProcessing ? 'Sending...' : 'Send Email'}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                    )}

                    {/* Quick Event */}
                    {hasCalendar && (
                        <Dialog open={showEventDialog} onOpenChange={setShowEventDialog}>
                            <DialogTrigger asChild>
                                <Button variant="outline" className="h-20 flex-col gap-2">
                                    <Plus className="h-6 w-6" />
                                    <span>Create Event</span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle>Quick Create Event</DialogTitle>
                                    <DialogDescription>
                                        Create an event in your Google Calendar
                                    </DialogDescription>
                                </DialogHeader>
                                <form onSubmit={handleCreateEvent} className="space-y-4">
                                    <div>
                                        <Label htmlFor="summary">Event Title</Label>
                                        <Input
                                            id="summary"
                                            value={eventData.summary}
                                            onChange={(e) => setEventData('summary', e.target.value)}
                                            placeholder="Meeting with team"
                                            required
                                        />
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="start">Start Time</Label>
                                            <Input
                                                id="start"
                                                type="datetime-local"
                                                value={eventData.start}
                                                onChange={(e) => setEventData('start', e.target.value)}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="end">End Time</Label>
                                            <Input
                                                id="end"
                                                type="datetime-local"
                                                value={eventData.end}
                                                onChange={(e) => setEventData('end', e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="location">Location</Label>
                                        <Input
                                            id="location"
                                            value={eventData.location}
                                            onChange={(e) => setEventData('location', e.target.value)}
                                            placeholder="Conference room, online, etc."
                                        />
                                    </div>
                                    
                                    <div>
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={eventData.description}
                                            onChange={(e) => setEventData('description', e.target.value)}
                                            placeholder="Event description..."
                                            rows={3}
                                        />
                                    </div>
                                    
                                    <div className="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setShowEventDialog(false)}
                                        >
                                            Cancel
                                        </Button>
                                        <Button type="submit" disabled={eventProcessing}>
                                            {eventProcessing ? 'Creating...' : 'Create Event'}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                    )}

                    {/* Search Shortcut */}
                    {hasGmail && (
                        <Button variant="outline" className="h-20 flex-col gap-2" asChild>
                            <a href="/gmail?action=search">
                                <Search className="h-6 w-6" />
                                <span>Search Email</span>
                            </a>
                        </Button>
                    )}

                    {/* Calendar View */}
                    {hasCalendar && (
                        <Button variant="outline" className="h-20 flex-col gap-2" asChild>
                            <a href="/calendar?view=today">
                                <Clock className="h-6 w-6" />
                                <span>Today's Events</span>
                            </a>
                        </Button>
                    )}
                </div>

                {/* Recent Activity */}
                <div className="mt-6 pt-4 border-t">
                    <h4 className="font-medium mb-3 flex items-center gap-2">
                        <Users className="h-4 w-4" />
                        Quick Tips
                    </h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-400">
                        {hasGmail && (
                            <div>
                                <strong>Gmail:</strong> Use labels to organize your emails automatically
                            </div>
                        )}
                        {hasCalendar && (
                            <div>
                                <strong>Calendar:</strong> Check for conflicts before scheduling
                            </div>
                        )}
                        <div>
                            <strong>Tip:</strong> Integrate both for automated meeting invitations
                        </div>
                        <div>
                            <strong>Security:</strong> Tokens auto-refresh to maintain access
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}