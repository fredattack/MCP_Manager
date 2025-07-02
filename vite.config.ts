import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.tsx',
                'resources/js/pages/dashboard.tsx',
                'resources/js/pages/integrations.tsx',
                'resources/js/pages/notion.tsx',
                'resources/js/pages/welcome.tsx',
                'resources/js/pages/ai/claude-chat.tsx',
                'resources/js/pages/integrations/todoist.tsx',
                'resources/js/pages/auth/confirm-password.tsx',
                'resources/js/pages/auth/forgot-password.tsx',
                'resources/js/pages/auth/login.tsx',
                'resources/js/pages/auth/register.tsx',
                'resources/js/pages/auth/reset-password.tsx',
                'resources/js/pages/auth/verify-email.tsx',
                'resources/js/pages/settings/appearance.tsx',
                'resources/js/pages/settings/password.tsx',
                'resources/js/pages/settings/profile.tsx',
            ],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    define: {
        'import.meta.env.VITE_MCP_SERVER_URL': JSON.stringify(process.env.MCP_SERVER_URL || 'http://localhost:9978'),
        'import.meta.env.VITE_MCP_SERVER_USER': JSON.stringify(process.env.MCP_SERVER_USER || 'admin@mcp-server.com'),
        'import.meta.env.VITE_MCP_SERVER_PASSWORD': JSON.stringify(process.env.MCP_SERVER_PASSWORD || 'Admin@123!'),
        'import.meta.env.VITE_MCP_API_TOKEN': JSON.stringify(process.env.MCP_API_TOKEN || ''),
    },
});
