# Configuration GitHub Actions - Guide Complet

## ✅ Étape 1 : Clés SSH Générées

Les clés SSH ont été générées avec succès dans `~/.ssh/mcp_manager_deploy`

---

## 📋 Étape 2 : Configurer le Droplet

### Option A : Script Automatique (Recommandé)

```bash
# Se connecter au droplet
ssh root@138.68.27.68

# Télécharger et exécuter le script de configuration
curl -o /tmp/setup-github-actions.sh https://raw.githubusercontent.com/YOUR-USERNAME/mcp-manager/main/deploy/setup-github-actions.sh
chmod +x /tmp/setup-github-actions.sh
/tmp/setup-github-actions.sh
```

### Option B : Configuration Manuelle

```bash
# Se connecter au droplet
ssh root@138.68.27.68

# Basculer sur l'utilisateur deploy
su - deploy

# Créer le répertoire SSH
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Ajouter la clé publique
nano ~/.ssh/authorized_keys
# Coller cette clé :
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAINoYlrUn8I8Q5ZHrfMCq3vi4TKdrlzmHUzk2UCMxvg+j github-actions-deploy-mcp-manager

# Sécuriser
chmod 600 ~/.ssh/authorized_keys
exit  # Retour root

# Configurer sudo pour deploy
cat > /etc/sudoers.d/deploy << 'EOF'
# Permissions pour GitHub Actions Deploy
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart all
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl reread
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl update
EOF

chmod 440 /etc/sudoers.d/deploy

# Tester la configuration
sudo -u deploy sudo -n systemctl reload php8.2-fpm --dry-run
```

---

## 🔐 Étape 3 : Tester la Connexion SSH

```bash
# Depuis votre machine locale
ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68

# Si ça fonctionne, vous devriez voir :
# Welcome to Ubuntu...
# deploy@mcp-manager-droplet:~$
```

**✅ Si la connexion fonctionne, passez à l'étape suivante**

**❌ Si erreur "Permission denied"** :
```bash
# Vérifier les permissions sur le droplet
ssh root@138.68.27.68
ls -la /home/deploy/.ssh/
cat /home/deploy/.ssh/authorized_keys
```

---

## 🔑 Étape 4 : Configurer les Secrets GitHub

### 4.1 Aller sur GitHub

1. Ouvrir votre repository sur GitHub
2. **Settings** → **Secrets and variables** → **Actions**
3. Cliquer sur **New repository secret**

### 4.2 Créer les 4 Secrets

#### Secret 1 : SSH_PRIVATE_KEY

**Nom** : `SSH_PRIVATE_KEY`

**Valeur** : Copier TOUT le contenu ci-dessous (incluant les lignes BEGIN/END)

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACDaGJa1J/CPEOWR63zAqt74uEyna5c5h1M5NlAjMb4PowAAAKj1hh7x9YYe
8QAAAAtzc2gtZWQyNTUxOQAAACDaGJa1J/CPEOWR63zAqt74uEyna5c5h1M5NlAjMb4Pow
AAAEDltaEl73XfujHzaplDr2g8Kz8xSdmJqtZlib3KBrnEFdoYlrUn8I8Q5ZHrfMCq3vi4
TKdrlzmHUzk2UCMxvg+jAAAAIWdpdGh1Yi1hY3Rpb25zLWRlcGxveS1tY3AtbWFuYWdlcg
ECAwQ=
-----END OPENSSH PRIVATE KEY-----
```

#### Secret 2 : SSH_HOST

**Nom** : `SSH_HOST`

**Valeur** : `138.68.27.68`

#### Secret 3 : SSH_USER

**Nom** : `SSH_USER`

**Valeur** : `deploy`

#### Secret 4 : SSH_PORT

**Nom** : `SSH_PORT`

**Valeur** : `22`

### 4.3 Vérifier

Vous devriez avoir **4 secrets configurés** :

```
✅ SSH_PRIVATE_KEY
✅ SSH_HOST
✅ SSH_USER
✅ SSH_PORT
```

---

## 🧪 Étape 5 : Tester le Déploiement

### Test Manuel (Recommandé pour la première fois)

1. Aller sur GitHub → **Actions**
2. Sélectionner le workflow **Deploy to DigitalOcean**
3. Cliquer sur **Run workflow**
4. Sélectionner la branche `main`
5. Cliquer sur **Run workflow**

### Observer le Déploiement

Le workflow va exécuter 3 jobs :

1. **test** (1-2 min) : Tests PHPUnit + Pint
2. **deploy** (2-3 min) : Déploiement sur le droplet
3. **rollback** : Seulement si le deploy échoue

### Logs en Temps Réel

Cliquer sur le workflow en cours d'exécution pour voir les logs détaillés :

```
🚀 Starting deployment...
📥 Pulling latest changes from main...
📦 Installing Composer dependencies...
📦 Installing NPM dependencies...
🔨 Building frontend assets...
🗄️  Running database migrations...
🧹 Clearing and optimizing caches...
🔐 Setting permissions...
🔄 Restarting services...
✅ Deployment completed successfully!
```

### Vérifier le Résultat

```bash
# Depuis votre machine locale
curl http://138.68.27.68/health

# Devrait retourner :
# {"status":"ok","timestamp":"..."}
```

---

## 🎯 Étape 6 : Test Automatique

Maintenant, chaque push sur `main` déclenchera automatiquement le déploiement :

```bash
# Faire un changement
echo "# Auto-deploy test" >> README.md

# Commit et push
git add README.md
git commit -m "test: trigger auto-deploy"
git push origin main

# Aller sur GitHub → Actions pour voir le workflow
```

---

## 🚨 Troubleshooting

### Erreur : "Permission denied (publickey)"

**Cause** : La clé publique n'est pas correctement configurée sur le droplet

**Solution** :
```bash
# Sur le droplet (en tant que root)
cat /home/deploy/.ssh/authorized_keys
# Vérifier que la clé est bien présente

# Vérifier les permissions
ls -la /home/deploy/.ssh/
# Devrait être :
# drwx------ 2 deploy deploy 4096 ... .ssh
# -rw------- 1 deploy deploy  123 ... authorized_keys
```

### Erreur : "sudo: a password is required"

**Cause** : Les permissions sudo ne sont pas configurées

**Solution** :
```bash
# Sur le droplet (en tant que root)
cat /etc/sudoers.d/deploy
# Vérifier que le fichier existe et contient les bonnes permissions

# Tester
sudo -u deploy sudo -n systemctl reload php8.2-fpm --dry-run
```

### Erreur : "composer: command not found"

**Cause** : Le projet n'a pas été initialisé sur le droplet

**Solution** :
```bash
# Exécuter d'abord le setup-droplet.sh
ssh root@138.68.27.68
cd /tmp && git clone https://github.com/YOUR-USERNAME/mcp-manager.git
cd mcp-manager
chmod +x deploy/setup-droplet.sh
./deploy/setup-droplet.sh
```

### Erreur : Health check failed

**Cause** : L'application n'a pas démarré correctement

**Solution** :
```bash
# Vérifier les logs sur le droplet
ssh deploy@138.68.27.68
sudo tail -50 /var/log/nginx/mcp-manager.error.log
sudo tail -50 /var/www/mcp-manager/storage/logs/laravel.log

# Vérifier les services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo supervisorctl status
```

---

## ✅ Checklist Complète

### Configuration Droplet
- [ ] Clés SSH générées localement
- [ ] Clé publique ajoutée sur le droplet (`~/.ssh/authorized_keys`)
- [ ] Permissions SSH correctes (700 pour .ssh, 600 pour authorized_keys)
- [ ] Sudo configuré pour l'utilisateur deploy (`/etc/sudoers.d/deploy`)
- [ ] Connexion SSH testée : `ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68`

### Configuration GitHub
- [ ] Secret `SSH_PRIVATE_KEY` ajouté
- [ ] Secret `SSH_HOST` ajouté (138.68.27.68)
- [ ] Secret `SSH_USER` ajouté (deploy)
- [ ] Secret `SSH_PORT` ajouté (22)
- [ ] Workflow file `.github/workflows/deploy.yml` committé

### Tests
- [ ] Test manuel du workflow (Actions → Run workflow)
- [ ] Workflow complété avec succès
- [ ] Health check passed (http://138.68.27.68/health)
- [ ] Test automatique (push sur main)

---

## 🎉 Configuration Terminée !

Une fois toutes les étapes validées, **chaque push sur `main` déclenchera automatiquement** :

1. ✅ Tests PHPUnit + Pint
2. 🚀 Déploiement sur le droplet
3. ✅ Health check
4. ⏪ Rollback automatique en cas d'erreur

**Temps de déploiement : 3-5 minutes**

---

## 📞 Aide Rapide

**Voir les clés générées** :
```bash
cat ~/.ssh/mcp_manager_deploy      # Clé privée
cat ~/.ssh/mcp_manager_deploy.pub  # Clé publique
```

**Tester la connexion** :
```bash
ssh -i ~/.ssh/mcp_manager_deploy deploy@138.68.27.68
```

**Voir les secrets GitHub** :
```
Settings → Secrets and variables → Actions
```

**Voir les workflows** :
```
Actions → Deploy to DigitalOcean
```