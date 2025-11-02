# 📋 Sprint 3 Detailed Plan

**Sprint:** Sprint 3 - Workflow Complet IA
**Dates:** J22-J35 (11 nov - 24 nov 2025)
**Durée:** 14 jours calendaires
**Effort estimé:** 28 jours-homme
**Thème:** Generate Code + Run Tests + Deploy

---

## 📊 Executive Summary

Sprint 3 marque la **complétion du workflow IA end-to-end** avec les capacités de génération de code, exécution de tests automatisés, et déploiement automatique. Ce sprint transforme l'application d'un outil d'analyse en une **plateforme d'automatisation complète**.

**Objectifs principaux:**
1. ✅ Compléter Sprint 2 à 100% (Tests E2E)
2. 🎯 Implémenter Generate Code Action
3. 🎯 Implémenter Run Tests Action
4. 🎯 Implémenter Deploy Pipeline Action
5. 🎯 Workflow complet Analyze → Generate → Test → Deploy

---

## 🎯 Prérequis (Sprint 2)

### ✅ Tous les prérequis sont validés

| Prérequis | Status | Evidence |
|-----------|--------|----------|
| **S2.11: AST Parser** | ✅ VALIDÉ | 4 tests passent, nikic/php-parser installé |
| **S2.12: Prompt Engineering** | ✅ VALIDÉ | Templates v1.0 + tests passent |
| **LLM Router** | ✅ VALIDÉ | OpenAI + Mistral avec fallback |
| **Workflow Engine Foundation** | ✅ VALIDÉ | Models + Queue + API opérationnels |
| **UI Workflows** | ✅ VALIDÉ | Pages + composants + WebSocket |

**Conclusion:** ✅ **SPRINT 3 PEUT DÉMARRER IMMÉDIATEMENT**

---

## 📋 Tâches Détaillées

### Phase 0: Complétion Sprint 2 (2 jours)

#### S2.10: Tests E2E ⚠️ (Reporté de Sprint 2)
**Priorité:** P1 (Haute)
**Effort:** 2 jours
**Assigné:** QA + Backend Lead

**Objectif:**
Créer des tests end-to-end pour valider le workflow complet Git → Clone → Analyze avec LLM mocké.

**Tâches détaillées:**
1. **Setup E2E Testing Framework** (0.5j)
   - Configuration Playwright ou Laravel Dusk
   - Setup de la base de données test
   - Configuration des mocks LLM

2. **Tests Git Integration** (0.5j)
   - Test: Connect GitHub OAuth
   - Test: Connect GitLab OAuth
   - Test: List repositories
   - Test: Clone repository

3. **Tests Workflow Execution** (0.5j)
   - Test: Create workflow "Analyze Repository"
   - Test: Execute workflow with mocked LLM
   - Test: Verify workflow status updates
   - Test: Verify results stored in DB

4. **Tests UI Workflow** (0.5j)
   - Test: Display workflows list
   - Test: Create new workflow from UI
   - Test: View workflow details
   - Test: Real-time updates via WebSocket

**Acceptance Criteria:**
- [ ] 15+ tests E2E créés et passent
- [ ] Coverage workflow complet > 80%
- [ ] Tests exécutables en CI/CD
- [ ] Documentation tests E2E complète

---

### Phase 1: Generate Code Action (5 jours)

#### S3.1: Workflow Engine - GenerateCodeAction
**Priorité:** P0 (Critique)
**Effort:** 5 jours
**Assigné:** Backend Lead

**Objectif:**
Implémenter l'action de génération de code via LLM basée sur l'analyse AST et les exigences utilisateur.

**Tâches détaillées:**

**Jour 1: Architecture & Design** (1j)
1. Définir l'interface `GenerateCodeAction`
2. Concevoir le workflow: Input → LLM → Output → Validation
3. Définir le format des prompts de génération
4. Créer les DTOs (Data Transfer Objects):
   - `GenerateCodeInput`: requirements, context, target_files
   - `GenerateCodeOutput`: generated_code, file_path, explanation

**Jour 2: Prompt Engineering** (1j)
1. Créer template `generate_code_v1.txt`
2. Intégrer AST context dans le prompt
3. Définir le format de réponse (JSON)
4. Ajouter exemples few-shot learning
5. Implémenter token budget management

**Jour 3-4: Implementation Core Logic** (2j)
1. Créer `app/Actions/Workflows/GenerateCodeAction.php`
2. Implémenter méthode `execute()`
3. Intégrer LLM Router pour génération
4. Parser et valider la réponse LLM
5. Sauvegarder le code généré:
   - Option 1: Branch Git automatique
   - Option 2: Stockage temporaire DB
   - Option 3: File system avec versioning
6. Implémenter error handling et retry logic
7. Ajouter logging détaillé

**Jour 5: Tests & Refinement** (1j)
1. Tests unitaires GenerateCodeAction (10+ tests)
2. Tests avec LLM mocké
3. Tests avec vrai LLM (OpenAI)
4. Validation qualité code généré
5. Performance testing

**Fichiers à créer:**
```
app/Actions/Workflows/GenerateCodeAction.php
app/DTOs/GenerateCodeInput.php
app/DTOs/GenerateCodeOutput.php
storage/prompts/generate_code_v1.txt
tests/Unit/Actions/GenerateCodeActionTest.php
tests/Feature/Workflows/GenerateCodeWorkflowTest.php
```

**Acceptance Criteria:**
- [ ] GenerateCodeAction implémenté et testé
- [ ] Génère du code PHP valide (syntax-check)
- [ ] Code suit les conventions Laravel
- [ ] 10+ tests unitaires passent
- [ ] Documentation technique complète
- [ ] Template prompt v1.0 validé

---

### Phase 2: Run Tests Action (4 jours)

#### S3.4: Workflow Engine - RunTestsAction
**Priorité:** P0 (Critique)
**Effort:** 4 jours
**Assigné:** Backend Lead

**Objectif:**
Exécuter automatiquement les tests (PHPUnit, Jest, Vitest) sur le code généré dans un environnement isolé.

**Tâches détaillées:**

**Jour 1: Architecture Isolation** (1j)
1. Concevoir l'isolation d'exécution:
   - Option A: Docker containers éphémères
   - Option B: Jail/chroot Linux
   - Option C: Processus isolés avec timeout
2. Choisir la meilleure approche (recommandé: Docker)
3. Créer le Dockerfile pour environnement test:
   ```dockerfile
   FROM php:8.2-cli
   RUN apt-get update && apt-get install -y git composer
   WORKDIR /code
   ```

**Jour 2: Implementation Core Logic** (1j)
1. Créer `app/Actions/Workflows/RunTestsAction.php`
2. Implémenter méthode `execute()`
3. Détecter le type de tests (PHPUnit, Jest, Vitest)
4. Préparer l'environnement d'exécution
5. Copier le code dans le container
6. Installer les dépendances (composer install, npm install)

**Jour 3: Exécution & Parsing** (1j)
1. Exécuter les tests dans le container
2. Parser le output (XML, JSON, TAP)
3. Extraire les résultats:
   - Tests passés / échoués
   - Assertions
   - Coverage
   - Erreurs détaillées
4. Stocker les résultats en DB
5. Nettoyer les ressources (kill container)

**Jour 4: Tests & Safety** (1j)
1. Implémenter timeout strict (max 5 minutes)
2. Limiter ressources (CPU, RAM)
3. Tests unitaires RunTestsAction (8+ tests)
4. Tests d'isolation (sécurité)
5. Tests de performance
6. Documentation sécurité

**Fichiers à créer:**
```
app/Actions/Workflows/RunTestsAction.php
app/Services/TestRunner/DockerTestRunner.php
app/Services/TestRunner/PHPUnitParser.php
app/Services/TestRunner/JestParser.php
docker/test-runner.dockerfile
tests/Unit/Actions/RunTestsActionTest.php
tests/Feature/Workflows/RunTestsWorkflowTest.php
```

**Acceptance Criteria:**
- [ ] RunTestsAction exécute PHPUnit correctement
- [ ] Isolation complète (sécurité)
- [ ] Timeout respecté (max 5 min)
- [ ] Résultats parsés et stockés
- [ ] 8+ tests unitaires passent
- [ ] Documentation sécurité complète

---

### Phase 3: Deploy Pipeline Action (4 jours)

#### S3.6: Workflow Engine - DeployPipelineAction
**Priorité:** P0 (Critique)
**Effort:** 4 jours
**Assigné:** Backend Lead

**Objectif:**
Créer automatiquement une Merge Request / Pull Request sur GitHub/GitLab avec le code généré et testé.

**Tâches détaillées:**

**Jour 1: Git Integration** (1j)
1. Étendre GitHubClient et GitLabClient:
   - Créer branche automatiquement
   - Commit code généré
   - Push vers remote
2. Implémenter nomenclature des branches:
   - Format: `agentops/feature-name-timestamp`
   - Exemple: `agentops/add-authentication-20251111-143022`

**Jour 2: Pull Request Creation** (1j)
1. Créer `app/Actions/Workflows/DeployPipelineAction.php`
2. Implémenter création PR/MR:
   - Titre automatique basé sur requirements
   - Description avec:
     - Résumé des changements
     - Fichiers modifiés
     - Résultats tests
     - Link vers workflow execution
3. Assigner labels automatiques:
   - `agentops`
   - `automated`
   - `needs-review`

**Jour 3: CI/CD Integration** (1j)
1. Intégrer avec GitLab CI/CD API:
   - Déclencher pipeline
   - Monitorer statut
   - Récupérer résultats
2. Intégrer avec GitHub Actions:
   - Déclencher workflow
   - Check status
   - Parse results
3. Stocker résultats CI/CD en DB

**Jour 4: Tests & Webhooks** (1j)
1. Tests unitaires DeployPipelineAction (8+ tests)
2. Tests d'intégration Git (mocké)
3. Setup webhooks pour notifications:
   - PR créée
   - CI/CD passed/failed
   - PR merged
4. Documentation déploiement

**Fichiers à créer:**
```
app/Actions/Workflows/DeployPipelineAction.php
app/Services/Git/PRService.php
app/Services/Git/CIService.php
tests/Unit/Actions/DeployPipelineActionTest.php
tests/Feature/Workflows/DeployWorkflowTest.php
```

**Acceptance Criteria:**
- [ ] DeployPipelineAction crée PR/MR correctement
- [ ] Branch créée avec nomenclature standard
- [ ] PR description complète et formatée
- [ ] CI/CD déclenché automatiquement
- [ ] Webhooks configurés
- [ ] 8+ tests unitaires passent

---

### Phase 4: Workflow Complet End-to-End (3 jours)

#### S3.10: UI - Page Workflow Detail
**Priorité:** P1 (Haute)
**Effort:** 2 jours
**Assigné:** Frontend Lead

**Objectif:**
Améliorer la page `/workflows/{id}` pour afficher toutes les étapes du workflow complet.

**Tâches détaillées:**

**Jour 1: Components** (1j)
1. Créer `WorkflowStepCard.tsx`:
   - Afficher chaque step (Analyze, Generate, Test, Deploy)
   - Status icon (pending, running, success, error)
   - Durée d'exécution
   - Logs expandables
2. Créer `CodeDiffViewer.tsx`:
   - Afficher le code généré
   - Syntax highlighting (Prism.js)
   - Diff view (before/after)
3. Créer `TestResultsPanel.tsx`:
   - Afficher résultats tests
   - Tests passés / échoués
   - Coverage percentage
   - Erreurs détaillées

**Jour 2: Integration & Polish** (1j)
1. Intégrer tous les composants dans `workflow-detail.tsx`
2. WebSocket updates en temps réel
3. Animations transitions entre steps
4. Loading states & skeletons
5. Error handling & retry UI
6. Documentation composants

**Fichiers à créer:**
```
resources/js/components/workflows/WorkflowStepCard.tsx
resources/js/components/workflows/CodeDiffViewer.tsx
resources/js/components/workflows/TestResultsPanel.tsx
resources/js/pages/workflows/workflow-detail.tsx
```

#### S3.11: WebSocket Avancé
**Priorité:** P2 (Moyenne)
**Effort:** 1 jour
**Assigné:** Backend Lead

**Objectif:**
Améliorer le système WebSocket avec rooms par workflow pour optimiser les performances.

**Tâches:**
1. Implémenter rooms Laravel Reverb:
   - Room par workflow: `workflow.{id}`
   - Broadcast uniquement aux users concernés
2. Optimiser payload events:
   - Envoyer uniquement les deltas
   - Compresser les données
3. Reconnection automatique
4. Tests performance WebSocket

---

### Phase 5: Tests & Documentation (2 jours)

#### S3.12: Tests d'Intégration Complets
**Effort:** 1 jour

**Objectif:**
Valider le workflow complet end-to-end avec tous les steps.

**Tests à créer:**
1. **Test: Workflow Analyze → Generate → Test → Deploy**
   - Setup: Repository de test
   - Exécution: Workflow complet
   - Validation:
     - Code généré valide
     - Tests passent
     - PR créée
     - CI/CD déclenché
   - Temps: < 10 minutes

2. **Test: Error Handling à chaque step**
   - Analyze fail
   - Generate fail (LLM error)
   - Test fail (code invalid)
   - Deploy fail (Git error)

3. **Test: Performance & Scalability**
   - 10 workflows simultanés
   - Vérifier isolation
   - Vérifier ressources

**Acceptance Criteria:**
- [ ] 20+ tests d'intégration passent
- [ ] Coverage global > 75%
- [ ] Performance < 10 min pour workflow complet
- [ ] 0 memory leaks

#### S3.13: Documentation Sprint 3
**Effort:** 1 jour

**Documents à créer:**
1. **User Guide: Workflow Complet**
   - Comment créer un workflow
   - Comment suivre l'exécution
   - Comment gérer les erreurs
   - Screenshots & vidéos

2. **Developer Guide: Extending Workflows**
   - Architecture patterns
   - Créer une nouvelle action
   - Intégrer un nouveau LLM
   - Best practices

3. **API Documentation**
   - Swagger/OpenAPI spec
   - Exemples curl
   - SDKs clients (future)

4. **Deployment Guide**
   - Requirements système
   - Installation production
   - Configuration avancée
   - Monitoring & logs

---

## 📅 Timeline & Milestones

### Week 1 (J22-J28: 11-17 nov)
- **J22-J23:** Phase 0 - Tests E2E (S2.10) ✅
- **J24-J28:** Phase 1 - Generate Code Action (jours 1-5) 🔄

**Milestone Week 1:** Tests E2E complétés + Generate Code implémenté

### Week 2 (J29-J35: 18-24 nov)
- **J29-J32:** Phase 2 - Run Tests Action (jours 1-4) 🔄
- **J33-J35:** Phase 3 - Deploy Pipeline Action (jours 1-3) 🔄

**Milestone Week 2:** Workflow complet fonctionnel

### Week 2 (suite) (J35: 24 nov)
- **J35:** Phase 4 & 5 - UI + Tests + Doc (finition) 🔄

**🎉 Milestone Final Sprint 3:** Workflow end-to-end validé et documenté

---

## 📊 Effort Breakdown

| Phase | Tâches | Effort | % Sprint |
|-------|--------|--------|----------|
| **Phase 0** | Tests E2E Sprint 2 | 2j | 7% |
| **Phase 1** | Generate Code Action | 5j | 18% |
| **Phase 2** | Run Tests Action | 4j | 14% |
| **Phase 3** | Deploy Pipeline Action | 4j | 14% |
| **Phase 4** | UI Workflow Detail | 3j | 11% |
| **Phase 5** | Tests & Documentation | 2j | 7% |
| **Buffer** | Imprévus | 8j | 29% |
| **Total** | | **28j** | **100%** |

**Équipe recommandée:**
- 1x Backend Lead (full-time)
- 1x Frontend Lead (part-time - Phase 4)
- 1x QA (part-time - Phase 0 & 5)

**Velocity:** ~28 story points / 14 jours = **2 story points/jour**

---

## 🔗 Dépendances

### Dépendances Internes
- ✅ Sprint 2 complété à 92%
- ✅ AST Parser opérationnel
- ✅ Prompt Engineering templates v1.0
- ✅ LLM Router avec fallback
- ✅ Workflow Engine foundation

### Dépendances Externes
- ✅ OpenAI API key (existant)
- ✅ Mistral API key (existant)
- ✅ GitHub OAuth (existant)
- ✅ GitLab OAuth (existant)
- 🔄 Docker installé (pour RunTestsAction)
- 🔄 GitLab CI/CD configuré (pour DeployPipeline)

### Dépendances Techniques
- 🔄 Augmenter quota OpenAI si nécessaire (>1000 req/jour)
- 🔄 Configurer Docker Engine sur serveur
- 🔄 Permissions Git pour créer branches & PRs

---

## ⚠️ Risques & Mitigations

### Risque 1: Génération de code invalide
**Probabilité:** Moyenne (40%)
**Impact:** Haute

**Mitigation:**
- Syntax validation avant sauvegarde
- Tests automatiques sur code généré
- Prompt engineering avec exemples
- Fallback: demander review humaine

### Risque 2: Tests longs à exécuter
**Probabilité:** Haute (60%)
**Impact:** Moyenne

**Mitigation:**
- Timeout strict (5 minutes)
- Kill automatique des containers
- Parallelization des tests
- Cache des dépendances (composer, npm)

### Risque 3: API LLM rate limiting
**Probabilité:** Moyenne (30%)
**Impact:** Moyenne

**Mitigation:**
- Queue system déjà en place
- Retry avec exponential backoff
- Fallback vers Mistral si OpenAI throttled
- Monitorer quota usage

### Risque 4: Sécurité exécution code
**Probabilité:** Basse (20%)
**Impact:** Critique

**Mitigation:**
- Isolation Docker stricte
- Pas de network access dans containers
- Limite ressources (CPU, RAM)
- Audit de sécurité avant prod
- Scan code avec PHPStan/ESLint

### Risque 5: Complexité UI Workflow Detail
**Probabilité:** Basse (20%)
**Impact:** Basse

**Mitigation:**
- Réutiliser composants existants Phase 2
- Design System déjà établi
- Prototypes rapides avec Figma
- User testing early

---

## ✅ Critères d'Acceptation Sprint 3

### Fonctionnels
- [ ] **Generate Code Action** génère du code PHP valide
- [ ] **Run Tests Action** exécute tests dans environnement isolé
- [ ] **Deploy Pipeline Action** crée PR/MR automatiquement
- [ ] **Workflow complet** s'exécute end-to-end sans erreur
- [ ] **UI** affiche toutes les étapes avec logs en temps réel
- [ ] **Tests E2E Sprint 2** passent (15+ tests)

### Non-Fonctionnels
- [ ] **Performance:** Workflow complet < 10 minutes
- [ ] **Sécurité:** Isolation Docker validée
- [ ] **Qualité:** Coverage > 75%
- [ ] **Scalabilité:** 10 workflows simultanés sans dégradation
- [ ] **Monitoring:** Logs structurés pour chaque step
- [ ] **Documentation:** User guide + Dev guide complets

### Business
- [ ] **Demo:** Vidéo démo < 2 minutes du workflow complet
- [ ] **Pitch:** Slides pour présentation clients
- [ ] **Feedback:** 5+ beta users testent et donnent feedback

---

## 🎯 Definition of Done

Un ticket est considéré comme "Done" quand:

1. ✅ **Code complété** et mergé dans `main`
2. ✅ **Tests écrits** (unit + integration) et passent
3. ✅ **Code review** fait par un autre dev
4. ✅ **Documentation** mise à jour (comments + README)
5. ✅ **Déployé** en staging et validé
6. ✅ **QA** validé (pas de bugs critiques)
7. ✅ **Performance** testée (pas de régression)
8. ✅ **Sécurité** auditée si nécessaire

---

## 📈 Métriques de Suivi

### Daily Standup Metrics
- **Velocity:** Story points complétés / jour (objectif: 2 SP/j)
- **Burndown:** Tasks remaining vs days left
- **Blockers:** Nombre de tickets bloqués
- **Code review queue:** PRs en attente review

### Sprint End Metrics
- **Completion rate:** Tasks done / total (objectif: >90%)
- **Bug count:** Bugs créés vs bugs résolus (objectif: 0 critical)
- **Test coverage:** % code couvert (objectif: >75%)
- **Tech debt:** Nouvelles TODOs ajoutées (objectif: <10)

### Quality Metrics
- **PHPStan:** Level max, 0 errors
- **Pint:** 100% compliant
- **Tests:** 100% passing
- **Performance:** API response time < 200ms p95

---

## 🚀 Post-Sprint 3

### Sprint 4 Preview
**Thème:** Monétisation + Deploy Production

**Features clés:**
1. Stripe integration (Laravel Cashier)
2. Plans tarifaires (Starter 39$, Team 99$)
3. Landing page & pricing page
4. Déploiement production DigitalOcean
5. Monitoring Sentry + logs

**🎉 Milestone:** J+30 - MVP COMPLET déployé en production

---

## 📞 Support & Communication

### Daily Standups
**Quand:** Tous les jours à 9h00
**Durée:** 15 minutes
**Format:**
- Ce que j'ai fait hier
- Ce que je fais aujourd'hui
- Mes blockers

### Sprint Review
**Quand:** J35 (24 nov) à 14h00
**Durée:** 1 heure
**Participants:** Toute l'équipe + stakeholders
**Agenda:**
- Demo workflow complet
- Retrospective Sprint 3
- Planning Sprint 4

### Communication
- **Slack:** #sprint-3 channel
- **GitHub:** Issues & PRs
- **Documentation:** Notion wiki
- **Code:** GitHub `/docs` folder

---

## 📚 Resources

### Documentation Externe
- [Laravel Horizon](https://laravel.com/docs/11.x/horizon)
- [Laravel Reverb](https://laravel.com/docs/11.x/reverb)
- [Docker Documentation](https://docs.docker.com/)
- [GitHub API](https://docs.github.com/en/rest)
- [GitLab API](https://docs.gitlab.com/ee/api/)
- [OpenAI API](https://platform.openai.com/docs)

### Documentation Interne
- [Sprint 1 Review](Sprint_1_Review.md)
- [Sprint 2 Validation Report](Sprint_2_Validation_Report.md)
- [Architecture Overview](../../CLAUDE.md)
- [Git OAuth Setup](../../git-oauth-setup.md)

---

## ✅ Checklist Démarrage Sprint 3

Avant de commencer Sprint 3, vérifier:

- [x] ✅ Sprint 2 validé à 92%
- [x] ✅ Documentation Sprint 2 complète
- [x] ✅ Environnement dev fonctionnel
- [x] ✅ Accès API LLM (OpenAI + Mistral)
- [ ] 🔄 Docker Engine installé
- [ ] 🔄 Permissions Git configurées
- [ ] 🔄 Staging environment prêt
- [ ] 🔄 Beta users identifiés (5+)
- [ ] 🔄 Sprint 3 kickoff meeting planifié

---

**Document créé le:** 28 octobre 2025
**Prochaine étape:** Démarrage Sprint 3 - J22 (11 novembre 2025)
**Navigation:** [← Sprint 2 Validation](Sprint_2_Validation_Report.md) | [100% Roadmap →](Sprint_2_To_100_Percent.md)
