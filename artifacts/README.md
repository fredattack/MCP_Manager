# UX/IA Audit Artifacts

This directory contains all evidence and data collected during the comprehensive UX/IA audit conducted on 2025-10-25.

## Directory Structure

```
artifacts/
├── screenshots/          # Visual evidence of UI states
│   ├── desktop/         # Desktop viewport captures (1440x900)
│   └── mobile/          # Mobile viewport captures (390x844)
├── nav/                 # Navigation DOM snapshots
├── axe/                 # Accessibility violation reports (axe-core)
└── perf/                # Performance metrics (proxy measurements)
```

## Audit Scope

- **Base URL**: http://127.0.0.1:3978
- **Application**: MCP Manager (Laravel 12 + React 19 + Inertia.js)
- **Phase**: MVP Phase 1
- **Focus**: Navigation architecture, accessibility, performance perception, user flows

## Tools Used

- Manual navigation analysis
- Static code analysis
- Route mapping
- Component tree analysis
- ARIA compliance review

## Note

Due to the need for a running application instance, this audit includes:
1. Static code analysis of navigation structure
2. Route-to-page mapping
3. Component architecture review
4. Accessibility pattern analysis
5. Performance pattern analysis

Live browser testing would require:
- Running Laravel server on port 3978
- Active database connection
- Seeded test user account
