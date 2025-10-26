# Task 2.9 - Workflows Client Interface: UX/UI Manifesto

**Version**: 1.0
**Date**: 2025-10-26
**Status**: Design Specification
**Target**: Client-facing `/workflows` interface

---

## Executive Summary

This manifesto defines the complete UX/UI vision for the client-facing workflows interface of AgentOps—the primary interaction point where developers describe tasks in plain English and watch AI agents generate, test, and deploy code autonomously.

**Core User Story**: "A developer describes a task in plain English → AI generates code, tests it, and deploys → What used to take 3 days now takes 30 minutes"

**Design Philosophy**: This is NOT an admin panel. This is the main product experience. Every pixel must inspire confidence, every interaction must feel effortless, and every moment must communicate radical transparency about what the AI is doing.

---

## Table of Contents

1. [Product Context](#1-product-context)
2. [User Journey Mapping](#2-user-journey-mapping)
3. [Information Architecture](#3-information-architecture)
4. [Visual Design Principles](#4-visual-design-principles)
5. [Component Specifications](#5-component-specifications)
6. [Interaction Patterns](#6-interaction-patterns)
7. [Performance & Technical Considerations](#7-performance--technical-considerations)
8. [Differentiation from Admin Interface](#8-differentiation-from-admin-interface)
9. [Success Metrics](#9-success-metrics)
10. [Implementation Guidelines](#10-implementation-guidelines)
11. [Appendix](#11-appendix)

---

## 1. Product Context

### 1.1 Target Persona: The Indie Hacker

**Primary Persona**: Pieter Levels-like developers
- Solo founders building products rapidly
- Value speed and simplicity over enterprise features
- Comfortable with CLI but appreciate beautiful UI
- Want to ship fast, not manage infrastructure
- Skeptical of AI hype but open to real productivity gains

**User Goals**:
1. Describe coding tasks in natural language
2. Watch AI agents work with full transparency
3. Trust the output quality (tests, code review)
4. Iterate quickly on results
5. Deploy without friction

**Pain Points AgentOps Solves**:
- "I spend 3 days on tasks that should take 30 minutes"
- "I don't trust AI-generated code without seeing the tests"
- "I want to automate my workflow but setup is too complex"
- "I need to see what the AI is doing, not just get a result"

### 1.2 Product Principles (from Vision Strategy)

1. **Developer Experience is Everything**
   - UI: Zero configuration, immediate value
   - Copy: Technical but friendly, precise not verbose

2. **Radical Transparency**
   - UI: Real-time logs, step-by-step progress
   - Never hide what the AI is doing

3. **Build for 10x, Not 10%**
   - UI: Focus on 10x faster workflows, not marginal improvements
   - Communicate time saved explicitly

4. **Ship Fast, Learn Faster**
   - UI: One-click deploys, instant feedback loops
   - Show deployment status in real-time

5. **Composability Over Monoliths**
   - UI: Modular workflow steps users can understand
   - Visual representation of workflow pipeline

### 1.3 Competitive Differentiation

**vs. GitHub Copilot**: Not just autocomplete—full task automation with tests and deployment
**vs. Cursor**: Not just an editor—complete DevOps pipeline from description to production
**vs. v0.dev**: Not just UI generation—full-stack features with backend logic and testing

**Our Unique Value**: End-to-end automation with radical transparency, designed for solo developers who ship fast.

---

## 2. User Journey Mapping

### 2.1 Complete User Journey (7 Steps)

#### Step 1: Landing on `/workflows`

**User State**: First-time visitor or returning user
**Goal**: Understand what workflows are and start one

**UI Elements**:
- Hero section with clear value proposition
- List of existing workflows (if any)
- Prominent "Create Workflow" CTA
- Empty state for first-time users

**Success Criteria**:
- User understands what workflows do within 5 seconds
- CTA is obvious and inviting
- Empty state inspires action (not intimidation)

**Emotional Arc**: Curiosity → Interest → Motivation to try

---

#### Step 2: Selecting Repository

**User State**: Motivated to create a workflow
**Goal**: Choose which codebase to work on

**UI Elements**:
- Repository selector (dropdown or cards)
- Repository metadata (language, last commit, file count)
- Inline help: "Don't see your repo? Connect GitHub/GitLab"

**Success Criteria**:
- Repository selection is one click
- Users can quickly identify their repo (search, icons)
- Clear path to add new repos if needed

**Emotional Arc**: Motivation → Focused action

---

#### Step 3: Configuring Workflow Task

**User State**: Repository selected, ready to describe task
**Goal**: Write a clear task description in plain English

**UI Elements**:
- Large textarea with placeholder examples
- Suggested prompts: "Add user authentication", "Create REST API for products", etc.
- Optional: LLM provider selector (with smart default)
- Advanced options (collapsed by default): code analysis, test requirements

**Success Criteria**:
- User writes task description naturally (no jargon required)
- Examples inspire without overwhelming
- Advanced users can customize without cluttering UI for beginners

**Emotional Arc**: Focused action → Confidence → Anticipation

**Copy Examples**:
```
Placeholder: "Describe what you want to build in plain English.
Examples:
- Add user authentication with email/password
- Create a REST API for managing blog posts
- Add dark mode support to the dashboard"
```

---

#### Step 4: Executing Workflow

**User State**: Task submitted, workflow running
**Goal**: Monitor progress and understand what's happening

**UI Elements**:
- Real-time status indicator (WebSocket-powered)
- Timeline/progress bar with completed steps
- Live log stream (collapsible, filterable)
- Estimated time remaining (if calculable)

**Success Criteria**:
- User sees immediate feedback (not stuck on loading spinner)
- Progress is granular enough to show activity (not "Processing...")
- Logs are readable but hideable for users who don't care

**Emotional Arc**: Anticipation → Engaged observation → Trust building

**Real-Time Updates** (via Laravel Echo):
```typescript
// Example broadcast events:
- WorkflowStatusUpdated (status: 'analyzing', 'generating', 'testing', 'deploying')
- StepCompleted (step: { name: 'Repository cloned', duration: '2.3s' })
- LogEntryCreated (log: { level: 'info', message: 'Running PHPUnit tests...' })
```

---

#### Step 5: Watching Progress (Core Experience)

**User State**: Actively watching workflow execute
**Goal**: Understand each step and feel confident in the process

**UI Elements**:
- **Step Timeline** (visual representation):
  1. ✅ Analyze Repository (2.3s)
  2. ✅ Generate Code (8.1s)
  3. 🔄 Run Tests (in progress...)
  4. ⏸️ Deploy (pending)

- **Live Log Viewer**:
  - Syntax-highlighted logs
  - Filterable by level (info, warning, error)
  - Expandable/collapsible sections
  - Auto-scroll with pause option

- **Current Activity Indicator**:
  - Animated pulse on active step
  - Brief description: "Running PHPUnit tests on 12 test cases..."

**Success Criteria**:
- User never wonders "Is it still working?"
- User can peek into details (logs) or stay high-level (timeline)
- Errors are surfaced immediately with actionable messages

**Emotional Arc**: Trust building → Fascination → Satisfaction (when completed)

**Visual Design Notes**:
- Use Monologue cyan (#19d0e8) for active step pulse
- Instrument Serif for step names (clear hierarchy)
- DM Mono for log entries (code context)
- Dark background (#121212) with subtle step cards

---

#### Step 6: Reviewing Results

**User State**: Workflow completed (success or failure)
**Goal**: Review generated code, tests, and deployment status

**UI Elements** (Success State):
- **Summary Card**:
  - "✅ Workflow completed in 47 seconds"
  - Files changed: 12 files, +247 lines, -89 lines
  - Tests passed: 12/12
  - Deployment: ✅ Deployed to production

- **Code Diff Viewer**:
  - Side-by-side or unified diff
  - Syntax-highlighted
  - Downloadable or copy to clipboard

- **Test Results**:
  - Pass/fail status for each test
  - Coverage metrics (if available)
  - Link to full test output

- **Deployment Info**:
  - URL of deployed app (if applicable)
  - Deployment logs (collapsible)

**UI Elements** (Failure State):
- **Error Summary**:
  - "❌ Workflow failed at: Run Tests"
  - Clear error message in plain English
  - Link to full error logs

- **Actionable Next Steps**:
  - "Retry workflow"
  - "Edit task description and try again"
  - "View error logs for debugging"

**Success Criteria**:
- User immediately knows if workflow succeeded or failed
- Results are detailed enough for review but scannable
- Clear path forward for both success and failure cases

**Emotional Arc**:
- Success: Satisfaction → Delight → Trust in product
- Failure: Disappointment → Understanding → Motivation to retry

---

#### Step 7: Iterating (Optional)

**User State**: Reviewed results, wants to improve or extend
**Goal**: Create follow-up workflow or refine existing task

**UI Elements**:
- "Create follow-up workflow" button (pre-fills context)
- "Edit and re-run" button
- "Share workflow" (copy link or export config)

**Success Criteria**:
- Iteration feels seamless (no starting from scratch)
- Users can build on previous work easily

**Emotional Arc**: Trust in product → Long-term engagement → Advocacy

---

### 2.2 User Journey Decision Points

| Decision Point | User Question | UI Answer |
|---------------|---------------|-----------|
| Landing | "Is this worth my time?" | Clear value prop + social proof (if available) |
| Repo Selection | "Which repo should I use?" | Visual cards with metadata + search |
| Task Config | "What should I write?" | Example prompts + placeholder text |
| Executing | "Is this stuck?" | Real-time progress + live logs |
| Watching | "Can I trust this?" | Transparent logs + test results |
| Results | "Did it work?" | Clear success/failure + actionable next steps |
| Iterating | "What's next?" | One-click follow-ups + sharing |

---

## 3. Information Architecture

### 3.1 Page Structure

#### `/workflows` (Index Page)

**Primary Content**:
1. **Hero Section** (if no workflows exist):
   - Headline: "Ship code 10x faster with AI agents"
   - Subheadline: "Describe tasks in plain English. Watch AI generate, test, and deploy code."
   - CTA: "Create Your First Workflow"

2. **Workflows List** (if workflows exist):
   - Grouped by status: Running (top), Completed, Failed
   - Each workflow card shows:
     - Task description (truncated to 1 line)
     - Repository name + icon
     - Status badge (Running, Completed, Failed)
     - Created timestamp
     - Duration (if completed)

3. **Filters & Search** (if 10+ workflows):
   - Search by task description
   - Filter by status, repository, LLM provider
   - Sort by: Most recent, Duration, Success rate

**Secondary Content**:
- "Create Workflow" FAB (floating action button) in bottom-right
- Empty state (if no workflows + first-time user):
  - Illustration (optional)
  - Clear CTA: "Create Your First Workflow"
  - Benefits list: "✅ AI generates code ✅ Tests automatically ✅ Deploys instantly"

**Content Hierarchy**:
1. **Priority 1**: CTA to create workflow (FAB or hero button)
2. **Priority 2**: Running workflows (live status)
3. **Priority 3**: Completed/failed workflows (history)

---

#### `/workflows/{id}` (Workflow Detail Page)

**Primary Content**:
1. **Header**:
   - Task description (full text, editable if workflow not started)
   - Repository name + link
   - Status badge (large, animated if running)
   - Action buttons: "Re-run", "Edit", "Delete"

2. **Main Content** (varies by status):

   **If Running**:
   - **Progress Timeline** (visual step-by-step):
     - Step 1: ✅ Analyze Repository (2.3s)
     - Step 2: 🔄 Generate Code (in progress, 8.1s elapsed)
     - Step 3: ⏸️ Run Tests (pending)
     - Step 4: ⏸️ Deploy (pending)

   - **Live Log Stream**:
     - Auto-scrolling terminal-style output
     - Filterable by level (info, warning, error)
     - Pause/resume auto-scroll
     - Download logs button

   **If Completed**:
   - **Summary Card**:
     - Total duration
     - Files changed (with diff stats)
     - Tests passed/failed
     - Deployment status + URL

   - **Code Diff Viewer** (tabbed):
     - Tab 1: All Changes
     - Tab 2: Generated Files
     - Tab 3: Modified Files
     - Tab 4: Deleted Files

   - **Test Results**:
     - Pass/fail summary
     - Individual test cases (expandable)
     - Coverage metrics (if available)

   - **Deployment Logs** (collapsible):
     - Deployment commands executed
     - Output from deployment process
     - Final deployment URL

   **If Failed**:
   - **Error Summary**:
     - Step where failure occurred
     - Error message (plain English)
     - Full stack trace (collapsible)

   - **Actionable Next Steps**:
     - "Retry workflow" button
     - "Edit task and re-run" button
     - "View logs" button (jumps to log section)

3. **Sidebar** (optional, for desktop):
   - Workflow metadata:
     - Created by: User name
     - Created at: Timestamp
     - LLM provider: OpenAI GPT-4
     - Repository: link
   - Related workflows (if any)

**Secondary Content**:
- Breadcrumb navigation: Workflows > [Task description]
- Share workflow button (copy link)
- Export workflow config (JSON download)

**Content Hierarchy**:
1. **Priority 1**: Current status (timeline/progress for running, summary for completed)
2. **Priority 2**: Live logs (running) or results (completed)
3. **Priority 3**: Metadata and secondary actions

---

### 3.2 Navigation Structure

```
/workflows (Index)
  ├── Create Workflow (modal or inline form)
  │   ├── Select Repository
  │   ├── Describe Task
  │   └── Configure Options (advanced, collapsed)
  │
  └── /workflows/{id} (Detail)
      ├── Progress/Results (main content)
      ├── Logs (collapsible)
      ├── Actions (re-run, edit, delete)
      └── Related Workflows (sidebar)
```

**Breadcrumb Trail**:
- Workflows > Create
- Workflows > [Task description]

---

### 3.3 State Management

#### Client State (React/Inertia)
- Current page data (workflows list or single workflow)
- UI state: filters, sort order, log visibility
- Form state: create/edit workflow inputs

#### Server State (Laravel)
- Workflows database records
- User permissions (via auth middleware)
- Repository metadata (from MCP integration)

#### Real-Time State (Laravel Echo / WebSocket)
- Workflow status updates (polling or WebSocket)
- Live log entries (streamed as they're created)
- Step completion events

**State Synchronization**:
- Use Laravel Echo to broadcast workflow events:
  - `WorkflowStatusUpdated`
  - `StepCompleted`
  - `LogEntryCreated`
- React components listen to channel: `workflows.{id}`
- Optimistic updates for user actions (re-run, delete)

---

## 4. Visual Design Principles

### 4.1 Monologue Design System Application

**Brand Colors** (from Monologue):
- **Primary (Cyan)**: `#19d0e8` (RGB 25, 208, 232)
  - Use for: Active states, CTAs, progress indicators, links
  - Accessibility: Ensure contrast ratio ≥ 4.5:1 on dark backgrounds

- **Background (Dark)**: `#121212` (near-black)
  - Use for: Page background, card backgrounds (slightly lighter: `#1a1a1a`)

- **Text Colors**:
  - Primary text: `#f5f5f5` (off-white, 96% opacity)
  - Secondary text: `#a0a0a0` (gray, 63% opacity)
  - Tertiary text: `#6b7280` (dim gray, 42% opacity)

**Status Colors**:
- **Success**: `#10b981` (green-500)
- **Warning**: `#f59e0b` (amber-500)
- **Error**: `#ef4444` (red-500)
- **Info**: `#3b82f6` (blue-500)
- **In Progress**: `#19d0e8` (cyan, brand color)
- **Pending**: `#6b7280` (gray-500)

**Typography** (from Monologue):
- **Display/Headlines**: `Instrument Serif` (400, 500 weights)
  - Use for: Page titles, workflow task descriptions, section headers
  - Sizes: 2.5rem (h1), 2rem (h2), 1.5rem (h3)

- **Body/UI**: `Inter` or system sans-serif (400, 500, 600 weights)
  - Use for: Body text, buttons, form labels, metadata
  - Sizes: 1rem (base), 0.875rem (small), 0.75rem (tiny)

- **Code/Logs**: `DM Mono` (400, 500 weights)
  - Use for: Code snippets, log entries, file paths, JSON output
  - Sizes: 0.875rem (code blocks), 0.75rem (inline code)

**Font Loading**:
```css
/* Already configured in Monologue system */
@import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital,wght@0,400;0,500;1,400&display=swap');
@import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&display=swap');
```

---

### 4.2 Layout & Spacing

**Container Widths**:
- **Index page**: Max-width 1400px (to show 3-4 workflow cards per row on wide screens)
- **Detail page**: Max-width 1200px (optimal reading width for logs and diffs)
- **Modals/Forms**: Max-width 600px (focused, distraction-free)

**Spacing Scale** (Tailwind 4 default):
- `xs`: 0.25rem (4px) - tight elements
- `sm`: 0.5rem (8px) - compact spacing
- `md`: 1rem (16px) - default spacing
- `lg`: 1.5rem (24px) - section spacing
- `xl`: 2rem (32px) - page-level spacing
- `2xl`: 3rem (48px) - major sections

**Grid System**:
- **Workflows list**: CSS Grid with auto-fit columns
  - Mobile: 1 column
  - Tablet: 2 columns
  - Desktop: 3-4 columns (auto-fit minmax(320px, 1fr))

- **Detail page**: Flexbox with optional sidebar
  - Mobile: Single column (main content only)
  - Desktop: 70% main content, 30% sidebar (if present)

---

### 4.3 Visual Hierarchy

**Z-Index Layers** (from lowest to highest):
1. **Base**: 0 - Page background
2. **Content**: 1 - Cards, sections
3. **Sticky**: 10 - Sticky headers, breadcrumbs
4. **Dropdown**: 50 - Dropdown menus, tooltips
5. **Modal**: 100 - Modal overlays
6. **Toast**: 200 - Toast notifications
7. **FAB**: 999 - Floating action button (always on top)

**Card Elevation** (subtle shadows for depth):
- **Resting**: `shadow-md` (0 4px 6px -1px rgba(0,0,0,0.3))
- **Hover**: `shadow-lg` (0 10px 15px -3px rgba(0,0,0,0.4))
- **Active/Selected**: `shadow-xl` + cyan border

**Visual Weight**:
- **High priority** (user should notice immediately):
  - Large size (h1, h2)
  - Bright colors (cyan, green for success)
  - Bold weight (500-600)
  - Animation (pulse, fade-in)

- **Medium priority** (secondary information):
  - Medium size (h3, base text)
  - Muted colors (gray-400, gray-500)
  - Regular weight (400)

- **Low priority** (metadata, hints):
  - Small size (0.75rem, 0.875rem)
  - Dim colors (gray-600, gray-700)
  - Light weight (400)

---

### 4.4 Animation & Motion

**Principles**:
1. **Purposeful**: Animations should communicate state changes, not distract
2. **Fast**: 150-300ms for UI transitions, 500ms max for complex animations
3. **Easing**: Use `ease-out` for entrances, `ease-in` for exits, `ease-in-out` for state changes
4. **Reduced Motion**: Respect `prefers-reduced-motion` (disable decorative animations)

**Animation Library**:
```css
/* Transition utilities (Tailwind 4) */
.transition-fast { transition: all 150ms ease-out; }
.transition-base { transition: all 200ms ease-out; }
.transition-slow { transition: all 300ms ease-out; }

/* Animation keyframes */
@keyframes pulse-cyan {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes slide-in-right {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
```

**When to Animate**:
- **Page transitions**: Fade-in new content (200ms)
- **Modal open/close**: Slide-in from top + backdrop fade (250ms)
- **Card hover**: Lift effect with shadow change (150ms)
- **Status changes**: Color transition + icon animation (200ms)
- **Live updates**: Slide-in new log entries (150ms)
- **Progress indicator**: Pulse animation on active step (1s loop)

**Accessibility**:
```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

### 4.5 Iconography

**Icon Library**: Lucide React (already in use)

**Key Icons for Workflows**:
- **Workflow**: `Workflow` icon (generic)
- **Repository**: `GitBranch` or `Folder`
- **LLM**: `Brain` or `Sparkles`
- **Running**: `Loader2` (animated spin)
- **Completed**: `CheckCircle`
- **Failed**: `XCircle`
- **Pending**: `Clock` or `Circle` (outline)
- **Logs**: `Terminal`
- **Code**: `Code2`
- **Tests**: `FlaskConical` or `CheckCheck`
- **Deploy**: `Rocket`
- **Edit**: `Pencil`
- **Delete**: `Trash2`
- **Re-run**: `RotateCw`
- **Download**: `Download`
- **Share**: `Share2`

**Icon Sizing**:
- **Small**: 16px (inline with text)
- **Medium**: 20px (buttons, badges)
- **Large**: 24px (section headers, cards)
- **Hero**: 32px+ (empty states, hero sections)

**Icon Color**:
- Match text color by default
- Use status colors for status icons (green for success, red for error, etc.)
- Use cyan for interactive icons (links, buttons)

---

## 5. Component Specifications

### 5.1 Core Components

---

#### Component 1: `WorkflowCard`

**Purpose**: Display workflow summary in index list

**Props**:
```typescript
interface WorkflowCardProps {
  workflow: {
    id: string;
    task_description: string;
    repository: {
      name: string;
      icon_url?: string;
    };
    status: 'pending' | 'running' | 'completed' | 'failed';
    created_at: string;
    completed_at?: string;
    duration?: number; // in seconds
    llm_provider?: string;
  };
  onClick?: () => void;
}
```

**Visual Design**:
- **Layout**: Card with padding 1.5rem, rounded-lg, shadow-md
- **Background**: `#1a1a1a` (slightly lighter than page background)
- **Hover state**: Lift effect (shadow-lg) + subtle cyan border
- **Responsive**: Full-width mobile, 320px min-width desktop

**Content Structure**:
```
┌─────────────────────────────────────┐
│ [Status Badge]        [Repository]  │ <- Header
│                                     │
│ Task description (truncated to      │ <- Main content
│ 2 lines max with ellipsis)          │
│                                     │
│ [LLM icon] GPT-4  [Clock] 47s       │ <- Footer metadata
│ Created 2 hours ago                 │
└─────────────────────────────────────┘
```

**Status Badge Variants**:
- **Pending**: Gray badge, `Clock` icon
- **Running**: Cyan badge with pulse animation, `Loader2` icon (spinning)
- **Completed**: Green badge, `CheckCircle` icon
- **Failed**: Red badge, `XCircle` icon

**Interaction**:
- Click anywhere on card → navigate to `/workflows/{id}`
- Hover → lift effect + cursor pointer
- Focus (keyboard) → cyan outline

**Code Example**:
```typescript
import { CheckCircle, XCircle, Loader2, Clock } from 'lucide-react';

export function WorkflowCard({ workflow, onClick }: WorkflowCardProps) {
  const statusConfig = {
    pending: { color: 'gray', icon: Clock, label: 'Pending' },
    running: { color: 'cyan', icon: Loader2, label: 'Running', animated: true },
    completed: { color: 'green', icon: CheckCircle, label: 'Completed' },
    failed: { color: 'red', icon: XCircle, label: 'Failed' },
  };

  const { color, icon: Icon, label, animated } = statusConfig[workflow.status];

  return (
    <div
      onClick={onClick}
      className="bg-gray-900 rounded-lg p-6 shadow-md hover:shadow-lg hover:border-cyan-500/50
                 border border-transparent transition-fast cursor-pointer"
    >
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <span className={`inline-flex items-center gap-2 px-3 py-1 rounded-full
                         bg-${color}-500/10 text-${color}-500 text-sm font-medium`}>
          <Icon size={16} className={animated ? 'animate-spin' : ''} />
          {label}
        </span>
        <span className="text-sm text-gray-400">{workflow.repository.name}</span>
      </div>

      {/* Task description */}
      <p className="text-gray-200 font-serif text-lg mb-4 line-clamp-2">
        {workflow.task_description}
      </p>

      {/* Footer metadata */}
      <div className="flex items-center gap-4 text-sm text-gray-500">
        {workflow.llm_provider && (
          <span className="flex items-center gap-1">
            <Brain size={14} />
            {workflow.llm_provider}
          </span>
        )}
        {workflow.duration && (
          <span className="flex items-center gap-1">
            <Clock size={14} />
            {workflow.duration}s
          </span>
        )}
        <span className="ml-auto">
          {formatRelativeTime(workflow.created_at)}
        </span>
      </div>
    </div>
  );
}
```

---

#### Component 2: `WorkflowExecutionStatus`

**Purpose**: Real-time progress timeline for running workflows

**Props**:
```typescript
interface WorkflowExecutionStatusProps {
  steps: Array<{
    id: string;
    name: string;
    status: 'pending' | 'running' | 'completed' | 'failed';
    duration?: number; // in seconds
    started_at?: string;
    completed_at?: string;
  }>;
  currentStep?: string; // ID of current step
}
```

**Visual Design**:
- **Layout**: Vertical timeline with connecting lines
- **Responsive**: Full-width, scales down gracefully on mobile
- **Animation**: Pulse on active step, fade-in on step completion

**Content Structure**:
```
┌─────────────────────────────────────┐
│ ✅ Analyze Repository                │
│ │  Completed in 2.3s                │
│ │                                   │
│ 🔄 Generate Code                     │ <- Active step (pulsing)
│ │  Running for 8.1s...              │
│ │                                   │
│ ⏸️ Run Tests                         │
│ │  Pending                          │
│ │                                   │
│ ⏸️ Deploy                            │
│    Pending                          │
└─────────────────────────────────────┘
```

**Step Status Indicators**:
- **Pending**: Gray circle (outline), dim text
- **Running**: Cyan circle (pulsing), bright text, `Loader2` icon spinning
- **Completed**: Green check icon, dim text, duration shown
- **Failed**: Red X icon, bright text, error message

**Code Example**:
```typescript
export function WorkflowExecutionStatus({ steps, currentStep }: WorkflowExecutionStatusProps) {
  return (
    <div className="space-y-4">
      {steps.map((step, index) => {
        const isActive = step.id === currentStep;
        const isLast = index === steps.length - 1;

        return (
          <div key={step.id} className="flex items-start gap-4">
            {/* Timeline connector */}
            <div className="flex flex-col items-center">
              {/* Step icon */}
              <div className={`
                w-8 h-8 rounded-full flex items-center justify-center
                ${step.status === 'completed' && 'bg-green-500/10 text-green-500'}
                ${step.status === 'running' && 'bg-cyan-500/10 text-cyan-500 animate-pulse'}
                ${step.status === 'failed' && 'bg-red-500/10 text-red-500'}
                ${step.status === 'pending' && 'bg-gray-700 text-gray-500'}
              `}>
                {step.status === 'completed' && <CheckCircle size={20} />}
                {step.status === 'running' && <Loader2 size={20} className="animate-spin" />}
                {step.status === 'failed' && <XCircle size={20} />}
                {step.status === 'pending' && <Clock size={20} />}
              </div>

              {/* Connecting line */}
              {!isLast && (
                <div className={`w-0.5 h-12 ${
                  step.status === 'completed' ? 'bg-green-500/30' : 'bg-gray-700'
                }`} />
              )}
            </div>

            {/* Step content */}
            <div className="flex-1 pb-8">
              <h3 className={`font-serif text-lg ${
                isActive ? 'text-gray-100' : 'text-gray-400'
              }`}>
                {step.name}
              </h3>
              <p className="text-sm text-gray-500 mt-1">
                {step.status === 'completed' && `Completed in ${step.duration}s`}
                {step.status === 'running' && `Running for ${getDuration(step.started_at)}...`}
                {step.status === 'pending' && 'Pending'}
                {step.status === 'failed' && 'Failed'}
              </p>
            </div>
          </div>
        );
      })}
    </div>
  );
}
```

---

#### Component 3: `LiveLogViewer`

**Purpose**: Display real-time logs with filtering and auto-scroll

**Props**:
```typescript
interface LiveLogViewerProps {
  logs: Array<{
    id: string;
    level: 'info' | 'warning' | 'error' | 'debug';
    message: string;
    timestamp: string;
  }>;
  isLive?: boolean; // Auto-scroll enabled
  maxHeight?: string; // e.g., '500px'
}
```

**Visual Design**:
- **Layout**: Terminal-style scrollable container
- **Background**: Pure black `#000000` (mimics terminal)
- **Font**: DM Mono, 0.875rem
- **Height**: Max 500px with scroll (or prop-based)
- **Auto-scroll**: Enabled by default, with pause button

**Content Structure**:
```
┌─────────────────────────────────────┐
│ [Info] [Warning] [Error] [Pause]   │ <- Filters + controls
├─────────────────────────────────────┤
│ 14:32:01 [INFO] Cloning repository  │
│ 14:32:03 [INFO] Running composer... │
│ 14:32:08 [WARN] Deprecated method   │
│ 14:32:10 [INFO] Running tests...    │
│ ...                                 │
│ [Auto-scrolling to latest]          │ <- Indicator
└─────────────────────────────────────┘
```

**Log Level Colors**:
- **Info**: `#3b82f6` (blue-500)
- **Warning**: `#f59e0b` (amber-500)
- **Error**: `#ef4444` (red-500)
- **Debug**: `#6b7280` (gray-500)

**Interaction**:
- Click log level filter → toggle filter on/off
- Click pause → stop auto-scroll (show "Resume" button)
- Scroll manually → auto-pause (show "Jump to latest" button)

**Code Example**:
```typescript
export function LiveLogViewer({ logs, isLive = true, maxHeight = '500px' }: LiveLogViewerProps) {
  const [filters, setFilters] = useState<Set<string>>(new Set(['info', 'warning', 'error']));
  const [isPaused, setIsPaused] = useState(!isLive);
  const scrollRef = useRef<HTMLDivElement>(null);

  // Auto-scroll effect
  useEffect(() => {
    if (!isPaused && scrollRef.current) {
      scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
    }
  }, [logs, isPaused]);

  const filteredLogs = logs.filter(log => filters.has(log.level));

  return (
    <div className="bg-black rounded-lg border border-gray-800 overflow-hidden">
      {/* Filters */}
      <div className="flex items-center gap-2 p-3 border-b border-gray-800 bg-gray-900">
        {['info', 'warning', 'error', 'debug'].map(level => (
          <button
            key={level}
            onClick={() => {
              const newFilters = new Set(filters);
              if (filters.has(level)) {
                newFilters.delete(level);
              } else {
                newFilters.add(level);
              }
              setFilters(newFilters);
            }}
            className={`px-3 py-1 rounded text-xs font-medium transition-fast ${
              filters.has(level)
                ? 'bg-gray-700 text-gray-200'
                : 'bg-transparent text-gray-500'
            }`}
          >
            {level.toUpperCase()}
          </button>
        ))}
        <button
          onClick={() => setIsPaused(!isPaused)}
          className="ml-auto px-3 py-1 rounded text-xs font-medium bg-cyan-500/10 text-cyan-500"
        >
          {isPaused ? 'Resume' : 'Pause'}
        </button>
      </div>

      {/* Logs */}
      <div
        ref={scrollRef}
        className="overflow-y-auto font-mono text-sm p-4 space-y-1"
        style={{ maxHeight }}
      >
        {filteredLogs.map(log => (
          <div key={log.id} className="flex items-start gap-3">
            <span className="text-gray-500 shrink-0">
              {new Date(log.timestamp).toLocaleTimeString()}
            </span>
            <span className={`shrink-0 font-medium ${
              log.level === 'info' && 'text-blue-500'
            } ${
              log.level === 'warning' && 'text-amber-500'
            } ${
              log.level === 'error' && 'text-red-500'
            } ${
              log.level === 'debug' && 'text-gray-500'
            }`}>
              [{log.level.toUpperCase()}]
            </span>
            <span className="text-gray-300">{log.message}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

#### Component 4: `CreateWorkflowButton` (FAB or Inline)

**Purpose**: Primary CTA to create new workflow

**Variants**:
1. **FAB** (Floating Action Button): Fixed bottom-right, always visible
2. **Inline**: Large button in hero section (empty state)

**Props**:
```typescript
interface CreateWorkflowButtonProps {
  variant: 'fab' | 'inline';
  onClick: () => void;
}
```

**Visual Design (FAB)**:
- **Size**: 64px × 64px circle
- **Position**: Fixed bottom-right, 2rem from edges
- **Color**: Cyan gradient background
- **Icon**: `Plus` icon (24px)
- **Shadow**: Large shadow (shadow-2xl)
- **Hover**: Lift effect + scale(1.05)

**Visual Design (Inline)**:
- **Size**: Full-width on mobile, max 400px on desktop
- **Padding**: 1rem vertical, 2rem horizontal
- **Color**: Cyan background with white text
- **Icon**: `Plus` icon (20px) + text "Create Workflow"
- **Hover**: Slight darken + lift effect

**Code Example**:
```typescript
export function CreateWorkflowButton({ variant, onClick }: CreateWorkflowButtonProps) {
  if (variant === 'fab') {
    return (
      <button
        onClick={onClick}
        className="fixed bottom-8 right-8 w-16 h-16 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-600
                   shadow-2xl hover:shadow-cyan-500/50 hover:scale-105 transition-all duration-200
                   flex items-center justify-center text-white z-[999]"
        aria-label="Create workflow"
      >
        <Plus size={24} />
      </button>
    );
  }

  return (
    <button
      onClick={onClick}
      className="w-full max-w-md px-8 py-4 rounded-lg bg-cyan-500 hover:bg-cyan-600
                 text-white font-medium text-lg shadow-lg hover:shadow-xl
                 transition-fast flex items-center justify-center gap-3"
    >
      <Plus size={20} />
      Create Your First Workflow
    </button>
  );
}
```

---

### 5.2 Supporting Components

---

#### `StatusBadge`

**Purpose**: Reusable status indicator

**Props**:
```typescript
interface StatusBadgeProps {
  status: 'pending' | 'running' | 'completed' | 'failed';
  size?: 'sm' | 'md' | 'lg';
}
```

**Visual Design**:
- **Small**: 0.75rem text, 0.5rem padding
- **Medium**: 0.875rem text, 0.75rem padding (default)
- **Large**: 1rem text, 1rem padding

**Code Example**:
```typescript
export function StatusBadge({ status, size = 'md' }: StatusBadgeProps) {
  const config = {
    pending: { color: 'gray', label: 'Pending', icon: Clock },
    running: { color: 'cyan', label: 'Running', icon: Loader2, animated: true },
    completed: { color: 'green', label: 'Completed', icon: CheckCircle },
    failed: { color: 'red', label: 'Failed', icon: XCircle },
  };

  const { color, label, icon: Icon, animated } = config[status];
  const sizeClasses = {
    sm: 'text-xs px-2 py-1',
    md: 'text-sm px-3 py-1',
    lg: 'text-base px-4 py-2',
  };

  return (
    <span className={`inline-flex items-center gap-2 rounded-full
                     bg-${color}-500/10 text-${color}-500 font-medium ${sizeClasses[size]}`}>
      <Icon size={size === 'sm' ? 14 : size === 'lg' ? 18 : 16}
            className={animated ? 'animate-spin' : ''} />
      {label}
    </span>
  );
}
```

---

#### `EmptyState`

**Purpose**: First-time user onboarding

**Props**:
```typescript
interface EmptyStateProps {
  title: string;
  description: string;
  action?: {
    label: string;
    onClick: () => void;
  };
  icon?: React.ComponentType;
}
```

**Visual Design**:
- **Layout**: Centered column, max-width 600px
- **Icon**: Large (64px), muted cyan color
- **Title**: Instrument Serif, 2rem, gray-200
- **Description**: Inter, 1rem, gray-400
- **CTA**: Large button (inline variant)

**Code Example**:
```typescript
export function EmptyState({ title, description, action, icon: Icon }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center py-16 px-4 text-center max-w-2xl mx-auto">
      {Icon && (
        <div className="mb-6 text-cyan-500/50">
          <Icon size={64} />
        </div>
      )}
      <h2 className="font-serif text-3xl text-gray-200 mb-3">{title}</h2>
      <p className="text-gray-400 text-lg mb-8">{description}</p>
      {action && (
        <CreateWorkflowButton variant="inline" onClick={action.onClick} />
      )}
    </div>
  );
}
```

---

#### `CodeDiffViewer`

**Purpose**: Display file changes with syntax highlighting

**Props**:
```typescript
interface CodeDiffViewerProps {
  files: Array<{
    path: string;
    changes: Array<{
      type: 'add' | 'remove' | 'unchanged';
      line_number: number;
      content: string;
    }>;
  }>;
}
```

**Visual Design**:
- **Layout**: Tabbed interface (one tab per file)
- **Syntax highlighting**: Use `react-syntax-highlighter` or similar
- **Diff indicators**: Green for additions, red for deletions
- **Line numbers**: Left gutter, monospace font

**Note**: This component is complex and may require a dedicated library like `react-diff-view`.

---

## 6. Interaction Patterns

### 6.1 Real-Time Feedback

**WebSocket Integration** (Laravel Echo):

```typescript
// Hook for listening to workflow updates
export function useWorkflowUpdates(workflowId: string) {
  const [workflow, setWorkflow] = useState<Workflow | null>(null);

  useEffect(() => {
    // Subscribe to workflow channel
    const channel = window.Echo.private(`workflows.${workflowId}`);

    channel
      .listen('WorkflowStatusUpdated', (e: { workflow: Workflow }) => {
        setWorkflow(e.workflow);
      })
      .listen('StepCompleted', (e: { step: WorkflowStep }) => {
        // Update specific step
        setWorkflow(prev => ({
          ...prev,
          steps: prev.steps.map(s =>
            s.id === e.step.id ? e.step : s
          ),
        }));
      })
      .listen('LogEntryCreated', (e: { log: LogEntry }) => {
        // Append new log entry
        setWorkflow(prev => ({
          ...prev,
          logs: [...prev.logs, e.log],
        }));
      });

    return () => {
      channel.stopListening('WorkflowStatusUpdated');
      channel.stopListening('StepCompleted');
      channel.stopListening('LogEntryCreated');
    };
  }, [workflowId]);

  return workflow;
}
```

**Backend Broadcasting** (Laravel):

```php
// app/Events/WorkflowStatusUpdated.php
class WorkflowStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workflow $workflow
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("workflows.{$this->workflow->id}"),
        ];
    }
}
```

**User Experience**:
- **Immediate feedback**: Status updates appear < 500ms after backend change
- **Visual cue**: New log entries fade in with slide-in animation
- **Sound (optional)**: Subtle notification sound on completion (user preference)

---

### 6.2 Loading States & Skeletons

**Skeleton Screens** (instead of spinners):

```typescript
export function WorkflowCardSkeleton() {
  return (
    <div className="bg-gray-900 rounded-lg p-6 shadow-md animate-pulse">
      {/* Header skeleton */}
      <div className="flex items-center justify-between mb-4">
        <div className="h-6 w-20 bg-gray-800 rounded-full"></div>
        <div className="h-4 w-32 bg-gray-800 rounded"></div>
      </div>

      {/* Task description skeleton */}
      <div className="space-y-2 mb-4">
        <div className="h-4 w-full bg-gray-800 rounded"></div>
        <div className="h-4 w-3/4 bg-gray-800 rounded"></div>
      </div>

      {/* Footer skeleton */}
      <div className="flex items-center gap-4">
        <div className="h-4 w-16 bg-gray-800 rounded"></div>
        <div className="h-4 w-12 bg-gray-800 rounded"></div>
      </div>
    </div>
  );
}
```

**When to use skeletons**:
- Initial page load (show 3-4 skeleton cards)
- Fetching workflow details (show timeline skeleton)
- Loading code diffs (show code block skeleton)

**Progressive Loading**:
1. Show skeleton (0ms)
2. Load critical data (workflow metadata, status)
3. Load secondary data (logs, diffs) asynchronously
4. Fade in each section as it loads

---

### 6.3 Error Handling

**Error States**:

1. **Workflow Failed** (expected error):
   - Show clear error message in plain English
   - Highlight failed step in timeline
   - Provide actionable next steps (retry, edit task)
   - Display error logs (collapsible)

2. **Network Error** (unexpected):
   - Toast notification: "Connection lost. Retrying..."
   - Auto-retry with exponential backoff
   - Show offline indicator in header

3. **Validation Error** (user input):
   - Inline error message below input field
   - Red border on input
   - Prevent form submission until fixed

4. **404 Not Found**:
   - Custom 404 page with link to workflows index
   - "This workflow doesn't exist or you don't have access"

**Error Recovery**:
```typescript
// Automatic retry with exponential backoff
async function fetchWorkflow(id: string, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      const response = await fetch(`/api/workflows/${id}`);
      if (response.ok) return response.json();
    } catch (error) {
      if (i === retries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, i)));
    }
  }
}
```

**User Feedback**:
- **Optimistic updates**: Show action immediately, revert on error
- **Toast notifications**: Non-intrusive for background errors
- **Error boundaries**: Catch React errors and show fallback UI

---

### 6.4 Keyboard Shortcuts

**Global Shortcuts**:
- `C`: Create new workflow (focus on task input)
- `/`: Focus search/filter input
- `Esc`: Close modal or cancel current action

**Workflow Detail Page**:
- `R`: Re-run workflow
- `E`: Edit workflow (if not started)
- `L`: Toggle log visibility
- `D`: Download logs

**Accessibility**:
- All shortcuts displayed in help tooltip (? icon in header)
- Shortcuts respect OS preferences (e.g., Cmd vs Ctrl)
- Shortcuts disabled when typing in input fields

**Implementation**:
```typescript
useEffect(() => {
  const handleKeyDown = (e: KeyboardEvent) => {
    // Ignore if user is typing in input
    if (['INPUT', 'TEXTAREA'].includes((e.target as HTMLElement).tagName)) {
      return;
    }

    if (e.key === 'c') {
      openCreateWorkflowModal();
    } else if (e.key === '/') {
      e.preventDefault();
      focusSearchInput();
    }
  };

  window.addEventListener('keydown', handleKeyDown);
  return () => window.removeEventListener('keydown', handleKeyDown);
}, []);
```

---

## 7. Performance & Technical Considerations

### 7.1 Perceived Performance

**Strategies**:

1. **Optimistic UI Updates**:
   - Create workflow → immediately add to list with "pending" status
   - Re-run workflow → instantly update status to "running"
   - Revert if server returns error

2. **Skeleton Screens**:
   - Show content structure while loading
   - Faster perceived load time than spinners

3. **Progressive Enhancement**:
   - Load critical content first (workflow metadata)
   - Defer non-critical content (logs, diffs)
   - Use `React.lazy()` for code-splitting

4. **Instant Feedback**:
   - Button states change immediately on click
   - Loading indicators appear < 100ms after action

**Code Example**:
```typescript
// Optimistic workflow creation
const { post } = useForm({ task_description: '', repository_id: '' });

const handleSubmit = (e: FormEvent) => {
  e.preventDefault();

  // Optimistic update (add to local state immediately)
  const tempWorkflow = {
    id: 'temp-' + Date.now(),
    status: 'pending',
    ...data,
  };
  addWorkflow(tempWorkflow);

  // Send to server
  post('/api/workflows', {
    onSuccess: (response) => {
      // Replace temp workflow with server response
      replaceWorkflow(tempWorkflow.id, response.workflow);
    },
    onError: () => {
      // Revert on error
      removeWorkflow(tempWorkflow.id);
      toast.error('Failed to create workflow. Please try again.');
    },
  });
};
```

---

### 7.2 Code Splitting & Lazy Loading

**Route-Based Splitting**:
```typescript
// resources/js/pages/workflows/index.tsx
const WorkflowsIndex = lazy(() => import('./index'));
const WorkflowsShow = lazy(() => import('./show'));

// In router
<Route path="/workflows" element={<Suspense fallback={<PageSkeleton />}><WorkflowsIndex /></Suspense>} />
<Route path="/workflows/:id" element={<Suspense fallback={<PageSkeleton />}><WorkflowsShow /></Suspense>} />
```

**Component-Based Splitting**:
```typescript
// Lazy load heavy components (e.g., code diff viewer)
const CodeDiffViewer = lazy(() => import('@/components/CodeDiffViewer'));

// Use only when needed
{showDiff && (
  <Suspense fallback={<div>Loading diff...</div>}>
    <CodeDiffViewer files={workflow.files} />
  </Suspense>
)}
```

**Bundle Size Targets**:
- Initial bundle: < 200KB (gzipped)
- Route chunks: < 100KB each
- Shared vendor chunk: < 300KB

---

### 7.3 Data Fetching Strategy

**Inertia.js Patterns**:

1. **Eager Loading** (critical data):
```php
// app/Http/Controllers/WorkflowController.php
public function show(Workflow $workflow)
{
    return Inertia::render('Workflows/Show', [
        'workflow' => $workflow->load(['repository', 'steps']),
    ]);
}
```

2. **Lazy Loading** (optional data):
```php
public function show(Workflow $workflow)
{
    return Inertia::render('Workflows/Show', [
        'workflow' => $workflow->load(['repository', 'steps']),
        'logs' => Inertia::lazy(fn () => $workflow->logs), // Loaded only when accessed
    ]);
}
```

3. **Deferred Props** (heavy data):
```php
// Load in background, show skeleton initially
public function show(Workflow $workflow)
{
    return Inertia::render('Workflows/Show', [
        'workflow' => $workflow,
        'codeDiff' => Inertia::defer(fn () => $this->generateCodeDiff($workflow)),
    ]);
}
```

**Frontend**:
```typescript
// Access deferred props with loading state
const { workflow, codeDiff } = usePage().props;

{codeDiff ? (
  <CodeDiffViewer files={codeDiff} />
) : (
  <CodeDiffSkeleton />
)}
```

---

### 7.4 Real-Time Optimization

**WebSocket Connection Management**:

1. **Lazy Connect**: Only connect to WebSocket when viewing workflow detail page
2. **Auto Disconnect**: Disconnect when user navigates away
3. **Reconnect Logic**: Auto-reconnect on connection loss with exponential backoff

```typescript
useEffect(() => {
  if (!workflowId) return;

  // Connect to Laravel Echo
  const channel = window.Echo.private(`workflows.${workflowId}`);

  // Reconnect logic
  window.Echo.connector.pusher.connection.bind('state_change', (states: any) => {
    if (states.current === 'disconnected') {
      toast.warning('Connection lost. Reconnecting...');
    } else if (states.current === 'connected') {
      toast.success('Connected');
    }
  });

  return () => {
    channel.stopListening('WorkflowStatusUpdated');
    window.Echo.leave(`workflows.${workflowId}`);
  };
}, [workflowId]);
```

**Throttling & Debouncing**:
- **Log updates**: Buffer logs and update UI every 500ms (not on every log entry)
- **Search input**: Debounce search queries by 300ms

---

## 8. Differentiation from Admin Interface

### 8.1 Philosophy

| Aspect | Admin Interface | Client Interface (Workflows) |
|--------|----------------|------------------------------|
| **Purpose** | Manage system (back-office) | Ship code fast (main product) |
| **User Mindset** | "I need to configure X" | "I want to build Y" |
| **Tone** | Technical, precise | Friendly, inspiring |
| **Visual Style** | Compact, functional | Generous spacing, delightful |
| **Interaction** | Forms, tables, CRUD | Conversations, timelines, progress |

---

### 8.2 Visual Differentiation

**Admin Interface**:
- **Layout**: Sidebar navigation, dense tables
- **Colors**: Muted grays, minimal color
- **Typography**: Small text (0.875rem base), compact line-height
- **Spacing**: Tight (0.5rem gaps, 1rem padding)
- **Components**: Data tables, forms, dropdowns

**Client Interface (Workflows)**:
- **Layout**: Center-aligned content, spacious cards
- **Colors**: Vibrant cyan accents, dynamic status colors
- **Typography**: Larger text (1rem base), generous line-height (1.6)
- **Spacing**: Generous (1rem gaps, 1.5-2rem padding)
- **Components**: Workflow cards, timelines, live logs, progress indicators

---

### 8.3 Copy & Messaging

**Admin Interface**:
- "Manage MCP Integrations"
- "Configure Server Settings"
- "View Audit Logs"

**Client Interface**:
- "Ship code 10x faster"
- "What do you want to build today?"
- "Watch AI agents work their magic"

**Tone Guidelines for Workflows**:
- **Encouraging**: "Great! Let's get started."
- **Transparent**: "Here's exactly what the AI is doing..."
- **Human**: "Oops, something went wrong. Let's try again."
- **Confident**: "Your code is ready to deploy."

---

### 8.4 Access Control

**Routing**:
- Admin interface: `/admin/*` or `/dashboard/*`
- Client interface: `/workflows`, `/repositories`, `/deployments`

**Optional Subdomain** (future):
- Admin: `admin.agentops.dev`
- Client: `app.agentops.dev` or root domain

**Navigation**:
- Admin: Sidebar with links to Integrations, Servers, Settings
- Client: Top nav with Workflows, Repositories (minimal, unobtrusive)

---

## 9. Success Metrics

### 9.1 Product Metrics (from Vision Strategy)

1. **Time to First Workflow** (Onboarding)
   - Target: < 2 minutes from signup to first workflow execution
   - Measurement: Track timestamp from account creation to first workflow submission

2. **Workflows per User per Week**
   - Target: > 10 workflows/user/week (engaged users)
   - Measurement: Average weekly workflow count per active user

3. **Workflow Success Rate**
   - Target: > 85% workflows complete successfully
   - Measurement: (Completed workflows / Total workflows) × 100

4. **Net Promoter Score (NPS)**
   - Target: > 50 (excellent for dev tools)
   - Measurement: "How likely are you to recommend AgentOps?" (0-10 scale)

5. **Task-to-Deploy Time**
   - Target: Reduce 3-day tasks to < 30 minutes
   - Measurement: Track workflow duration from start to deployment

---

### 9.2 UX Metrics

1. **Workflow Detail Page Engagement**
   - **Metric**: Time spent on workflow detail page while running
   - **Target**: > 60% of workflow duration (users are engaged, watching progress)

2. **Log Viewer Usage**
   - **Metric**: % of users who expand logs
   - **Target**: 40-60% (balance between transparency and simplicity)

3. **Error Recovery Rate**
   - **Metric**: % of failed workflows that are re-run
   - **Target**: > 70% (users trust the system enough to retry)

4. **Repeat Usage**
   - **Metric**: % of users who create 2+ workflows
   - **Target**: > 80% (product delivers value, users come back)

---

### 9.3 Performance Metrics

1. **Page Load Time**
   - **Metric**: Time to interactive (TTI) for `/workflows`
   - **Target**: < 1.5 seconds (3G connection)

2. **Real-Time Update Latency**
   - **Metric**: Time from backend event to frontend update
   - **Target**: < 500ms (feels instant)

3. **Skeleton-to-Content Time**
   - **Metric**: Time from skeleton display to real content
   - **Target**: < 1 second (perceived speed)

---

## 10. Implementation Guidelines

### 10.1 Development Phases

**Phase 1: Core Functionality (MVP)**
- [ ] Workflows index page with list and create button
- [ ] Workflow detail page with static status (no real-time)
- [ ] Basic components: WorkflowCard, StatusBadge, EmptyState
- [ ] Inertia.js routing and page setup

**Phase 2: Real-Time & Polish**
- [ ] Laravel Echo integration for live updates
- [ ] WorkflowExecutionStatus timeline component
- [ ] LiveLogViewer with filtering and auto-scroll
- [ ] Skeleton screens for loading states

**Phase 3: Advanced Features**
- [ ] Code diff viewer for completed workflows
- [ ] Keyboard shortcuts
- [ ] Search and filtering
- [ ] Error recovery flows (retry, edit, etc.)

---

### 10.2 File Structure

```
resources/js/
├── pages/
│   └── Workflows/
│       ├── Index.tsx          # /workflows (list)
│       ├── Show.tsx           # /workflows/{id} (detail)
│       └── components/        # Page-specific components
│           ├── CreateWorkflowModal.tsx
│           ├── WorkflowFilters.tsx
│           └── WorkflowEmptyState.tsx
│
├── components/
│   ├── ui/                    # Generic UI components
│   │   ├── StatusBadge.tsx
│   │   ├── EmptyState.tsx
│   │   └── Button.tsx
│   │
│   └── workflows/             # Workflow-specific components
│       ├── WorkflowCard.tsx
│       ├── WorkflowExecutionStatus.tsx
│       ├── LiveLogViewer.tsx
│       └── CodeDiffViewer.tsx
│
├── hooks/
│   ├── use-workflow-updates.ts   # WebSocket hook
│   └── use-workflows.ts           # Fetch workflows (SWR or Inertia)
│
└── types/
    └── workflow.d.ts          # TypeScript interfaces
```

**Backend Structure**:
```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── WorkflowController.php   # API endpoints
│
├── Events/
│   ├── WorkflowStatusUpdated.php
│   ├── StepCompleted.php
│   └── LogEntryCreated.php
│
└── Broadcasting/
    └── WorkflowChannel.php    # Private channel authorization
```

---

### 10.3 TypeScript Interfaces

```typescript
// resources/js/types/workflow.d.ts

export interface Workflow {
  id: string;
  user_id: string;
  repository_id: string;
  task_description: string;
  llm_provider: 'openai' | 'mistral' | 'anthropic';
  status: 'pending' | 'running' | 'completed' | 'failed';
  created_at: string;
  updated_at: string;
  started_at?: string;
  completed_at?: string;
  duration?: number; // in seconds

  // Relationships
  repository: Repository;
  steps: WorkflowStep[];
  executions: WorkflowExecution[];
}

export interface WorkflowStep {
  id: string;
  workflow_id: string;
  name: string;
  description?: string;
  status: 'pending' | 'running' | 'completed' | 'failed' | 'skipped';
  order: number;
  started_at?: string;
  completed_at?: string;
  duration?: number;
  output?: Record<string, any>;
  error_message?: string;
}

export interface WorkflowExecution {
  id: string;
  workflow_id: string;
  status: 'pending' | 'running' | 'completed' | 'failed';
  started_at?: string;
  completed_at?: string;
  result?: Record<string, any>;
  logs: LogEntry[];
}

export interface LogEntry {
  id: string;
  level: 'debug' | 'info' | 'warning' | 'error';
  message: string;
  timestamp: string;
  context?: Record<string, any>;
}

export interface Repository {
  id: string;
  name: string;
  full_name: string; // e.g., "user/repo"
  provider: 'github' | 'gitlab';
  url: string;
  default_branch: string;
  language?: string;
  icon_url?: string;
}
```

---

### 10.4 API Endpoints

**Workflows API** (`routes/api.php`):

```php
Route::middleware(['auth:web'])->group(function () {
    // List workflows
    Route::get('/workflows', [WorkflowController::class, 'index']);

    // Create workflow
    Route::post('/workflows', [WorkflowController::class, 'store']);

    // Show workflow
    Route::get('/workflows/{workflow}', [WorkflowController::class, 'show']);

    // Re-run workflow
    Route::post('/workflows/{workflow}/rerun', [WorkflowController::class, 'rerun']);

    // Cancel workflow
    Route::post('/workflows/{workflow}/cancel', [WorkflowController::class, 'cancel']);

    // Delete workflow
    Route::delete('/workflows/{workflow}', [WorkflowController::class, 'destroy']);

    // Get workflow logs (if not included in show)
    Route::get('/workflows/{workflow}/logs', [WorkflowController::class, 'logs']);
});
```

**Broadcasting Events**:
```php
// config/broadcasting.php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        ],
    ],
],
```

---

### 10.5 Accessibility Checklist

**WCAG 2.1 AA Compliance**:

- [ ] **Keyboard Navigation**:
  - All interactive elements accessible via Tab
  - Focus indicators clearly visible (cyan outline)
  - Logical tab order (top-to-bottom, left-to-right)

- [ ] **Screen Readers**:
  - All images have `alt` text
  - ARIA labels for icon-only buttons (`aria-label="Create workflow"`)
  - ARIA live regions for real-time updates (`role="status"` on log viewer)

- [ ] **Color Contrast**:
  - Text: ≥ 4.5:1 ratio (AA) or ≥ 7:1 (AAA)
  - UI components: ≥ 3:1 ratio
  - Check cyan (#19d0e8) on dark backgrounds with contrast checker

- [ ] **Motion**:
  - Respect `prefers-reduced-motion` (disable animations)
  - No auto-playing videos or infinite loops

- [ ] **Forms**:
  - Labels for all inputs
  - Error messages announced by screen readers
  - Validation errors linked to inputs (`aria-describedby`)

**Testing Tools**:
- Lighthouse (Chrome DevTools) - Accessibility score > 90
- axe DevTools (browser extension) - Zero violations
- Keyboard-only navigation test - All features accessible

---

## 11. Appendix

### 11.1 Design Tokens Reference

**Colors** (Tailwind 4 + Monologue):
```css
/* Brand */
--color-cyan-500: #19d0e8;
--color-cyan-600: #17b8d0;

/* Status */
--color-green-500: #10b981;
--color-amber-500: #f59e0b;
--color-red-500: #ef4444;
--color-blue-500: #3b82f6;

/* Neutrals */
--color-gray-50: #f9fafb;
--color-gray-100: #f3f4f6;
--color-gray-200: #e5e7eb;
--color-gray-300: #d1d5db;
--color-gray-400: #9ca3af;
--color-gray-500: #6b7280;
--color-gray-600: #4b5563;
--color-gray-700: #374151;
--color-gray-800: #1f2937;
--color-gray-900: #111827;

/* Backgrounds */
--bg-dark: #121212;
--bg-dark-lighter: #1a1a1a;
```

**Typography**:
```css
/* Font Families */
--font-serif: 'Instrument Serif', Georgia, serif;
--font-sans: 'Inter', system-ui, -apple-system, sans-serif;
--font-mono: 'DM Mono', 'Courier New', monospace;

/* Font Sizes */
--text-xs: 0.75rem;      /* 12px */
--text-sm: 0.875rem;     /* 14px */
--text-base: 1rem;       /* 16px */
--text-lg: 1.125rem;     /* 18px */
--text-xl: 1.25rem;      /* 20px */
--text-2xl: 1.5rem;      /* 24px */
--text-3xl: 1.875rem;    /* 30px */
--text-4xl: 2.25rem;     /* 36px */
```

**Spacing**:
```css
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-3: 0.75rem;   /* 12px */
--space-4: 1rem;      /* 16px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-12: 3rem;     /* 48px */
--space-16: 4rem;     /* 64px */
```

---

### 11.2 Tailwind Utility Patterns

**Common Patterns**:

```html
<!-- Card -->
<div class="bg-gray-900 rounded-lg p-6 shadow-md hover:shadow-lg transition-fast">

<!-- Button (Primary) -->
<button class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg font-medium transition-fast">

<!-- Button (Secondary) -->
<button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 rounded-lg font-medium transition-fast">

<!-- Status Badge -->
<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-sm font-medium">

<!-- Section Header -->
<h2 class="font-serif text-2xl text-gray-200 mb-4">

<!-- Body Text -->
<p class="text-gray-400 leading-relaxed">

<!-- Code Block -->
<pre class="bg-black rounded-lg p-4 font-mono text-sm text-gray-300 overflow-x-auto">
```

---

### 11.3 Complete Code Example: Workflow Index Page

```typescript
// resources/js/pages/Workflows/Index.tsx

import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import AppLayout from '@/layouts/AppLayout';
import WorkflowCard from '@/components/workflows/WorkflowCard';
import EmptyState from '@/components/ui/EmptyState';
import CreateWorkflowModal from './components/CreateWorkflowModal';
import { Workflow } from '@/types/workflow';

interface Props {
  workflows: Workflow[];
}

export default function WorkflowsIndex({ workflows }: Props) {
  const [searchQuery, setSearchQuery] = useState('');
  const [showCreateModal, setShowCreateModal] = useState(false);

  const filteredWorkflows = workflows.filter(w =>
    w.task_description.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const groupedWorkflows = {
    running: filteredWorkflows.filter(w => w.status === 'running'),
    completed: filteredWorkflows.filter(w => w.status === 'completed'),
    failed: filteredWorkflows.filter(w => w.status === 'failed'),
  };

  return (
    <AppLayout>
      <Head title="Workflows" />

      {/* Header */}
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-serif text-4xl text-gray-100 mb-2">
              Workflows
            </h1>
            <p className="text-gray-400 text-lg">
              Ship code 10x faster with AI agents
            </p>
          </div>

          {workflows.length > 0 && (
            <button
              onClick={() => setShowCreateModal(true)}
              className="px-6 py-3 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg
                         font-medium shadow-lg hover:shadow-xl transition-fast flex items-center gap-2"
            >
              <Plus size={20} />
              Create Workflow
            </button>
          )}
        </div>

        {/* Search */}
        {workflows.length > 10 && (
          <div className="relative mb-8">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" size={20} />
            <input
              type="text"
              placeholder="Search workflows..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-12 pr-4 py-3 bg-gray-900 border border-gray-800 rounded-lg
                         text-gray-200 placeholder-gray-500 focus:border-cyan-500 focus:ring-1
                         focus:ring-cyan-500 transition-fast"
            />
          </div>
        )}

        {/* Workflows List */}
        {filteredWorkflows.length === 0 ? (
          <EmptyState
            title="No workflows yet"
            description="Create your first workflow to start shipping code faster with AI agents."
            icon={Workflow}
            action={{
              label: 'Create Your First Workflow',
              onClick: () => setShowCreateModal(true),
            }}
          />
        ) : (
          <div className="space-y-8">
            {/* Running workflows */}
            {groupedWorkflows.running.length > 0 && (
              <section>
                <h2 className="text-xl font-serif text-gray-300 mb-4">
                  Running ({groupedWorkflows.running.length})
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {groupedWorkflows.running.map(workflow => (
                    <WorkflowCard
                      key={workflow.id}
                      workflow={workflow}
                      onClick={() => router.visit(`/workflows/${workflow.id}`)}
                    />
                  ))}
                </div>
              </section>
            )}

            {/* Completed workflows */}
            {groupedWorkflows.completed.length > 0 && (
              <section>
                <h2 className="text-xl font-serif text-gray-300 mb-4">
                  Completed ({groupedWorkflows.completed.length})
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {groupedWorkflows.completed.map(workflow => (
                    <WorkflowCard
                      key={workflow.id}
                      workflow={workflow}
                      onClick={() => router.visit(`/workflows/${workflow.id}`)}
                    />
                  ))}
                </div>
              </section>
            )}

            {/* Failed workflows */}
            {groupedWorkflows.failed.length > 0 && (
              <section>
                <h2 className="text-xl font-serif text-gray-300 mb-4">
                  Failed ({groupedWorkflows.failed.length})
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {groupedWorkflows.failed.map(workflow => (
                    <WorkflowCard
                      key={workflow.id}
                      workflow={workflow}
                      onClick={() => router.visit(`/workflows/${workflow.id}`)}
                    />
                  ))}
                </div>
              </section>
            )}
          </div>
        )}
      </div>

      {/* FAB (if workflows exist) */}
      {workflows.length > 0 && (
        <button
          onClick={() => setShowCreateModal(true)}
          className="fixed bottom-8 right-8 w-16 h-16 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-600
                     shadow-2xl hover:shadow-cyan-500/50 hover:scale-105 transition-all duration-200
                     flex items-center justify-center text-white z-[999]"
          aria-label="Create workflow"
        >
          <Plus size={24} />
        </button>
      )}

      {/* Create Modal */}
      {showCreateModal && (
        <CreateWorkflowModal
          onClose={() => setShowCreateModal(false)}
        />
      )}
    </AppLayout>
  );
}
```

---

### 11.4 Resources & References

**Design System**:
- Monologue Design System: `/docs/03-ui-ux/brand-monologue/`
- Tailwind 4 Docs: https://tailwindcss.com/docs
- Radix UI: https://www.radix-ui.com/

**Product Strategy**:
- PRD v2.0: `/docs/agentOps/Specs/PRD_v2.md`
- Vision Strategy: `/docs/agentOps/Specs/Vision_strategie_produit.md`

**Technical Stack**:
- Laravel 12: https://laravel.com/docs/12.x
- Inertia.js v2: https://inertiajs.com/
- React 19: https://react.dev/
- Laravel Echo: https://laravel.com/docs/12.x/broadcasting

**UI Libraries**:
- Lucide Icons: https://lucide.dev/
- React Syntax Highlighter: https://github.com/react-syntax-highlighter/react-syntax-highlighter
- React Diff Viewer: https://github.com/praneshr/react-diff-viewer

---

## Conclusion

This manifesto defines the complete UX/UI vision for the client-facing workflows interface of AgentOps. It is designed to:

1. **Inspire confidence**: Through radical transparency and real-time feedback
2. **Delight users**: With thoughtful animations, generous spacing, and beautiful design
3. **Ship fast**: By reducing 3-day tasks to 30 minutes with AI automation
4. **Build trust**: By showing exactly what the AI is doing, not hiding complexity
5. **Scale gracefully**: With modular components, performance optimization, and accessibility

**Next Steps**:
1. Review and approve this manifesto
2. Begin Phase 1 implementation (core functionality)
3. Test with real users and iterate based on feedback
4. Measure success metrics and refine UX accordingly

**Ownership**: This is the PRIMARY user interface of AgentOps. Every design decision must reflect our product principles: Developer Experience is Everything, Radical Transparency, Build for 10x Not 10%, and Ship Fast Learn Faster.

---

**Document Version**: 1.0
**Last Updated**: 2025-10-26
**Maintained By**: Product & Design Team
**Status**: Ready for Implementation
