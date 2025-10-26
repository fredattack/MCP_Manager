import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { Calendar, Clock, MapPin, Plus, Users } from 'lucide-react';
import React, { useState } from 'react';

interface CalendarEvent {
    id: string;
    summary: string;
    description?: string;
    start: {
        dateTime: string;
        timeZone?: string;
    };
    end: {
        dateTime: string;
        timeZone?: string;
    };
    attendees?: Array<{
        email: string;
        responseStatus: string;
    }>;
    location?: string;
    status: string;
}

interface GoogleCalendar {
    id: string;
    summary: string;
    description?: string;
    primary?: boolean;
    backgroundColor?: string;
}

interface Props {
    status: {
        authenticated: boolean;
        email: string;
    };
    calendars: GoogleCalendar[];
    todayEvents: CalendarEvent[];
}

export default function CalendarIndex({ status = { authenticated: false, email: '' }, calendars = [], todayEvents = [] }: Props) {
    const [showCreateEvent, setShowCreateEvent] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        summary: '',
        description: '',
        start: {
            dateTime: '',
            timeZone: 'UTC',
        },
        end: {
            dateTime: '',
            timeZone: 'UTC',
        },
        attendees: [{ email: '' }],
        location: '',
    });

    const handleCreateEvent = (e: React.FormEvent) => {
        e.preventDefault();
        post('/calendar/events', {
            onSuccess: () => {
                setShowCreateEvent(false);
                setData({
                    summary: '',
                    description: '',
                    start: { dateTime: '', timeZone: 'UTC' },
                    end: { dateTime: '', timeZone: 'UTC' },
                    attendees: [{ email: '' }],
                    location: '',
                });
            },
        });
    };

    const formatEventTime = (dateTimeString: string) => {
        return new Date(dateTimeString).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
    };

    const addAttendee = () => {
        setData('attendees', [...data.attendees, { email: '' }]);
    };

    const removeAttendee = (index: number) => {
        const newAttendees = data.attendees.filter((_, i) => i !== index);
        setData('attendees', newAttendees);
    };

    const updateAttendee = (index: number, email: string) => {
        const newAttendees = [...data.attendees];
        newAttendees[index] = { email };
        setData('attendees', newAttendees);
    };

    return (
        <AppLayout>
            <Head title="Google Calendar" />

            <div className="mx-auto max-w-7xl p-6">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Google Calendar</h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">Connected as {status.email}</p>
                    </div>
                    <Button onClick={() => setShowCreateEvent(true)} className="flex items-center gap-2">
                        <Plus className="h-4 w-4" />
                        Create Event
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Calendars List */}
                    <div className="lg:col-span-1">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Calendar className="h-5 w-5" />
                                    My Calendars
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {calendars.map((calendar) => (
                                        <div key={calendar.id} className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <div
                                                    className="h-3 w-3 rounded-full"
                                                    style={{ backgroundColor: calendar.backgroundColor || '#3b82f6' }}
                                                />
                                                <span className="text-sm font-medium">{calendar.summary}</span>
                                                {calendar.primary && (
                                                    <Badge variant="secondary" className="text-xs">
                                                        Primary
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Today's Events */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="h-5 w-5" />
                                    Today's Events
                                </CardTitle>
                                <CardDescription>
                                    {todayEvents.length} event{todayEvents.length !== 1 ? 's' : ''} scheduled for today
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {todayEvents.length === 0 ? (
                                    <p className="py-8 text-center text-gray-500">No events scheduled for today</p>
                                ) : (
                                    <div className="space-y-4">
                                        {todayEvents.map((event) => (
                                            <div key={event.id} className="rounded-lg border p-4 transition-shadow hover:shadow-md">
                                                <div className="flex items-start justify-between">
                                                    <div className="flex-1">
                                                        <h3 className="text-lg font-medium">{event.summary}</h3>
                                                        {event.description && (
                                                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">{event.description}</p>
                                                        )}
                                                        <div className="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                                            <div className="flex items-center gap-1">
                                                                <Clock className="h-4 w-4" />
                                                                {formatEventTime(event.start.dateTime)} - {formatEventTime(event.end.dateTime)}
                                                            </div>
                                                            {event.location && (
                                                                <div className="flex items-center gap-1">
                                                                    <MapPin className="h-4 w-4" />
                                                                    {event.location}
                                                                </div>
                                                            )}
                                                            {event.attendees && event.attendees.length > 0 && (
                                                                <div className="flex items-center gap-1">
                                                                    <Users className="h-4 w-4" />
                                                                    {event.attendees.length} attendee{event.attendees.length !== 1 ? 's' : ''}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <Badge variant={event.status === 'confirmed' ? 'default' : 'secondary'}>{event.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Create Event Modal */}
                {showCreateEvent && (
                    <div className="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4">
                        <Card className="max-h-[80vh] w-full max-w-2xl overflow-y-auto">
                            <CardHeader>
                                <CardTitle>Create New Event</CardTitle>
                                <CardDescription>Add a new event to your calendar</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleCreateEvent} className="space-y-4">
                                    <div>
                                        <Label htmlFor="summary">Event Title</Label>
                                        <Input
                                            id="summary"
                                            value={data.summary}
                                            onChange={(e) => setData('summary', e.target.value)}
                                            placeholder="Meeting with team"
                                            required
                                        />
                                        {errors.summary && <p className="mt-1 text-sm text-red-500">{errors.summary}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Event description..."
                                            rows={3}
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="start_time">Start Time</Label>
                                            <Input
                                                id="start_time"
                                                type="datetime-local"
                                                value={data.start.dateTime}
                                                onChange={(e) => setData('start', { ...data.start, dateTime: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="end_time">End Time</Label>
                                            <Input
                                                id="end_time"
                                                type="datetime-local"
                                                value={data.end.dateTime}
                                                onChange={(e) => setData('end', { ...data.end, dateTime: e.target.value })}
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <Label htmlFor="location">Location</Label>
                                        <Input
                                            id="location"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                            placeholder="Conference room, online, etc."
                                        />
                                    </div>

                                    <div>
                                        <Label>Attendees</Label>
                                        {data.attendees.map((attendee, index) => (
                                            <div key={index} className="mt-2 flex gap-2">
                                                <Input
                                                    type="email"
                                                    value={attendee.email}
                                                    onChange={(e) => updateAttendee(index, e.target.value)}
                                                    placeholder="attendee@example.com"
                                                    className="flex-1"
                                                />
                                                {data.attendees.length > 1 && (
                                                    <Button type="button" variant="outline" onClick={() => removeAttendee(index)}>
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>
                                        ))}
                                        <Button type="button" variant="outline" onClick={addAttendee} className="mt-2">
                                            Add Attendee
                                        </Button>
                                    </div>

                                    <div className="flex justify-end gap-2">
                                        <Button type="button" variant="outline" onClick={() => setShowCreateEvent(false)}>
                                            Cancel
                                        </Button>
                                        <Button type="submit" disabled={processing}>
                                            {processing ? 'Creating...' : 'Create Event'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
