import { useToast } from '@/hooks/ui/use-toast';
import { api } from '@/lib/api';
import { queryClient } from '@/lib/react-query';
import { useMutation, UseMutationOptions, UseMutationResult } from '@tanstack/react-query';
import { AxiosError } from 'axios';

interface ServiceResponse<T> {
    success: boolean;
    data?: T;
    message?: string;
    error?: string;
}

interface UseServiceMutationOptions<TData = unknown, TVariables = unknown>
    extends Omit<UseMutationOptions<TData, AxiosError, TVariables>, 'mutationFn'> {
    invalidateQueries?: readonly unknown[][];
    showSuccessToast?: boolean;
    showErrorToast?: boolean;
    successMessage?: string;
    errorMessage?: string;
}

type HttpMethod = 'post' | 'put' | 'patch' | 'delete';

/**
 * Generic hook for mutating service endpoints
 */
export function useServiceMutation<TData = unknown, TVariables = unknown>(
    url: string | ((variables: TVariables) => string),
    method: HttpMethod = 'post',
    options?: UseServiceMutationOptions<TData, TVariables>,
): UseMutationResult<TData, AxiosError, TVariables> {
    const { toast } = useToast();
    const {
        invalidateQueries = [],
        showSuccessToast = true,
        showErrorToast = true,
        successMessage,
        errorMessage,
        onSuccess,
        onError,
        ...mutationOptions
    } = options || {};

    return useMutation<TData, AxiosError, TVariables>({
        mutationFn: async (variables: TVariables) => {
            const endpoint = typeof url === 'function' ? url(variables) : url;
            const response = await api[method]<ServiceResponse<TData>>(endpoint, variables);

            if (!response.data.success) {
                throw new Error(response.data.message || 'Request failed');
            }

            return response.data.data!;
        },
        onSuccess: (data, variables, context) => {
            // Invalidate related queries
            invalidateQueries.forEach((queryKey) => {
                queryClient.invalidateQueries({ queryKey });
            });

            // Show success toast
            if (showSuccessToast) {
                toast.success('Success', successMessage || 'Operation completed successfully');
            }

            // Call custom onSuccess handler
            onSuccess?.(data, variables, context);
        },
        onError: (error, variables, context) => {
            // Show error toast
            if (showErrorToast) {
                const responseData = error.response?.data as { message?: string } | undefined;
                const message = responseData?.message || error.message || errorMessage || 'Operation failed';
                toast.error('Error', message);
            }

            // Call custom onError handler
            onError?.(error, variables, context);
        },
        ...mutationOptions,
    });
}

/**
 * Hook for optimistic updates
 */
export function useOptimisticMutation<TData = unknown, TVariables = unknown>(
    url: string | ((variables: TVariables) => string),
    queryKey: readonly unknown[],
    method: HttpMethod = 'put',
    options?: UseServiceMutationOptions<TData, TVariables> & {
        optimisticUpdate: (oldData: TData | undefined, variables: TVariables) => TData;
    },
): UseMutationResult<TData, AxiosError, TVariables> {
    const { optimisticUpdate, ...restOptions } = options || {};

    return useServiceMutation<TData, TVariables>(url, method, {
        ...restOptions,
        onMutate: async (variables) => {
            // Cancel any outgoing refetches
            await queryClient.cancelQueries({ queryKey });

            // Snapshot the previous value
            const previousData = queryClient.getQueryData<TData>(queryKey);

            // Optimistically update to the new value
            if (optimisticUpdate) {
                queryClient.setQueryData<TData>(queryKey, (oldData) => optimisticUpdate(oldData, variables));
            }

            // Return a context object with the snapshotted value
            return { previousData };
        },
        onError: (error, variables, context) => {
            // If the mutation fails, use the context returned from onMutate to roll back
            if (context?.previousData !== undefined) {
                queryClient.setQueryData(queryKey, context.previousData);
            }

            // Call the original onError
            restOptions.onError?.(error, variables, context as { previousData?: TData });
        },
        onSettled: () => {
            // Always refetch after error or success
            queryClient.invalidateQueries({ queryKey });
        },
    });
}

/**
 * Specific hooks for common operations
 */
export function useCreateMutation<TData = unknown, TVariables = unknown>(url: string, options?: UseServiceMutationOptions<TData, TVariables>) {
    return useServiceMutation<TData, TVariables>(url, 'post', {
        successMessage: 'Created successfully',
        ...options,
    });
}

export function useUpdateMutation<TData = unknown, TVariables = unknown>(
    url: string | ((variables: TVariables) => string),
    options?: UseServiceMutationOptions<TData, TVariables>,
) {
    return useServiceMutation<TData, TVariables>(url, 'put', {
        successMessage: 'Updated successfully',
        ...options,
    });
}

export function useDeleteMutation<TData = unknown, TVariables = unknown>(
    url: string | ((variables: TVariables) => string),
    options?: UseServiceMutationOptions<TData, TVariables>,
) {
    return useServiceMutation<TData, TVariables>(url, 'delete', {
        successMessage: 'Deleted successfully',
        ...options,
    });
}
