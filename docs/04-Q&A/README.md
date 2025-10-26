# 📚 Documentation Q&A - MCP Manager

> **Centre de ressources et documentation technique**
> **Dernière mise à jour** : 26 octobre 2025

---

## 📂 Contenu du dossier

Ce dossier contient toute la documentation technique, les guides pratiques et les réponses aux questions fréquentes sur le projet MCP Manager.

---

## 🧪 Stratégie de Tests

### 📘 [Stratégie Complète de Tests](./STRATEGIE_TESTS_COMPLETE.md)

**Vue d'ensemble de la stratégie de tests pour MCP Manager**

**Contenu** :
- Vue d'ensemble et objectifs stratégiques
- Pyramide de tests (Unit → Integration → Feature → E2E)
- Infrastructure actuelle (Backend PHPUnit + Frontend Vitest + E2E Playwright)
- Standards et bonnes pratiques
- Roadmap d'amélioration sur 3 phases
- Métriques de succès et objectifs de couverture

**Pour qui ?** : Tous les développeurs, chefs de projet

**Durée de lecture** : 20-30 minutes

---

### 📗 [Guide Tests d'Intégration Frontend](./GUIDE_TESTS_INTEGRATION_FRONTEND.md)

**Guide didactique pour développeurs backend découvrant les tests frontend**

**Contenu** :
- Introduction : Qu'est-ce qu'un test d'intégration frontend ?
- Différences avec les tests backend (comparaisons Laravel/React)
- Installation et configuration (Vitest recommandé)
- Premiers pas avec Testing Library
- Tester des composants React (simples, avec état, avec effets)
- Tester des hooks personnalisés
- Tester avec React Query (queries, mutations)
- Tester des formulaires Inertia
- Patterns avancés et debugging
- Exercices pratiques avec solutions

**Pour qui ?** : Développeurs backend découvrant les tests frontend

**Durée de lecture** : 30-45 minutes

**Niveau** : Débutant à Intermédiaire

---

### 📕 [Guide Playwright E2E](./GUIDE_PLAYWRIGHT_E2E.md)

**Guide complet sur les tests End-to-End avec Playwright**

**Contenu** :
- Introduction à Playwright (comparaison avec Selenium, Cypress)
- Comparaison détaillée avec les tests backend Laravel
- Installation et configuration complète
- Premiers pas : Votre premier test
- Sélecteurs et locators (CSS, texte, ARIA, helpers)
- Actions utilisateur (navigation, clics, formulaires, clavier, souris)
- Assertions et vérifications
- Gestion de l'asynchrone (auto-waiting, attentes explicites)
- Fixtures et helpers (auth, database, fixtures personnalisées)
- Tests de workflows (cas pratique complet)
- Tests avec WebSocket (Reverb)
- Tests d'accessibilité (Axe Core)
- Debugging et troubleshooting
- Page Object Model (POM)
- Best practices
- Exercices pratiques

**Pour qui ?** : Développeurs backend découvrant les tests E2E

**Durée de lecture** : 45-60 minutes

**Niveau** : Débutant à Avancé

---

### 📙 [Exemples Pratiques de Tests pour Workflows](./EXEMPLES_TESTS_WORKFLOWS.md)

**Collection d'exemples prêts à l'emploi pour tester les workflows**

**Contenu** :
- **Tests Backend (PHPUnit)** :
  - API Workflow - Création
  - Exécution de Workflow
  - Service Workflow

- **Tests Frontend (Vitest)** :
  - Composant WorkflowCard
  - Hook useWorkflows
  - Page Workflows Index

- **Tests E2E (Playwright)** :
  - Parcours complet de création de workflow
  - Exécution de workflow avec logs temps réel

- **Tests d'intégration complets** :
  - Test Full-Stack : Création → Exécution → Vérification

**Pour qui ?** : Tous les développeurs (exemples copy-paste)

**Niveau** : Tous niveaux

---

### 📓 [Configuration et Exécution des Tests](./CONFIGURATION_EXECUTION_TESTS.md)

**Guide pratique pour configurer et exécuter tous les types de tests**

**Contenu** :
- **Configuration Backend (PHPUnit)** :
  - Configuration existante
  - Commandes de tests
  - Configuration de la couverture de code (PCOV)

- **Configuration Frontend (Vitest)** :
  - Installation complète
  - Configuration Vitest
  - Fichier de setup
  - Scripts package.json

- **Configuration E2E (Playwright)** :
  - Installation
  - Configuration Playwright
  - Structure des dossiers
  - Setup global et fixtures

- **Scripts et commandes** :
  - Makefile pour tous les tests
  - Scripts package.json complets
  - Raccourcis bash

- **CI/CD avec GitHub Actions** :
  - Configuration GitHub Actions
  - Badge de statut

- **Pre-commit hooks** :
  - Configuration Husky
  - Scripts pre-commit et pre-push

- **Troubleshooting** :
  - Problèmes courants Backend
  - Problèmes courants Frontend
  - Problèmes courants Playwright

**Pour qui ?** : DevOps, développeurs configurant l'environnement

**Niveau** : Tous niveaux

---

## 📖 Comment utiliser cette documentation ?

### Si vous êtes nouveau sur le projet

1. Commencez par la **[Stratégie Complète de Tests](./STRATEGIE_TESTS_COMPLETE.md)** pour comprendre la vision globale
2. Lisez le **[Guide de Configuration](./CONFIGURATION_EXECUTION_TESTS.md)** pour installer les outils
3. Suivez les **[Exemples Pratiques](./EXEMPLES_TESTS_WORKFLOWS.md)** pour voir du code concret

### Si vous êtes développeur backend

1. Lisez le **[Guide Tests d'Intégration Frontend](./GUIDE_TESTS_INTEGRATION_FRONTEND.md)** (très didactique)
2. Puis le **[Guide Playwright](./GUIDE_PLAYWRIGHT_E2E.md)** pour les tests E2E
3. Consultez les **[Exemples Pratiques](./EXEMPLES_TESTS_WORKFLOWS.md)** pour du code

### Si vous configurez l'environnement

1. Allez directement au **[Guide de Configuration](./CONFIGURATION_EXECUTION_TESTS.md)**
2. Suivez la checklist de configuration
3. Testez avec les **[Exemples Pratiques](./EXEMPLES_TESTS_WORKFLOWS.md)**

### Si vous cherchez du code à copier

1. Consultez les **[Exemples Pratiques](./EXEMPLES_TESTS_WORKFLOWS.md)**
2. Adaptez à votre contexte
3. Référez-vous aux guides si besoin de plus de détails

---

## 🎯 Objectifs de la stratégie de tests

### Objectifs à court terme (Sprint 3 - 2 semaines)

- ✅ Configurer Vitest pour les tests frontend
- ✅ Configurer Playwright pour les tests E2E
- ✅ Atteindre 60% de couverture frontend
- ✅ Écrire 5 tests E2E critiques

### Objectifs à moyen terme (Sprint 4 - 2 semaines)

- ✅ Atteindre 85% de couverture backend
- ✅ Tests d'intégration Notion complète
- ✅ Tests d'intégration Git (GitHub, GitLab)
- ✅ Tests WebSocket (Reverb)

### Objectifs à long terme (Sprint 5 - 1 semaine)

- ✅ Optimiser la vitesse des tests
- ✅ 20+ tests E2E critiques
- ✅ Tous les tests passent en CI/CD
- ✅ Temps d'exécution < 5 min pour toute la suite

---

## 📊 État actuel (26 octobre 2025)

### Backend (PHP/Laravel)

- ✅ **40+ tests** PHPUnit
- ✅ Organisation par domaine métier
- ✅ Factories pour tous les modèles principaux
- ✅ 100% de passage sur Sprint 2
- ⚠️ Couverture à mesurer

### Frontend (React/TypeScript)

- ⚠️ **4 tests** existants (ChatInput, MessageItem, use-claude-chat, nlp-engine)
- ❌ Pas de configuration Vitest
- ❌ Pas de tests pour les workflows
- ❌ Couverture < 10%

### E2E (Playwright)

- ✅ Packages installés (`@playwright/test`, `playwright`)
- ❌ Pas de `playwright.config.ts`
- ❌ Pas de tests `.spec.ts`
- ❌ Structure `tests/e2e/` à créer

---

## 🚀 Prochaines actions recommandées

### Action 1 : Configuration initiale (1 jour)

1. Créer `vitest.config.ts`
2. Créer `setupTests.ts`
3. Créer `playwright.config.ts`
4. Créer la structure `tests/e2e/`

### Action 2 : Premiers tests frontend (2 jours)

1. Tester `WorkflowCard`
2. Tester `useWorkflows`
3. Tester `WorkflowsIndex`

### Action 3 : Premiers tests E2E (3 jours)

1. Test de login
2. Test de création de workflow
3. Test d'exécution de workflow

### Action 4 : Intégration CI/CD (1 jour)

1. Créer `.github/workflows/tests.yml`
2. Configurer les hooks pre-commit
3. Vérifier que tout passe

---

## 📝 Notes importantes

### Pour les développeurs

- **Tests backend** : Vous maîtrisez déjà PHPUnit, les guides frontend sont très didactiques
- **Tests frontend** : Similaires aux tests backend, mais avec du DOM au lieu de HTTP
- **Tests E2E** : Comme des tests de fonctionnalité Laravel, mais dans un vrai navigateur

### Pour les DevOps

- Tous les outils sont déjà dans `package.json` et `composer.json`
- La configuration CI/CD est prête à copier-coller
- Les tests peuvent tourner en parallèle pour plus de vitesse

### Pour les chefs de projet

- Stratégie complète sur 3 sprints (5 semaines)
- Objectifs clairs et mesurables
- Roadmap détaillée dans la stratégie globale

---

## 🆘 Besoin d'aide ?

### Documentation externe

- **PHPUnit** : https://phpunit.de/documentation.html
- **Laravel Testing** : https://laravel.com/docs/12.x/testing
- **Vitest** : https://vitest.dev/
- **Testing Library** : https://testing-library.com/
- **Playwright** : https://playwright.dev/

### Dans ce projet

- Consultez les guides dans ce dossier
- Regardez les exemples de tests existants dans `tests/`
- Posez des questions dans les issues GitHub

---

## 📅 Historique des mises à jour

- **26 octobre 2025** : Création de la documentation complète de tests
  - Stratégie globale
  - Guide tests d'intégration frontend
  - Guide Playwright E2E
  - Exemples pratiques workflows
  - Configuration et exécution

---

**Bon courage pour la mise en place de votre stratégie de tests !** 🎉
