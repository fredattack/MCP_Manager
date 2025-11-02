# Git OAuth Quick Start Checklist

Follow these steps to get GitHub and GitLab OAuth working in your MCP Manager application.

## ✅ Pre-flight Checklist

- [ ] Laravel application is running (`php artisan serve --port=3978`)
- [ ] You have a GitHub account
- [ ] You have a GitLab account (optional)
- [ ] `.env` file is configured with `APP_URL=http://localhost:3978`

---

## 🔧 GitHub Setup (5 minutes)

### 1. Create GitHub OAuth App
👉 Go to: https://github.com/settings/developers

Click **"New OAuth App"** and fill in:
- **Application name**: `MCP Manager` (or your choice)
- **Homepage URL**: `http://localhost:3978`
- **Authorization callback URL**: `http://localhost:3978/api/git/github/oauth/callback`

### 2. Get Credentials
- Copy the **Client ID**
- Click **"Generate a new client secret"**
- Copy the **Client Secret** (you won't see it again!)

### 3. Update .env
```env
GITHUB_CLIENT_ID=paste_client_id_here
GITHUB_CLIENT_SECRET=paste_client_secret_here
```

### 4. Clear Config Cache
```bash
php artisan config:clear
```

---

## 🔧 GitLab Setup (5 minutes)

### 1. Create GitLab Application
👉 Go to: https://gitlab.com/-/profile/applications

Fill in:
- **Name**: `MCP Manager`
- **Redirect URI**: `http://localhost:3978/api/git/gitlab/oauth/callback`
- **Confidential**: ✅ Yes
- **Scopes**: Select these:
  - ✅ `api`
  - ✅ `read_user`
  - ✅ `read_repository`
  - ✅ `write_repository`

### 2. Get Credentials
- Copy the **Application ID** (this is your Client ID)
- Copy the **Secret**

### 3. Update .env
```env
GITLAB_CLIENT_ID=paste_application_id_here
GITLAB_CLIENT_SECRET=paste_secret_here
```

### 4. Clear Config Cache
```bash
php artisan config:clear
```

---

## 🧪 Test the Integration

### 1. Start the Application
```bash
# Option 1: Start all services
composer dev

# Option 2: Start manually
# Terminal 1:
php artisan serve --port=3978

# Terminal 2:
npm run dev
```

### 2. Navigate to Git Connections
1. Open browser: http://localhost:3978
2. Log in to your account
3. Click **"Git Connections"** in the sidebar (new link!)
4. Click **"Connect GitHub"** or **"Connect GitLab"**

### 3. Authorize
- You'll be redirected to GitHub/GitLab
- Review the permissions
- Click **"Authorize"**
- You'll be redirected back with a success message!

### 4. Verify
You should see:
- ✅ Your username and avatar
- ✅ Connection status: "Connecté" (Active)
- ✅ List of authorized scopes
- ✅ Disconnect and Reconnect buttons

---

## 🐛 Troubleshooting

### "Application not found"
- ❌ OAuth app doesn't exist
- ✅ Create the app in GitHub/GitLab settings

### "Redirect URI mismatch"
- ❌ Callback URL in OAuth app doesn't match your .env
- ✅ Check both URLs are: `http://localhost:3978/api/git/{provider}/oauth/callback`
- ✅ No trailing slash!

### "Invalid client"
- ❌ Client ID or Secret is wrong
- ✅ Re-copy from GitHub/GitLab
- ✅ Run `php artisan config:clear`
- ✅ Restart Laravel server

### "Session expired"
- ❌ Normal if you waited too long (10 min timeout)
- ✅ Just try again

### Still not working?
```bash
# Check logs
tail -f storage/logs/laravel.log

# Verify config is loaded
php artisan tinker
>>> config('services.github')
>>> config('services.gitlab')
```

All values should show your credentials (not null).

---

## 📚 Next Steps

Once connected:
- Sync your repositories
- Set up webhooks for real-time updates
- Clone repositories locally
- Manage workflows

For detailed documentation, see: [docs/git-oauth-setup.md](./git-oauth-setup.md)

---

## 🔗 Quick Links

- **Create GitHub OAuth App**: https://github.com/settings/developers
- **Create GitLab Application**: https://gitlab.com/-/profile/applications
- **Git Connections Page**: http://localhost:3978/git/connections
- **Full Documentation**: [git-oauth-setup.md](./git-oauth-setup.md)

---

## ✨ Summary

**GitHub**:
1. Create OAuth App → https://github.com/settings/developers
2. Copy Client ID & Secret
3. Add to `.env`
4. `php artisan config:clear`
5. Test at http://localhost:3978/git/connections

**GitLab**:
1. Create Application → https://gitlab.com/-/profile/applications
2. Copy Application ID & Secret
3. Add to `.env`
4. `php artisan config:clear`
5. Test at http://localhost:3978/git/connections

**Done!** 🎉
