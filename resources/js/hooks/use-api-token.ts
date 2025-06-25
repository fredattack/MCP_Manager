import axios from 'axios';
import { useCallback, useEffect, useState } from 'react';

export function useApiToken() {
    const [apiToken, setApiToken] = useState<string | null>(localStorage.getItem('api_token'));
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<Error | null>(null);

    const fetchApiToken = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/api-token', { withCredentials: true });
            const token = response.data.api_token;

            // Store the token in localStorage for persistence
            localStorage.setItem('api_token', token);
            setApiToken(token);

            return token;
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to fetch API token');
            setError(error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);

    // Fetch the token on initial load if not already in localStorage
    useEffect(() => {
        if (!apiToken) {
            fetchApiToken().catch(console.error);
        }
    }, [apiToken, fetchApiToken]);

    return {
        apiToken,
        loading,
        error,
        fetchApiToken,
    };
}
