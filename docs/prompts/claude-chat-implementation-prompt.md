# Prompt pour Implémenter un Chat Claude dans MCP Manager

## Contexte

Je développe une interface de chat intégrée dans MCP Manager qui doit communiquer avec l'endpoint `MCP_SERVER_URL/llm/chat` (ou `/claude/chat` selon la configuration). L'interface doit reproduire l'expérience utilisateur de Claude.ai avec un design Atlassian/JIRA.

## Objectif

Implémenter une interface de chat complète avec :
- **Panneau gauche** : Zone de conversation (questions/réponses)
- **Panneau droit** : Canvas pour afficher les réponses formatées de Claude (code, tableaux, markdown)
- **Design Atlassian** : Respecter les patterns UI de JIRA/Confluence
- **Tailwind CSS 4** : Utilisation obligatoire pour tout le styling

## Spécifications Détaillées

### 1. Architecture de la Page

```
┌─────────────────────────────────────────────────────────────┐
│                     TopBar (Navigation)                      │
├─────────┬───────────────────────────────┬──────────────────┤
│ Sidebar │    Chat Panel (Left)          │  Canvas (Right)  │
│  (Nav)  │  ┌─────────────────────┐     │ ┌──────────────┐ │
│         │  │ Message Thread      │     │ │ Formatted    │ │
│         │  │ ┌─────────────────┐ │     │ │ Response     │ │
│         │  │ │ User Question   │ │     │ │              │ │
│         │  │ └─────────────────┘ │     │ │ - Code       │ │
│         │  │ ┌─────────────────┐ │     │ │ - Tables     │ │
│         │  │ │ Claude Response │ │     │ │ - Markdown   │ │
│         │  │ └─────────────────┘ │     │ │ - Charts     │ │
│         │  └─────────────────────┘     │ └──────────────┘ │
│         │  ┌─────────────────────┐     │                  │
│         │  │ Input Area         │     │  Actions Bar     │
│         │  └─────────────────────┘     │                  │
└─────────┴───────────────────────────────┴──────────────────┘
```

### 2. Structure des Composants

```typescript
// Structure des fichiers à créer
resources/js/
├── pages/
│   └── ai/
│       └── claude-chat.tsx
├── components/
│   └── ai/
│       ├── chat/
│       │   ├── ChatPanel.tsx
│       │   ├── MessageList.tsx
│       │   ├── MessageItem.tsx
│       │   ├── ChatInput.tsx
│       │   └── TypingIndicator.tsx
│       ├── canvas/
│       │   ├── CanvasPanel.tsx
│       │   ├── CodeBlock.tsx
│       │   ├── MarkdownRenderer.tsx
│       │   ├── TableRenderer.tsx
│       │   └── ChartRenderer.tsx
│       └── shared/
│           ├── ModelSelector.tsx
│           ├── ChatActions.tsx
│           └── ExportOptions.tsx
├── hooks/
│   └── ai/
│       ├── use-claude-chat.ts
│       ├── use-chat-history.ts
│       └── use-canvas-sync.ts
└── types/
    └── ai/
        └── claude.types.ts
```

### 3. Fonctionnalités Requises

#### Chat Panel (Gauche)
1. **Liste des messages** avec scroll automatique
2. **Avatar distincts** pour user et Claude
3. **Timestamps** sur chaque message
4. **Status indicators** (envoi, reçu, erreur)
5. **Markdown support** dans les messages
6. **Code highlighting** inline
7. **Typing indicator** animé pendant la génération
8. **Actions par message** : copier, régénérer, éditer

#### Canvas Panel (Droite)
1. **Rendu dynamique** selon le type de contenu
2. **Code blocks** avec syntax highlighting et bouton copier
3. **Tables** interactives avec tri/filtre
4. **Markdown** avec support complet (headers, lists, links)
5. **Charts** pour visualisations de données
6. **Export** en PDF/Markdown/HTML
7. **Zoom** et plein écran
8. **Synchronisation** avec le message sélectionné

#### Input Area
1. **Textarea auto-resize** 
2. **Support markdown** avec preview
3. **File upload** pour contexte
4. **Commandes slash** (/, /clear, /export)
5. **Historique** avec flèches haut/bas
6. **Boutons d'action** : Send, Clear, Settings

### 4. Intégration API

```typescript
// Endpoint à utiliser
POST ${MCP_SERVER_URL}/llm/chat

// Format de la requête
{
  "messages": [
    {
      "role": "user",
      "content": "Question de l'utilisateur"
    }
  ],
  "model": "gpt-4" | "claude-3-opus" | "mistral-large",
  "temperature": 0.7,
  "max_tokens": 4000,
  "stream": true  // Pour streaming des réponses
}

// Headers requis
{
  "Authorization": "Bearer ${token}",
  "Content-Type": "application/json"
}
```

### 5. Design Atlassian avec Tailwind 4

#### Palette de Couleurs
```css
/* Variables Tailwind à utiliser */
- Background: bg-white dark:bg-gray-900
- Surface: bg-gray-50 dark:bg-gray-800
- Borders: border-gray-200 dark:border-gray-700
- Primary: bg-blue-600 (Atlassian blue)
- Text: text-gray-900 dark:text-gray-100
- Secondary: text-gray-600 dark:text-gray-400
```

#### Composants UI Patterns
1. **Messages User** :
   - Background: `bg-white dark:bg-gray-800`
   - Border: `border-l-4 border-blue-500`
   - Padding: `p-4`
   - Shadow: `shadow-sm hover:shadow-md`

2. **Messages Claude** :
   - Background: `bg-gray-50 dark:bg-gray-900`
   - Border: `border-l-4 border-green-500`
   - Avatar avec logo Claude

3. **Canvas Blocks** :
   - Code: `bg-gray-900 text-gray-100 rounded-md p-4`
   - Tables: Style Confluence avec headers `bg-gray-100`
   - Actions: Boutons style JIRA `hover:bg-gray-100 rounded p-2`

### 6. État et Gestion des Données

```typescript
// Store Zustand pour le chat
interface ChatStore {
  conversations: Map<string, Conversation>;
  activeConversationId: string | null;
  messages: Message[];
  isLoading: boolean;
  streamingMessageId: string | null;
  
  // Actions
  sendMessage: (content: string) => Promise<void>;
  regenerateMessage: (messageId: string) => Promise<void>;
  selectMessage: (messageId: string) => void;
  clearConversation: () => void;
  exportConversation: (format: 'pdf' | 'md' | 'html') => void;
}

// Types
interface Message {
  id: string;
  role: 'user' | 'assistant';
  content: string;
  timestamp: Date;
  status: 'sending' | 'sent' | 'error';
  metadata?: {
    model?: string;
    tokens?: number;
    processingTime?: number;
  };
}
```

### 7. Fonctionnalités Avancées

#### Streaming des Réponses
```typescript
// Utiliser EventSource ou fetch avec ReadableStream
const response = await fetch(`${MCP_SERVER_URL}/llm/chat`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ ...payload, stream: true })
});

const reader = response.body?.getReader();
// Implémenter le parsing des chunks SSE
```

#### Raccourcis Clavier
- `Cmd/Ctrl + Enter` : Envoyer message
- `Cmd/Ctrl + K` : Nouvelle conversation
- `Cmd/Ctrl + /` : Toggle canvas
- `Cmd/Ctrl + E` : Export
- `Escape` : Annuler génération

#### Persistance Locale
- Sauvegarder les conversations dans IndexedDB
- Historique des 30 derniers jours
- Recherche dans les conversations
- Tags et favoris

### 8. Animations et Transitions

```css
/* Animations Tailwind 4 */
- Message apparition: animate-in slide-in-from-bottom
- Typing indicator: animate-pulse
- Canvas transition: transition-all duration-300
- Loading skeleton: animate-shimmer
```

### 9. Responsive Design

#### Mobile (< 768px)
- Canvas en overlay/modal
- Swipe pour afficher canvas
- Input fixé en bas
- Messages compacts

#### Tablet (768px - 1024px)
- Canvas collapsible
- 60/40 split
- Touch gestures

#### Desktop (> 1024px)
- 50/50 split ajustable
- Drag pour redimensionner
- Multi-panels si besoin

### 10. Exemple de Code à Implémenter

```tsx
// ChatPanel.tsx principal
import { useState, useRef, useEffect } from 'react';
import { useClaudeChat } from '@/hooks/ai/use-claude-chat';
import { MessageList } from './MessageList';
import { ChatInput } from './ChatInput';
import { TypingIndicator } from './TypingIndicator';

export function ChatPanel() {
  const { messages, sendMessage, isLoading } = useClaudeChat();
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Auto-scroll to bottom
    scrollRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  return (
    <div className="flex flex-col h-full bg-white dark:bg-gray-900">
      {/* Header */}
      <div className="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
          Claude Assistant
        </h2>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto px-6 py-4">
        <MessageList messages={messages} />
        {isLoading && <TypingIndicator />}
        <div ref={scrollRef} />
      </div>

      {/* Input */}
      <div className="border-t border-gray-200 dark:border-gray-700 p-4">
        <ChatInput onSend={sendMessage} disabled={isLoading} />
      </div>
    </div>
  );
}
```

### 11. Tests à Implémenter

1. **Tests unitaires** pour chaque composant
2. **Tests d'intégration** pour le flux complet
3. **Tests E2E** pour les interactions utilisateur
4. **Tests de performance** pour le streaming
5. **Tests d'accessibilité** WCAG 2.1 AA

### 12. Métriques à Tracker

- Temps de réponse API
- Taux d'erreur
- Utilisation des modèles
- Satisfaction utilisateur (thumbs up/down)
- Features utilisées (export, canvas, etc.)

## Livrables Attendus

1. **Page complète** `/ai/claude-chat` fonctionnelle
2. **Composants réutilisables** documentés
3. **Hooks personnalisés** pour la logique
4. **Types TypeScript** complets
5. **Tests** avec couverture > 80%
6. **Documentation** d'utilisation
7. **Storybook** pour les composants UI

## Contraintes Techniques

- **React 19** avec TypeScript 5.7
- **Tailwind CSS 4** exclusivement (pas de CSS custom)
- **Design System Atlassian** strict
- **Performance** : First paint < 1s, TTI < 3s
- **Accessibilité** : WCAG 2.1 AA minimum
- **SEO** : Pas nécessaire (app privée)

## Références Design

- Chat UI : Claude.ai, ChatGPT
- Design System : JIRA, Confluence
- Canvas : Notion, Obsidian
- Code Blocks : GitHub, VS Code

Implémente cette solution en respectant scrupuleusement l'architecture MCP Manager existante, les patterns React modernes, et le design Atlassian avec Tailwind 4.