import { useQuery, UseQueryOptions, UseQueryResult } from '@tanstack/react-query';
import { AxiosError } from 'axios';
import { api } from '@/lib/api';

interface ServiceResponse<T> {
    success: boolean;
    data?: T;
    message?: string;
    error?: string;
}

interface UseServiceQueryOptions<TData = unknown> extends Omit<UseQueryOptions<TData, AxiosError>, 'queryKey' | 'queryFn'> {
    onError?: (error: AxiosError) => void;
    onSuccess?: (data: TData) => void;
}

/**
 * Generic hook for querying service endpoints
 * @param queryKey - The query key for caching
 * @param url - The API endpoint URL
 * @param options - Additional query options
 */
export function useServiceQuery<TData = unknown>(
    queryKey: readonly unknown[],
    url: string,
    options?: UseServiceQueryOptions<TData>
): UseQueryResult<TData, AxiosError> {
    return useQuery<TData, AxiosError>({
        queryKey,
        queryFn: async () => {
            const response = await api.get<ServiceResponse<TData>>(url);
            
            if (!response.data.success) {
                throw new Error(response.data.message || 'Request failed');
            }
            
            return response.data.data!;
        },
        ...options,
    });
}

/**
 * Hook for paginated queries
 */
export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export function usePaginatedQuery<TData = unknown>(
    queryKey: readonly unknown[],
    url: string,
    page: number = 1,
    perPage: number = 10,
    options?: UseServiceQueryOptions<PaginatedResponse<TData>>
): UseQueryResult<PaginatedResponse<TData>, AxiosError> {
    const urlWithParams = `${url}?page=${page}&per_page=${perPage}`;
    
    return useServiceQuery<PaginatedResponse<TData>>(
        [...queryKey, { page, perPage }],
        urlWithParams,
        options
    );
}

/**
 * Hook for infinite queries (for infinite scroll)
 */
export { useInfiniteQuery } from '@tanstack/react-query';

/**
 * Prefetch data for a query
 */
export async function prefetchQuery<TData = unknown>(
    queryKey: readonly unknown[],
    url: string
): Promise<void> {
    const { queryClient } = await import('@/lib/react-query');
    
    await queryClient.prefetchQuery({
        queryKey,
        queryFn: async () => {
            const response = await api.get<ServiceResponse<TData>>(url);
            
            if (!response.data.success) {
                throw new Error(response.data.message || 'Request failed');
            }
            
            return response.data.data!;
        },
    });
}