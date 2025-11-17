# 🚀 Guide de Déploiement Rapide - MCP Manager

## État Actuel

✅ **Droplet** : 138.68.27.68 (vierge, pas encore configuré)
✅ **MCP Server** : https://mcp-server-app-6gann.ondigitalocean.app/ (opérationnel)
✅ **Clés SSH** : Générées dans `~/.ssh/mcp_manager_deploy`

---

## 📋 Plan d'Action (3 Phases)

### Phase 1 : Setup Initial du Droplet (30 min)
### Phase 2 : Configuration GitHub Actions (10 min)
### Phase 3 : Premier Déploiement (5 min)

---

## 🎯 Phase 1 : Setup Initial du Droplet

Le droplet est vierge et doit être initialisé avec :
- Stack LAMP (Nginx, PHP 8.2, Composer, Node.js)
- Utilisateur `deploy`
- Supervisor, Fail2ban, Firewall
- Configuration Nginx et SSL

### Option A : Setup Automatique (Recommandé)

```bash
# 1. Se connecter au droplet
ssh root@138.68.27.68

# 2. Cloner le repository temporairement
cd /tmp
git clone https://github.com/YOUR-USERNAME/mcp-manager.git
cd mcp-manager

# 3. Éditer le script de setup
nano deploy/setup-droplet.sh

# ⚠️ MODIFIER CES 2 LIGNES :
# Ligne 12 : DOMAIN="138.68.27.68"  # ou votre-domaine.com
# Ligne 13 : GITHUB_REPO="git@github.com:YOUR-USERNAME/mcp-manager.git"

# 4. Lancer le setup
chmod +x deploy/setup-droplet.sh
./deploy/setup-droplet.sh
```

**Le script va** :
- Installer toute la stack nécessaire
- Créer l'utilisateur `deploy`
- Configurer Nginx, Supervisor, Firewall
- **PAUSE** : Il demandera une clé SSH publique GitHub
  - Sur votre machine : `cat ~/.ssh/id_rsa.pub` ou créer une deploy key
  - Ajouter sur GitHub : Settings → Deploy keys
- Cloner le projet
- Installer dependencies

**⏱️ Durée : ~20-30 minutes**

### Option B : Setup Manuel

Si vous préférez installer manuellement, suivez le guide détaillé :
`deploy/DEPLOYMENT_GUIDE.md` - Sections Étapes 2 & 3

---

## 🔐 Phase 2 : Configuration GitHub Actions

Une fois le droplet configuré avec l'utilisateur `deploy` :

### 2.1 Configurer le Droplet pour GitHub Actions

```bash
# Depuis votre machine locale
cd /Users/fred/PhpstormProjects/mcp_manager

# Exécuter le script de configuration
ssh root@138.68.27.68 << 'ENDSSH'
DEPLOY_USER="deploy"

# Ajouter la clé publique GitHub Actions
sudo -u $DEPLOY_USER mkdir -p /home/$DEPLOY_USER/.ssh
sudo -u $DEPLOY_USER chmod 700 /home/$DEPLOY_USER/.ssh
echo "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAINoYlrUn8I8Q5ZHrfMCq3vi4TKdrlzmHUzk2UCMxvg+j github-actions-deploy-mcp-manager" | sudo -u $DEPLOY_USER tee -a /home/$DEPLOY_USER/.ssh/authorized_keys
sudo -u $DEPLOY_USER chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys

# Configurer sudo
cat > /etc/sudoers.d/deploy << 'EOF'
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart all
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl reread
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl update
EOF
chmod 440 /etc/sudoers.d/deploy
ENDSSH

echo "✅ Droplet configuré pour GitHub Actions"
```

### 2.2 Tester la Connexion SSH

```bash
ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68
# Devrait se connecter sans demander de mot de passe
```

### 2.3 Configurer les Secrets GitHub

1. **Aller sur GitHub** :
   `https://github.com/YOUR-USERNAME/mcp-manager/settings/secrets/actions`

2. **Créer 4 secrets** :

| Nom | Valeur |
|-----|--------|
| `SSH_PRIVATE_KEY` | Contenu complet de `~/.ssh/mcp_manager_deploy` |
| `SSH_HOST` | `138.68.27.68` |
| `SSH_USER` | `deploy` |
| `SSH_PORT` | `22` |

**Pour copier la clé privée** :
```bash
cat ~/.ssh/mcp_manager_deploy
# Copier TOUT (incluant BEGIN et END)
```

---

## 🚀 Phase 3 : Premier Déploiement

### 3.1 Vérifier que tout est prêt

```bash
# ✅ Droplet configuré (Phase 1)
ssh root@138.68.27.68 "id deploy"
# Devrait afficher : uid=1000(deploy) gid=1000(deploy) groups=1000(deploy)...

# ✅ SSH fonctionne (Phase 2)
ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68 "echo OK"
# Devrait afficher : OK

# ✅ Secrets GitHub configurés (Phase 2)
# Vérifier manuellement sur GitHub
```

### 3.2 Lancer le Premier Déploiement

**Option A : Test Manuel**

1. Aller sur GitHub → **Actions**
2. Sélectionner **Deploy to DigitalOcean**
3. Cliquer **Run workflow** → Sélectionner `main` → **Run workflow**

**Option B : Test Automatique**

```bash
# Faire un commit test
git add .
git commit -m "feat: enable auto-deploy"
git push origin main

# Le workflow se déclenche automatiquement
# Suivre sur : https://github.com/YOUR-USERNAME/mcp-manager/actions
```

### 3.3 Observer le Déploiement

Le workflow va exécuter :

1. ✅ **Tests** (~2 min) : PHPUnit + Pint
2. 🚀 **Deploy** (~3 min) :
   - Pull code
   - Install dependencies
   - Build assets
   - Migrate DB
   - Cache configs
   - Restart services
3. ✅ **Health Check** : Vérifie `http://138.68.27.68/health`

### 3.4 Vérifier le Résultat

```bash
# Test health
curl http://138.68.27.68/health

# Devrait retourner :
# {"status":"ok",...}

# Ouvrir dans navigateur
open http://138.68.27.68
```

---

## ✅ Checklist Complète

### Phase 1 : Setup Droplet
- [ ] SSH connecté : `ssh root@138.68.27.68`
- [ ] Script setup lancé : `./deploy/setup-droplet.sh`
- [ ] Utilisateur deploy créé : `id deploy`
- [ ] Nginx installé : `systemctl status nginx`
- [ ] PHP-FPM installé : `systemctl status php8.2-fpm`
- [ ] Projet cloné : `ls /var/www/mcp-manager`

### Phase 2 : GitHub Actions
- [ ] Clé publique ajoutée sur droplet
- [ ] Sudo configuré pour deploy
- [ ] SSH teste OK : `ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68`
- [ ] 4 secrets GitHub configurés
- [ ] Workflow file `.github/workflows/deploy.yml` présent

### Phase 3 : Déploiement
- [ ] Workflow lancé (manuel ou auto)
- [ ] Tests passés ✅
- [ ] Déploiement réussi ✅
- [ ] Health check OK : `http://138.68.27.68/health`
- [ ] Application accessible : `http://138.68.27.68`

---

## 🚨 Problèmes Fréquents

### "sudo: unknown user deploy"
**Cause** : Le setup initial n'a pas été fait
**Solution** : Exécuter Phase 1 (setup-droplet.sh)

### "Permission denied (publickey)"
**Cause** : La clé publique n'est pas sur le droplet
**Solution** : Relancer la section 2.1 de Phase 2

### "composer: command not found"
**Cause** : Le setup n'a pas installé les outils
**Solution** : Vérifier que setup-droplet.sh s'est exécuté complètement

### "Health check failed"
**Cause** : L'application n'a pas démarré
**Solution** :
```bash
ssh deploy@138.68.27.68
sudo tail -50 /var/www/mcp-manager/storage/logs/laravel.log
sudo systemctl status php8.2-fpm nginx
```

---

## 📞 Aide Rapide

**Documentation complète** :
- `deploy/DEPLOYMENT_GUIDE.md` - Guide détaillé complet
- `deploy/GITHUB_ACTIONS_SETUP.md` - Configuration GitHub Actions
- `.github/workflows/README.md` - Documentation du workflow

**Scripts disponibles** :
- `deploy/setup-droplet.sh` - Setup initial automatique
- `deploy/setup-github-actions.sh` - Configuration GitHub Actions
- `deploy/deploy.sh` - Déploiement manuel

**Commandes utiles** :
```bash
# Voir les clés SSH
cat ~/.ssh/mcp_manager_deploy.pub

# Tester connexion droplet
ssh root@138.68.27.68

# Tester connexion deploy
ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68

# Voir les logs GitHub Actions
# https://github.com/YOUR-USERNAME/mcp-manager/actions
```

---

## 🎯 Résumé en Une Commande

Une fois le setup initial fait (Phase 1), pour déployer :

```bash
git push origin main
```

C'est tout ! GitHub Actions se charge du reste automatiquement.

---

**⏱️ Temps total estimé : 45 minutes**
- Phase 1 : 30 min (setup initial, une seule fois)
- Phase 2 : 10 min (configuration GitHub Actions, une seule fois)
- Phase 3 : 5 min (premier déploiement, puis automatique)