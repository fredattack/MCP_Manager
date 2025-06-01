import { chromium, Browser, Page } from 'playwright';
import * as fs from 'fs';
import * as path from 'path';
import * as dotenv from 'dotenv';
import axios from 'axios';

// Load environment variables
dotenv.config();

// Define interfaces for invoice data
interface InvoiceMetadata {
  invoiceNumber: string;
  invoiceDate: string;
  amount: number;
  downloadUrl: string;
}

// Logger setup
class Logger {
  private logFilePath: string;

  constructor() {
    const logDir = path.join(process.cwd(), 'logs');
    if (!fs.existsSync(logDir)) {
      fs.mkdirSync(logDir, { recursive: true });
    }
    this.logFilePath = path.join(logDir, 'adobe-fetch.log');
  }

  log(message: string, level: 'info' | 'error' | 'warn' = 'info'): void {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${level.toUpperCase()}] ${message}`;

    // Log to console
    console.log(logMessage);

    // Log to file
    fs.appendFileSync(this.logFilePath, logMessage + '\n');
  }
}

// Create logger instance
const logger = new Logger();

/**
 * Main function to start Adobe invoice fetching process
 */
export async function startAdobeFetch(): Promise<void> {
  logger.log('Starting Adobe invoice fetch process');

  let browser: Browser | null = null;

  try {
    // Get supplier credentials from database or environment
    const username = process.env.ADOBE_USERNAME || 'info@hddev.be';
    const password = process.env.ADOBE_PASSWORD || 'pdu6twh.pvw7ZFA5unk';
    const loginUrl = process.env.ADOBE_LOGIN_URL || 'https://auth.services.adobe.com/fr_FR/deeplink.html?deeplink=ssofirst&callback=https%3A%2F%2Fims-na1.adobelogin.com%2Fims%2Fadobeid%2Fadobedotcom2%2FAdobeID%2Ftoken%3Fredirect_uri%3Dhttps%253A%252F%252Faccount.adobe.com%252F%2523from_ims%253Dtrue%2526old_hash%253D%2526api%253Dauthorize%26scope%3DAdobeID%252Copenid%252Cgnav%252Ccreative_cloud%252Ccreative_sdk%252Cprofile%252Caddress%252Cemail%252Cread_organizations%252Cadditional_info.projectedProductContext%252Cadditional_info.roles&client_id=adobedotcom2';
    const invoicesUrl = process.env.ADOBE_INVOICES_URL || 'https://account.adobe.com/orders/billing-history?search=AE02698100035CBE';

    logger.log(`Connecting to Adobe portal: ${loginUrl}`);

    // Launch browser
    browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    // Login to Adobe
    await login(page, loginUrl, username, password);

    // Navigate to invoices page
    await navigateToInvoices(page, invoicesUrl);

    // Extract invoice metadata
    const invoices = await extractInvoiceMetadata(page);
    logger.log(`Found ${invoices.length} invoices`);

    // Check which invoices need to be downloaded
    const invoicesToDownload = await checkInvoicesToDownload(invoices);
    logger.log(`${invoicesToDownload.length} invoices need to be downloaded`);

    // Mark invoices as downloadable
    await markInvoicesAsDownloadable(invoicesToDownload);

    logger.log('Adobe invoice fetch process completed successfully');
  } catch (error) {
    logger.log(`Error during Adobe invoice fetch: ${error}`, 'error');
  } finally {
    // Close browser
    if (browser) {
      await browser.close();
    }
  }
}

/**
 * Login to Adobe portal
 */
async function login(page: Page, loginUrl: string, username: string, password: string): Promise<void> {
  try {
    logger.log('Attempting to login to Adobe portal');

    // Navigate to login page
    logger.log(`Navigating to login URL:`);
    await page.goto(loginUrl);

    // Log the current URL after navigation
    const _currentUrl = page.url();
    logger.log(`Current page URL after navigation: ${_currentUrl}`);

    // Take a screenshot for debugging
    const screenshotPath = path.join(process.cwd(), 'logs', 'adobe-login-page.png');
    await page.screenshot({ path: screenshotPath });
    logger.log(`Screenshot saved to: ${screenshotPath}`);

    // Log page title
    const title = await page.title();
    logger.log(`Page title: ${title}`);

    // Wait for login form to be visible with increased timeout
    logger.log('Waiting for email input field...');
    try {
      // Try to find the email input with a longer timeout
      await page.waitForSelector('input[type="email"]', { timeout: 60000 });
      logger.log('Email input field found');
    }
    catch (error) {
      logger.log(`Email input not found with standard selector, trying alternative selectors...`, 'warn');

      // Try alternative selectors that might be used for the email field
      const alternativeSelectors = [
        '#EmailPage-EmailField', // Common Adobe selector
        'input[name="username"]', // Generic username field
        'input[name="email"]', // Generic email field
        'input.email-input', // Class-based selector
        'form input[type="text"]' // Any text input in a form
      ];

      let selectorFound = false;
      for (const selector of alternativeSelectors) {
        try {
          logger.log(`Trying selector: ${selector}`);
          await page.waitForSelector(selector, { timeout: 10000 });
          logger.log(`Found alternative input field with selector: ${selector}`);

          // Take another screenshot after finding the alternative selector
          const altScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-login-alt-selector.png');
          await page.screenshot({ path: altScreenshotPath });

          // Use this selector for filling in the username
          await page.fill(selector, username);
          await page.click('button[type="submit"], input[type="submit"]');

          // Wait a moment for the click to register
          await page.waitForTimeout(2000);

          // Navigate to the password page URL
          const passwordPageUrl = 'https://auth.services.adobe.com/fr_FR/deeplink.html#/password';
          logger.log(`Navigating to password page URL: ${passwordPageUrl}`);
          await page.goto(passwordPageUrl);

          // Log the current URL after navigation
          const passwordPageCurrentUrl = page.url();
          logger.log(`Current URL after navigating to password page: ${passwordPageCurrentUrl}`);

          // Take a screenshot after navigation
          const passwordPageScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-password-page-navigation-alt.png');
          await page.screenshot({ path: passwordPageScreenshotPath });
          logger.log(`Screenshot after navigating to password page saved to: ${passwordPageScreenshotPath}`);

          selectorFound = true;
          break;
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (_selectorError) {
          logger.log(`Selector ${selector} not found`, 'warn');
        }
      }

      if (!selectorFound) {
        // If we still can't find any input field, throw the original error
        logger.log(`No input field found after trying all alternative selectors`, 'error');
        throw error;
      }

      // If we found an alternative selector, we need to wait for the password field
      logger.log('Waiting for password field after using alternative selector...');
      try {
        // First, wait for any loading indicators to disappear
        const loadingIndicators = [
          '.loading-indicator',
          '.spinner',
          '[role="progressbar"]'
        ];

        for (const indicator of loadingIndicators) {
          try {
            const loadingElement = await page.$(indicator);
            if (loadingElement) {
              logger.log(`Found loading indicator: ${indicator}, waiting for it to disappear...`);
              await page.waitForSelector(`${indicator}:not(:visible)`, { timeout: 10000 });
            }
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          } catch (_error) {
            // Ignore errors if the loading indicator is not found
          }
        }

        // Wait for the password field to be visible (not just present in DOM)
        await page.waitForSelector('input[type="password"]:visible, input[name="passwd"]:not([aria-hidden="true"])', { timeout: 30000 });
        logger.log('Password field is now visible after using alternative selector');
      } catch (error) {
        logger.log(`Error waiting for password field to become visible after using alternative selector: ${error}`, 'warn');
        // Try an alternative approach - wait a bit and check if password field exists
        logger.log('Trying alternative approach to find password field...');
        await page.waitForTimeout(5000); // Wait 5 seconds

        // Check if password field exists even if hidden
        const hiddenPassword = await page.$('input[type="password"], input[name="passwd"]');
        if (hiddenPassword) {
          logger.log('Found password field but it might be hidden, continuing anyway');
        } else {
          throw new Error('Password field not found after clicking continue button');
        }
      }

      // Take a screenshot before entering password
      const passwordScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-password-page-alt.png');
      await page.screenshot({ path: passwordScreenshotPath });
      logger.log(`Password page screenshot saved to: ${passwordScreenshotPath}`);

      // Fill in password
      logger.log('Filling in password after using alternative selector...');
      try {
        // Try to find the password field with various selectors
        const passwordSelectors = [
          'input[type="password"]:visible',
          'input[name="passwd"]:not([aria-hidden="true"])',
          'input[type="password"]',
          'input[name="passwd"]'
        ];

        let passwordFieldFound = false;
        for (const selector of passwordSelectors) {
          try {
            const passwordField = await page.$(selector);
            if (passwordField) {
              logger.log(`Found password field with selector: ${selector}`);
              await page.fill(selector, password);
              passwordFieldFound = true;
              break;
            }
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          } catch (_error) {
            logger.log(`Failed to fill password with selector: ${selector}`, 'warn');
          }
        }

        if (!passwordFieldFound) {
          throw new Error('Could not find a password field to fill');
        }

        logger.log('Password filled, clicking submit button...');
        await page.click('button[type="submit"], input[type="submit"]');
        logger.log('Submitted password');

        // Wait for navigation after login
        logger.log('Waiting for navigation after login...');
        try {
          await page.waitForNavigation({ timeout: 60000 });
          logger.log('Navigation completed');
        } catch (navError) {
          logger.log(`Navigation timeout after login: ${navError}`, 'warn');
          // Continue anyway, as the page might have changed without a full navigation event
        }

        // Take a screenshot after login attempt
        const afterLoginScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-after-login-alt.png');
        await page.screenshot({ path: afterLoginScreenshotPath });
        logger.log(`After login screenshot saved to: ${afterLoginScreenshotPath}`);

      } catch (error) {
        logger.log(`Error filling password after using alternative selector: ${error}`, 'error');
        throw error;
      }

      return; // Skip the rest of the login process since we've already handled it
    }

    // Fill in username
    await page.fill('input[type="email"]', username);
    logger.log('Email filled, clicking continue button...');
    await page.click('button[type="submit"]');

    // Wait a moment for the click to register
    await page.waitForTimeout(2000);

    // Navigate to the password page URL
    const passwordPageUrl = 'https://auth.services.adobe.com/fr_FR/deeplink.html#/password';
    logger.log(`Navigating to password page URL: ${passwordPageUrl}`);
    await page.goto(passwordPageUrl);

    // Log the current URL after navigation
    const passwordPageCurrentUrl = page.url();
    logger.log(`Current URL after navigating to password page: ${passwordPageCurrentUrl}`);

    // Take a screenshot after navigation
    const passwordPageScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-password-page-navigation.png');
    await page.screenshot({ path: passwordPageScreenshotPath });
    logger.log(`Screenshot after navigating to password page saved to: ${passwordPageScreenshotPath}`);

    // Wait for the continue button to process and the password field to become visible
    logger.log('Waiting for password field to become visible...');
    try {
      // First, wait for any loading indicators to disappear
      const loadingIndicators = [
        '.loading-indicator',
        '.spinner',
        '[role="progressbar"]'
      ];

      for (const indicator of loadingIndicators) {
        try {
          const loadingElement = await page.$(indicator);
          if (loadingElement) {
            logger.log(`Found loading indicator: ${indicator}, waiting for it to disappear...`);
            await page.waitForSelector(`${indicator}:not(:visible)`, { timeout: 20000 });
          }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (_error) {
          // Ignore errors if the loading indicator is not found
        }
      }

      // Wait for the password field to be visible (not just present in DOM)
      await page.waitForSelector('input[type="password"]:visible, input[name="passwd"]:not([aria-hidden="true"])', { timeout: 30000 });
      logger.log('Password field is now visible');
    } catch (error) {
      logger.log(`Error waiting for password field to become visible: ${error}`, 'warn');
      // Try an alternative approach - wait a bit and check if password field exists
      logger.log('Trying alternative approach to find password field...');
      await page.waitForTimeout(5000); // Wait 5 seconds

      // Check if password field exists even if hidden
      const hiddenPassword = await page.$('input[type="password"], input[name="passwd"]');
      if (hiddenPassword) {
        logger.log('Found password field but it might be hidden, continuing anyway');
      } else {
        throw new Error('Password field not found after clicking continue button');
      }
    }

    // Take a screenshot before entering password
    const passwordScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-password-page.png');
    await page.screenshot({ path: passwordScreenshotPath });
    logger.log(`Password page screenshot saved to: ${passwordScreenshotPath}`);

    // Fill in password
    logger.log('Filling in password...');
    try {
      // Try to find the password field with various selectors
      const passwordSelectors = [
        'input[type="password"]:visible',
        'input[name="passwd"]:not([aria-hidden="true"])',
        'input[type="password"]',
        'input[name="passwd"]'
      ];

      let passwordFieldFound = false;
      for (const selector of passwordSelectors) {
        try {
          const passwordField = await page.$(selector);
          if (passwordField) {
            logger.log(`Found password field with selector: ${selector}`);
            await page.fill(selector, password);
            passwordFieldFound = true;
            break;
          }
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (_error) {
          logger.log(`Failed to fill password with selector: ${selector}`, 'warn');
        }
      }

      if (!passwordFieldFound) {
        throw new Error('Could not find a password field to fill');
      }

      logger.log('Password filled, clicking submit button...');
      await page.click('button[type="submit"], input[type="submit"]');
      logger.log('Submitted password');
    } catch (error) {
      logger.log(`Error filling password: ${error}`, 'error');
      throw error;
    }

    // Wait for navigation after login
    logger.log('Waiting for navigation after login...');
    try {
      await page.waitForNavigation({ timeout: 60000 });
      logger.log('Navigation completed');
    } catch (navError) {
      logger.log(`Navigation timeout after login: ${navError}`, 'warn');
      // Continue anyway, as the page might have changed without a full navigation event
    }

    // Take a screenshot after login attempt
    const afterLoginScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-after-login.png');
    await page.screenshot({ path: afterLoginScreenshotPath });
    logger.log(`After login screenshot saved to: ${afterLoginScreenshotPath}`);

    // Log the current URL after login attempt
    const afterLoginUrl = page.url();
    logger.log(`URL after login attempt: ${afterLoginUrl}`);

    // Check if we're logged in by looking for common elements that appear after login
    logger.log('Checking if login was successful...');
    const loggedInIndicators = [
      'a:has-text("Sign Out")',
      'button:has-text("Sign Out")',
      '.user-profile',
      '.account-menu',
      '.user-account'
    ];

    let isLoggedIn = false;
    for (const indicator of loggedInIndicators) {
      try {
        const element = await page.$(indicator);
        if (element) {
          logger.log(`Login confirmed: Found indicator "${indicator}"`);
          isLoggedIn = true;
          break;
        }
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (_error) {
        // Ignore errors when checking for indicators
      }
    }

    if (isLoggedIn) {
      logger.log('Successfully logged in to Adobe portal');
    } else {
      logger.log('Could not confirm successful login, but continuing anyway', 'warn');
    }
  } catch (error) {
    logger.log(`Login failed: ${error}`, 'error');
    throw error;
  }
}

/**
 * Navigate to invoices page
 */
async function navigateToInvoices(page: Page, invoicesUrl: string): Promise<void> {
  try {
    logger.log(`Navigating to invoices page: ${invoicesUrl}`);

    // Log current URL before navigation
    const beforeNavUrl = page.url();
    logger.log(`URL before navigation to invoices: ${beforeNavUrl}`);

    // Take a screenshot before navigation
    const beforeNavScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-before-invoices-nav.png');
    await page.screenshot({ path: beforeNavScreenshotPath });
    logger.log(`Screenshot before navigation saved to: ${beforeNavScreenshotPath}`);

    // Navigate directly to invoices URL
    logger.log('Attempting to navigate to invoices URL...');
    await page.goto(invoicesUrl, { timeout: 60000 });
    logger.log('Navigation to invoices URL completed');

    // Log current URL after navigation
    const afterNavUrl = page.url();
    logger.log(`URL after navigation to invoices: ${afterNavUrl}`);

    // Take a screenshot after navigation
    const afterNavScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-after-invoices-nav.png');
    await page.screenshot({ path: afterNavScreenshotPath });
    logger.log(`Screenshot after navigation saved to: ${afterNavScreenshotPath}`);

    // Wait for invoices to load with increased timeout
    logger.log('Waiting for invoices to load...');
    try {
      await page.waitForSelector('.invoice-list, .billing-history', { timeout: 60000 });
      logger.log('Invoice list or billing history found');
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (_selectorError) {
      logger.log(`Standard invoice selectors not found, trying alternative selectors...`, 'warn');

      // Try alternative selectors that might be used for the invoice list
      const alternativeSelectors = [
        '.orders-list', // Common order list class
        '.order-history',
        '.transaction-history',
        '.billing-table',
        'table.invoices',
        '.invoice-container',
        // Add any selector that might contain invoice data
        'table', // Any table on the page
        '.table', // Common table class
        '[data-testid="orders-table"]', // Test ID selector
        '[data-testid="billing-table"]'
      ];

      let selectorFound = false;
      for (const selector of alternativeSelectors) {
        try {
          logger.log(`Trying alternative invoice selector: ${selector}`);
          await page.waitForSelector(selector, { timeout: 10000 });
          logger.log(`Found alternative invoice element with selector: ${selector}`);
          selectorFound = true;
          break;
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (_altSelectorError) {
          logger.log(`Alternative selector ${selector} not found`, 'warn');
        }
      }

      if (!selectorFound) {
        // If we still can't find any invoice elements, log the HTML for debugging
        logger.log(`No invoice elements found after trying all alternative selectors`, 'error');

        // Get the page HTML for debugging
        const html = await page.content();
        const htmlPath = path.join(process.cwd(), 'logs', 'adobe-invoices-page.html');
        fs.writeFileSync(htmlPath, html);
        logger.log(`Page HTML saved to: ${htmlPath}`, 'warn');

        // Continue anyway, as we might still be able to extract some information
        logger.log('Continuing despite not finding invoice elements', 'warn');
      }
    }

    // Take a final screenshot after waiting for elements
    const finalScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-invoices-page.png');
    await page.screenshot({ path: finalScreenshotPath });
    logger.log(`Final invoices page screenshot saved to: ${finalScreenshotPath}`);

    logger.log('Successfully navigated to invoices page');
  } catch (error) {
    logger.log(`Navigation to invoices page failed: ${error}`, 'error');

    // Take a screenshot on error
    try {
      const errorScreenshotPath = path.join(process.cwd(), 'logs', 'adobe-invoices-error.png');
      await page.screenshot({ path: errorScreenshotPath });
      logger.log(`Error screenshot saved to: ${errorScreenshotPath}`);
    } catch (screenshotError) {
      logger.log(`Failed to take error screenshot: ${screenshotError}`, 'error');
    }

    throw error;
  }
}

/**
 * Extract invoice metadata from the page
 */
async function extractInvoiceMetadata(page: Page): Promise<InvoiceMetadata[]> {
  try {
    logger.log('Extracting invoice metadata');

    // This selector will need to be adjusted based on Adobe's actual HTML structure
    const invoiceElements = await page.$$('.invoice-item, .billing-item');
    const invoices: InvoiceMetadata[] = [];

    for (const element of invoiceElements) {
      // Extract invoice details - selectors will need to be adjusted
      const invoiceNumber = await element.$eval('.invoice-number', el => el.textContent?.trim() || '');
      const invoiceDate = await element.$eval('.invoice-date', el => el.textContent?.trim() || '');
      const amount = await element.$eval('.invoice-amount', el => {
        const amountText = el.textContent?.trim() || '0';
        return parseFloat(amountText.replace(/[^0-9.-]+/g, ''));
      });
      const downloadUrl = await element.$eval('.download-link', el => el.getAttribute('href') || '');

      invoices.push({
        invoiceNumber,
        invoiceDate,
        amount,
        downloadUrl
      });
    }

    logger.log(`Extracted metadata for ${invoices.length} invoices`);
    return invoices;
  } catch (error) {
    logger.log(`Error extracting invoice metadata: ${error}`, 'error');
    throw error;
  }
}

/**
 * Check which invoices need to be downloaded
 */
async function checkInvoicesToDownload(invoices: InvoiceMetadata[]): Promise<InvoiceMetadata[]> {
  try {
    logger.log('Checking which invoices need to be downloaded');

    const invoicesToDownload: InvoiceMetadata[] = [];

    for (const invoice of invoices) {
      // Check if invoice exists in database
      const exists = await checkInvoiceExistsInDatabase(invoice.invoiceNumber);

      // Check if invoice exists on disk
      const existsOnDisk = await checkInvoiceExistsOnDisk(invoice.invoiceNumber);

      if (!exists && !existsOnDisk) {
        invoicesToDownload.push(invoice);
      }
    }

    return invoicesToDownload;
  } catch (error) {
    logger.log(`Error checking invoices to download: ${error}`, 'error');
    throw error;
  }
}

/**
 * Check if invoice exists in database
 */
async function checkInvoiceExistsInDatabase(invoiceNumber: string): Promise<boolean> {
  try {
    // This would be an API call to your Laravel backend
    const response = await axios.get(`/api/invoices/check/${invoiceNumber}`);
    return response.data.exists;
  } catch (error) {
    logger.log(`Error checking if invoice exists in database: ${error}`, 'error');
    // Assume it doesn't exist if there's an error
    return false;
  }
}

/**
 * Check if invoice exists on disk
 */
async function checkInvoiceExistsOnDisk(invoiceNumber: string): Promise<boolean> {
  try {
    // Check in the invoices directory
    const invoicesDir = path.join(process.cwd(), 'invoices', 'adobe');

    // This is a simplified check - in reality, you'd need to search subdirectories
    const files = fs.readdirSync(invoicesDir);
    return files.some(file => file.includes(invoiceNumber));
  } catch (error) {
    logger.log(`Error checking if invoice exists on disk: ${error}`, 'error');
    // Assume it doesn't exist if there's an error
    return false;
  }
}

/**
 * Mark invoices as downloadable
 */
async function markInvoicesAsDownloadable(invoices: InvoiceMetadata[]): Promise<void> {
  try {
    logger.log(`Marking ${invoices.length} invoices as downloadable`);

    // This would be an API call to your Laravel backend
    await axios.post('/api/invoices/mark-downloadable', { invoices });

    logger.log('Successfully marked invoices as downloadable');
  } catch (error) {
    logger.log(`Error marking invoices as downloadable: ${error}`, 'error');
    throw error;
  }
}

// If this script is run directly
// In ES modules, we can check if the current file's URL ends with this filename
const isMainModule = import.meta.url.endsWith('adobe-fetch.ts') || import.meta.url.endsWith('adobe-fetch.js');
if (isMainModule) {
  startAdobeFetch()
    .then(() => process.exit(0))
    .catch(error => {
      console.error('Error:', error);
      process.exit(1);
    });
}
