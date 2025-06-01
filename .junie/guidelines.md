# 🧭 Project Guidelines – EasyInvoice Starter Kit

> Laravel 12 · React 19 · TailwindCSS 4 · Vite 6 · PostgreSQL

## 🎯 Purpose

This guide defines the coding standards, architecture conventions, and development best practices to ensure consistency, readability, scalability, and testability across the project. It also serves as a reference for AI agents or new contributors.

---

## 🛠 Backend Guidelines (Laravel 12)

### 🔧 General Principles

- Strict adherence to **PSR-12** coding standards.
- Use **Laravel 12** with **PHP 8.4**.
- Favor **type hints** and **return types** everywhere.
- Use strict SOLID principles. 
- Use **Service + Action Pattern** only (no repositories).
- Stick to **PHP attributes** for routing, validation, etc.
- Favor **typed DTOs** and **form request classes**.
- Use **PostgreSQL** (strict schema, typed columns).

### 🧪 Testing

- Unit tests: **PHPUnit** only.
- Functional tests: **Pest** (optional).
- Coverage goal: **80% minimum**.
- Run all QA checks via `make quality-check`.

### 📦 Tools & Quality

- **PHPStan**: Level max, no warning allowed.
- **Rector**: Applied via `make rector`.
- **Pre-commit hooks**: Lint, format, rector check.
- Documentation via `make docs` (scramble).

### ✅ Naming Conventions

| Element        | Convention              | Example                         |
|----------------|--------------------------|----------------------------------|
| Services       | `PascalCase`             | `InvoiceFetcherService`         |
| Actions        | `Verb+NounAction`        | `DownloadInvoiceAction`         |
| Controllers    | `PascalCaseController`   | `InvoiceController`             |
| Models         | `Singular`               | `Invoice`, `Portal`             |
| Routes         | RESTful (resource routes) | `Route::apiResource(...)`       |

---

## 💻 Frontend Guidelines (React 19 + Vite 6 + TailwindCSS 4)

### 📐 Project Architecture

Modular structure inspired by feature-sliced design:

```
/pages          # Route-bound views
/services       # API logic using tanstack/react-query
/stores         # Zustand (preferred), Context fallback
/ui             # Pure UI logic with TailwindCSS
```

### 📚 State Management

- Use **Zustand** for local/global state.
- Do not overuse context unless it's for theming/auth.
- Favor **React Query** for all API calls (caching, mutation, status).

### 🧪 Testing & Linting

- ESLint (airbnb + prettier + typescript)
- Coverage: 80%+ via `make coverage`
- E2E tests: **Cypress**
- React component/unit tests: **Vitest** or **Jest**

### 🎨 Styling

- Use **TailwindCSS 4** only.
- Encapsulate complex components inside `/ui`
- Avoid inline styles unless dynamic.
- Use `clsx` or `classnames` for conditional class logic.

# 🎨 UI/UX & Web Design Guidelines

---

## 🌟 Design Principles

- **Clarity First**: Interface visuelle épurée, priorité au contenu et à la hiérarchie visuelle.
- **Consistency**: Utiliser un design system réutilisable (couleurs, composants, espacement).
- **Affordance visuelle**: Tous les éléments interactifs doivent clairement sembler cliquables.
- **Feedback immédiat**: Chaque action de l’utilisateur (clic, chargement, erreur) doit déclencher un feedback visuel ou sonore.
- **Accessibility First**: Minimum WCAG AA, contrastes adaptés, navigable au clavier, textes accessibles.
- **Mobile-First**: Concevoir et tester d’abord pour mobile, ensuite adapter pour desktop.

---

## 🧠 IA-Friendly Components

- **Composants Atomiques**: Créer des composants simples et prédictibles (`Button`, `Card`, `Modal`).
- **Prompt Zones**: Prévoir des zones pour suggestion automatique ou inline AI prompts.
- **Editable Zones**: Zones d’entrée (`textarea`, champs) bien balisées pour une prise en main facile par l'IA.
- **Design tokens**: Exposer couleurs, tailles, espacements sous forme de tokens pour manipulation programmatique.

---

## 🧩 Structure Visuelle

- **Max Width Container**: `max-w-[1200px] mx-auto px-4`
- **Spacing System**: Tailwind spacing scale strictement utilisée (ex: `space-y-6`, `gap-4`)
- **Consistent Layout Grid**: Grilles en 12 ou 24 colonnes selon le contexte.
- **Whitespace Pro Design**: Utilisation généreuse de l’espace vide pour éviter la surcharge cognitive.

---

## 🎯 UX Best Practices

- **Onboarding ultra simplifié**: Une IA doit pouvoir guider un nouvel utilisateur en 3 étapes max.
- **Zero State Design**: Afficher des visuels illustratifs ou appels à action quand une section est vide.
- **Confirmation & Undo**: Toujours permettre à l'utilisateur d'annuler une action (ex: delete avec snackbar “undo”).
- **Navigation**: `SideBar` ou `TabBar` persistante, claire et avec des icônes + labels.
- **Responsiveness**: Interactions tactiles fluides, boutons accessibles avec le pouce (mobile).

---

## 📱 Components Guidelines

| Composant | Règle UI/UX |
|----------|--------------|
| **Button** | Taille min. `44x44px`, états : `hover`, `focus`, `disabled` |
| **Input** | Label visible + placeholder explicite + `focus ring` |
| **Modal** | `Scroll lock` + fermeture via `ESC` + fond semi-transparent |
| **Table** | `Sticky header`, responsive (`collapse` ou `scroll`) |
| **Card** | Ombres douces (`shadow-md`), padding cohérent (`p-4` ou `p-6`) |


### ✅ Naming Conventions psr-12 

| Element     | Convention      | Example               |
|-------------|------------------|------------------------|
| Pages       | PascalCase       | `Dashboard.jsx`        |
| Services    | camelCase        | `getInvoices.js`       |
| Stores      | camelCase        | `useInvoiceStore.js`   |
| UI          | PascalCase       | `InvoiceCard.jsx`      |
| Folders     | kebab-case       | `invoice-history`      |

---

## 🚫 Common Antipatterns

### Backend

- ❌ No `Repository` pattern — use `Service + Action`.
- ❌ No logic in controllers.
- ❌ Avoid using Facades inside services (favor dependency injection).
- ❌ Avoid complex validation rules inline (use `FormRequest`).

### Frontend

- ❌ No use of `axios` directly — always use services + react-query.
- ❌ No direct state mutation — always go through Zustand/React state logic.
- ❌ Avoid global CSS or style tags — Tailwind only.

---

## 📊 CI/CD & Automation

### Makefile Commands (key targets)

| Command           | Purpose                         |
|------------------|----------------------------------|
| `make start`      | Start the app locally (port 1978) |
| `make test`       | Run all tests                   |
| `make lint`       | Run ESLint                      |
| `make stan`       | Run PHPStan                     |
| `make rector`     | Run Rector                      |
| `make coverage`   | Generate coverage reports       |
| `make docs`       | Generate API documentation      |
| `make format`     | Prettier format                 |
| `make quality-check` | Run all static checks       |

---

## 📋 Example API Structure (Laravel)

```php
#[Route('GET', '/api/invoices')]
public function listInvoices(ListInvoicesAction $action): JsonResponse
{
    return response()->json($action->execute());
}
```

```php
// ListInvoicesAction.php
class ListInvoicesAction
{
    public function execute(): array
    {
        return Invoice::latest()->get()->toArray();
    }
}
```

---

## 📋 Example Store (Zustand)

```js
import { create } from 'zustand';

export const useInvoiceStore = create((set) => ({
  invoices: [],
  setInvoices: (data) => set({ invoices: data }),
}));
```

---

## 📋 Example Service (React)

```js
// /services/invoice.js
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

export const useInvoices = () =>
  useQuery(['invoices'], async () => {
    const { data } = await axios.get('/api/invoices');
    return data;
  });
```

---

## 📌 KPIs to Track

- ✅ 80%+ test coverage (front & back)
- ✅ CI always green
- ✅ Lint/Stan/Rector all pass with `make quality-check`
- ✅ Manual invoice extraction < 30s
- ✅ OCR accuracy > 85%

## Quality-check

- ✅ No critical bugs in production
- never add ignore rules in ESLint or PHPStan
- never edit the Makefile without user specific approval
