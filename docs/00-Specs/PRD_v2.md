# Product Requirements Document (PRD) v2.0
## AgentOps - Plateforme Micro-SaaS d'Automatisation IA pour Développeurs

---

**Version:** 2.0
**Date:** 25 octobre 2025
**Statut:** Approuvé
**Architecture:** 2 applications (Laravel+React monolithe + AI Engine)

---

## 1. Synthèse Exécutive

**AgentOps** est une plateforme micro-SaaS Laravel + React qui révolutionne le développement logiciel en fournissant un assistant IA autonome capable de générer, tester et déployer du code automatiquement.

### Proposition de Valeur Unique

Contrairement aux 1000+ assistants IA existants qui se limitent à l'autocomplétion de code, AgentOps orchestre l'intégralité du workflow de développement - de la compréhension du repo à la génération de tests, en passant par le déploiement automatique via CI/CD.

### Modèle Économique

- **Pricing:** 39 $/mois (solo) ou 99 $/mois (équipe)
- **Objectif 90 jours:** 100 utilisateurs actifs et 20 payants générant 1 000 $/mois de MRR
- **Objectif 12 mois:** 1 000 utilisateurs payants → 1 000 000 $/ARR

### Architecture v2.0 (2 Applications)

**Changement majeur:** Consolidation en 2 applications au lieu de 3

1. **Application Principale (Laravel 12 + React 19 + Inertia.js)**
   - Backend API + Frontend en une seule application monolithique modulaire
   - Authentification, gestion projets, workflows, facturation
   - Communication temps réel via Laravel Echo (WebSocket)

2. **AI Engine (FastAPI + Python 3.12)**
   - Service externe dédié aux opérations IA
   - Serveur MCP (Model Context Protocol)
   - LLM Router, Code Analyzer, générateurs

**Avantages de cette architecture:**
- ✅ **Simplicité opérationnelle** : Un seul déploiement pour l'app principale
- ✅ **Performance** : Pas de latence réseau backend ↔ frontend
- ✅ **SEO ready** : Inertia.js permet SSR si nécessaire (Phase 2)
- ✅ **Isolation IA** : Le compute intensif (LLM) reste séparé et scalable indépendamment
- ✅ **Time-to-market** : Stack Laravel+Inertia familière, développement plus rapide

---

## 2. Objectifs et Métriques de Succès

### Objectifs Business (3 mois)

- **Utilisateurs:** 1 000 inscrits bêta
- **Conversion:** 100 payants à 39 $/mois
- **Revenus:** 3 900 $/mois MRR
- **Croissance:** 100 utilisateurs payants → 1 000 000 $/ARR (objectif à 12 mois)

### Objectifs Produit (90 jours)

- **J+30:** MVP fonctionnel avec authentification, gestion de projets, connexion GitLab/GitHub
- **J+60:** Bêta publique via Product Hunt + Hacker News (100 utilisateurs actifs)
- **J+90:** Produit rentable avec facturation Stripe et séquence d'emails IA

### KPIs de Succès

| Catégorie | Métrique | Cible |
|-----------|----------|-------|
| **Adoption** | Conversion bêta → payant | 10% |
| **Rétention** | Investissement initial | < 1 000 $ |
| **Technique** | Temps de développement économisé | 90% |
| **Technique** | Vitesse d'onboarding | +40% (Code Intelligence Map) |
| **Technique** | Coûts API LLM | -60% (LLM Router) |
| **Technique** | Temps de review | -70% (Explain & Review IA) |
| **Traction** | Clients LinkedIn closés | 10 clients (100 prospects, 4 semaines) |

---

## 3. Problème Résolu et Public Cible

### Problème Principal

Les développeurs disposent de milliers d'outils IA d'autocomplétion, mais aucun ne comprend véritablement leur codebase, ne génère de tests pertinents, ne lance de pipelines CI/CD ou ne déploie en production.

**Résultat:** Ils travaillent encore à la ligne de code, pas au niveau du projet.

### Problématiques Spécifiques Résolues

1. **Manque d'automatisation end-to-end** → AgentOps orchestre tout le workflow
2. **Manque de régularité de publication** → Automatisation contenu Twitter via calendrier
3. **Over-engineering du MVP** → Focus "Done > Perfect" avec release à J+30
4. **Sous-monétisation** → Bouton "Upgrade" dès le MVP

### Public Cible

#### Persona Principal: Pieter Levels-like Developer

**Qui:** Créateur solo de Nomad List, Remote OK, Rebase
**Caractéristiques:**
- 100% indie hacker, aucun employé, aucun investisseur
- Génère +3 M$/an en revenus récurrents
- Stack: PHP, JavaScript, Docker, Supabase, GPT
- Philosophie: "Build once, automate forever"

**Motivations:**
- Expert Laravel/React, CI/CD, TDD → capitalise sur sa stack forte
- Veut scaler sans lever de fonds
- Niche forte (dev tools + IA automation) où les utilisateurs paient vite

#### Segments Secondaires

1. **Développeurs solo, freelances** - 39 $/mois
2. **CTOs de petites boîtes** - 99 $/mois équipe

#### Anti-Persona

- Influenceurs startup cherchant du buzz
- Personnes sans compétences techniques
- Ceux qui attendent qu'on crée une idée à leur place

---

## 4. Spécifications des Fonctionnalités

### 4.1. 🤖 Workflow IA Autonome "Code → Test → Deploy"

**Description:** Premier agent IA capable de livrer un commit complet, testé et déployé sans intervention humaine.

**User Story:**
> En tant que développeur, je veux écrire une tâche naturelle (ex: "Add authentication to the API using Sanctum") et qu'AgentOps clone mon repo, analyse sa structure, génère le code, crée les tests PHPUnit, exécute la pipeline GitLab CI/CD et me notifie du résultat, afin de réduire mon sprint de 3 jours à 30 minutes.

**Flux Technique:**

1. **Clone du repo** (GitLab/GitHub via OAuth)
2. **Analyse structure** → Détection dépendances via AST parser (Laravel Backend)
3. **Génération code** → Controller, Tests, Routes via **AI Engine (FastAPI/MCP)**
4. **Exécution tests** → PHPUnit, Jest, ESLint (Laravel Backend + Queue)
5. **Push commit** → Branche `feature/ia-task-123` avec merge request automatique
6. **WebSocket broadcast** → Laravel Echo pour suivi temps réel (Frontend React)

**Impact Mesurable:**
- ✅ Réduction de 90% du temps dev sur tâches répétitives
- ✅ Différenciateur clé vs Copilot/Cody (qui travaillent à la ligne, pas au projet)

---

### 4.2. 🧠 Code Intelligence Map - Vision Sémantique du Projet

**Description:** Graphe interactif généré automatiquement par IA pour comprendre instantanément les relations entre classes, services, modèles et migrations d'un projet Laravel.

**User Story:**
> En tant que développeur rejoignant un projet existant, je veux visualiser instantanément les dépendances entre mes services, contrôleurs et modèles sous forme de graphe interactif, afin de réduire mon temps d'onboarding de plusieurs jours à quelques minutes.

**Flux Technique:**

1. **Parser AST** (nikic/php-parser + Babel parser JS) → AI Engine
2. **Génération graphe** Neo4j-like rendu via React Flow (Frontend)
3. **Résumé contextuel** via LLM ("Explain this service in plain English") → AI Engine

**Impact Mesurable:**
- ✅ +40% de vitesse d'onboarding
- ✅ Outil de compréhension instantanée pour équipes techniques

---

### 4.3. 📋 TDD Copilot - Générateur de Tests Intelligents

**Description:** Agent IA qui surveille les commits Sentry, analyse les endpoints non testés et génère automatiquement les fichiers PHPUnit/Pest/Jest correspondants.

**User Story:**
> En tant que développeur ayant mergé du code sans tests, je veux qu'AgentOps détecte automatiquement les endpoints manquants, génère les tests unitaires via un prompt LLM contextualisé et me propose une PR automatique, afin d'atteindre +30% de couverture de test sans effort humain.

**Flux Technique:**

1. **Surveillance commits** Sentry + erreurs (Laravel Horizon Job)
2. **Analyse endpoints** non testés (AST parsing)
3. **Génération tests** PHPUnit/Pest/Jest via prompt LLM → AI Engine
4. **Option auto-commit** (activable)

**Impact Mesurable:**
- ✅ Couverture de test +30% sans effort humain
- ✅ Tests basés sur vraies erreurs production

---

### 4.4. 🤖 LLM Router - Intelligence Multi-Modèles Automatique

**Description:** Service intelligent qui choisit automatiquement le meilleur LLM (GPT pour génération de code, Mistral pour refactor, Local Ollama pour devs auto-hébergés) selon la tâche, optimisant coûts et cohérence.

**User Story:**
> En tant qu'utilisateur d'AgentOps, je ne veux pas choisir manuellement entre GPT-4, Mistral ou Ollama pour chaque action IA, car le système doit automatiquement router ma demande vers le modèle le plus performant et économique selon le contexte.

**Concept:**
- **GPT-4** pour génération de code complexe
- **Mistral** pour refactor rapide
- **Claude-3-Haiku** pour bon équilibre
- **Ollama (local)** pour devs auto-hébergés

**Flux Technique:**

1. **Service LLMRouter** + Provider pattern (AI Engine)
2. **Stockage token usage** (coût réel par job) → PostgreSQL
3. **Benchmarks automatiques**
4. **Transparence** : tableau performances par modèle (Frontend)

**Impact Mesurable:**
- ✅ Réduction 60% des coûts API
- ✅ Résultats plus fiables et cohérents
- ✅ Prévisibilité des coûts, ROI mesurable

---

### 4.5. 💬 Explain & Review - Code Review IA Contextuelle

**Description:** Agent IA qui commente le code comme un senior dev, explique les intentions, détecte les failles possibles et les incohérences de logique, puis peut réécrire la PR entière si besoin.

**User Story:**
> En tant que développeur solo sans QA ni lead technique, je veux qu'AgentOps analyse automatiquement mes Pull Requests, explique les intentions du code, détecte les failles de logique et propose des améliorations concrètes, afin de gagner des heures de review et éviter les bugs en production.

**Flux Technique:**

1. **GitLab/GitHub MR API** → Webhook trigger (Laravel)
2. **Prompt LLM** avec AST + diff contextuel → AI Engine
3. **Plugin VSCode** (facultatif) relié à `/api/review`
4. **Option "Explain this code to me like I'm new"**

**Impact Mesurable:**
- ✅ Gain de plusieurs heures de review
- ✅ Parfait pour petites équipes sans QA ni lead technique

---

## 5. Architecture Applicative v2.0 (2 Applications)

### Architecture Globale

```
┌─────────────────────────────────────────────────────────┐
│             APPLICATION PRINCIPALE                      │
│        Laravel 12 + React 19 + Inertia.js              │
│─────────────────────────────────────────────────────────│
│                                                          │
│  ┌────────────────────────────────────────────────┐   │
│  │          BACKEND (Laravel 12)                   │   │
│  │  • Authentification (Sanctum + 2FA)            │   │
│  │  • Gestion Projets & Repositories              │   │
│  │  • Workflow Orchestration (Jobs + Queue)       │   │
│  │  • WebSocket Broadcasting (Laravel Echo)       │   │
│  │  • Stripe Billing (Cashier)                    │   │
│  │  • API REST pour AI Engine                     │   │
│  └────────────────────────────────────────────────┘   │
│                        ▲                                 │
│                        │ Inertia.js (SSR-capable)        │
│                        ▼                                 │
│  ┌────────────────────────────────────────────────┐   │
│  │         FRONTEND (React 19)                     │   │
│  │  • Dashboard Multi-Tenant                      │   │
│  │  • Workflow Viewer (temps réel)                │   │
│  │  • Code Intelligence Map (React Flow)          │   │
│  │  • Settings & Billing UI                       │   │
│  │  • Tailwind CSS + shadcn/ui                    │   │
│  └────────────────────────────────────────────────┘   │
│                                                          │
└───────────────────────┬──────────────────────────────────┘
                        │
                        │ API HTTP + WebSocket
                        ▼
┌─────────────────────────────────────────────────────────┐
│              AI ENGINE (FastAPI)                        │
│─────────────────────────────────────────────────────────│
│  • LLM Router (GPT/Mistral/Claude/Ollama)              │
│  • Code Analyzer (AST parser multi-langage)            │
│  • Code Generator (MCP Protocol)                       │
│  • Test Generator (contextualisé)                      │
│  • Review Engine (diff analysis)                       │
│  • Embeddings & Semantic Search (pg_vector)            │
└─────────────────────────────────────────────────────────┘
```

### 5.1. Application Principale (Laravel + React + Inertia.js)

#### Backend Core (Laravel 12 + PHP 8.4)

**Services Principaux:**
- `GitProviderService` (GitLab/GitHub OAuth + API)
- `WorkflowOrchestrationService` (orchestration jobs)
- `LLMProxyService` (communication AI Engine)
- `BillingService` (Stripe Cashier)

**Actions Laravel:**
- `AnalyzeRepositoryAction`
- `GenerateCodeAction`
- `RunTestsAction`
- `DeployPipelineAction`

**Events:**
- `WorkflowStarted`
- `CodeGenerated`
- `TestFailed`
- `Deployed`

**Authentication:**
- Laravel Sanctum + JWT (RS256)
- Multi-tenant (Team-based RBAC)
- 2FA obligatoire pour actions sensibles

**Queue System:**
- Laravel Horizon (Redis)
- Priority queues (paying users SLA < 5min)
- Dead letter queues + retry logic

#### Frontend (React 19 + Vite + Tailwind)

**Pages Principales (Inertia.js):**
- `/dashboard` → Workflows & jobs overview
- `/repositories` → Connexions GitLab/GitHub
- `/workflows/:id` → Logs, étapes, résultats (temps réel)
- `/intelligence/:repoId` → Code Intelligence Map
- `/settings` → Clés API, tokens LLM
- `/billing` → Stripe integration (Pricing + Subscriptions)

**Composants UI:**
- `WorkflowViewer` (WebSocket temps réel)
- `CodeGraph` (React Flow interactive)
- `PricingTable` (Stripe checkout)
- `RepositorySelector` (OAuth flow)

**State Management:**
- React Query (server state)
- Zustand (client state)
- Inertia.js (page state + routing)

**Real-time:**
- Laravel Echo (Socket.io) pour broadcast WebSocket
- Channels: `workflow.{id}.progress`, `team.{id}.notifications`

---

### 5.2. AI Engine (FastAPI + Python 3.12)

**Architecture MCP (Model Context Protocol):**

```python
# FastAPI endpoints
@app.post("/api/ai/analyze")
async def analyze_repository(request: AnalyzeRequest):
    # AST parsing (tree-sitter)
    ast = await parse_codebase(request.repo_path)
    graph = build_dependency_graph(ast)
    return AnalyzeResponse(graph=graph)

@app.post("/api/ai/generate")
async def generate_code(request: GenerateRequest):
    # LLM Router decision
    model = llm_router.select_model(request.context, request.task_type)
    code = await call_llm_with_retry(model, prompt)
    return GenerateResponse(code=code, model_used=model)

@app.post("/api/ai/test")
async def generate_tests(request: TestRequest):
    # Analyse endpoints + génération tests
    tests = await test_generator.create(request.endpoints)
    return TestResponse(tests=tests)

@app.post("/api/ai/review")
async def review_code(request: ReviewRequest):
    # Code review contextuel
    review = await review_engine.analyze(request.diff, request.context)
    return ReviewResponse(comments=review.comments)
```

**ML Libraries:**
- Langchain (LLM orchestration)
- tiktoken (token counting)
- tree-sitter (AST parsing)
- transformers (local Ollama inference)

**Isolation & Scaling:**
- Service indépendant → scaling séparé
- GPU instances (Phase 3: g4dn.xlarge AWS)
- Circuit breaker + retry logic
- Response caching (Redis)

---

## 6. Stack Technique v2.0

### Application Principale

| Couche | Technologie | Justification |
|--------|-------------|---------------|
| **Backend** | Laravel 12 (PHP 8.4) | Expertise founder, écosystème mature (Sanctum, Horizon, Cashier) |
| **Frontend** | React 19 + TypeScript | Maturité, performance, composants réutilisables |
| **Bridge** | Inertia.js | SSR-capable, pas de split backend/frontend, SEO ready |
| **Build** | Vite 6 | HMR instantané, build optimisé |
| **UI** | Tailwind CSS 4 + shadcn/ui | Utility-first, composants accessibles |
| **DB** | PostgreSQL 16 | ACID, extensions (pg_vector), mature |
| **Cache** | Redis 7 Cluster | Sub-ms latency, pub/sub, queue |
| **Queue** | RabbitMQ | Retry logic, DLQ, persistent queues |

### AI Engine

| Composant | Technologie | Justification |
|-----------|-------------|---------------|
| **Framework** | FastAPI (Python 3.12) | Standard ML/AI, async performance |
| **LLM Routing** | Custom LLMRouter + Provider pattern | Optimisation coûts 60% |
| **AST Parsing** | tree-sitter | Multi-langage (PHP, JS, TS) |
| **Embeddings** | OpenAI text-embedding-3 + pg_vector | Semantic search |

### Infrastructure

| Composant | Phase 1-2 | Phase 3 |
|-----------|-----------|---------|
| **Hébergement** | DigitalOcean Droplets | AWS EKS (Kubernetes) |
| **Load Balancer** | DO Load Balancer | AWS ALB |
| **DNS/CDN** | Cloudflare | Cloudflare |
| **Secrets** | .env files (encrypted) | HashiCorp Vault |
| **Monitoring** | Prometheus + Grafana | + Jaeger + ELK |
| **CI/CD** | GitLab CI | GitLab CI + Canary deploys |

---

## 7. Exigences Non-Fonctionnelles

### 7.1. Performance

| Endpoint/Action | Latence Cible (p95) | Throughput Cible |
|----------------|---------------------|------------------|
| GET /api/projects | < 100ms | 500 req/s |
| POST /api/workflows (création) | < 200ms | 100 req/s |
| Workflow complet (analyze → deploy) | < 10 min | 10 workflows/min |
| WebSocket message delivery | < 200ms | 1000 msg/s |
| Code Intelligence parsing | < 30s (50 fichiers) | N/A |

**Stratégies:**
- Caching agressif (Redis): repos parsés, résultats LLM
- Database indexing optimisé (queries < 50ms)
- CDN pour assets statiques (Cloudflare)
- Pagination systématique (max 100 items/page)

### 7.2. Scalabilité

| Phase | Utilisateurs | Workflows/jour | Infrastructure |
|-------|--------------|----------------|----------------|
| **Phase 1** | 100 | 1 000 | 3 nodes (1 App + 1 AI + 1 DB) |
| **Phase 2** | 1 000 | 10 000 | 6 nodes (2 App + 2 AI + 2 DB) |
| **Phase 3** | 10 000 | 100 000 | 10+ nodes (K8s autoscaling) |

**Stratégies:**
- **Horizontal Scaling:** App stateless (sessions Redis) → scaling linéaire
- **Vertical Scaling:** Optimisation queries, connection pooling
- **DB Read Replicas:** PostgreSQL streaming replication

### 7.3. Sécurité

**Modèle Defense in Depth:**

1. **Network Security:** Cloudflare WAF, VPC privé, Security Groups
2. **Application Security:** TLS 1.3, rate limiting, input validation, CSRF tokens
3. **Authentication:** JWT (RS256, 1h TTL), refresh tokens, MFA (TOTP)
4. **Authorization:** RBAC (Owner/Admin/Developer/Viewer), Row-Level Security
5. **Data Security:** Encryption at rest (AES-256), encryption in transit (TLS 1.3)
6. **Monitoring:** Security event logging, intrusion detection, vulnerability scanning

**Compliance:**
- GDPR (Phase 1): Consent management, data portability, right to deletion
- SOC 2 Type II (Phase 3, M+18)

### 7.4. Disponibilité

| Phase | Uptime SLA | Downtime Max/mois | RTO | RPO |
|-------|-----------|-------------------|-----|-----|
| **Phase 1** | 95% | 36 heures | 4h | 24h |
| **Phase 2** | 99% | 7.2 heures | 1h | 6h |
| **Phase 3** | 99.9% | 43 minutes | 15min | 1h |

**Stratégies HA:**
- Multi-AZ deployment (2 zones minimum)
- Load balancer avec health checks
- DB Master-Replica avec automatic failover
- Backups quotidiens automatiques (retention 30 jours)

---

## 8. Roadmap Produit (90 jours)

### Sprint 1 - Fondation (Semaine 1, J1-7)

**Objectif:** Back + Front minimal viable pour login et gestion de projets

**Livrables:**
- ✅ Init Laravel 12 + Inertia.js + React
- ✅ Docker Compose (PostgreSQL, Redis, RabbitMQ)
- ✅ Auth Sanctum + Users + Teams
- ✅ Routes REST `/api/projects`, `/api/auth`
- ✅ Pages Inertia: Login, Dashboard, Projects
- ✅ TDD : tests unitaires auth, création projet

**Critères de Validation:**
- Login fonctionnel + Dashboard basique
- Affichage projets mockés
- Déploiement Docker réussi en local

---

### Sprint 2 - Connexions Git et LLM (Semaine 2, J8-14)

**Objectif:** Relier l'app à GitLab/GitHub et AI Engine

**Livrables Backend:**
- ✅ `GitProviderService` + OAuth flow
- ✅ Routes: `GET /api/repositories`, `POST /api/repositories/sync`
- ✅ Service `LLMProxyService` (communication AI Engine)
- ✅ Tests: mocks Git + génération IA

**Livrables Frontend:**
- ✅ Page "Connect Repository" (Inertia)
- ✅ Formulaire OAuth GitLab + token
- ✅ UI "Ask Agent" (prompt → génération mocké)

**Livrables AI Engine:**
- ✅ FastAPI endpoints `/api/ai/analyze`, `/api/ai/generate`
- ✅ LLM Router basique (GPT-4 + Mistral)

**Critères de Validation:**
- Connexion GitLab fonctionnelle (OAuth)
- Génération IA simulée via AI Engine

---

### Sprint 3 - Workflow Engine (Semaine 3, J15-21)

**Objectif:** Exécuter un vrai workflow (analyze → generate → test → deploy)

**Livrables Backend:**
- ✅ Models: `Workflow`, `Step`, `Job`, `Log`
- ✅ Actions Laravel (Analyze, Generate, RunTests, Deploy)
- ✅ Events + observers pour logging
- ✅ WebSocket broadcasting (Laravel Echo)

**Livrables Frontend:**
- ✅ Page "Workflow Viewer" (Inertia): timeline + logs
- ✅ Statut live (WebSocket)
- ✅ Actions: "Run Workflow" / "Cancel"

**Critères de Validation:**
- Workflow complet mocké visible temps réel
- Logs détaillés accessibles
- Possibilité d'annuler workflow en cours

---

### Sprint 4 - Déploiement & Monétisation (Semaine 4, J22-30)

**Objectif:** Rendre l'application monétisable et publiable

**Livrables Backend:**
- ✅ Multi-tenancy (Team ID + RLS)
- ✅ Stripe Billing (Cashier)
- ✅ Endpoint public `/api/public/demo`
- ✅ Webhook `stripe/webhook`

**Livrables Frontend:**
- ✅ Pricing + Billing page (Inertia)
- ✅ UI clean (Tailwind + shadcn)
- ✅ Landing page minimaliste
- ✅ Onboarding "Créer compte" + "Connecter GitLab"

**Livrables Infra:**
- ✅ CI/CD GitLab (tests + lint + deploy Docker)
- ✅ Hébergement: DigitalOcean droplets

**Critères de Validation:**
- ✅ MVP complet & monétisable
- ✅ Paiement Stripe fonctionnel (test + prod)
- ✅ MVP déployé et accessible publiquement

---

## 9. Canaux de Distribution

### 9.1. LinkedIn Outbound (B2B)

**Stratégie:**
1. **Optimiser profil:** "SaaS RH white-label pour consultants et startups"
2. **Lister 100 cibles B2B:** agences RH, SaaS, intégrateurs (Hunter.io + Sales Navigator)
3. **Séquence LinkedIn (5 jours):**
   - Jour 1: Message connexion ("J'ai bossé un moteur RH/IA prêt à intégrer, ton avis 5 min ?")
   - Jour 3: Lien démo + call Calendly
   - Jour 5: Message vocal LinkedIn
   - Jour 7: Relance + démo
4. **Clôture:** 1/10 achète (projection: 10 clients closés)

**Projection:**
- 100 prospects → 10 clients closés
- Revenus M2: 50 000 $ + 10 000 $/mois récurrents → 1 M $ + en < 10 mois

---

### 9.2. Twitter/X + Product Hunt + Newsletter

**Pourquoi:**
- Canal naturel des devs et fondateurs indie
- Capitalise sur stack (Laravel/React, TDD, CI/CD, IA)
- 100% propriété
- Niche forte où utilisateurs paient vite

**Stratégie:**

1. **Build in Public sur X (3 posts/semaine):**
   - Threads devlogs, mini-démos, transparence
   - Vidéos Loom courtes (< 60s)
   - Partages techniques (TDD, Docker, CI/CD)
   - Exemple: "Day 3 of building AgentOps: first AI-generated test passing in pipeline"

2. **Product Hunt (J+60):**
   - Vidéo Loom < 60s montrant produit
   - 3 KPIs: visiteurs → inscrits → payants

3. **Stats mensuelles publiques:**
   - Transparence → croissance organique
   - Inspire confiance early adopters

**Ressources:**
- Twitter @levelsio (documenter tout en temps réel)
- Blog: levels.io – Build Once, Automate Forever
- Indie Hackers profile

---

## 10. Risques et Mitigations

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| **Manque de régularité publication** | Pas de traction Twitter | Élevée | Calendrier Twitter automatisé (3 posts/semaine) |
| **Over-engineering MVP** | Produit jamais testé | Moyenne | Release J+30 même incomplet ("Done > Perfect") |
| **Sous-monétisation retard Stripe** | Audience non convertie | Moyenne | Bouton "Upgrade" dès MVP |
| **Coûts API LLM élevés** | Burn rate insoutenable | Moyenne | LLM Router, monitoring strict |
| **Adoption lente** | MRR insuffisant | Moyenne | Campagne LinkedIn + Product Hunt + Hacker News |

---

## 11. Critères d'Acceptation

### Validation MVP (J+30)

- ✅ MVP prêt et montrable
- ✅ Auth + Dashboard + Connexion Git fonctionnels
- ✅ 1 workflow complet end-to-end (mocked)
- ✅ UI/UX clean (Tailwind + shadcn)
- ✅ Déploiement production réussi

### Validation Bêta Publique (J+60)

- ✅ 100 utilisateurs actifs
- ✅ Workflows réels (non-mocked)
- ✅ Facturation Stripe activée
- ✅ Product Hunt launch

### Validation Rentabilité (J+90)

- ✅ 20 abonnés payants à 39 $ ≥ 780 $/mois
- ✅ ROI positif (< 1 000 $ dépensés)
- ✅ 10% conversion bêta → payant

---

## 12. Conclusion

**AgentOps v2.0** adopte une architecture **2 applications** (Laravel+React monolithe + AI Engine) pour maximiser:
- ✅ **Simplicité opérationnelle** (1 déploiement principal vs 2)
- ✅ **Time-to-market** (Inertia.js = stack familière)
- ✅ **Performance** (pas de latence réseau backend ↔ frontend)
- ✅ **Isolation compute IA** (scaling indépendant)

**Prochaines étapes:**
1. Validation architecture avec équipe technique
2. Setup repository Git + infrastructure IaC
3. Sprint 1 (J1-7): Fondation
4. Sprint 2-4: MVP complet
5. Launch Product Hunt (J+60)

---

**Document préparé par:** Product Manager (AI Assistant)
**Date:** 25 octobre 2025
**Version:** 2.0
**Statut:** En attente de validation équipe technique
