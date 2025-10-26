# Monologue Design System - React Components

This directory contains React component examples based on the design patterns extracted from [monologue.to](https://www.monologue.to/).

## Installation & Setup

1. **Install the required fonts:**

```html
<!-- Add to your HTML <head> -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Mono:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
```

2. **Import the CSS variables:**

```jsx
import '../css/variables.css';
```

3. **Configure Tailwind CSS:**

Use the provided `tailwind.config.js` or merge it with your existing configuration.

## Available Components

### Button

A flexible button component with multiple variants and states.

```jsx
import Button from './Button';

// Variants
<Button variant="primary">Download for Mac</Button>
<Button variant="secondary">Learn More</Button>
<Button variant="ghost">Get Started</Button>
<Button variant="link">View Details</Button>

// Sizes
<Button size="sm">Small</Button>
<Button size="md">Medium</Button>
<Button size="lg">Large</Button>

// States
<Button disabled>Disabled</Button>
<Button loading>Loading...</Button>

// With icons
<Button leftIcon={<DownloadIcon />}>Download</Button>
<Button rightIcon={<ArrowIcon />}>Continue</Button>
```

**Props:**
- `variant`: 'primary' | 'secondary' | 'ghost' | 'link' (default: 'primary')
- `size`: 'sm' | 'md' | 'lg' (default: 'md')
- `leftIcon`: ReactNode
- `rightIcon`: ReactNode
- `disabled`: boolean
- `loading`: boolean
- `className`: string

### Card

A container component for grouping related content.

```jsx
import Card from './Card';

<Card>
  <Card.Header>
    <h3 className="text-h2">Title</h3>
  </Card.Header>
  <Card.Body>
    <p className="text-body-sm">Content goes here</p>
  </Card.Body>
  <Card.Footer>
    <Button>Action</Button>
  </Card.Footer>
</Card>

// Variants
<Card variant="default">Default card</Card>
<Card variant="elevated">Elevated card</Card>
<Card variant="ghost">Ghost card (no background)</Card>

// Padding
<Card padding="none">No padding</Card>
<Card padding="sm">Small padding</Card>
<Card padding="md">Medium padding</Card>
<Card padding="lg">Large padding</Card>
```

**Props:**
- `variant`: 'default' | 'elevated' | 'ghost' (default: 'default')
- `padding`: 'none' | 'sm' | 'md' | 'lg' (default: 'md')
- `className`: string

### Badge

A small label component for status indicators or tags.

```jsx
import Badge from './Badge';

// Variants
<Badge variant="default">Default</Badge>
<Badge variant="primary">Primary</Badge>
<Badge variant="accent">Early Bird</Badge>
<Badge variant="success">Active</Badge>
<Badge variant="muted">Muted</Badge>

// Sizes
<Badge size="sm">Small</Badge>
<Badge size="md">Medium</Badge>
<Badge size="lg">Large</Badge>
```

**Props:**
- `variant`: 'default' | 'primary' | 'accent' | 'success' | 'muted' (default: 'default')
- `size`: 'sm' | 'md' | 'lg' (default: 'md')
- `className`: string

### Input

A form input component with label, help text, and error states.

```jsx
import Input from './Input';

// Basic input
<Input
  label="Email"
  type="email"
  placeholder="you@example.com"
/>

// With error
<Input
  label="Name"
  error="This field is required"
/>

// With help text
<Input
  label="Username"
  helpText="Choose a unique username"
/>

// With icons
<Input
  label="Search"
  leftIcon={<SearchIcon />}
  placeholder="Search..."
/>

// Textarea
<Input
  label="Bio"
  as="textarea"
  rows={4}
  placeholder="Tell us about yourself"
/>
```

**Props:**
- `label`: string
- `error`: string
- `helpText`: string
- `leftIcon`: ReactNode
- `rightIcon`: ReactNode
- `as`: 'input' | 'textarea' (default: 'input')
- `className`: string
- `containerClassName`: string
- All standard input/textarea attributes

## Typography Classes

Use these utility classes for consistent typography:

```jsx
<h1 className="text-display">Large hero text</h1>
<h1 className="text-h1">Heading 1</h1>
<h2 className="text-h2">Heading 2</h2>
<p className="text-body">Body text</p>
<p className="text-body-sm">Small body text</p>
<p className="text-caption">Caption text</p>
```

## Color Usage

```jsx
// Background colors
<div className="bg-background">Primary background</div>
<div className="bg-background-secondary">Secondary background</div>
<div className="bg-background-elevated">Elevated surface</div>

// Text colors
<p className="text-foreground">Primary text</p>
<p className="text-foreground-secondary">Secondary text</p>
<p className="text-foreground-muted">Muted text</p>

// Brand colors
<div className="text-brand-primary">Brand primary</div>
<div className="text-brand-accent">Brand accent</div>
```

## Accessibility

All components include basic accessibility features:

- Proper ARIA attributes
- Focus states with visible focus rings
- Keyboard navigation support
- Semantic HTML elements
- Color contrast compliance (WCAG AA minimum)

## Dark Mode

The design system is dark by default. To implement a light mode, add the `[data-theme="light"]` attribute to your root element and use the commented light mode CSS variables in `variables.css`.

```jsx
// Toggle dark/light mode
<html data-theme="light">
```

## Customization

You can customize components by:

1. **Using the className prop** to add additional Tailwind classes
2. **Modifying the design tokens** in `design-tokens.json`
3. **Updating CSS variables** in `variables.css`
4. **Extending the Tailwind config** in `tailwind.config.js`

## Browser Support

These components work in all modern browsers that support:
- CSS Grid and Flexbox
- CSS Custom Properties (variables)
- ES6+ JavaScript features

For older browsers, consider adding polyfills for custom properties and transpiling the JavaScript.
