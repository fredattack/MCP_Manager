import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Reply, ReplyAll, Forward, Archive, Trash2, Star } from 'lucide-react';

interface GmailMessage {
    id: string;
    subject: string;
    body: string;
    from: string;
    to: string[];
    cc?: string[];
    bcc?: string[];
    date: string;
    labels: string[];
    isUnread: boolean;
    attachments?: Array<{
        filename: string;
        mimeType: string;
        size: number;
    }>;
}

interface Props {
    message: GmailMessage;
}

export default function GmailShow({ message = {
    id: '',
    subject: 'No message',
    body: '',
    from: '',
    to: [],
    date: new Date().toISOString(),
    labels: [],
    isUnread: false
} }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    };

    const handleReply = () => {
        // Implementation for reply functionality
        console.log('Reply to:', message.id);
    };

    const handleReplyAll = () => {
        // Implementation for reply all functionality
        console.log('Reply all to:', message.id);
    };

    const handleForward = () => {
        // Implementation for forward functionality
        console.log('Forward:', message.id);
    };

    const handleArchive = () => {
        // Implementation for archive functionality
        console.log('Archive:', message.id);
    };

    const handleDelete = () => {
        // Implementation for delete functionality
        console.log('Delete:', message.id);
    };

    const handleStar = () => {
        // Implementation for star functionality
        console.log('Star:', message.id);
    };

    return (
        <AppLayout>
            <Head title={`Gmail - ${message.subject}`} />

            <div className="mx-auto max-w-4xl p-6">
                
                {/* Header */}
                <div className="mb-6 flex items-center gap-4">
                    <Link href="/gmail">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Inbox
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {message.subject}
                        </h1>
                        <p className="mt-1 text-gray-600 dark:text-gray-400">
                            From {message.from}
                        </p>
                    </div>
                </div>
                            
                            {/* Message Actions */}
                            <div className="flex justify-between items-center mb-6 pb-4 border-b">
                                <div className="flex gap-2">
                                    {message.labels.map((label) => (
                                        <Badge key={label} variant="secondary">
                                            {label}
                                        </Badge>
                                    ))}
                                </div>
                                <div className="flex gap-2">
                                    <Button variant="outline" size="sm" onClick={handleStar}>
                                        <Star className="h-4 w-4" />
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={handleReply}>
                                        <Reply className="h-4 w-4" />
                                        Reply
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={handleReplyAll}>
                                        <ReplyAll className="h-4 w-4" />
                                        Reply All
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={handleForward}>
                                        <Forward className="h-4 w-4" />
                                        Forward
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={handleArchive}>
                                        <Archive className="h-4 w-4" />
                                        Archive
                                    </Button>
                                    <Button variant="outline" size="sm" onClick={handleDelete}>
                                        <Trash2 className="h-4 w-4" />
                                        Delete
                                    </Button>
                                </div>
                            </div>

                            {/* Message Details */}
                            <Card>
                                <CardHeader>
                                    <div className="space-y-2">
                                        <CardTitle className="text-lg">{message.subject}</CardTitle>
                                        <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <div>
                                                <span className="font-medium">From:</span> {message.from}
                                            </div>
                                            <div>
                                                <span className="font-medium">To:</span> {message.to.join(', ')}
                                            </div>
                                            {message.cc && message.cc.length > 0 && (
                                                <div>
                                                    <span className="font-medium">CC:</span> {message.cc.join(', ')}
                                                </div>
                                            )}
                                            {message.bcc && message.bcc.length > 0 && (
                                                <div>
                                                    <span className="font-medium">BCC:</span> {message.bcc.join(', ')}
                                                </div>
                                            )}
                                            <div>
                                                <span className="font-medium">Date:</span> {formatDate(message.date)}
                                            </div>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    {/* Attachments */}
                                    {message.attachments && message.attachments.length > 0 && (
                                        <div className="mb-6">
                                            <h3 className="font-medium mb-2">Attachments</h3>
                                            <div className="space-y-2">
                                                {message.attachments.map((attachment, index) => (
                                                    <div
                                                        key={index}
                                                        className="flex items-center justify-between p-2 border rounded"
                                                    >
                                                        <div>
                                                            <span className="font-medium">{attachment.filename}</span>
                                                            <span className="text-sm text-gray-500 ml-2">
                                                                ({Math.round(attachment.size / 1024)} KB)
                                                            </span>
                                                        </div>
                                                        <Button variant="outline" size="sm">
                                                            Download
                                                        </Button>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {/* Message Body */}
                                    <div className="prose max-w-none dark:prose-invert">
                                        <div 
                                            dangerouslySetInnerHTML={{ __html: message.body }}
                                            className="whitespace-pre-wrap"
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Quick Actions */}
                            <div className="mt-6 flex justify-center gap-2">
                                <Button onClick={handleReply} className="flex items-center gap-2">
                                    <Reply className="h-4 w-4" />
                                    Reply
                                </Button>
                                <Button variant="outline" onClick={handleReplyAll} className="flex items-center gap-2">
                                    <ReplyAll className="h-4 w-4" />
                                    Reply All
                                </Button>
                                <Button variant="outline" onClick={handleForward} className="flex items-center gap-2">
                                    <Forward className="h-4 w-4" />
                                    Forward
                                </Button>
                            </div>
                        </div>
        </AppLayout>
    );
}