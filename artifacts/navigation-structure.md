# Navigation Structure Map

**Application:** MCP Manager
**Analysis Date:** 2025-10-25
**Source:** `resources/js/components/app-sidebar.tsx`

---

## Visual Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│ SIDEBAR HEADER                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │  [App Logo]                                             │ │
│ │  (Links to /dashboard)                                  │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ SIDEBAR CONTENT                                              │
│                                                              │
│ MAIN NAVIGATION (NavMain)                                    │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ ☰ Dashboard                    /dashboard               │ │
│ │ ⚙ MCP Dashboard                /mcp/dashboard           │ │
│ │ 🛡 MCP Server Config            /mcp/server/config      │ │
│ │ 💬 Claude Chat                  /ai/claude-chat         │ │
│ │ 🧠 Commandes Naturelles ⚠️      /ai/natural-language    │ │
│ │ 📅 Daily Planning               /daily-planning         │ │
│ │ 🔌 Integration Manager [New]    /integrations/manager   │ │
│ │ 🔌 Integrations (Old) ⚠️        /integrations           │ │
│ │ 📄 Notion Pages                 /notion                 │ │
│ │ 📊 JIRA                         /jira                   │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                              │
│ INTEGRATIONS (IntegrationsNav)                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ ✅ Todoist                      🟢 /integrations/todoist │ │
│ │ 🔌 Google                       ⚫ /integrations/google  │ │
│ │ 📧 Gmail                        ⚫ /gmail                │ │
│ │ 📆 Calendar                     ⚫ /calendar             │ │
│ │ ❌ JIRA ⚠️ (duplicate)          ⚫ /integrations/jira    │ │
│ │ 🚨 Sentry                       🔴 /integrations/sentry │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│ SIDEBAR FOOTER                                               │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ FOOTER NAVIGATION (NavFooter)                           │ │
│ │ 📁 Repository       (external: laravel/react-starter)   │ │
│ │ 📖 Documentation    (external: Laravel docs)            │ │
│ └─────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ USER MENU (NavUser)                                     │ │
│ │ [User Avatar] John Doe                                  │ │
│ │   ↓ Settings                                            │ │
│ │   ↓ Logout                                              │ │
│ └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

**Legend:**
- 🟢 Green dot = Connected
- ⚫ Gray dot = Disconnected
- 🔴 Red dot = Error
- ⚠️ = Identified issue in audit

---

## Route-to-File Mapping

### Main Navigation Pages

| Route | Page Component | Controller | Middleware | Status |
|-------|---------------|------------|------------|--------|
| `/dashboard` | `pages/dashboard.tsx` | Closure | `auth, verified` | ✅ Exists |
| `/mcp/dashboard` | Unknown | `McpIntegrationController@index` | `auth, verified` | ⚠️ Not analyzed |
| `/mcp/server/config` | Unknown | `McpServerConfigController@show` | `auth, verified` | ⚠️ Not analyzed |
| `/ai/claude-chat` | Unknown | Closure | `auth, verified` | ⚠️ Not analyzed |
| `/ai/natural-language` | Unknown | Closure | `auth, verified` | ⚠️ Not analyzed |
| `/daily-planning` | Unknown | `DailyPlanningController@index` | `auth, verified` | ⚠️ Not analyzed |
| `/integrations/manager` | Unknown | `IntegrationManagerController@index` | `auth, verified, EnsureMcpConnection` | ⚠️ Gated |
| `/integrations` | `pages/integrations.tsx` | Closure | `auth, verified` | ✅ Exists |
| `/notion` | Unknown | Closure | `auth, verified` | ⚠️ Not analyzed |
| `/jira` | Unknown | Closure | `auth, verified` | ⚠️ Not analyzed |

### Integration-Specific Pages

| Route | Page Component | Middleware | Status Badge |
|-------|---------------|------------|--------------|
| `/integrations/todoist` | `pages/integrations/todoist.tsx` | `auth, verified, has.integration:todoist` | 🟢 Connected |
| `/integrations/google` | Unknown | `auth, verified` | ⚫ Disconnected |
| `/gmail` | Unknown | `auth, verified, has.integration:gmail` | ⚫ Disconnected |
| `/calendar` | Unknown | `auth, verified, has.integration:calendar` | ⚫ Disconnected |
| `/integrations/jira` | Unknown | `auth, verified` | ⚫ Disconnected |
| `/integrations/sentry` | **No route defined** | N/A | 🔴 Error |

### Settings Pages

| Route | Page Component | Controller | Redirect |
|-------|---------------|------------|----------|
| `/settings` | N/A | N/A | → `/settings/profile` |
| `/settings/profile` | Unknown | `Settings\ProfileController@edit` | - |
| `/settings/password` | Unknown | `Settings\PasswordController@edit` | - |
| `/settings/appearance` | Unknown | Closure | - |

---

## Navigation Issues Summary

### Critical Issues
1. **Duplicate Entries**
   - JIRA appears in both main nav (`/jira`) and integrations nav (`/integrations/jira`)
   - Different routes, unclear purpose differentiation

2. **Language Inconsistency**
   - "Commandes Naturelles" is French while all other items are English
   - No i18n system detected in codebase

3. **Technical Debt Exposed**
   - "Integrations (Old)" suffix signals incomplete migration
   - Confuses users about which page to use

4. **Non-Functional Links**
   - Sentry shows in nav with "error" status but has no defined route
   - Likely placeholder for future integration

5. **Boilerplate Links**
   - Footer links point to Laravel React boilerplate, not project repo
   - Documentation link opens Laravel docs, not project docs

### Middleware Gotchas
Several routes have middleware that blocks access until conditions are met:

- **`has.integration:todoist`** - User must have connected Todoist
- **`has.integration:gmail`** - User must have connected Gmail
- **`has.integration:calendar`** - User must have connected Calendar
- **`EnsureMcpConnection`** - User must have connected MCP server

**Recommendation:** Add user-friendly error messages when middleware blocks access, guiding users to complete setup.

---

## Active State Detection

**Current Implementation:**
```typescript
isActive={item.href === page.url}
```

**Issue:** Exact match only. Nested routes won't show parent as active.

**Example:**
- User on `/integrations/todoist/tasks`
- "Integrations" nav item shows as **inactive** (no highlight)
- Expected: "Integrations" should show active state for child routes

**Recommendation:** Implement prefix matching:
```typescript
isActive={page.url.startsWith(item.href)}
```

---

## Accessibility Notes

### Good Practices
- All nav items have icons for visual scanning
- Tooltips shown when sidebar is collapsed
- Status badges provide at-a-glance integration state

### Issues
- Status badges use color-only indicators (WCAG 1.4.1 violation)
- No aria-label on status dots
- Integration status dots are 2x2px (too small for accessibility)

### Recommended Fix
```typescript
const getStatusBadge = (status?: string) => {
    switch (status) {
        case 'connected':
            return (
                <Badge
                    className="bg-success ml-auto h-2 w-2 p-0"
                    aria-label="Connected"
                    title="Connected"
                />
            );
        // ... similar for other states
    }
};
```

---

## Responsive Behavior

### Desktop (>768px)
- Collapsible sidebar (expands/collapses with Cmd/Ctrl+B)
- Icon-only mode when collapsed (tooltips show on hover)
- Fixed width: 16rem (expanded) / 3rem (collapsed)

### Mobile (<768px)
- Sheet drawer (slides in from left)
- Full overlay when open
- Triggered by hamburger button in header
- Width: 18rem

### Transitions
- 200ms ease-linear for collapse animation
- Potential CLS risk if not measured during animation

---

## Integration Status System

**Data Source:** `integrationStatuses` from Inertia shared props

**Status Flow:**
1. Server loads user's integration accounts
2. Checks status for each service (todoist, gmail, calendar)
3. Passes as shared prop: `{ todoist: 'connected', gmail: 'disconnected' }`
4. AppSidebar maps statuses to navigation items
5. Badge component renders colored dot

**Limitation:** Hardcoded mapping in `app-sidebar.tsx` lines 167-177
- Only supports todoist, gmail, calendar
- New integrations require manual mapping addition
- Consider dynamic status loading for scalability

---

## Recommended Improvements

### Short-Term (Quick Wins)
1. Remove "(Old)" suffix from Integrations nav item
2. Translate "Commandes Naturelles" to "Natural Language"
3. Remove duplicate JIRA entry or differentiate labels
4. Add aria-label to status badges
5. Update footer links to project repo/docs

### Medium-Term (UX Polish)
1. Implement prefix-based active state matching
2. Add visual separator between main nav and integrations
3. Clarify dashboard naming ("Dashboard" vs "MCP Dashboard")
4. Add external link icons to footer items
5. Remove Sentry from nav or implement route

### Long-Term (Architecture)
1. Implement dynamic integration status loading
2. Add i18n system for multilingual support
3. Create navigation configuration file (replace hardcoded arrays)
4. Build integration registry pattern for scalability
5. Add user permission system for conditional nav items

---

**Generated:** 2025-10-25 via static code analysis
**Source Files:**
- `resources/js/components/app-sidebar.tsx`
- `routes/web.php`
- `routes/settings.php`
