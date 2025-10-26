import { queryKeys } from '@/lib/react-query';
import { useServiceQuery } from './use-service-query';

// Types
interface NotionPage {
    id: string;
    title: string;
    icon?: string | null;
    cover?: string | null;
    properties?: Record<string, unknown>;
    parent?: {
        type: string;
        page_id?: string;
        database_id?: string;
    };
    children?: NotionPage[];
}

interface NotionDatabase {
    id: string;
    title: string;
    icon?: string | null;
    cover?: string | null;
    properties: Record<string, unknown>;
}

interface NotionBlock {
    id: string;
    type: string;
    [key: string]: unknown;
}

// Query hooks
export function useNotionPages(pageId?: string) {
    const params = pageId ? `?page_id=${pageId}` : '';

    return useServiceQuery<NotionPage[]>(queryKeys.notionPages({ pageId }), `/api/notion/pages-tree${params}`);
}

export function useNotionPage(pageId: string) {
    return useServiceQuery<NotionPage>(queryKeys.notionPage(pageId), `/api/notion/page/${pageId}`, {
        enabled: !!pageId,
    });
}

export function useNotionBlocks(pageId: string) {
    return useServiceQuery<NotionBlock[]>(queryKeys.notionBlocks(pageId), `/api/notion/blocks/${pageId}`, {
        enabled: !!pageId,
    });
}

export function useNotionDatabases() {
    return useServiceQuery<NotionDatabase[]>(queryKeys.notionDatabases(), '/api/notion/databases');
}

// Prefetch functions for better UX
export async function prefetchNotionPages(pageId?: string) {
    const { queryClient } = await import('@/lib/react-query');
    const params = pageId ? `?page_id=${pageId}` : '';

    await queryClient.prefetchQuery({
        queryKey: queryKeys.notionPages({ pageId }),
        queryFn: async () => {
            const { api } = await import('@/lib/api');
            const response = await api.get(`/api/notion/pages-tree${params}`);

            if (!response.data.success) {
                throw new Error(response.data.message || 'Failed to fetch pages');
            }

            return response.data.data;
        },
    });
}

// Combined hook for Notion dashboard
export function useNotionDashboard() {
    const pages = useNotionPages();
    const databases = useNotionDatabases();

    return {
        pages,
        databases,
        isLoading: pages.isLoading || databases.isLoading,
        isError: pages.isError || databases.isError,
        refetchAll: () => {
            pages.refetch();
            databases.refetch();
        },
    };
}
