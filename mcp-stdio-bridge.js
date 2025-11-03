#!/usr/bin/env node

/**
 * MCP STDIO Bridge
 *
 * This script acts as a bridge between Claude Desktop (STDIO) and the Laravel MCP HTTP endpoint.
 * It reads MCP requests from stdin, forwards them to the Laravel API, and writes responses to stdout.
 */

const https = require('https');
const http = require('http');

// Get configuration from environment variables
const MCP_SERVER_URL = process.env.MCP_SERVER_URL || 'http://localhost:9978/mcp';
const AUTHORIZATION = process.env.AUTHORIZATION || '';

// Parse URL
const url = new URL(MCP_SERVER_URL);
const isHttps = url.protocol === 'https:';
const httpModule = isHttps ? https : http;

// Log to stderr (stdout is reserved for MCP protocol)
function log(message) {
    console.error(`[MCP Bridge] ${message}`);
}

// Send MCP request to Laravel endpoint
function sendRequest(jsonrpcRequest, callback) {
    const data = JSON.stringify(jsonrpcRequest);

    const options = {
        hostname: url.hostname,
        port: url.port || (isHttps ? 443 : 80),
        path: url.pathname,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': Buffer.byteLength(data),
            'Authorization': AUTHORIZATION,
        },
    };

    log(`Sending request to ${MCP_SERVER_URL}`);

    const req = httpModule.request(options, (res) => {
        let responseData = '';

        res.on('data', (chunk) => {
            responseData += chunk;
        });

        res.on('end', () => {
            try {
                const jsonResponse = JSON.parse(responseData);
                callback(null, jsonResponse);
            } catch (error) {
                callback(new Error(`Failed to parse response: ${error.message}`));
            }
        });
    });

    req.on('error', (error) => {
        log(`Request error: ${error.message}`);
        callback(error);
    });

    req.write(data);
    req.end();
}

// Main STDIO loop
function main() {
    log('MCP STDIO Bridge starting...');
    log(`Server URL: ${MCP_SERVER_URL}`);

    let buffer = '';

    process.stdin.setEncoding('utf8');

    process.stdin.on('data', (chunk) => {
        buffer += chunk;

        // Process complete JSON-RPC messages (newline-delimited)
        let newlineIndex;
        while ((newlineIndex = buffer.indexOf('\n')) !== -1) {
            const line = buffer.slice(0, newlineIndex).trim();
            buffer = buffer.slice(newlineIndex + 1);

            if (line.length === 0) continue;

            try {
                const request = JSON.parse(line);
                log(`Received request: ${request.method || 'unknown'}`);

                sendRequest(request, (error, response) => {
                    if (error) {
                        const errorResponse = {
                            jsonrpc: '2.0',
                            id: request.id || null,
                            error: {
                                code: -32603,
                                message: error.message,
                            },
                        };
                        process.stdout.write(JSON.stringify(errorResponse) + '\n');
                    } else {
                        process.stdout.write(JSON.stringify(response) + '\n');
                    }
                });
            } catch (error) {
                log(`Failed to parse request: ${error.message}`);
                const errorResponse = {
                    jsonrpc: '2.0',
                    id: null,
                    error: {
                        code: -32700,
                        message: 'Parse error',
                    },
                };
                process.stdout.write(JSON.stringify(errorResponse) + '\n');
            }
        }
    });

    process.stdin.on('end', () => {
        log('STDIO ended, shutting down...');
        process.exit(0);
    });

    process.on('SIGINT', () => {
        log('Received SIGINT, shutting down...');
        process.exit(0);
    });

    process.on('SIGTERM', () => {
        log('Received SIGTERM, shutting down...');
        process.exit(0);
    });
}

// Start the bridge
main();
