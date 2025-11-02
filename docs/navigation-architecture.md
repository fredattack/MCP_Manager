# Navigation Architecture

This document explains how navigation works in the MCP Manager application.

## Overview

The application uses a **single sidebar component** (`app-sidebar.tsx`) as the source of truth for all navigation items.

## Key Files

### Primary Navigation File (EDIT THIS)
- **`resources/js/components/app-sidebar.tsx`** ← **MAIN FILE FOR NAVIGATION**
  - Contains all navigation configuration
  - Three sections: Main Navigation, Integrations, Footer

### Supporting Components (DO NOT EDIT THESE)
- `resources/js/components/nav-main.tsx` - Renders main navigation section
- `resources/js/components/nav-footer.tsx` - Renders footer navigation section
- `resources/js/components/nav-user.tsx` - Renders user profile section
- `resources/js/components/ui/sidebar.tsx` - shadcn/ui sidebar primitives (UI library)

### Layout Components
- `resources/js/layouts/app-layout.tsx` - Main app wrapper
- `resources/js/layouts/app/app-sidebar-layout.tsx` - Sidebar layout template
- `resources/js/components/app-sidebar-header.tsx` - Top header with breadcrumbs

## How to Add/Edit Navigation Items

### 1. Open the Main Navigation File
```bash
resources/js/components/app-sidebar.tsx
```

### 2. Choose the Right Section

#### Main Navigation Items (Line ~55)
```typescript
const mainNavItems: NavItem[] = [
    {
        title: 'Page Title',      // Display name in sidebar
        href: '/route/path',       // Route URL
        icon: LucideIcon,          // Icon from lucide-react
        badge?: 'New',             // Optional badge text
    },
    // Add your item here...
];
```

**Use for**: Dashboard, Workflows, Settings, Features, Tools

#### Integration Items (Line ~119)
```typescript
const integrationItems: NavItem[] = [
    {
        title: 'Service Name',
        href: '/integrations/service',
        icon: LucideIcon,
        status: 'connected',       // connected | disconnected | error
    },
    // Add your item here...
];
```

**Use for**: Third-party service integrations that have connection status

#### Footer Items (Line ~162)
```typescript
const footerNavItems: NavItem[] = [
    {
        title: 'Link Text',
        href: 'https://...',       // Can be external URL
        icon: LucideIcon,
    },
    // Add your item here...
];
```

**Use for**: External links, documentation, repository links

### 3. Import the Icon (if new)

At the top of `app-sidebar.tsx`, add your icon to the imports:

```typescript
import {
    AlertCircle,
    BookOpen,
    // ... other icons
    YourNewIcon,  // ← Add here
} from 'lucide-react';
```

Find icons at: https://lucide.dev/icons

### 4. Save and Test

The dev server will hot-reload automatically. Check the sidebar to see your new item.

## Component Hierarchy

```
All Pages
    ↓
AppLayout
    ↓
AppSidebarLayout
    ├─> AppSidebar ← NAVIGATION DEFINED HERE
    │   ├─> NavMain (renders mainNavItems)
    │   ├─> IntegrationsNav (renders integrationItems)
    │   ├─> NavFooter (renders footerNavItems)
    │   └─> NavUser (user profile)
    │
    └─> AppSidebarHeader (breadcrumbs + MCP status)
```

## Example: Adding a New Page

Let's say you want to add a "Git Connections" page:

### 1. Create the Route
In `routes/web.php`:
```php
Route::get('git/connections', [GitConnectionsController::class, 'index'])
    ->name('git.connections');
```

### 2. Add Navigation Item
In `resources/js/components/app-sidebar.tsx`:
```typescript
import { GitBranch } from 'lucide-react'; // Add icon import

const mainNavItems: NavItem[] = [
    // ... existing items
    {
        title: 'Git Connections',
        href: '/git/connections',
        icon: GitBranch,
    },
    // ... more items
];
```

### 3. Done!
The navigation link will appear automatically in the sidebar.

## Navigation Item Types

```typescript
interface NavItem {
    title: string;           // Display text
    href: string;            // Route path or URL
    icon: LucideIconType;    // Icon component
    badge?: string;          // Optional badge (e.g., "New", "Beta")
    status?: 'connected' | 'disconnected' | 'error'; // For integrations only
}
```

## Common Mistakes to Avoid

### ❌ DON'T Edit Old Sidebar Components
These files were deleted because they're not used:
- ~~`components/common/navigation/Sidebar/Sidebar.tsx`~~ (DELETED)
- ~~`components/common/navigation/Sidebar/SidebarItem.tsx`~~ (DELETED)
- ~~`components/common/navigation/Sidebar/SidebarSection.tsx`~~ (DELETED)

### ✅ DO Edit This File
- `resources/js/components/app-sidebar.tsx` ← **THIS ONE!**

### ❌ DON'T Create Multiple Navigation Sources
Everything should be in `app-sidebar.tsx`

### ✅ DO Keep Navigation in One Place
Single source of truth = easier to maintain

## Dynamic Navigation Features

### Integration Status Badges
Integration items automatically show status badges:
- 🟢 Green dot = `status: 'connected'`
- 🔴 Red dot = `status: 'error'`
- ⚪ Gray dot = `status: 'disconnected'`

Status is dynamically updated from the backend via shared Inertia props:

```typescript
const integrationStatuses = (page.props as SharedData).integrationStatuses || {};
```

### Active Route Highlighting
The current page is automatically highlighted in the sidebar using:
```typescript
isActive={item.href === page.url}
```

## Breadcrumbs

Breadcrumbs are configured per-page, not in the sidebar file:

```typescript
// In your page component
<AppLayout breadcrumbs={[
    { label: 'Home', href: '/dashboard' },
    { label: 'Settings', href: '/settings' },
    { label: 'Profile' }, // No href = current page
]}>
    {/* page content */}
</AppLayout>
```

## Best Practices

1. **Keep navigation items alphabetical** within their section (except Dashboard first)
2. **Use clear, concise titles** (2-3 words max)
3. **Choose appropriate icons** from lucide-react that match the page purpose
4. **Group related items** together in the array
5. **Test the route exists** before adding to navigation
6. **Use badges sparingly** - only for truly new or important features

## Troubleshooting

### "My navigation item doesn't appear"
- Check you're editing `app-sidebar.tsx` (not the old Sidebar.tsx)
- Verify the icon is imported
- Check browser console for errors
- Try hard refresh (Cmd+Shift+R)

### "Icon doesn't show"
- Import the icon from lucide-react at the top
- Ensure icon name matches exactly (case-sensitive)
- Check icon exists at https://lucide.dev/icons

### "Navigation shows old items"
- Hard refresh browser (Cmd+Shift+R)
- Check Vite dev server is running (`npm run dev`)
- Restart dev server if needed

### "Status badge not updating"
- Check backend is sending `integrationStatuses` in Inertia props
- Verify the key matches (e.g., 'todoist', 'gmail', 'calendar')
- Check browser Network tab for prop data

## File Structure Summary

```
resources/js/
├── components/
│   ├── app-sidebar.tsx ✅ MAIN NAVIGATION FILE
│   ├── nav-main.tsx (renders main nav)
│   ├── nav-footer.tsx (renders footer nav)
│   ├── nav-user.tsx (renders user section)
│   ├── app-sidebar-header.tsx (breadcrumbs + status)
│   └── ui/
│       └── sidebar.tsx (shadcn/ui primitives)
├── layouts/
│   ├── app-layout.tsx (main wrapper)
│   └── app/
│       └── app-sidebar-layout.tsx (sidebar layout)
└── pages/
    └── [your pages].tsx
```

## Related Documentation

- [Git OAuth Setup](./git-oauth-setup.md) - Setting up Git integrations
- [Git OAuth Quick Start](./git-oauth-quick-start.md) - Quick setup guide

## Questions?

If you're unsure where to add something:
- **Feature/Tool/Page** → `mainNavItems`
- **Third-party Integration** → `integrationItems`
- **External Link** → `footerNavItems`

When in doubt, add it to `mainNavItems`.
