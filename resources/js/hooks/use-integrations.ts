import axios from 'axios';
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
            const response = await axios.get<IntegrationAccount[]>('/api/integrations', {
                headers,
                withCredentials: true,
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
            const response = await axios.post<IntegrationAccount>('/api/integrations', data, {
                headers,
                withCredentials: true,
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
            const response = await axios.put<IntegrationAccount>(`/api/integrations/${id}`, data, {
                headers,
                withCredentials: true,
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
            await axios.delete(`/api/integrations/${id}`, {
                headers,
                withCredentials: true,
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
