import '@testing-library/jest-dom';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';

// Nettoyage après chaque test
afterEach(() => {
    cleanup();
});

// Mock de window.matchMedia (pour les tests de responsive)
Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query) => ({
        matches: false,
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
    })),
});

// Mock d'IntersectionObserver (pour les composants avec lazy loading)
global.IntersectionObserver = class IntersectionObserver {
    constructor() {}
    disconnect() {}
    observe() {}
    takeRecords() {
        return [];
    }
    unobserve() {}
} as unknown as typeof IntersectionObserver;

// Mock de window.scrollTo (souvent utilisé)
global.scrollTo = vi.fn();

// Mock de ResizeObserver
global.ResizeObserver = class ResizeObserver {
    constructor() {}
    disconnect() {}
    observe() {}
    unobserve() {}
} as unknown as typeof ResizeObserver;

// Mock de requestAnimationFrame (pour les animations)
global.requestAnimationFrame = vi.fn((callback) => {
    callback(0);
    return 0;
});

global.cancelAnimationFrame = vi.fn();
