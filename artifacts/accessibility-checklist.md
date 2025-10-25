# Accessibility Checklist - MCP Manager

**WCAG 2.1 Compliance Audit**
**Date:** 2025-10-25
**Method:** Static code analysis + pattern review
**Target:** Level AA compliance

---

## Summary Score (Estimated)

| Level | Estimated Compliance | Blockers |
|-------|---------------------|----------|
| **WCAG 2.1 Level A** | ~85% | Color-only status indicators |
| **WCAG 2.1 Level AA** | ~70% | Color contrast not verified (requires live testing) |
| **WCAG 2.1 Level AAA** | Not evaluated | Not MVP target |

---

## 1. Perceivable

### 1.1 Text Alternatives

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **1.1.1 Non-text Content (A)** | ⚠️ Partial | Icons in navigation have visual meaning but no explicit alt/aria-label | Verify in live testing; Lucide icons typically inherit title from parent text |

**Findings:**
- Navigation icons rendered via `<item.icon />` component (Lucide React)
- Accompanied by text labels in most cases (good)
- PlaceholderPattern SVG on dashboard has no `aria-hidden` or `<title>` element
- User avatar in NavUser component not analyzed (check if decorative or informative)

**Recommendations:**
```tsx
// For decorative SVGs
<PlaceholderPattern className="..." aria-hidden="true" />

// For informative icons without text
<Mail className="h-4 w-4" aria-label="Gmail integration" />
```

---

### 1.2 Time-based Media
**N/A** - No video or audio content detected

---

### 1.3 Adaptable

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **1.3.1 Info and Relationships (A)** | ✅ Good | Semantic HTML used: `<main>`, `<header>`, `<nav>` (partially) | Add `<nav>` role to sidebar |
| **1.3.2 Meaningful Sequence (A)** | ✅ Good | Navigation items in logical order, breadcrumbs provide context | None |
| **1.3.3 Sensory Characteristics (A)** | ⚠️ Partial | Integration status relies partially on color (green/gray/red dots) | Add text labels or tooltips |

**Findings:**
- **Good:** Card component uses semantic structure (CardHeader, CardTitle, CardContent)
- **Good:** Breadcrumbs component provides hierarchical context
- **Issue:** Integration status badges use only color and position (2x2px colored dots)
- **Issue:** No `<nav>` landmark on sidebar itself (wrapped in generic `<div>`)

**Recommendations:**
```tsx
// In app-sidebar.tsx
<nav aria-label="Main navigation" data-slot="sidebar-navigation">
  <SidebarContent>
    <NavMain items={mainNavItems} />
    <IntegrationsNav items={dynamicIntegrationItems} />
  </SidebarContent>
</nav>
```

---

### 1.4 Distinguishable

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **1.4.1 Use of Color (A)** | ❌ Fail | Integration status badges use ONLY color (green/gray/red) | **CRITICAL** - Add aria-label and text/icon |
| **1.4.2 Audio Control (A)** | ✅ N/A | No auto-playing audio | None |
| **1.4.3 Contrast (Minimum) (AA)** | ⚠️ Unknown | Requires live testing with color picker | Test in browser |
| **1.4.4 Resize Text (AA)** | ✅ Good | TailwindCSS uses rem units, responsive to browser zoom | None |
| **1.4.5 Images of Text (AA)** | ✅ Good | App logo likely SVG or image, but text rendered as HTML | Verify logo is SVG with text fallback |
| **1.4.10 Reflow (AA)** | ✅ Good | Responsive design with mobile Sheet, desktop collapsible sidebar | None |
| **1.4.11 Non-text Contrast (AA)** | ⚠️ Unknown | UI component borders and focus indicators use theme variables | Test in browser |
| **1.4.12 Text Spacing (AA)** | ✅ Good | TailwindCSS defaults support text spacing adjustments | None |
| **1.4.13 Content on Hover/Focus (AA)** | ✅ Good | Tooltips appear on hover (sidebar collapsed), dismissible on mouse leave | None |

**Critical Issue - 1.4.1 Use of Color:**
```tsx
// Current implementation (BAD)
case 'connected':
    return <Badge className="bg-success ml-auto h-2 w-2 p-0" />;

// Recommended fix (GOOD)
case 'connected':
    return (
        <span className="flex items-center gap-1">
            <CheckCircle2 className="h-3 w-3 text-success" aria-hidden="true" />
            <Badge
                className="bg-success ml-auto h-2 w-2 p-0"
                aria-label="Connected"
                title="Connected"
            />
        </span>
    );
```

**Contrast Testing Required:**
- Sidebar text on sidebar background
- Active navigation item background vs. inactive
- Button text on button background
- Focus indicator ring color vs. background
- Placeholder pattern stroke vs. card background

---

## 2. Operable

### 2.1 Keyboard Accessible

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **2.1.1 Keyboard (A)** | ✅ Good | All Inertia Links, Buttons, and interactive elements keyboard-accessible | None |
| **2.1.2 No Keyboard Trap (A)** | ⚠️ Unknown | Requires live testing of modals, dropdowns, Sheet | Test Sheet drawer and DropdownMenu |
| **2.1.4 Character Key Shortcuts (A)** | ✅ Good | Cmd/Ctrl+B requires modifier key (prevents conflicts) | None |

**Findings:**
- **Good:** Sidebar toggle shortcut `Cmd/Ctrl+B` uses modifier (line 95-108 in sidebar.tsx)
- **Good:** Radix UI components (Sheet, DropdownMenu) have built-in focus trapping
- **Good:** `focus-visible:ring-2` used throughout for keyboard focus indicators
- **Unknown:** Tab order through navigation items (requires live test)
- **Unknown:** Focus return after Sheet closes on mobile

**Keyboard Shortcuts Documented:**
- `Cmd/Ctrl + B` - Toggle sidebar

**Testing Checklist:**
- [ ] Tab through all navigation items in order
- [ ] Tab into and out of Sheet drawer on mobile
- [ ] Tab into and out of User dropdown menu
- [ ] Verify focus indicator visible on all interactive elements
- [ ] Verify focus returns to trigger after closing modals
- [ ] Test keyboard navigation with screen reader (VoiceOver/NVDA)

---

### 2.2 Enough Time
**N/A** - No time limits detected

---

### 2.3 Seizures and Physical Reactions

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **2.3.1 Three Flashes or Below (A)** | ✅ Good | No flashing content detected | None |

---

### 2.4 Navigable

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **2.4.1 Bypass Blocks (A)** | ⚠️ Partial | Sidebar and main content separated, but no skip link | Add "Skip to main content" link |
| **2.4.2 Page Titled (A)** | ✅ Good | `<Head title="Dashboard" />` in pages | Verify all pages have unique titles |
| **2.4.3 Focus Order (A)** | ⚠️ Unknown | Requires live testing | Test tab order |
| **2.4.4 Link Purpose (A)** | ✅ Good | Navigation links have descriptive text ("Dashboard", "Integrations", etc.) | None |
| **2.4.5 Multiple Ways (AA)** | ⚠️ Partial | Sidebar navigation + breadcrumbs, no search or sitemap | Add search or sitemap for AA compliance |
| **2.4.6 Headings and Labels (AA)** | ✅ Good | SidebarGroupLabel for "Integrations", page titles present | None |
| **2.4.7 Focus Visible (AA)** | ✅ Good | `focus-visible:ring-2` on interactive elements | Verify ring color has sufficient contrast |

**Recommendations:**
```tsx
// Add skip link in app-sidebar-header.tsx or app-shell.tsx
<a
  href="#main-content"
  className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-white focus:p-4 focus:rounded"
>
  Skip to main content
</a>

// Add id to main content area
<main id="main-content" data-slot="sidebar-inset" ...>
```

---

### 2.5 Input Modalities

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **2.5.1 Pointer Gestures (A)** | ✅ Good | No complex gestures detected (no drag, pinch, swipe) | None |
| **2.5.2 Pointer Cancellation (A)** | ✅ Good | Click/tap handlers on up event (Radix UI default) | None |
| **2.5.3 Label in Name (A)** | ✅ Good | Visible labels match accessible names | None |
| **2.5.4 Motion Actuation (A)** | ✅ Good | No motion-based controls detected | None |
| **2.5.5 Target Size (AA)** | ⚠️ Partial | Navigation buttons likely meet 44x44px, but status badges are 2x2px | Increase status badge size or make parent clickable |

**Findings:**
- Navigation menu buttons use `h-8` class (32px) - **below 44px recommendation**
- Mobile Sheet drawer likely increases touch targets (verify in live test)
- Status badges are 2x2px - **far too small for interaction**
- SidebarMenuButton has `after:absolute after:-inset-2` on mobile (increases hit area) - **good!**

**Recommendations:**
- Status badges should not be interactive (current implementation correct)
- Verify navigation items have adequate spacing on mobile
- Consider increasing `h-8` to `h-11` (44px) for better touch targets

---

## 3. Understandable

### 3.1 Readable

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **3.1.1 Language of Page (A)** | ⚠️ Unknown | Requires checking `<html lang="...">` attribute | Verify in live DOM |
| **3.1.2 Language of Parts (AA)** | ❌ Fail | "Commandes Naturelles" in French without `lang="fr"` attribute | **CRITICAL** - Fix language or add lang attribute |

**Recommendations:**
```tsx
// Option 1: Fix the label (recommended)
{
    title: 'Natural Language',
    href: '/ai/natural-language',
    icon: Brain,
}

// Option 2: Add lang attribute (if keeping French)
<span lang="fr">Commandes Naturelles</span>
```

---

### 3.2 Predictable

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **3.2.1 On Focus (A)** | ✅ Good | No context changes on focus (tooltips appear but don't change page) | None |
| **3.2.2 On Input (A)** | ✅ Good | No form inputs analyzed, Inertia Links don't trigger on input | None |
| **3.2.3 Consistent Navigation (AA)** | ⚠️ Issue | Duplicate JIRA entries, inconsistent integration grouping | Remove duplicate, standardize IA |
| **3.2.4 Consistent Identification (AA)** | ⚠️ Issue | Same service (JIRA) has different icons (Kanban vs. XCircle) | Use consistent icon |

**Findings:**
- Navigation order consistent across pages (Inertia preserves layout)
- **Issue:** JIRA appears twice with different icons and routes
- **Issue:** "Integrations (Old)" signals inconsistent experience
- Integration status badges use consistent color scheme (good)

---

### 3.3 Input Assistance

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **3.3.1 Error Identification (A)** | ⚠️ Unknown | No form validation code analyzed, middleware errors not user-facing | Add error messages for middleware failures |
| **3.3.2 Labels or Instructions (A)** | ⚠️ Unknown | Requires analysis of form pages | Test integration setup forms |
| **3.3.3 Error Suggestion (AA)** | ⚠️ Unknown | No validation logic analyzed | Test form error states |
| **3.3.4 Error Prevention (AA)** | ⚠️ Unknown | No destructive actions analyzed (delete, disconnect) | Test integration disconnect flow |

**Recommendations:**
- Add user-friendly error messages for middleware failures:
  - `has.integration:gmail` → "Connect Gmail to access this feature"
  - `EnsureMcpConnection` → "Connect MCP server to manage integrations"
- Show confirmation dialogs for destructive actions (disconnect integration)
- Provide undo option or grace period for irreversible actions

---

## 4. Robust

### 4.1 Compatible

| Criterion | Status | Evidence | Fix Required |
|-----------|--------|----------|--------------|
| **4.1.1 Parsing (A)** | ✅ Good | React ensures valid HTML, TypeScript prevents type errors | None |
| **4.1.2 Name, Role, Value (A)** | ⚠️ Partial | Radix UI provides ARIA patterns, but custom status badges lack labels | Add aria-label to badges |
| **4.1.3 Status Messages (AA)** | ⚠️ Unknown | No live regions or status messages analyzed | Check toast notifications, error messages |

**Findings:**
- **Good:** Radix UI components (Sheet, Dropdown, Tooltip) have built-in ARIA
- **Good:** SidebarTrigger has `<span className="sr-only">Toggle Sidebar</span>`
- **Good:** Sheet has sr-only SheetTitle and SheetDescription
- **Issue:** Integration status badges have no aria-label or role
- **Unknown:** Flash messages component implementation (not analyzed)

---

## Priority Action Items

### P0 - Critical (Blocks WCAG Level A)
1. **Add aria-label to integration status badges**
   - File: `app-sidebar.tsx` lines 130-140
   - Fix: Add `aria-label="Connected"` etc.
   - Effort: 5 minutes

2. **Fix language inconsistency**
   - File: `app-sidebar.tsx` line 43
   - Fix: Change "Commandes Naturelles" → "Natural Language"
   - Effort: 2 minutes

3. **Add skip-to-main-content link**
   - File: `app-sidebar-header.tsx` or `app-shell.tsx`
   - Fix: Add skip link before sidebar
   - Effort: 10 minutes

### P1 - High (Improves WCAG Level AA)
4. **Add `<nav>` landmark to sidebar**
   - File: `app-sidebar.tsx` line 179-203
   - Fix: Wrap SidebarContent in `<nav aria-label="Main navigation">`
   - Effort: 5 minutes

5. **Add external link indicators**
   - File: `app-sidebar.tsx` footer links
   - Fix: Add ExternalLink icon and `rel="noopener noreferrer"`
   - Effort: 10 minutes

6. **Verify color contrast ratios**
   - Requires: Live browser testing with color picker
   - Fix: Adjust theme colors if needed
   - Effort: 30 minutes

### P2 - Medium (Polish)
7. **Increase touch target size on mobile**
   - File: Navigation menu buttons
   - Fix: Change `h-8` to `h-11` for 44px minimum
   - Effort: 5 minutes

8. **Add user-friendly middleware error messages**
   - Files: Middleware classes
   - Fix: Return Inertia redirect with flash message
   - Effort: 30 minutes per middleware

---

## Testing Checklist

### Automated Testing (Required)
- [ ] Run axe-core on all pages (use axe DevTools browser extension)
- [ ] Run Lighthouse accessibility audit
- [ ] Validate HTML with W3C validator
- [ ] Check color contrast with WebAIM contrast checker

### Manual Testing (Required)
- [ ] Navigate entire site with keyboard only (no mouse)
- [ ] Test with screen reader (VoiceOver on macOS, NVDA on Windows)
- [ ] Zoom page to 200% and verify no content cut off
- [ ] Test in high contrast mode (Windows) or increased contrast (macOS)
- [ ] Test with browser zoom at 400% (1.4.4 Resize Text)

### Assistive Technology Testing (Recommended)
- [ ] Test with Dragon NaturallySpeaking (voice control)
- [ ] Test with ZoomText (screen magnification)
- [ ] Test with browser reader mode (Safari, Firefox)
- [ ] Test with Windows Narrator
- [ ] Test with JAWS (if enterprise license available)

---

## Tools & Resources

### Browser Extensions
- **axe DevTools** - Free accessibility scanner
- **WAVE** - WebAIM evaluation tool
- **Lighthouse** - Built into Chrome DevTools
- **Accessibility Insights** - Microsoft's testing toolkit

### Online Tools
- **WebAIM Contrast Checker** - https://webaim.org/resources/contrastchecker/
- **W3C HTML Validator** - https://validator.w3.org/
- **ARIA Authoring Practices** - https://www.w3.org/WAI/ARIA/apg/

### Screen Readers
- **VoiceOver** - Built into macOS (Cmd+F5)
- **NVDA** - Free for Windows - https://www.nvaccess.org/
- **JAWS** - Paid, industry standard - https://www.freedomscientific.com/

---

**Generated:** 2025-10-25
**Next Review:** After P0 fixes implemented + live browser testing
