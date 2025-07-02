import apiClient from '@/lib/api/client';
import { useState } from 'react';
import { IntegrationAccount, IntegrationStatus, IntegrationType } from '../types/integrations';
import { useApiToken } from './use-api-token';

interface UseIntegrationsOptions {
    onSuccess?: (data: IntegrationAccount[] | IntegrationAccount | number) => void;
    onError?: (error: Error) => void;
}

export function useIntegrations(options: UseIntegrationsOptions = {}) {
    const { apiToken } = useApiToken();
    const [integrations, setIntegrations] = useState<IntegrationAccount[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<Error | null>(null);

    const fetchIntegrations = async () => {
        setLoading(true);
        setError(null);

        try {
            const headers = apiToken ? { Authorization: `Bearer ${apiToken}` } : {};
            const response = await apiClient.get<IntegrationAccount[]>('/api/integrations', {
                headers,
            });
            setIntegrations(response.data);
            options.onSuccess?.(response.data);
            return response.data;
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to fetch integrations');
            setError(error);
            options.onError?.(error);
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const createIntegration = async (data: { type: IntegrationType; access_token: string; meta?: Record<string, unknown> }) => {
        setLoading(true);
        setError(null);

        try {
            const headers = apiToken ? { Authorization: `Bearer ${apiToken}` } : {};
            const response = await apiClient.post<IntegrationAccount>('/api/integrations', data, {
                headers,
            });
            setIntegrations((prev) => [...prev, response.data]);
            options.onSuccess?.(response.data);
            return response.data;
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to create integration');
            setError(error);
            options.onError?.(error);
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const updateIntegration = async (
        id: number,
        data: {
            access_token?: string;
            meta?: Record<string, unknown>;
            status?: IntegrationStatus;
        },
    ) => {
        setLoading(true);
        setError(null);

        try {
            const headers = apiToken ? { Authorization: `Bearer ${apiToken}` } : {};
            const response = await apiClient.put<IntegrationAccount>(`/api/integrations/${id}`, data, {
                headers,
            });
            setIntegrations((prev) => prev.map((integration) => (integration.id === id ? response.data : integration)));
            options.onSuccess?.(response.data);
            return response.data;
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to update integration');
            setError(error);
            options.onError?.(error);
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const deleteIntegration = async (id: number) => {
        setLoading(true);
        setError(null);

        try {
            const headers = apiToken ? { Authorization: `Bearer ${apiToken}` } : {};
            await apiClient.delete(`/api/integrations/${id}`, {
                headers,
            });
            setIntegrations((prev) => prev.filter((integration) => integration.id !== id));
            options.onSuccess?.(id);
            return id;
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to delete integration');
            setError(error);
            options.onError?.(error);
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return {
        integrations,
        loading,
        error,
        fetchIntegrations,
        createIntegration,
        updateIntegration,
        deleteIntegration,
    };
}
