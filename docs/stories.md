# ✅ TODO LIST – Phase 2: Prototype – Automated Invoice Retrieval & Processing

## 🎯 Objectif général

Créer un prototype automatisé capable de se connecter à un portail fournisseur, télécharger des factures, les stocker, appliquer un OCR et exporter les données, avec une interface React user-friendly et une couverture de test minimale de 80%.

---

## 🧩 Stories & tâches détaillées

### 📘 Story 1: Initialisation du projet front et back

#### ✅ Tâches

- [X] Créer le projet avec laravel 12 et le starter kit React
- [X] init le repo Git (https://github.com/fredattack/easyinvoice)
- [X] Configurer les scripts Makefile pour les commandes courantes
- [X] Configurer ESLint avec les règles strictes (airbnb + typescript + prettier)
- [X] Initialiser le projet Laravel avec PHPStan niveau max
- [X] Créer les pipelines CI pour tests, linting et coverage 
- [X] creer un pre-commit hook pour exécuter le linter, prettier et rector
- [X] Documenter le setup dans le README

#### 🎯 KPI

- CI verte à chaque push
- ESLint et PHPStan passent sans erreur
- Couverture de test initiale visible

#### ✅ Critères d'acceptation

- Projet lancé localement avec un `make start` sur un port specifique ex 1978
- lancer `make test` pour exécuter les tests
- lancer `make lint` pour exécuter le linter
- lancer `make coverage` pour générer la couverture de test
- lancer `make docs` pour générer la documentation avec scramble
- lancer `make stan` pour  exécuter PHPStan
- lancer `make format` pour formater le code avec Prettier
- lancer `make rector` pour exécuter Rector
- lancer `make quality-check` pour exécuter les tests, le linter, prettier, rector et PHPStan
- Tests unitaires et fonctionnels écrits

---

### 🤖 Story 2 – Script d'automatisation Playwright : Connexion & Navigation Portail Fournisseur

#### 📝 Objectif

Créer un script Node.js basé sur **Playwright** pour :
- Se connecter au portail Adobe via SSO.
- Naviguer automatiquement à la page des factures.
- Détecter et identifier les factures disponibles.
- Marquer celles à télécharger (non encore enregistrées).
- Logguer chaque étape pour audit et debug.

---

#### ✅ Tâches techniques

- [x] Créer un script (`scripts/adobe-fetch.ts`) basé sur Playwright.
- [ ] Implémenter l’authentification via l’URL SSO Adobe :
  https://auth.services.adobe.com/fr_FR/deeplink.html?deeplink=ssofirst&callback=https%3A%2F%2Fims-na1.adobelogin.com%2Fims%2Fadobeid%2FSunbreakWebUI1%2FAdobeID%2Ftoken%3Fredirect_uri%3Dhttps%253A%252F%252Faccount.adobe.com%252Forders%252Fbilling-history%2523old_hash%253D%2526from_ims%253Dtrue%253Fclient_id%253DSunbreakWebUI1%2526api%253Dauthorize%2526scope%253DAdobeID%252Copenid%252Cacct_mgmt_api%252Cgnav%252Cread_countries_regions%252Csocial.link%252Cunlink_social_account%252Cadditional_info.address.mail_to%252Cclient.scopes.read%252Cpublisher.read%252Cadditional_info.account_type%252Cadditional_info.roles%252Cadditional_info.social%252Cadditional_info.screen_name%252Cadditional_info.optionalAgreements%252Cadditional_info.secondary_email%252Cadditional_info.secondary_email_verified%252Cadditional_info.phonetic_name%252Cadditional_info.dob%252Cupdate_profile.all%252Csecurity_profile.read%252Csecurity_profile.update%252Cadmin_manage_user_consent%252Cadmin_slo%252Cpiip_write%252Cmps%252Clast_password_update%252Cupdate_email%252Cread_organizations%252Cemail_verification.w%252Cuds_write%252Cuds_read%252Cfirefly_api%252Cpasskey_read%252Cpasskey_write%252Caccount_cluster.read%252Caccount_cluster.update%252Cadditional_info.authenticatingAccount%2526reauth%253Dtrue%26state%3D%257B%2522jslibver%2522%253A%2522v2-v0.31.0-2-g1e8a8a8%2522%252C%2522nonce%2522%253A%25223194402582850829%2522%257D%26code_challenge_method%3Dplain%26use_ms_for_expiry%3Dtrue&client_id=SunbreakWebUI1&scope=AdobeID%2Copenid%2Cacct_mgmt_api%2Cgnav%2Cread_countries_regions%2Csocial.link%2Cunlink_social_account%2Cadditional_info.address.mail_to%2Cclient.scopes.read%2Cpublisher.read%2Cadditional_info.account_type%2Cadditional_info.roles%2Cadditional_info.social%2Cadditional_info.screen_name%2Cadditional_info.optionalAgreements%2Cadditional_info.secondary_email%2Cadditional_info.secondary_email_verified%2Cadditional_info.phonetic_name%2Cadditional_info.dob%2Cupdate_profile.all%2Csecurity_profile.read%2Csecurity_profile.update%2Cadmin_manage_user_consent%2Cadmin_slo%2Cpiip_write%2Cmps%2Clast_password_update%2Cupdate_email%2Cread_organizations%2Cemail_verification.w%2Cuds_write%2Cuds_read%2Cfirefly_api%2Cpasskey_read%2Cpasskey_write%2Caccount_cluster.read%2Caccount_cluster.update%2Cadditional_info.authenticatingAccount%2Creauthenticated&state=%7B%22jslibver%22%3A%22v2-v0.31.0-2-g1e8a8a8%22%2C%22nonce%22%3A%223194402582850829%22%7D&relay=99cc2a3f-9210-465c-89ba-48eb48b9654e&locale=fr_FR&flow_type=token&idp_flow_type=login&reauthenticate=force&s_p=google%2Cfacebook%2Capple%2Cmicrosoft%2Cline%2Ckakao&response_type=token&code_challenge_method=plain&redirect_uri=https%3A%2F%2Faccount.adobe.com%2Forders%2Fbilling-history%23old_hash%3D%26from_ims%3Dtrue%3Fclient_id%3DSunbreakWebUI1%26api%3Dauthorize%26scope%3DAdobeID%2Copenid%2Cacct_mgmt_api%2Cgnav%2Cread_countries_regions%2Csocial.link%2Cunlink_social_account%2Cadditional_info.address.mail_to%2Cclient.scopes.read%2Cpublisher.read%2Cadditional_info.account_type%2Cadditional_info.roles%2Cadditional_info.social%2Cadditional_info.screen_name%2Cadditional_info.optionalAgreements%2Cadditional_info.secondary_email%2Cadditional_info.secondary_email_verified%2Cadditional_info.phonetic_name%2Cadditional_info.dob%2Cupdate_profile.all%2Csecurity_profile.read%2Csecurity_profile.update%2Cadmin_manage_user_consent%2Cadmin_slo%2Cpiip_write%2Cmps%2Clast_password_update%2Cupdate_email%2Cread_organizations%2Cemail_verification.w%2Cuds_write%2Cuds_read%2Cfirefly_api%2Cpasskey_read%2Cpasskey_write%2Caccount_cluster.read%2Caccount_cluster.update%2Cadditional_info.authenticatingAccount%26reauth%3Dtrue&use_ms_for_expiry=true#/
- [x] Naviguer automatiquement vers la section "Factures".
    - URL : `https://account.adobe.com/orders/billing-history?search=AE02698100035CBE`
    - Identifiants :
        - username : `info@hddev.be`
        - password : `123456`
- [x] Intégrer un système de logs (console + fichier `/logs/adobe-fetch.log`).
- [x] Capturer les métadonnées de chaque facture visible (date, numéro, montant, nom, lien de téléchargement).
- [x] Vérifier si la facture existe déjà dans la base de données ou sur le disque.
- [x] Marquer comme "téléchargeable" les factures absentes.
- [x] Exposer une fonction `startAdobeFetch()` pour appeler le script via une simple commande (ex. bouton frontend ou cron).

---

#### 🧩 Structure de données associée

- Modèle **Supplier**
    - `id`
    - `name`
    - `login_url`
    - `invoices_url`
    - `username`
    - `password`
    - `script_identifier` (ex: `adobe`)
    - `active`

- Modèle **Invoice**
    - `id`
    - `supplier_id`
    - `invoice_number`
    - `invoice_date`
    - `amount`
    - `downloaded_at` (nullable)
    - `file_path`

- Table de relation : un `Supplier` possède plusieurs `Invoice`.

---

#### ✅ Tests E2E

- Utiliser **Jest + Playwright** :
    - Tester la capacité du script à :
        - Atteindre la page de login.
        - S’authentifier.
        - Atteindre la page de factures.
        - Détecter des factures.
        - Retourner les données au bon format.
    - Mock possible des credentials via `.env.test` ou fichier JSON sécurisé.

---

#### 📊 KPI

- Taux de succès de connexion > 90 % sur 20 exécutions.
- Temps moyen entre lancement et arrivée sur la section factures < 5 secondes.
- Taux de factures correctement listées > 95 %.

---

#### ✅ Critères d'acceptation

- Le script s’exécute en 1 commande (`npm run fetch:adobe`) ou clic UI.
- Les logs d’exécution sont accessibles dans `/logs`.
- Une facture visible dans l’historique et absente du disque est identifiée comme à télécharger.
- La base de données est mise à jour avec les nouvelles factures détectées.
- Tous les tests E2E passent (Playwright + Jest).

---

#### 🚫 Contraintes

- Ne pas intégrer l’OCR ou l’export CSV/JSON dans cette story.
- Ne pas s’occuper de l’interface utilisateur autre qu’un bouton ou une commande de lancement.
- Stockage local temporaire dans `/invoices/adobe/YYYY/MM`.

---

#### 🔐 Sécurité

- Ne jamais exposer les identifiants en clair dans le code source.
- Gérer les credentials via un fichier `.env` ou un vault sécurisé.

---

### 📥 Story 3: Téléchargement et stockage des factures

#### ✅ Tâches

- [ ] Créer un dossier `/invoices/{fournisseur}/{année}/{mois}`
- [ ] Télécharger la facture en PDF
- [ ] Tester la présence du fichier en local
- [ ] Simuler plusieurs cas : facture unique, plusieurs factures, aucune facture

#### 🎯 KPI

- 100% de factures téléchargeables enregistrées au bon chemin
- Pas de doublons

#### ✅ Critères d'acceptation

- Tests valident l’emplacement et la présence des fichiers

---

### 🧠 Story 4: OCR avec Tesseract

#### ✅ Tâches

- [ ] Intégrer Tesseract dans le pipeline
- [ ] Extraire les données clés : date, numéro, montant, fournisseur
- [ ] Mapper les champs vers un schéma standard JSON
- [ ] Écrire des tests d'extraction sur différents modèles de factures

#### 🎯 KPI

- Taux de précision OCR > 85%
- Extraction des 4 champs clés sur > 90% des factures

#### ✅ Critères d'acceptation

- Tests unitaires valident les champs extraits

---

### 💾 Story 5: Export CSV/JSON

#### ✅ Tâches

- [ ] Générer fichier CSV et JSON des données extraites
- [ ] Vérifier les colonnes : date, montant, fournisseur, numéro
- [ ] Intégrer un bouton dans le frontend pour lancer l'export

#### 🎯 KPI

- 100% des exports générés sans erreur
- Vérification manuelle = données valides

#### ✅ Critères d'acceptation

- Contenu CSV/JSON valide et exporté dans `/exports`

---

### 🧪 Story 6: Interface utilisateur React (MVP)

#### ✅ Tâches

- [ ] Créer interface avec les pages : Dashboard, Logs, Extraction, Historique
- [ ] Intégrer composants UI (shadcn/ui ou MUI)
- [ ] Ajouter une timeline des étapes d’extraction
- [ ] Intégrer affichage des logs (succès/erreurs)

#### 🎯 KPI

- Temps pour lancer une extraction < 30s
- Interface 100% fonctionnelle sur mobile et desktop

#### ✅ Critères d'acceptation

- Tests E2E Cypress couvrent les principales fonctionnalités
- L’UX est validée par 2 utilisateurs test

---

### 📊 Story 7: Mesure de la performance & logs

#### ✅ Tâches

- [ ] Logger le taux de réussite de téléchargement
- [ ] Logger la précision OCR
- [ ] Logger les erreurs d’accès, de parsing, d’écriture
- [ ] Générer des métriques hebdo dans une page "Stats"

#### 🎯 KPI

- Journalisation de 100% des actions critiques
- Tableau de bord disponible via l'interface

#### ✅ Critères d'acceptation

- Les logs sont visibles et filtrables dans l’interface

---

## ✅ TDD & Qualité

- Couverture minimale de 80% (frontend et backend)
- Tests E2E avec Cypress et Playwright
- Tests unitaires et fonctionnels backend (Pest)
- PHPStan (niveau max) sans erreur
- ESLint strict sans warning

---

## 📁 Structure de répertoires

```
/scripts         # Scripts d’automatisation (Playwright)
/frontend        # Interface React
/backend         # Laravel + API
/invoices        # Factures téléchargées
/exports         # Données extraites CSV/JSON
/tests           # Tests globaux
```

---

## 📆 Planning prévisionnel (prototype)

| Semaine | Tâches principales          |
| ------- | ---------------------------- |
| S1      | Setup projet + Playwright    |
| S2      | Download + stockage factures |
| S3      | Extraction OCR + mapping     |
| S4      | Interface React + export     |
| S5      | Logs + Stats + TDD           |
