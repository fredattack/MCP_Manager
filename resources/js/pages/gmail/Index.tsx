import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Search, Mail, Send } from 'lucide-react';

interface GmailMessage {
    id: string;
    subject: string;
    snippet: string;
    from: string;
    date: string;
    labels: string[];
    isUnread: boolean;
}

interface GmailLabel {
    id: string;
    name: string;
    type: string;
}

interface Props {
    status: {
        authenticated: boolean;
        email: string;
    };
    messages: GmailMessage[];
    labels: GmailLabel[];
}

export default function GmailIndex({ 
    status = { authenticated: false, email: '' }, 
    messages = [], 
    labels = [] 
}: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [showCompose, setShowCompose] = useState(false);
    
    const { data, setData, post, processing, errors } = useForm({
        to: '',
        subject: '',
        body: '',
        body_type: 'text',
    });

    const handleSendEmail = (e: React.FormEvent) => {
        e.preventDefault();
        post('/gmail/send', {
            onSuccess: () => {
                setShowCompose(false);
                setData({ to: '', subject: '', body: '', body_type: 'text' });
            },
        });
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        // Implementation for search functionality
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    };

    return (
        <AppLayout>
            <Head title="Gmail" />

            <div className="mx-auto max-w-7xl p-6">
                
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Gmail
                        </h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">
                            Connected as {status.email}
                        </p>
                    </div>
                    <Button onClick={() => setShowCompose(true)} className="flex items-center gap-2">
                        <Send className="h-4 w-4" />
                        Compose
                    </Button>
                </div>
                            
                            {/* Search Bar */}
                            <div className="mb-6">
                                <form onSubmit={handleSearch} className="flex gap-2">
                                    <div className="flex-1">
                                        <Input
                                            type="text"
                                            placeholder="Search emails..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            className="w-full"
                                        />
                                    </div>
                                    <Button type="submit" variant="outline">
                                        <Search className="h-4 w-4" />
                                    </Button>
                                </form>
                            </div>

                            {/* Labels */}
                            <div className="mb-6">
                                <div className="flex flex-wrap gap-2">
                                    {labels.map((label) => (
                                        <Badge key={label.id} variant="secondary">
                                            {label.name}
                                        </Badge>
                                    ))}
                                </div>
                            </div>

                            {/* Messages List */}
                            <div className="space-y-4">
                                {messages.map((message) => (
                                    <Card key={message.id} className="cursor-pointer hover:shadow-md transition-shadow">
                                        <CardHeader className="pb-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex items-center gap-2">
                                                        <Mail className={`h-4 w-4 ${message.isUnread ? 'text-blue-500' : 'text-gray-400'}`} />
                                                        {message.isUnread && <div className="w-2 h-2 bg-blue-500 rounded-full" />}
                                                    </div>
                                                    <div>
                                                        <CardTitle className="text-sm font-medium">
                                                            {message.from}
                                                        </CardTitle>
                                                        <CardDescription className="text-xs">
                                                            {formatDate(message.date)}
                                                        </CardDescription>
                                                    </div>
                                                </div>
                                                <div className="flex gap-2">
                                                    {message.labels.map((label) => (
                                                        <Badge key={label} variant="outline" className="text-xs">
                                                            {label}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </div>
                                        </CardHeader>
                                        <CardContent>
                                            <Link href={`/gmail/${message.id}`}>
                                                <h3 className="font-medium mb-2">{message.subject}</h3>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                    {message.snippet}
                                                </p>
                                            </Link>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            {/* Compose Modal */}
                            {showCompose && (
                                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                                    <Card className="w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                                        <CardHeader>
                                            <CardTitle>Compose Email</CardTitle>
                                            <CardDescription>
                                                Send a new email message
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent>
                                            <form onSubmit={handleSendEmail} className="space-y-4">
                                                <div>
                                                    <Label htmlFor="to">To</Label>
                                                    <Input
                                                        id="to"
                                                        type="email"
                                                        value={data.to}
                                                        onChange={(e) => setData('to', e.target.value)}
                                                        placeholder="recipient@example.com"
                                                        required
                                                    />
                                                    {errors.to && <p className="text-red-500 text-sm mt-1">{errors.to}</p>}
                                                </div>
                                                
                                                <div>
                                                    <Label htmlFor="subject">Subject</Label>
                                                    <Input
                                                        id="subject"
                                                        value={data.subject}
                                                        onChange={(e) => setData('subject', e.target.value)}
                                                        placeholder="Email subject"
                                                        required
                                                    />
                                                    {errors.subject && <p className="text-red-500 text-sm mt-1">{errors.subject}</p>}
                                                </div>
                                                
                                                <div>
                                                    <Label htmlFor="body_type">Format</Label>
                                                    <Select value={data.body_type} onValueChange={(value) => setData('body_type', value)}>
                                                        <SelectTrigger>
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="text">Plain Text</SelectItem>
                                                            <SelectItem value="html">HTML</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                                
                                                <div>
                                                    <Label htmlFor="body">Message</Label>
                                                    <Textarea
                                                        id="body"
                                                        value={data.body}
                                                        onChange={(e) => setData('body', e.target.value)}
                                                        placeholder="Type your message here..."
                                                        rows={8}
                                                        required
                                                    />
                                                    {errors.body && <p className="text-red-500 text-sm mt-1">{errors.body}</p>}
                                                </div>
                                                
                                                <div className="flex justify-end gap-2">
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        onClick={() => setShowCompose(false)}
                                                    >
                                                        Cancel
                                                    </Button>
                                                    <Button type="submit" disabled={processing}>
                                                        {processing ? 'Sending...' : 'Send'}
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