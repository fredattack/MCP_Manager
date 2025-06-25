
# 🔧 Plan d’intégration – Notion dans MCP Manager

## 🎯 Objectif

Permettre à chaque utilisateur de :
- Connecter son compte Notion personnel ou professionnel.
- Naviguer dans l’arborescence de ses pages.
- Consulter les contenus et blocs d’une page.
- Gérer cette intégration de façon isolée et réutilisable (future compatibilité LLM, email, etc.).

---

## 1. 📐 Architecture & Conception

### 1.1. Structure modulaire
- **Modèle pivot : `IntegrationAccount`**
  - Type d'intégration (`notion`, `gmail`, `openai`, etc.)
  - Clés et métadonnées (token d’API, nom de workspace)
  - Référence utilisateur
  - Statut (activé, désactivé)
- Enum d’intégration pour séparer logiques métier par type.

### 1.2. Activation et configuration
- Activation/désactivation depuis l’UI.
- Ajout/modification/suppression de la clé Notion.
- Affichage des intégrations existantes.

### 1.3. Backend Laravel
- Routes :
  - `GET /integrations`
  - `POST /integrations/notion`
  - `PUT /integrations/notion`
  - `DELETE /integrations/notion`
- Orchestration des appels au MCP Server.

---

## 2. 🗃️ Backend Laravel – Orchestration

### 2.1. Base de données
- Table `integration_accounts` :
  - `id`, `user_id`, `type`, `access_token`, `meta`, `status`, `created_at`, `updated_at`

### 2.2. Services
- `NotionIntegrationService`
- Appels vers MCP Server avec header d’authentification

### 2.3. Middleware
- Vérification que l’intégration Notion est active avant action

---

## 3. 🖼️ Frontend React – Interface

### 3.1. Composants
- **IntegrationCard** (Notion)
- **Modal de configuration**
- **Vue arborescence pages**
- **Vue page Notion & blocs**

### 3.2. Gestion d’état
- Store local : intégrations actives, token, cache pages

### 3.3. UX
- Chargement, erreurs, feedback utilisateur

---

## 4. 🔁 Communication avec MCP Server

### 4.1. Côté Laravel
- Appels HTTP vers :
  - `GET /notion/pages-tree`
  - `GET /notion/databases`
  - `GET /notion/page/{id}`
  - `GET /notion/blocks/{page_id}`

### 4.2. Sécurité
- Ajout du token utilisateur dans les headers

---

## 5. ✅ Validations & contraintes

### Backend
- Token chiffré en base
- Un seul compte Notion actif par utilisateur

### Frontend
- UI accessible uniquement si l’intégration est active

---

## 6. 🔬 Tests

### Laravel
- Tests unitaires `IntegrationAccount`
- Tests fonctionnels API + appel MCP Server

### React
- Tests UI + navigation
- Gestion d’erreur + mock API

---

## 7. ⚠️ Points d’attention

- Respect du quota API Notion
- Généralisation des composants pour autres intégrations
- Cas d'invalidation ou désactivation
