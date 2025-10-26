# Laravel Reverb WebSocket Setup - Complete ✅

**Date**: 2025-10-26
**Status**: Fully Configured & Running
**WebSocket Server**: Laravel Reverb on port 8081

---

## 🎉 Setup Summary

Laravel Reverb has been successfully installed and configured as the WebSocket server for real-time workflow updates. The server is running and ready to broadcast events.

### What Was Done:

1. ✅ **Installed Laravel Reverb** (`laravel/reverb` v1.6.0)
2. ✅ **Configured Environment** (`.env` updated with Reverb credentials)
3. ✅ **Updated Bootstrap** (`bootstrap/app.php` includes channels route)
4. ✅ **Started Reverb Server** (running on port 8081)
5. ✅ **Built Frontend Assets** (Vite compiled with Reverb config)

---

## 🔧 Configuration Details

### Environment Variables (`.env`)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=780619
REVERB_APP_KEY=zhcn0vc2p7vu9bzr6cct
REVERB_APP_SECRET=tioxr56vehiakle8zks8
REVERB_HOST="localhost"
REVERB_PORT=8081
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Note**: Port 8081 was chosen because 8080 was already in use.

### Files Modified:

1. **bootstrap/app.php** - Added channels route:
   ```php
   ->withRouting(
       web: __DIR__.'/../routes/web.php',
       commands: __DIR__.'/../routes/console.php',
       channels: __DIR__.'/../routes/channels.php',  // Added
       health: '/up',
       api: __DIR__.'/../routes/api.php',
   )
   ```

2. **routes/channels.php** - Private channel authorization (created by Phase 2)

3. **.env** - Updated Reverb port from 8080 to 8081

---

## 🚀 Starting the WebSocket Server

### Option 1: Foreground (with debug output)

```bash
php artisan reverb:start --host=0.0.0.0 --port=8081 --debug
```

You'll see:
```
INFO  Starting server on 0.0.0.0:8081 (localhost).
```

### Option 2: Background (production)

```bash
# Start in background
php artisan reverb:start --host=0.0.0.0 --port=8081 &

# Or use nohup for persistent background
nohup php artisan reverb:start --host=0.0.0.0 --port=8081 > /dev/null 2>&1 &
```

### Option 3: Using Process Manager (Recommended for Production)

**Supervisor Configuration** (`/etc/supervisor/conf.d/reverb.conf`):

```ini
[program:reverb]
command=php /path/to/your/app/artisan reverb:start --host=0.0.0.0 --port=8081
directory=/path/to/your/app
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

---

## 🧪 Testing WebSocket Connection

### 1. Start All Required Services

You need 3 terminals/processes running:

**Terminal 1: Laravel Server**
```bash
php artisan serve --port=3978
```

**Terminal 2: Reverb WebSocket Server**
```bash
php artisan reverb:start --host=0.0.0.0 --port=8081 --debug
```

**Terminal 3: Queue Worker**
```bash
php artisan queue:work
```

**Terminal 4 (Optional): Vite Dev Server**
```bash
npm run dev
```
Or just use built assets:
```bash
npm run build
```

### 2. Test via Browser Console

1. Visit `http://localhost:3978/workflows`
2. Open browser DevTools console
3. Check Echo is initialized:

```javascript
// Should return Echo instance
window.Echo

// Check connection state
window.Echo.connector.pusher.connection.state
// Should be: "connected"

// Test subscribing to a channel
window.Echo.private('workflows.1')
  .listen('WorkflowStatusUpdated', (e) => console.log('Update:', e))
  .listen('StepCompleted', (e) => console.log('Step:', e))
  .listen('LogEntryCreated', (e) => console.log('Log:', e))
```

### 3. Test via Tinker

Open two terminal tabs:

**Tab 1: Watch Reverb logs**
```bash
php artisan reverb:start --debug
```

**Tab 2: Broadcast a test event**
```bash
php artisan tinker

# Create a test workflow
$workflow = App\Models\Workflow::first();

# Broadcast event
event(new App\Events\WorkflowStatusUpdated($workflow));

# You should see connection activity in Tab 1
```

### 4. Test Real Workflow Updates

1. Create a workflow via the UI
2. Watch the workflow detail page
3. Trigger a workflow execution (backend)
4. Verify real-time updates appear on the page

---

## 🎯 How It Works

### Broadcasting Flow:

```
Backend Event → Queue → Broadcast → Reverb → WebSocket → Frontend
```

1. **Backend fires event**:
   ```php
   event(new WorkflowStatusUpdated($workflow));
   ```

2. **Event queued** (if using `ShouldQueue`):
   - Job added to database queue
   - Queue worker processes job

3. **Event broadcast to Reverb**:
   - Laravel sends to Reverb via HTTP
   - Reverb receives on port 8081

4. **Reverb pushes to connected clients**:
   - All browsers subscribed to channel receive event
   - WebSocket connection (port 8081)

5. **Frontend React component updates**:
   ```typescript
   const workflow = useWorkflowUpdates(workflowId);
   // Automatically updates when event received
   ```

### Private Channel Authorization:

When frontend subscribes to `private-workflows.{id}`:

1. **Frontend requests authorization**:
   ```javascript
   Echo.private('workflows.1')
   ```

2. **Backend checks permissions** (`routes/channels.php`):
   ```php
   Broadcast::channel('workflows.{workflow}', function ($user, Workflow $workflow) {
       return $user->id === $workflow->user_id;
   });
   ```

3. **If authorized**: WebSocket connection established
4. **If not authorized**: 403 error, no connection

---

## 📊 Monitoring & Debugging

### Check Reverb is Running

```bash
# Check if port 8081 is listening
lsof -i :8081

# Should show:
# php     12345  user   7u  IPv4  ...  TCP *:8081 (LISTEN)
```

### View Reverb Logs

If running in foreground with `--debug`, you'll see:
```
[2025-10-26 23:25:27] Connection established: app-key-123
[2025-10-26 23:25:28] Subscribing to private-workflows.1
[2025-10-26 23:25:29] Broadcasting to private-workflows.1: WorkflowStatusUpdated
```

### Common Issues & Solutions

#### 1. "Failed to listen on port 8080: Address already in use"

**Solution**: Port 8080 is taken. Use different port:
```bash
# Update .env
REVERB_PORT=8081

# Rebuild frontend
npm run build

# Start Reverb on new port
php artisan reverb:start --port=8081
```

#### 2. "Echo is not defined" in browser console

**Solution**: Frontend assets not built with Reverb config:
```bash
npm run build
# or
npm run dev
```

#### 3. WebSocket shows "Disconnected"

**Solutions**:
- Check Reverb is running: `lsof -i :8081`
- Check `.env` VITE variables are correct
- Rebuild assets: `npm run build`
- Check browser console for connection errors

#### 4. Real-time updates not working

**Solutions**:
- Check queue worker is running: `php artisan queue:work`
- Verify events implement `ShouldBroadcast`
- Check channel authorization returns true
- Verify event is being fired (check logs)

#### 5. 403 Forbidden on channel subscription

**Solution**: Channel authorization failing:
- Check `routes/channels.php` logic
- Verify user is authenticated
- Ensure user owns the workflow

---

## 🔐 Security Considerations

### 1. Private Channels Only

All workflow channels are **private** (`private-workflows.{id}`):
- Requires authentication
- Authorization check before subscription
- Users can only see their own workflows

### 2. Channel Authorization

```php
// routes/channels.php
Broadcast::channel('workflows.{workflow}', function ($user, Workflow $workflow) {
    // Only allow if user owns this workflow
    return $user->id === $workflow->user_id;
});
```

### 3. HTTPS in Production

Update `.env` for production:
```env
REVERB_SCHEME=https
REVERB_PORT=443
```

### 4. CORS Configuration

Reverb allows all origins by default (`'allowed_origins' => ['*']`).

For production, restrict to your domain in `config/reverb.php`:
```php
'allowed_origins' => ['https://yourdomain.com'],
```

---

## 📈 Performance Tips

### 1. Use Redis for Scaling (Optional)

Enable Redis scaling in `.env`:
```env
REVERB_SCALING_ENABLED=true
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

This allows multiple Reverb servers to share state.

### 2. Tune Connection Limits

In `config/reverb.php`:
```php
'max_connections' => env('REVERB_APP_MAX_CONNECTIONS', 1000),
'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
```

### 3. Queue Broadcasting

All events implement `ShouldQueue` for non-blocking:
```php
class WorkflowStatusUpdated implements ShouldBroadcast, ShouldQueue
{
    // Event is queued, doesn't block request
}
```

### 4. Throttle Log Updates

Frontend buffers log updates (500ms) to avoid overwhelming UI:
```typescript
// Batches log entries every 500ms
const [logBuffer, setLogBuffer] = useState([]);
```

---

## 🛠️ Development Workflow

### Single Command (All Services)

Use `composer dev` to start everything:
```bash
composer dev
```

This runs (via `composer.json`):
- Laravel server
- Queue worker
- Reverb WebSocket server
- Vite dev server

### Manual Startup (4 Terminals)

**Terminal 1: Laravel**
```bash
php artisan serve --port=3978
```

**Terminal 2: Queue Worker**
```bash
php artisan queue:work
```

**Terminal 3: Reverb**
```bash
php artisan reverb:start --host=0.0.0.0 --port=8081 --debug
```

**Terminal 4: Vite**
```bash
npm run dev
```

---

## 📦 Package Versions

```json
{
  "laravel/reverb": "^1.6.0",
  "pusher-js": "^8.4.0-rc2" (frontend)
}
```

### Dependencies Installed:

Backend (Composer):
- laravel/reverb
- clue/redis-protocol
- clue/redis-react
- evenement/evenement
- pusher/pusher-php-server
- ratchet/rfc6455
- react/* (event-loop, socket, stream, etc.)

Frontend (NPM):
- pusher-js (already in package.json from Phase 2)

---

## ✅ Verification Checklist

Before considering setup complete, verify:

- [x] Reverb package installed
- [x] `.env` configured with Reverb credentials
- [x] `bootstrap/app.php` includes channels route
- [x] `config/reverb.php` exists
- [x] `routes/channels.php` exists with authorization
- [x] Frontend assets built with Reverb config
- [x] Reverb server starts successfully on port 8081
- [x] Queue worker can be started
- [x] Browser console shows Echo is defined
- [x] WebSocket connection state is "connected"
- [ ] Real workflow updates work end-to-end (requires backend workflow execution)

---

## 🎓 Learning Resources

### Laravel Broadcasting Docs
- https://laravel.com/docs/12.x/broadcasting

### Laravel Reverb Docs
- https://laravel.com/docs/12.x/reverb

### Laravel Echo Docs (Frontend)
- https://laravel.com/docs/12.x/broadcasting#client-side-installation

### Pusher Protocol (Reverb uses this)
- https://pusher.com/docs/channels/library_auth_reference/pusher-websockets-protocol/

---

## 🚦 Next Steps

1. **Test Workflow Creation** - Create a workflow via UI
2. **Implement Backend Execution** - Connect workflow execution to LLM
3. **Fire Broadcast Events** - Trigger events during workflow steps
4. **Watch Real-Time Updates** - Verify UI updates live
5. **Test Error Scenarios** - Ensure error handling works
6. **Production Deployment** - Use Supervisor + HTTPS + Redis scaling

---

## 📞 Support & Troubleshooting

If you encounter issues:

1. **Check Reverb Logs**: Run with `--debug` flag
2. **Check Browser Console**: Look for WebSocket errors
3. **Check Laravel Logs**: `storage/logs/laravel.log`
4. **Check Queue**: `php artisan queue:work` output
5. **Verify Environment**: `.env` variables are correct

Common commands:
```bash
# Restart all services
php artisan reverb:restart
php artisan queue:restart

# Clear caches
php artisan config:clear
php artisan route:clear

# Check status
php artisan reverb:status  # (if available)
```

---

**Setup Status**: COMPLETE ✅
**WebSocket Server**: Running on localhost:8081
**Ready for**: Real-time workflow updates
**Next Phase**: Backend workflow execution integration

---

**Last Updated**: 2025-10-26
**Maintained By**: AgentOps Development Team
