import { chromium, type Browser, type Page } from '@playwright/test';
import { mkdir, writeFile } from 'fs/promises';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const BASE_URL = 'http://localhost:3978';
const SCREENSHOT_DIR = join(__dirname, 'screenshots');

// Viewports for responsive testing
const VIEWPORTS = {
    mobile: { width: 375, height: 667 },
    tablet: { width: 768, height: 1024 },
    desktop: { width: 1440, height: 900 },
};

// Pages to capture
const PAGES = [
    { name: 'homepage', path: '/', requiresAuth: false },
    { name: 'login', path: '/login', requiresAuth: false },
    { name: 'register', path: '/register', requiresAuth: false },
    { name: 'dashboard', path: '/dashboard', requiresAuth: true },
    { name: 'integrations', path: '/integrations', requiresAuth: true },
    { name: 'notion', path: '/notion', requiresAuth: true },
    { name: 'mcp-dashboard', path: '/mcp/dashboard', requiresAuth: true },
    { name: 'settings-profile', path: '/settings/profile', requiresAuth: true },
    { name: 'settings-appearance', path: '/settings/appearance', requiresAuth: true },
];

async function setupDirectories() {
    for (const viewport of Object.keys(VIEWPORTS)) {
        await mkdir(join(SCREENSHOT_DIR, viewport), { recursive: true });
    }
}

async function login(page: Page) {
    console.log('Logging in...');
    await page.goto(`${BASE_URL}/login`);

    // Wait for the page to load
    await page.waitForLoadState('networkidle');

    // Try to login with test credentials or register a new account
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');

    try {
        await page.click('button[type="submit"]');
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 5000 });
        console.log('Login successful');
    } catch {
        console.log('Login failed, attempting to register...');
        await page.goto(`${BASE_URL}/register`);
        await page.waitForLoadState('networkidle');
        await page.fill('input[name="name"]', 'Test User');
        await page.fill('input[name="email"]', 'test@example.com');
        await page.fill('input[name="password"]', 'password123');
        await page.fill('input[name="password_confirmation"]', 'password123');
        await page.click('button[type="submit"]');
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 5000 });
        console.log('Registration successful');
    }
}

async function captureScreenshots() {
    const browser: Browser = await chromium.launch({ headless: true });

    await setupDirectories();

    for (const [viewportName, viewport] of Object.entries(VIEWPORTS)) {
        console.log(`\n📱 Capturing ${viewportName} screenshots...`);

        const context = await browser.newContext({ viewport });
        const page = await context.newPage();

        // Login once for authenticated pages
        let isAuthenticated = false;

        for (const pageConfig of PAGES) {
            try {
                console.log(`  📸 Capturing: ${pageConfig.name}`);

                if (pageConfig.requiresAuth && !isAuthenticated) {
                    await login(page);
                    isAuthenticated = true;
                }

                if (!pageConfig.requiresAuth && isAuthenticated) {
                    // Logout for public pages
                    await context.clearCookies();
                    isAuthenticated = false;
                }

                await page.goto(`${BASE_URL}${pageConfig.path}`, {
                    waitUntil: 'networkidle',
                    timeout: 10000,
                });

                // Wait for content to load
                await page.waitForTimeout(1000);

                const screenshotPath = join(SCREENSHOT_DIR, viewportName, `${pageConfig.name}.png`);
                await page.screenshot({
                    path: screenshotPath,
                    fullPage: true,
                });

                console.log(`    ✓ Saved to ${viewportName}/${pageConfig.name}.png`);
            } catch (error) {
                console.error(`    ✗ Error capturing ${pageConfig.name}:`, error instanceof Error ? error.message : String(error));
            }
        }

        await context.close();
    }

    await browser.close();
    console.log('\n✅ Screenshot capture complete!');
}

// Extract design tokens from the page
async function extractDesignTokens() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    await page.goto(`${BASE_URL}/login`);

    const tokens = await page.evaluate(() => {
        const computedStyle = getComputedStyle(document.documentElement);
        const cssVars: Record<string, string> = {};

        // Extract CSS custom properties
        const style = document.documentElement.style;
        for (let i = 0; i < style.length; i++) {
            const prop = style[i];
            if (prop.startsWith('--')) {
                cssVars[prop] = style.getPropertyValue(prop).trim();
            }
        }

        // Also extract from :root computed style
        const rootStyle = window.getComputedStyle(document.documentElement);
        const allProps = Array.from(rootStyle);
        allProps.forEach(prop => {
            if (prop.startsWith('--')) {
                const value = rootStyle.getPropertyValue(prop).trim();
                if (value && !cssVars[prop]) {
                    cssVars[prop] = value;
                }
            }
        });

        return {
            colors: cssVars,
            fontFamily: computedStyle.fontFamily,
            fontSize: computedStyle.fontSize,
        };
    });

    await browser.close();

    return tokens;
}

async function main() {
    console.log('🚀 Starting UI Audit Screenshot Capture\n');
    console.log(`Base URL: ${BASE_URL}`);
    console.log(`Screenshot directory: ${SCREENSHOT_DIR}\n`);

    try {
        // Capture screenshots
        await captureScreenshots();

        // Extract design tokens
        console.log('\n🎨 Extracting design tokens...');
        const tokens = await extractDesignTokens();

        const tokensPath = join(__dirname, 'design-tokens-extracted.json');
        await writeFile(tokensPath, JSON.stringify(tokens, null, 2));
        console.log(`✓ Design tokens saved to design-tokens-extracted.json`);

    } catch (error) {
        console.error('❌ Error:', error);
        process.exit(1);
    }
}

void main();
