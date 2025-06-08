import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

interface NotionPage {
    id: string;
    title: string;
    url: string;
}

export default function Notion() {
    const [notionPages, setNotionPages] = useState<NotionPage[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [pageId, setPageId] = useState<string>('');

    const fetchNotionPages = async () => {
        setLoading(true);
        setError(null);

        try {
            // Include page_id as a query parameter if provided
            const params = pageId ? { page_id: pageId } : {};
            const response = await axios.get('/api/notion/fetch', { params });
            setNotionPages(response.data);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to fetch Notion pages');
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <Head title="Notion Pages" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h1 className="mb-6 text-2xl font-semibold">Notion Pages</h1>

                            <div className="mb-4">
                                <label htmlFor="pageId" className="mb-2 block text-sm font-medium">
                                    Page ID (optional - uses default if not provided)
                                </label>
                                <input
                                    type="text"
                                    id="pageId"
                                    value={pageId}
                                    onChange={(e) => setPageId(e.target.value)}
                                    className="w-full rounded-md border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-800"
                                    placeholder="Enter Notion page ID"
                                />
                            </div>

                            <button
                                onClick={fetchNotionPages}
                                disabled={loading}
                                className="rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 disabled:opacity-50"
                            >
                                {loading ? 'Loading...' : 'Fetch Notion Pages'}
                            </button>

                            {error && <div className="mt-4 rounded-md bg-red-100 p-4 text-red-700">{error}</div>}

                            {notionPages.length > 0 && (
                                <div className="mt-6">
                                    <h2 className="mb-4 text-xl font-semibold">Results</h2>
                                    <ul className="space-y-2">
                                        {notionPages.map((page) => (
                                            <li key={page.id} className="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                                                <h3 className="font-medium">{page.title}</h3>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">ID: {page.id}</p>
                                                <a
                                                    href={page.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-blue-500 hover:underline"
                                                >
                                                    View Page
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
