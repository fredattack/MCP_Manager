# MCP Manager - Guide de Déploiement DigitalOcean

## 📋 Infrastructure Existante

### ✅ Ressources Déjà Provisionnées
```
Projet DigitalOcean: 6cf09da9-2e1a-4bd1-88c2-0de1e5b0e451

🖥️  Droplet MCP Manager
   - Nom: mcp-manager-droplet
   - IP: 138.68.27.68
   - Région: Frankfurt (fra)
   - État: Provisionné (vierge)

🚀 MCP Server (App Platform)
   - App ID: 5931937c-f29e-4a60-abe2-04dfb5c82c11
   - URL: https://mcp-server-app-6gann.ondigitalocean.app/
   - État: ✅ Opérationnel (healthy)
   - API Docs: /docs
```

### Prérequis
- ✅ Accès SSH au droplet 138.68.27.68
- ✅ Clé SSH configurée pour DigitalOcean
- 🔑 Accès au MCP Server existant
- 📦 Repository GitHub du projet

---

## 🚀 Déploiement sur Droplet (138.68.27.68)

### Avantages du Droplet
- ✅ Contrôle complet de l'environnement
- ✅ Performance prévisible
- ✅ Coût fixe et optimisé
- ✅ Droplet déjà provisionné et prêt

### 🎬 Étape 0 : Préparer en Local

```bash
# Depuis votre machine locale
cd /Users/fred/PhpstormProjects/mcp_manager

# 1. Générer la clé d'application Laravel
php artisan key:generate --show
# ⚠️ COPIER LA SORTIE (commence par "base64:...")

# 2. Vérifier que le repository est à jour
git status
git add .
git commit -m "Prepare for deployment"
git push origin main
```

### 📝 Étape 1 : Créer les Managed Databases

**Via l'interface DigitalOcean** (projet: 6cf09da9-2e1a-4bd1-88c2-0de1e5b0e451) :

1. **PostgreSQL** :
   - Aller dans Databases → Create Database
   - Engine: PostgreSQL 16
   - Name: `mcp-manager-postgres`
   - Region: Frankfurt (FRA1)
   - Size: Basic (1GB RAM, 10GB disk) - ~$15/mois
   - ⚠️ **Important** : Noter les credentials après création

2. **Redis** :
   - Engine: Redis 7
   - Name: `mcp-manager-redis`
   - Region: Frankfurt (FRA1)
   - Size: Basic (1GB RAM) - ~$15/mois

3. **Trusted Sources** :
   - Ajouter l'IP du droplet : `138.68.27.68`

### 🔧 Étape 2 : Configuration Initiale du Droplet

```bash
# Se connecter au droplet
ssh root@138.68.27.68

# Vérifier le système
lsb_release -a
# Devrait être Ubuntu 22.04 ou 24.04

# Mettre à jour le système
apt-get update && apt-get upgrade -y
```

### 📦 Étape 3 : Préparer et Lancer le Setup Automatisé

```bash
# Sur le droplet (en tant que root)

# Cloner temporairement pour récupérer les scripts
cd /tmp
git clone https://github.com/YOUR-USERNAME/mcp-manager.git
cd mcp-manager

# Éditer le script de setup
nano deploy/setup-droplet.sh

# ⚠️ MODIFIER CES LIGNES :
# DOMAIN="your-domain.com"          → DOMAIN="138.68.27.68"  # ou votre domaine
# GITHUB_REPO="git@github.com:..."  → GITHUB_REPO="git@github.com:YOUR-USERNAME/mcp-manager.git"

# Rendre le script exécutable et lancer
chmod +x deploy/setup-droplet.sh
./deploy/setup-droplet.sh
```

**Le script va automatiquement** :
- ✅ Installer PHP 8.2, Nginx, Composer, Node.js 20
- ✅ Configurer le firewall (ports 22, 80, 443)
- ✅ Créer l'utilisateur `deploy`
- ✅ Installer Supervisor, Certbot, Fail2ban
- ✅ Cloner le repository
- ✅ Configurer Nginx et Supervisor

**⏸️ PAUSE** : Le script va demander la clé SSH publique pour GitHub. Copier et ajouter comme Deploy Key.

### 🔐 Étape 4 : Configuration de l'Environnement

```bash
# Se reconnecter en tant qu'utilisateur deploy
su - deploy
cd /var/www/mcp-manager

# Copier et éditer le fichier .env
cp .env.production.example .env
nano .env
```

**Configuration .env critique** :

```env
# Application
APP_NAME="MCP Manager"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # ⚠️ COLLER LA CLÉ GÉNÉRÉE À L'ÉTAPE 0
APP_URL=http://138.68.27.68  # ou https://votre-domaine.com

# Database - PostgreSQL (depuis DigitalOcean)
DB_CONNECTION=pgsql
DB_HOST=your-postgres-cluster.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=mcp_manager
DB_USERNAME=doadmin
DB_PASSWORD=*** # ⚠️ Depuis la console DO
DB_SSLMODE=require

# Redis (depuis DigitalOcean)
REDIS_HOST=your-redis-cluster.db.ondigitalocean.com
REDIS_PASSWORD=*** # ⚠️ Depuis la console DO
REDIS_PORT=25061
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# MCP Server Integration
MCP_SERVER_URL=https://mcp-server-app-6gann.ondigitalocean.app
MCP_API_TOKEN=*** # ⚠️ À obtenir (voir section suivante)
VITE_MCP_SERVER_URL=https://mcp-server-app-6gann.ondigitalocean.app

# Mail (SendGrid recommandé)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=*** # ⚠️ Votre API key SendGrid
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
```

### 🔑 Étape 4.1 : Obtenir le Token MCP Server

```bash
# Depuis votre machine locale, créer un admin sur le MCP Server
curl -X POST https://mcp-server-app-6gann.ondigitalocean.app/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@mcp-manager.local",
    "password": "ChangeThisPassword123!",
    "full_name": "MCP Manager Admin"
  }'

# Se logger et obtenir le token
curl -X POST https://mcp-server-app-6gann.ondigitalocean.app/auth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin@mcp-manager.local&password=ChangeThisPassword123!"

# Copier le "access_token" de la réponse et le mettre dans .env
```

### 🚀 Étape 5 : Déploiement Final

```bash
# Sur le droplet, en tant que deploy
cd /var/www/mcp-manager

# Installer les dépendances
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Migrations et optimisation
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Permissions finales
sudo chown -R deploy:www-data /var/www/mcp-manager
sudo chmod -R 775 storage bootstrap/cache
```

### 🔄 Étape 6 : Démarrer les Services

```bash
# Redémarrer tous les services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart all

# Vérifier les statuts
sudo systemctl status php8.2-fpm
sudo systemctl status nginx
sudo supervisorctl status
```

### ✅ Étape 7 : Vérification

```bash
# Test health endpoint
curl http://138.68.27.68/health

# Depuis votre navigateur :
# http://138.68.27.68
```

---

## 🔗 Configuration de l'Intégration MCP Manager ↔ MCP Server

### Dans MCP Manager (.env)

```env
# MCP Server Configuration
MCP_SERVER_URL=https://mcp-server-app-6gann.ondigitalocean.app
MCP_API_TOKEN=your_token_here

# Frontend (Vite)
VITE_MCP_SERVER_URL=https://mcp-server-app-6gann.ondigitalocean.app
```

### Créer un Utilisateur Admin dans MCP Server

```bash
# Option 1 : Via l'API
curl -X POST https://mcp-server-app-6gann.ondigitalocean.app/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@votre-domaine.com",
    "password": "SecurePassword123!",
    "full_name": "Admin User"
  }'

# Option 2 : Si admin token disponible
curl -X POST https://mcp-server-app-6gann.ondigitalocean.app/admin/users \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@votre-domaine.com",
    "password": "SecurePassword123!",
    "full_name": "Admin User",
    "role": "admin",
    "is_active": true
  }'
```

### Obtenir un Token d'API

```bash
# Login et récupération du token
curl -X POST https://mcp-server-app-6gann.ondigitalocean.app/auth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin@votre-domaine.com&password=SecurePassword123!"

# Response :
# {
#   "access_token": "eyJ...",
#   "token_type": "bearer"
# }
```

---

## 📊 Architecture Déployée

```
┌─────────────────────────────────────────────────────────┐
│                   DigitalOcean Frankfurt                │
│─────────────────────────────────────────────────────────│
│                                                         │
│   🚀 MCP Server (App Platform) - ✅ LIVE               │
│   ┌──────────────────────────────────────────────┐     │
│   │ https://mcp-server-app-6gann.ondigitalocean. │     │
│   │ app/ FastAPI + Auth + 110+ API Endpoints    │     │
│   └────────────────┬─────────────────────────────┘     │
│                    │ API Calls                         │
│                    ▼                                    │
│   🖥️  MCP Manager Droplet - 138.68.27.68 - 🚧 SETUP   │
│   ┌──────────────────────────────────────────────┐     │
│   │ Laravel 12 + React 19 + Inertia.js          │     │
│   │ Nginx + PHP-FPM + Supervisor                │     │
│   │ Port 80/443                                  │     │
│   └────────────────┬─────────────────────────────┘     │
│                    │                                    │
│                    ▼                                    │
│   ☁️  Managed Services                                 │
│   ┌──────────────────────────────────────────────┐     │
│   │ PostgreSQL 16 (mcp-manager-postgres)        │     │
│   │ Redis 7 (mcp-manager-redis)                 │     │
│   └──────────────────────────────────────────────┘     │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🔍 Vérification Post-Déploiement

### Tests de Connectivité de Base

```bash
# 1. Ping du droplet
ping -c 3 138.68.27.68

# 2. Test du serveur web
curl http://138.68.27.68

# 3. Test du health endpoint Laravel
curl http://138.68.27.68/health

# 4. Test de connectivité au MCP Server depuis le droplet
ssh root@138.68.27.68 "curl -I https://mcp-server-app-6gann.ondigitalocean.app/health"
```

### Checklist Complète

- [ ] **Infrastructure**
  - [ ] Droplet accessible via SSH (138.68.27.68)
  - [ ] PostgreSQL managé créé et accessible
  - [ ] Redis managé créé et accessible
  - [ ] Trusted source ajoutée pour l'IP du droplet

- [ ] **Services Système**
  - [ ] Nginx installé et démarré
  - [ ] PHP 8.2-FPM installé et démarré
  - [ ] Composer installé (version 2.x)
  - [ ] Node.js 20 installé
  - [ ] Supervisor configuré et actif
  - [ ] Firewall (UFW) actif (ports 22, 80, 443)

- [ ] **Application**
  - [ ] Repository cloné dans `/var/www/mcp-manager`
  - [ ] Fichier `.env` configuré avec toutes les variables
  - [ ] APP_KEY généré
  - [ ] Dependencies installées (composer + npm)
  - [ ] Assets build (npm run build)
  - [ ] Migrations exécutées
  - [ ] Caches Laravel générés

- [ ] **Intégration MCP Server**
  - [ ] MCP_SERVER_URL configuré
  - [ ] MCP_API_TOKEN obtenu et configuré
  - [ ] Test de connexion réussi

- [ ] **Tests Fonctionnels**
  - [ ] Page d'accueil charge (http://138.68.27.68)
  - [ ] Assets CSS/JS chargent correctement
  - [ ] Login fonctionne
  - [ ] Dashboard accessible
  - [ ] Queue workers actifs (supervisorctl status)

### Commandes de Diagnostic

```bash
# Sur le droplet, vérifier les services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo supervisorctl status

# Vérifier les logs
sudo tail -50 /var/log/nginx/mcp-manager.error.log
sudo tail -50 /var/www/mcp-manager/storage/logs/laravel.log
sudo tail -50 /var/www/mcp-manager/storage/logs/worker.log

# Vérifier les permissions
ls -la /var/www/mcp-manager/storage
ls -la /var/www/mcp-manager/bootstrap/cache

# Tester la connexion PostgreSQL
psql "postgresql://doadmin:PASSWORD@HOST:25060/mcp_manager?sslmode=require" -c "SELECT version();"

# Tester Redis
redis-cli -h HOST -p 25061 -a PASSWORD ping
```

---

## 🚨 Dépannage

### Problème : Build échoue

**Solution** :
```bash
# Vérifier les logs de build
doctl apps logs <app-id> --type=build

# Vérifier package.json et composer.json
```

### Problème : Database connection failed

**Solution** :
```bash
# Vérifier que DATABASE_URL est correctement configuré
# Vérifier que le managed database est dans le même VPC
# Tester la connexion manuellement
```

### Problème : Assets non trouvés

**Solution** :
```bash
# Vérifier que npm run build a réussi
# Vérifier le fichier public/build/manifest.json
# Vérifier les logs Nginx
```

---

## 💰 Coût Estimé (Configuration Actuelle)

### Infrastructure Existante
- **MCP Server** (App Platform) : ~$12/mois ✅ Déjà actif
- **Droplet** (138.68.27.68) : ~$24-48/mois selon la taille ✅ Provisionné

### À Créer
- **PostgreSQL 16** (Basic 1GB) : ~$15/mois
- **Redis 7** (Basic 1GB) : ~$15/mois

### Total Mensuel Estimé : ~$66-90/mois
- Coût actuel (MCP Server + Droplet) : ~$36-60/mois
- Coût additionnel (DBs) : ~$30/mois

**🎯 Optimisation possible** : Utiliser SQLite pour commencer (économie de $30/mois)

---

## 🎯 Checklist de Déploiement Rapide

### Phase 1 : Préparation (10 min)
- [ ] Générer APP_KEY : `php artisan key:generate --show`
- [ ] Push code sur GitHub : `git push origin main`
- [ ] Créer PostgreSQL managé (ou SQLite temporaire)
- [ ] Créer Redis managé
- [ ] Noter credentials DB

### Phase 2 : Setup Droplet (20 min)
- [ ] SSH : `ssh root@138.68.27.68`
- [ ] Cloner repo : `cd /tmp && git clone ...`
- [ ] Éditer `deploy/setup-droplet.sh` (DOMAIN, GITHUB_REPO)
- [ ] Lancer : `./deploy/setup-droplet.sh`
- [ ] Ajouter Deploy Key GitHub

### Phase 3 : Configuration (15 min)
- [ ] Configurer `.env` sur le droplet
- [ ] Obtenir MCP_API_TOKEN
- [ ] Installer dependencies : `composer install && npm ci && npm run build`
- [ ] Migrer DB : `php artisan migrate --force`

### Phase 4 : Lancement (5 min)
- [ ] Redémarrer services : `sudo systemctl restart nginx php8.2-fpm`
- [ ] Vérifier : `curl http://138.68.27.68`
- [ ] Tester dans navigateur

### Phase 5 : Post-Déploiement (optionnel)
- [ ] Configurer domaine personnalisé
- [ ] Setup SSL avec Certbot
- [ ] Monitoring et alertes
- [ ] Backups automatiques

**Temps total estimé : 50 minutes**

---

## 📚 Ressources

- **Infrastructure**
  - [DigitalOcean Projet](https://cloud.digitalocean.com/projects/6cf09da9-2e1a-4bd1-88c2-0de1e5b0e451)
  - [MCP Server App](https://cloud.digitalocean.com/apps/5931937c-f29e-4a60-abe2-04dfb5c82c11)

- **Documentation**
  - [MCP Server API Docs](https://mcp-server-app-6gann.ondigitalocean.app/docs)
  - [Laravel 12 Deployment](https://laravel.com/docs/12.x/deployment)
  - [DigitalOcean Droplets](https://docs.digitalocean.com/products/droplets/)

- **Scripts de Déploiement**
  - `deploy/setup-droplet.sh` - Setup initial automatisé
  - `deploy/deploy.sh` - Déploiement incrémental
  - `deploy/nginx.conf` - Configuration Nginx
  - `deploy/supervisor.conf` - Configuration Supervisor

---

## 🚀 Démarrage Rapide

```bash
# Depuis votre machine locale
cd /Users/fred/PhpstormProjects/mcp_manager
php artisan key:generate --show  # Copier le résultat

# Sur le droplet
ssh root@138.68.27.68
cd /tmp && git clone https://github.com/YOUR-USERNAME/mcp-manager.git
cd mcp-manager
nano deploy/setup-droplet.sh  # Éditer DOMAIN et GITHUB_REPO
chmod +x deploy/setup-droplet.sh && ./deploy/setup-droplet.sh

# Suivre les instructions du script
# Configurer .env
# Relancer les services
# Tester : http://138.68.27.68
```

**📞 Besoin d'aide ?** Consulter la section Dépannage ci-dessus ou les logs :
- Nginx : `/var/log/nginx/mcp-manager.error.log`
- Laravel : `/var/www/mcp-manager/storage/logs/laravel.log`
- Workers : `/var/www/mcp-manager/storage/logs/worker.log`