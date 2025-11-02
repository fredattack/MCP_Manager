# Git OAuth Setup Guide

This guide will help you configure OAuth authentication for GitHub and GitLab in your MCP Manager application.

## Overview

The application uses OAuth 2.0 with PKCE (Proof Key for Code Exchange) for secure authentication with Git providers. This allows users to connect their GitHub and GitLab accounts to sync repositories.

## Prerequisites

- Laravel application running (default: `http://localhost:3978`)
- Accounts on GitHub and/or GitLab
- Admin access to create OAuth applications

---

## GitHub OAuth Setup

### Step 1: Create a GitHub OAuth App

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click on **"OAuth Apps"** in the left sidebar
3. Click **"New OAuth App"** button

### Step 2: Configure the OAuth App

Fill in the following information:

- **Application name**: `MCP Manager` (or your preferred name)
- **Homepage URL**: `http://localhost:3978` (change to your production URL when deploying)
- **Application description**: (optional) `MCP Manager - Git repository management`
- **Authorization callback URL**: `http://localhost:3978/api/git/github/oauth/callback`

> **Important**: The callback URL must match exactly what's in your `.env` file

### Step 3: Generate Client Secret

1. After creating the app, click **"Generate a new client secret"**
2. **Copy the Client Secret immediately** - you won't be able to see it again!
3. Also copy the **Client ID** from the app details

### Step 4: Configure Environment Variables

Add the following to your `.env` file:

```env
GITHUB_CLIENT_ID=your_github_client_id_here
GITHUB_CLIENT_SECRET=your_github_client_secret_here
GITHUB_REDIRECT_URI=http://localhost:3978/api/git/github/oauth/callback
```

### Step 5: Verify Configuration

Run the following command to check your configuration:

```bash
php artisan tinker
```

Then in tinker:

```php
config('services.github.client_id')
config('services.github.client_secret')
config('services.github.redirect')
```

All values should be populated (not null).

### GitHub OAuth Scopes

The application requests the following scopes:
- `repo` - Full control of private repositories
- `read:user` - Read user profile data
- `user:email` - Read user email addresses

---

## GitLab OAuth Setup

### Step 1: Create a GitLab Application

1. Go to [GitLab Applications](https://gitlab.com/-/profile/applications)
2. Or navigate to: **User Settings** → **Applications**

### Step 2: Configure the Application

Fill in the following information:

- **Name**: `MCP Manager` (or your preferred name)
- **Redirect URI**: `http://localhost:3978/api/git/gitlab/oauth/callback`
- **Confidential**: ✅ **Yes** (checked)
- **Scopes**: Select the following:
  - ✅ `api` - Access the authenticated user's API
  - ✅ `read_user` - Read user information
  - ✅ `read_repository` - Read repositories
  - ✅ `write_repository` - Write to repositories

### Step 3: Save and Copy Credentials

1. Click **"Save application"**
2. **Copy the Application ID** (this is your Client ID)
3. **Copy the Secret** immediately - you won't be able to see it again!

### Step 4: Configure Environment Variables

Add the following to your `.env` file:

```env
GITLAB_CLIENT_ID=your_gitlab_application_id_here
GITLAB_CLIENT_SECRET=your_gitlab_secret_here
GITLAB_REDIRECT_URI=http://localhost:3978/api/git/gitlab/oauth/callback
```

### Step 5: Verify Configuration

Run the following command to check your configuration:

```bash
php artisan tinker
```

Then in tinker:

```php
config('services.gitlab.client_id')
config('services.gitlab.client_secret')
config('services.gitlab.redirect')
```

All values should be populated (not null).

### GitLab OAuth Scopes

The application requests the following scopes:
- `api` - Full API access
- `read_user` - Read user information
- `read_repository` - Read repository data
- `write_repository` - Write to repositories

---

## Testing the OAuth Flow

### 1. Start the Application

```bash
# Terminal 1: Start Laravel server
php artisan serve --port=3978

# Terminal 2: Start Vite dev server (for frontend)
npm run dev
```

Or use the unified command:

```bash
composer dev
```

### 2. Navigate to Git Connections

1. Open your browser to `http://localhost:3978`
2. Log in to the application
3. Navigate to **Git Connections** (in the sidebar)
4. Click **"Connect GitHub"** or **"Connect GitLab"**

### 3. Complete OAuth Flow

1. You'll be redirected to GitHub/GitLab
2. Review the requested permissions
3. Click **"Authorize"**
4. You'll be redirected back to the application
5. You should see a success notification

### 4. Verify Connection

On the Git Connections page, you should see:
- Your username and avatar
- Connection status (Active)
- Authorized scopes
- Options to disconnect or reconnect

---

## Troubleshooting

### Common Issues

#### 1. "Application not found" or 404 error

**Problem**: The OAuth application doesn't exist or the URL is incorrect.

**Solutions**:
- Verify the OAuth app exists in GitHub/GitLab settings
- Check that you're using the correct account
- Ensure the app wasn't deleted

#### 2. "Redirect URI mismatch"

**Problem**: The callback URL in your OAuth app doesn't match your `.env` configuration.

**Solutions**:
- Check `GITHUB_REDIRECT_URI` / `GITLAB_REDIRECT_URI` in `.env`
- Verify the callback URL in GitHub/GitLab settings
- Ensure there are no trailing slashes
- Check the port number matches (default: 3978)

**Example correct URIs**:
```
http://localhost:3978/api/git/github/oauth/callback
http://localhost:3978/api/git/gitlab/oauth/callback
```

#### 3. "Invalid client" or "Client authentication failed"

**Problem**: Client ID or Secret is incorrect or missing.

**Solutions**:
- Verify `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` in `.env`
- Verify `GITLAB_CLIENT_ID` and `GITLAB_CLIENT_SECRET` in `.env`
- Regenerate the secret if needed
- Clear config cache: `php artisan config:clear`
- Restart the Laravel server

#### 4. "Session expired" or "Invalid state"

**Problem**: The OAuth state parameter doesn't match (CSRF protection).

**Solutions**:
- Try the connection again (the error is expected if you wait too long)
- Clear browser cache/cookies
- Check that your cache driver is working: `php artisan cache:clear`

#### 5. "Rate limit exceeded"

**Problem**: Too many requests to GitHub/GitLab API.

**Solutions**:
- Wait a few minutes before trying again
- GitHub: Check your [rate limit status](https://api.github.com/rate_limit)
- GitLab: Check your [rate limit status](https://docs.gitlab.com/ee/security/rate_limits.html)

#### 6. Connection appears but shows "Error" status

**Problem**: Token exchange succeeded but fetching user data failed.

**Solutions**:
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify the scopes are correct
- Try disconnecting and reconnecting
- Check if GitHub/GitLab services are operational

### Debugging Tips

#### Enable Debug Mode

In `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

#### Check Logs

```bash
# Watch Laravel logs in real-time
tail -f storage/logs/laravel.log

# Search for OAuth-related errors
grep -i "oauth" storage/logs/laravel.log
grep -i "github\|gitlab" storage/logs/laravel.log
```

#### Test API Endpoints Manually

```bash
# Test OAuth start endpoint
curl -X POST http://localhost:3978/api/git/github/oauth/start \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"

# Should return: {"auth_url": "https://github.com/login/oauth/authorize?...", ...}
```

#### Check Configuration

```bash
php artisan tinker

# Check GitHub config
config('services.github')

# Check GitLab config
config('services.gitlab')

# Check APP_URL
config('app.url')
```

---

## Production Deployment

### Update Callback URLs

When deploying to production:

1. **Update OAuth Apps**:
   - GitHub: Update callback URL to `https://yourdomain.com/api/git/github/oauth/callback`
   - GitLab: Update redirect URI to `https://yourdomain.com/api/git/gitlab/oauth/callback`

2. **Update Environment Variables**:
   ```env
   APP_URL=https://yourdomain.com
   GITHUB_REDIRECT_URI=https://yourdomain.com/api/git/github/oauth/callback
   GITLAB_REDIRECT_URI=https://yourdomain.com/api/git/gitlab/oauth/callback
   ```

3. **Use HTTPS**: Always use HTTPS in production for security

4. **Clear Caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

### Security Considerations

- **Never commit `.env` file** - it contains secrets
- **Use strong secrets** - regenerate if compromised
- **Rotate secrets regularly** in production
- **Use HTTPS only** in production
- **Monitor failed auth attempts** in logs
- **Set up webhook secrets** for production (optional)

---

## Architecture Overview

### OAuth Flow (PKCE)

1. **User clicks "Connect GitHub/GitLab"**
   - Frontend: `POST /api/git/{provider}/oauth/start`
   - Backend generates: state, code_verifier, code_challenge
   - Stores state in cache (10 min expiry)
   - Returns: auth_url

2. **User redirects to GitHub/GitLab**
   - Authorizes the application
   - GitHub/GitLab redirects back with `code` and `state`

3. **Callback handler processes authorization**
   - Frontend: `GET /api/git/{provider}/oauth/callback?code=...&state=...`
   - Backend validates state (CSRF protection)
   - Exchanges code for access_token (with code_verifier)
   - Fetches user data from provider
   - Creates/updates GitConnection record
   - Redirects to `/git/connections?{provider}_connected=true`

4. **Success notification displayed**
   - Connection appears on the page
   - User can now use Git features

### Database Schema

The `git_connections` table stores:
- `user_id` - The Laravel user
- `provider` - github or gitlab (enum)
- `external_user_id` - Provider's user ID
- `access_token_enc` - Encrypted access token
- `refresh_token_enc` - Encrypted refresh token (if available)
- `scopes` - JSON array of authorized scopes
- `status` - active, expired, error (enum)
- `expires_at` - Token expiration timestamp
- `meta` - JSON with username, email, avatar_url

### Key Files

**Backend**:
- `app/Http/Controllers/Api/GitOAuthController.php` - OAuth endpoints
- `app/Services/Git/GitOAuthService.php` - OAuth logic (PKCE, token exchange)
- `app/Models/GitConnection.php` - Database model
- `app/Enums/GitProvider.php` - Provider enum (GitHub, GitLab)
- `config/services.php` - OAuth configuration

**Frontend**:
- `resources/js/pages/git/connections.tsx` - Git Connections UI
- `resources/js/components/common/navigation/Sidebar/Sidebar.tsx` - Navigation

**Routes**:
- `routes/api.php` - OAuth API endpoints

---

## Support

If you continue to have issues:

1. Check the [GitHub OAuth documentation](https://docs.github.com/en/developers/apps/building-oauth-apps)
2. Check the [GitLab OAuth documentation](https://docs.gitlab.com/ee/api/oauth2.html)
3. Review the application logs
4. Verify all environment variables are set correctly
5. Ensure the Laravel server is running on the correct port

---

## Quick Reference

### GitHub OAuth App URLs
- Create app: https://github.com/settings/developers
- Callback URL: `http://localhost:3978/api/git/github/oauth/callback`

### GitLab OAuth App URLs
- Create app: https://gitlab.com/-/profile/applications
- Callback URL: `http://localhost:3978/api/git/gitlab/oauth/callback`

### Required Environment Variables

```env
# GitHub
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://localhost:3978/api/git/github/oauth/callback

# GitLab
GITLAB_CLIENT_ID=
GITLAB_CLIENT_SECRET=
GITLAB_REDIRECT_URI=http://localhost:3978/api/git/gitlab/oauth/callback
```

### Useful Commands

```bash
# Check configuration
php artisan tinker
config('services.github')
config('services.gitlab')

# Clear caches
php artisan config:clear
php artisan cache:clear

# Watch logs
tail -f storage/logs/laravel.log

# Start servers
composer dev
# or
php artisan serve --port=3978
npm run dev
```
