# Monologue Design System Integration Guide

## Overview

This guide explains how to use the newly integrated Monologue design system components in the MCP Manager application. The integration follows a progressive, non-breaking approach where new components coexist with existing ones.

## Quick Start

### View the Design System Showcase

Visit `/design-system` (development only) to see all components in action with side-by-side comparisons.

### Import Components

```tsx
import { MonologueButton } from '@/components/ui/MonologueButton';
import { MonologueCard } from '@/components/ui/MonologueCard';
import { MonologueBadge } from '@/components/ui/MonologueBadge';
```

## Components

### MonologueButton

A flexible button component with dark-first design and smooth transitions.

#### Variants

- **primary** - White background with dark text (main CTAs)
- **secondary** - Semi-transparent white background
- **ghost** - Transparent with brand color text
- **link** - Transparent with underline

#### Sizes

- **sm** - Small (px-3 py-1)
- **md** - Medium (px-4 py-2) - Default
- **lg** - Large (px-5 py-3)

#### Usage Examples

```tsx
// Primary CTA
<MonologueButton variant="primary" size="md">
  Connect Integration
</MonologueButton>

// Secondary action
<MonologueButton variant="secondary" size="sm">
  Learn More
</MonologueButton>

// Ghost button with icon
<MonologueButton variant="ghost" leftIcon={<Icon />}>
  Settings
</MonologueButton>

// Loading state
<MonologueButton variant="primary" loading>
  Syncing...
</MonologueButton>
```

#### Props

```tsx
interface MonologueButtonProps {
  variant?: 'primary' | 'secondary' | 'ghost' | 'link';
  size?: 'sm' | 'md' | 'lg';
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  loading?: boolean;
  disabled?: boolean;
  className?: string;
  // ...extends React.ButtonHTMLAttributes<HTMLButtonElement>
}
```

### MonologueCard

A container component for grouping related content with dark backgrounds.

#### Variants

- **default** - Dark background (#141414) with muted borders
- **elevated** - Lighter background (#282828) with visible borders
- **ghost** - Transparent with no border

#### Padding Options

- **none** - No padding
- **sm** - Small (p-3)
- **md** - Medium (p-5) - Default
- **lg** - Large (p-6)

#### Usage Examples

```tsx
// Basic card with header and body
<MonologueCard>
  <MonologueCard.Header>Integration Settings</MonologueCard.Header>
  <MonologueCard.Body>
    Configure your MCP integration settings here.
  </MonologueCard.Body>
</MonologueCard>

// Elevated card with footer actions
<MonologueCard variant="elevated" padding="lg">
  <MonologueCard.Header>Notion Integration</MonologueCard.Header>
  <MonologueCard.Body>
    Connect your Notion workspace to sync pages and databases.
  </MonologueCard.Body>
  <MonologueCard.Footer>
    <MonologueButton variant="primary">Connect</MonologueButton>
    <MonologueButton variant="ghost">Cancel</MonologueButton>
  </MonologueCard.Footer>
</MonologueCard>

// Minimal ghost card
<MonologueCard variant="ghost" padding="none">
  <MonologueCard.Body>Minimal content</MonologueCard.Body>
</MonologueCard>
```

#### Props

```tsx
interface MonologueCardProps {
  variant?: 'default' | 'elevated' | 'ghost';
  padding?: 'none' | 'sm' | 'md' | 'lg';
  className?: string;
  // ...extends React.HTMLAttributes<HTMLDivElement>
}
```

### MonologueBadge

A small label component for status indicators and tags, optimized for MCP integration statuses.

#### Variants

**Status Variants (for MCP integrations):**
- **active** - Green for active integrations
- **inactive** - Gray for inactive integrations
- **error** - Red for error states
- **pending** - Yellow for pending operations

**Brand Variants:**
- **primary** - Cyan brand color
- **accent** - Light cyan accent
- **default** - Semi-transparent white
- **muted** - Gray muted

#### Sizes

- **sm** - Small (px-2 py-0.5)
- **md** - Medium (px-3 py-1) - Default
- **lg** - Large (px-4 py-1.5)

#### Usage Examples

```tsx
// Status badges for integrations
<MonologueBadge variant="active">Active</MonologueBadge>
<MonologueBadge variant="inactive">Inactive</MonologueBadge>
<MonologueBadge variant="error">Connection Failed</MonologueBadge>
<MonologueBadge variant="pending">Syncing...</MonologueBadge>

// Brand badges
<MonologueBadge variant="primary">New Feature</MonologueBadge>
<MonologueBadge variant="accent">Beta</MonologueBadge>

// Different sizes
<MonologueBadge variant="active" size="sm">Active</MonologueBadge>
<MonologueBadge variant="active" size="lg">Active</MonologueBadge>
```

#### Props

```tsx
interface MonologueBadgeProps {
  variant?: 'default' | 'primary' | 'accent' | 'active' | 'inactive' | 'error' | 'pending' | 'muted';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
  // ...extends React.HTMLAttributes<HTMLSpanElement>
}
```

## Typography

### Font Families

The Monologue design system includes two custom fonts:

- **Instrument Serif** - For headings and display text
- **DM Mono** - For body text and UI elements

#### Tailwind Classes

```tsx
// Instrument Serif (headings)
<h1 className="font-monologue-serif text-4xl">Heading</h1>

// DM Mono (body/UI)
<p className="font-monologue-mono text-sm">Body text</p>
```

## Colors

### Brand Colors

Access via Tailwind classes with `monologue-` prefix:

```tsx
// Brand colors
className="bg-monologue-brand-primary"     // #19d0e8 - Cyan
className="bg-monologue-brand-accent"      // #44ccff - Light cyan
className="bg-monologue-brand-success"     // #a6ee98 - Green

// Neutral colors
className="bg-monologue-neutral-900"       // #010101 - Almost black
className="bg-monologue-neutral-800"       // #141414 - Very dark gray
className="bg-monologue-neutral-700"       // #282828 - Dark gray
className="bg-monologue-neutral-600"       // #3f3f3f - Medium-dark gray
className="bg-monologue-neutral-500"       // #545454 - Medium gray
className="bg-monologue-neutral-100"       // #fbfaf7 - Off-white
className="bg-monologue-neutral-white"     // #ffffff - White
```

### CSS Variables

You can also use CSS variables directly:

```css
var(--monologue-brand-primary)
var(--monologue-brand-accent)
var(--monologue-brand-success)
var(--monologue-bg-primary)
var(--monologue-bg-secondary)
var(--monologue-text-primary)
var(--monologue-text-secondary)
var(--monologue-border-default)
```

## Transitions & Animations

### Timing Functions

```tsx
// Fast transition (200ms)
className="transition-all duration-fast"

// Smooth easing
className="ease-smooth"

// Combined
className="transition-all duration-fast ease-smooth"
```

### Opacity Values

Monologue includes specific opacity tokens:

```tsx
className="opacity-10"  // 0.1
className="opacity-12"  // 0.12
className="opacity-36"  // 0.36
className="opacity-48"  // 0.48
className="opacity-64"  // 0.64
```

## Migration from Existing Components

### Button Migration

**Before (shadcn/ui):**
```tsx
import { Button } from '@/components/ui/button';

<Button variant="default">Click me</Button>
<Button variant="secondary">Cancel</Button>
```

**After (Monologue):**
```tsx
import { MonologueButton } from '@/components/ui/MonologueButton';

<MonologueButton variant="primary">Click me</MonologueButton>
<MonologueButton variant="secondary">Cancel</MonologueButton>
```

### Card Migration

**Before (shadcn/ui):**
```tsx
import { Card } from '@/components/ui/card';

<Card className="p-6">
  <h3>Title</h3>
  <p>Content</p>
</Card>
```

**After (Monologue):**
```tsx
import { MonologueCard } from '@/components/ui/MonologueCard';

<MonologueCard>
  <MonologueCard.Header>Title</MonologueCard.Header>
  <MonologueCard.Body>Content</MonologueCard.Body>
</MonologueCard>
```

### Badge Migration

**Before (shadcn/ui):**
```tsx
import { Badge } from '@/components/ui/badge';

<Badge>Active</Badge>
<Badge variant="destructive">Error</Badge>
```

**After (Monologue):**
```tsx
import { MonologueBadge } from '@/components/ui/MonologueBadge';

<MonologueBadge variant="active">Active</MonologueBadge>
<MonologueBadge variant="error">Error</MonologueBadge>
```

## Accessibility

All Monologue components follow WCAG AA standards:

### Focus States
- All interactive elements have visible focus rings
- Focus ring uses brand primary color: `#19d0e8`
- Ring offset for better visibility on dark backgrounds

### Color Contrast
- Text on backgrounds meets WCAG AA contrast ratios
- Status colors (active, error, pending) tested for accessibility

### Keyboard Navigation
- All components fully keyboard accessible
- Proper ARIA attributes where needed
- Disabled states prevent interaction but remain readable

## Best Practices

### When to Use Monologue Components

✅ **Use Monologue for:**
- New features and pages
- MCP integration panels
- Settings and configuration screens
- Marketing/landing sections

⚠️ **Keep Existing for:**
- Core dashboard (migrate later)
- Auth pages (migrate later)
- Critical user flows (migrate after testing)

### Dark-First Design

Monologue is dark-first. Tips:
- Test on both dark and light modes
- Use semantic color variables for adaptability
- Leverage opacity for layering

### Performance

- All components use `React.forwardRef` for better DOM access
- Loading states prevent layout shift
- Transitions are hardware-accelerated (transform, opacity)

## Component Mapping Reference

| Current Component | Monologue Component | Notes |
|------------------|-------------------|-------|
| `Button` | `MonologueButton` | New variants: ghost, link |
| `Card` | `MonologueCard` | Subcomponents: Header, Body, Footer |
| `Badge` | `MonologueBadge` | Status variants for MCP integrations |

## Testing

Before deploying changes:

1. **Visual Testing**
   - Visit `/design-system` to review components
   - Test dark/light mode
   - Verify responsive behavior

2. **Accessibility Testing**
   - Tab through all interactive elements
   - Test with screen reader
   - Verify color contrast

3. **Browser Testing**
   - Chrome, Firefox, Safari
   - Mobile browsers
   - Check transitions/animations

## Support

For questions or issues:
- Check `/design-system` for visual examples
- Review component source in `resources/js/components/ui/Monologue*.tsx`
- See migration plan in `MIGRATION_PLAN.md`
