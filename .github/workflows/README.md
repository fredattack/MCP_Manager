# GitHub Actions - Configuration des Secrets

Pour que le déploiement automatique fonctionne, vous devez configurer les secrets suivants dans votre repository GitHub.

## 📋 Secrets Requis

Aller dans **Settings → Secrets and variables → Actions → New repository secret**

### 1. SSH_PRIVATE_KEY

**Clé SSH privée pour se connecter au droplet**

```bash
# Générer une nouvelle paire de clés SSH (sur votre machine locale)
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/mcp_manager_deploy

# Copier le contenu de la clé privée
cat ~/.ssh/mcp_manager_deploy
```

- Copier **TOUTE** la sortie (y compris `-----BEGIN OPENSSH PRIVATE KEY-----` et `-----END OPENSSH PRIVATE KEY-----`)
- Coller dans le secret `SSH_PRIVATE_KEY`

**Ensuite, ajouter la clé publique au droplet** :

```bash
# Copier la clé publique
cat ~/.ssh/mcp_manager_deploy.pub

# Sur le droplet (en tant qu'utilisateur deploy)
ssh root@138.68.27.68
su - deploy
echo "COLLER_ICI_LA_CLÉ_PUBLIQUE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 2. SSH_HOST

**L'adresse IP du droplet**

```
Valeur: 138.68.27.68
```

### 3. SSH_USER

**L'utilisateur SSH (créé par setup-droplet.sh)**

```
Valeur: deploy
```

### 4. SSH_PORT

**Port SSH (par défaut)**

```
Valeur: 22
```

---

## 🧪 Tester le Workflow

### Test Manuel

Vous pouvez déclencher le workflow manuellement :

1. Aller dans **Actions** → **Deploy to DigitalOcean**
2. Cliquer **Run workflow**
3. Sélectionner la branche `main`
4. Cliquer **Run workflow**

### Test Automatique

```bash
# Faire un commit et push sur main
git add .
git commit -m "test: trigger deployment"
git push origin main
```

Le workflow se déclenchera automatiquement.

---

## 📊 Étapes du Workflow

### Job 1: Test (obligatoire)
- ✅ Checkout code
- ✅ Setup PHP 8.4
- ✅ Install dependencies
- ✅ Run PHPUnit tests
- ✅ Run Pint (code style)

### Job 2: Deploy (si tests passent)
- ✅ Connect via SSH
- ✅ Pull latest code
- ✅ Install dependencies
- ✅ Build assets
- ✅ Run migrations
- ✅ Clear & cache configs
- ✅ Restart services
- ✅ Health check

### Job 3: Rollback (si deploy échoue)
- ⏪ Revert to previous commit
- ⏪ Rebuild application
- ⏪ Restart services

---

## 🔍 Monitoring

### Voir les Logs

Dans GitHub :
- **Actions** → Sélectionner le workflow → Voir les détails

### Vérifier le Déploiement

```bash
# Depuis votre machine locale
curl http://138.68.27.68/health

# Vérifier la version déployée (sur le droplet)
ssh deploy@138.68.27.68 "cd /var/www/mcp-manager && git log -1 --oneline"
```

---

## 🛡️ Sécurité

### Permissions Sudo pour l'Utilisateur Deploy

Le script nécessite que l'utilisateur `deploy` puisse redémarrer les services :

```bash
# Sur le droplet (en tant que root)
visudo

# Ajouter cette ligne :
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm, /usr/bin/supervisorctl restart all
```

---

## 🚨 Troubleshooting

### Erreur : Permission denied (publickey)

```bash
# Vérifier que la clé publique est bien ajoutée
ssh deploy@138.68.27.68 "cat ~/.ssh/authorized_keys"

# Vérifier les permissions
ssh deploy@138.68.27.68 "ls -la ~/.ssh/"
```

### Erreur : npm ci failed

```bash
# Sur le droplet, vérifier Node.js
ssh deploy@138.68.27.68 "node -v"

# Si Node.js n'est pas installé ou mauvaise version
ssh root@138.68.27.68
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
```

### Erreur : php artisan migrate failed

```bash
# Vérifier la connexion à la base de données
ssh deploy@138.68.27.68 "cd /var/www/mcp-manager && php artisan db:show"

# Vérifier le fichier .env
ssh deploy@138.68.27.68 "cd /var/www/mcp-manager && cat .env | grep DB_"
```

---

## 📈 Améliorations Futures

### Notifications Slack

Ajouter à la fin du job `deploy` :

```yaml
- name: Notify Slack
  if: always()
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    text: 'Deployment to production'
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

### Tests E2E

Ajouter avant le job `deploy` :

```yaml
e2e-tests:
  name: E2E Tests
  runs-on: ubuntu-latest
  needs: test
  steps:
    - name: Run Playwright
      run: npx playwright test
```

---

## ✅ Checklist de Configuration

- [ ] Générer paire de clés SSH
- [ ] Ajouter clé privée à GitHub Secrets (`SSH_PRIVATE_KEY`)
- [ ] Ajouter clé publique au droplet (`~/.ssh/authorized_keys`)
- [ ] Configurer secrets GitHub (`SSH_HOST`, `SSH_USER`, `SSH_PORT`)
- [ ] Configurer permissions sudo pour `deploy`
- [ ] Tester workflow manuellement
- [ ] Faire un push sur `main` pour tester auto-deploy
- [ ] Vérifier health check après déploiement

---

**🎉 Une fois configuré, chaque push sur `main` déploiera automatiquement !**