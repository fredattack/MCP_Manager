import { test, expect, Page } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';
import * as dotenv from 'dotenv';
import { startAdobeFetch } from '../../scripts/adobe-fetch';

// Load environment variables
dotenv.config({ path: '.env.test' });

// Mock credentials for testing
const TEST_USERNAME = 'info@hddev.be';
const TEST_PASSWORD = 'pdu6twh.pvw7ZFA5unk';
const TEST_LOGIN_URL = 'https://auth.services.adobe.com/fr_FR/deeplink.html?deeplink=ssofirst&callback=https%3A%2F%2Fims-na1.adobelogin.com%2Fims%2Fadobeid%2FSunbreakWebUI1%2FAdobeID%2Ftoken%3Fredirect_uri%3Dhttps%253A%252F%252Faccount.adobe.com%252Forders%252Fbilling-history%2523old_hash%253D%2526from_ims%253Dtrue%253Fclient_id%253DSunbreakWebUI1%2526api%253Dauthorize%2526scope%253DAdobeID%252Copenid%252Cacct_mgmt_api%252Cgnav%252Cread_countries_regions%252Csocial.link%252Cunlink_social_account%252Cadditional_info.address.mail_to%252Cclient.scopes.read%252Cpublisher.read%252Cadditional_info.account_type%252Cadditional_info.roles%252Cadditional_info.social%252Cadditional_info.screen_name%252Cadditional_info.optionalAgreements%252Cadditional_info.secondary_email%252Cadditional_info.secondary_email_verified%252Cadditional_info.phonetic_name%252Cadditional_info.dob%252Cupdate_profile.all%252Csecurity_profile.read%252Csecurity_profile.update%252Cadmin_manage_user_consent%252Cadmin_slo%252Cpiip_write%252Cmps%252Clast_password_update%252Cupdate_email%252Cread_organizations%252Cemail_verification.w%252Cuds_write%252Cuds_read%252Cfirefly_api%252Cpasskey_read%252Cpasskey_write%252Caccount_cluster.read%252Caccount_cluster.update%252Cadditional_info.authenticatingAccount%2526reauth%253Dtrue%26state%3D%257B%2522jslibver%2522%253A%2522v2-v0.31.0-2-g1e8a8a8%2522%252C%2522nonce%2522%253A%25223194402582850829%2522%257D%26code_challenge_method%3Dplain%26use_ms_for_expiry%3Dtrue&client_id=SunbreakWebUI1&scope=AdobeID%2Copenid%2Cacct_mgmt_api%2Cgnav%2Cread_countries_regions%2Csocial.link%2Cunlink_social_account%2Cadditional_info.address.mail_to%2Cclient.scopes.read%2Cpublisher.read%2Cadditional_info.account_type%2Cadditional_info.roles%2Cadditional_info.social%2Cadditional_info.screen_name%2Cadditional_info.optionalAgreements%2Cadditional_info.secondary_email%2Cadditional_info.secondary_email_verified%2Cadditional_info.phonetic_name%2Cadditional_info.dob%2Cupdate_profile.all%2Csecurity_profile.read%2Csecurity_profile.update%2Cadmin_manage_user_consent%2Cadmin_slo%2Cpiip_write%2Cmps%2Clast_password_update%2Cupdate_email%2Cread_organizations%2Cemail_verification.w%2Cuds_write%2Cuds_read%2Cfirefly_api%2Cpasskey_read%2Cpasskey_write%2Caccount_cluster.read%2Caccount_cluster.update%2Cadditional_info.authenticatingAccount%2Creauthenticated&state=%7B%22jslibver%22%3A%22v2-v0.31.0-2-g1e8a8a8%22%2C%22nonce%22%3A%223194402582850829%22%7D&relay=99cc2a3f-9210-465c-89ba-48eb48b9654e&locale=fr_FR&flow_type=token&idp_flow_type=login&reauthenticate=force&s_p=google%2Cfacebook%2Capple%2Cmicrosoft%2Cline%2Ckakao&response_type=token&code_challenge_method=plain&redirect_uri=https%3A%2F%2Faccount.adobe.com%2Forders%2Fbilling-history%23old_hash%3D%26from_ims%3Dtrue%3Fclient_id%3DSunbreakWebUI1%26api%3Dauthorize%26scope%3DAdobeID%2Copenid%2Cacct_mgmt_api%2Cgnav%2Cread_countries_regions%2Csocial.link%2Cunlink_social_account%2Cadditional_info.address.mail_to%2Cclient.scopes.read%2Cpublisher.read%2Cadditional_info.account_type%2Cadditional_info.roles%2Cadditional_info.social%2Cadditional_info.screen_name%2Cadditional_info.optionalAgreements%2Cadditional_info.secondary_email%2Cadditional_info.secondary_email_verified%2Cadditional_info.phonetic_name%2Cadditional_info.dob%2Cupdate_profile.all%2Csecurity_profile.read%2Csecurity_profile.update%2Cadmin_manage_user_consent%2Cadmin_slo%2Cpiip_write%2Cmps%2Clast_password_update%2Cupdate_email%2Cread_organizations%2Cemail_verification.w%2Cuds_write%2Cuds_read%2Cfirefly_api%2Cpasskey_read%2Cpasskey_write%2Caccount_cluster.read%2Caccount_cluster.update%2Cadditional_info.authenticatingAccount%26reauth%3Dtrue&use_ms_for_expiry=true#/';
const TEST_INVOICES_URL = 'https://account.adobe.com/orders/billing-history';

// Test suite for Adobe fetch script
test.describe('Adobe Invoice Fetcher', () => {
  // Test login functionality
  test('should be able to reach the login page', async ({ page }: { page: Page }) => {
    // Navigate to login page
    await page.goto(TEST_LOGIN_URL);

    // Check if we're on a login page (look for email input)
    const emailInput = await page.$('input[type="email"]');
    expect(emailInput).not.toBeNull();
  });

  // Test authentication
  test('should be able to authenticate', async ({ page }: { page: Page }) => {
    // This test is marked as skipped in CI environments to avoid actual authentication
    test.skip(process.env.CI === 'true', 'Skipping authentication test in CI environment');

    // Navigate to login page
      console.log('booooom', TEST_LOGIN_URL);
    await page.goto(TEST_LOGIN_URL);

    // Fill in username
    await page.fill('input[type="email"]', TEST_USERNAME);
    await page.click('button[type="submit"]');

    // Wait for password field
    await page.waitForSelector('input[type="password"]');

    // Fill in password
    await page.fill('input[type="password"]', TEST_PASSWORD);
    await page.click('button[type="submit"]');

    // Wait for successful login (this will depend on Adobe's actual UI)
    await page.waitForNavigation();

    // Check if we're logged in (this will depend on Adobe's actual UI)
    const logoutButton = await page.$('a:has-text("Sign Out"), button:has-text("Sign Out")');
    expect(logoutButton).not.toBeNull();
  });

  // Test navigation to invoices page
  test('should be able to navigate to invoices page', async ({ page }: { page: Page }) => {
    // This test is marked as skipped in CI environments
    test.skip(process.env.CI === 'true', 'Skipping navigation test in CI environment');

    // Login first
    await login(page);

    // Navigate to invoices page
    await page.goto(TEST_INVOICES_URL);

    // Check if we're on the invoices page (this will depend on Adobe's actual UI)
    const invoiceList = await page.$('.invoice-list, .billing-history');
    expect(invoiceList).not.toBeNull();
  });

  // Test invoice metadata extraction
  test('should be able to extract invoice metadata', async ({ page }: { page: Page }) => {
    // This test is marked as skipped in CI environments
    test.skip(process.env.CI === 'true', 'Skipping extraction test in CI environment');

    // Login and navigate to invoices page
    await login(page);
    await page.goto(TEST_INVOICES_URL);

    // Wait for invoices to load
    await page.waitForSelector('.invoice-list, .billing-history');

    // Check if there are invoice items
    const invoiceItems = await page.$$('.invoice-item, .billing-item');
    expect(invoiceItems.length).toBeGreaterThan(0);

    // Check if we can extract data from the first invoice
    const firstInvoice = invoiceItems[0];

    // These selectors will need to be adjusted based on Adobe's actual HTML structure
    const invoiceNumber = await firstInvoice.$('.invoice-number');
    const invoiceDate = await firstInvoice.$('.invoice-date');
    const invoiceAmount = await firstInvoice.$('.invoice-amount');
    const downloadLink = await firstInvoice.$('.download-link');

    expect(invoiceNumber).not.toBeNull();
    expect(invoiceDate).not.toBeNull();
    expect(invoiceAmount).not.toBeNull();
    expect(downloadLink).not.toBeNull();
  });

  // Test logging functionality
  test('should create log file', async () => {
    // Mock the browser functions to avoid actual browser launch
    jest.mock('playwright', () => ({
      chromium: {
        launch: jest.fn().mockResolvedValue({
          newContext: jest.fn().mockResolvedValue({
            newPage: jest.fn().mockResolvedValue({
              goto: jest.fn().mockResolvedValue(null),
              waitForSelector: jest.fn().mockResolvedValue(null),
              fill: jest.fn().mockResolvedValue(null),
              click: jest.fn().mockResolvedValue(null),
              waitForNavigation: jest.fn().mockResolvedValue(null),
              $$: jest.fn().mockResolvedValue([]),
            }),
          }),
          close: jest.fn().mockResolvedValue(null),
        }),
      },
    }));

    // Run the script with mocked functions
    await startAdobeFetch();

    // Check if log file was created
    const logFilePath = path.join(process.cwd(), 'logs', 'adobe-fetch.log');
    expect(fs.existsSync(logFilePath)).toBe(true);
  });
});

// Helper function to login
async function login(page: Page): Promise<void> {
  await page.goto(TEST_LOGIN_URL);
  await page.fill('input[type="email"]', TEST_USERNAME);
  await page.click('button[type="submit"]');
  await page.waitForSelector('input[type="password"]');
  await page.fill('input[type="password"]', TEST_PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForNavigation();
}
