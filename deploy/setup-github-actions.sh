#!/bin/bash

# Script pour configurer le droplet pour GitHub Actions
# À exécuter sur le droplet en tant que root

set -e

DROPLET_IP="138.68.27.68"
DEPLOY_USER="deploy"

echo "======================================"
echo "Configuration GitHub Actions - Droplet"
echo "======================================"
echo ""

# Vérifier qu'on est root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Ce script doit être exécuté en tant que root"
    exit 1
fi

# Vérifier que l'utilisateur deploy existe
if ! id "$DEPLOY_USER" &>/dev/null; then
    echo "❌ L'utilisateur $DEPLOY_USER n'existe pas"
    echo "   Exécutez d'abord le script setup-droplet.sh"
    exit 1
fi

echo "✅ Utilisateur $DEPLOY_USER trouvé"
echo ""

# Configurer SSH pour deploy
echo "📝 Configuration SSH pour $DEPLOY_USER..."
sudo -u $DEPLOY_USER mkdir -p /home/$DEPLOY_USER/.ssh
sudo -u $DEPLOY_USER chmod 700 /home/$DEPLOY_USER/.ssh
sudo -u $DEPLOY_USER touch /home/$DEPLOY_USER/.ssh/authorized_keys
sudo -u $DEPLOY_USER chmod 600 /home/$DEPLOY_USER/.ssh/authorized_keys

echo ""
echo "======================================"
echo "⚠️  AJOUTER LA CLÉ PUBLIQUE"
echo "======================================"
echo ""
echo "Sur votre machine locale, exécutez :"
echo ""
echo "  cat ~/.ssh/mcp_manager_deploy.pub"
echo ""
echo "Puis collez la clé publique ci-dessous et appuyez sur Entrée :"
read -r PUBLIC_KEY

if [ -z "$PUBLIC_KEY" ]; then
    echo "❌ Aucune clé fournie"
    exit 1
fi

# Ajouter la clé publique
echo "$PUBLIC_KEY" | sudo -u $DEPLOY_USER tee -a /home/$DEPLOY_USER/.ssh/authorized_keys > /dev/null
echo "✅ Clé publique ajoutée"

# Configurer sudo pour deploy
echo ""
echo "📝 Configuration sudo pour $DEPLOY_USER..."
cat > /etc/sudoers.d/deploy << 'EOF'
# Permissions pour GitHub Actions Deploy
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php8.2-fpm
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart all
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl reread
deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl update
EOF

chmod 440 /etc/sudoers.d/deploy
echo "✅ Permissions sudo configurées"

# Tester la configuration sudo
echo ""
echo "🧪 Test des permissions sudo..."
if sudo -u $DEPLOY_USER sudo -n systemctl reload php8.2-fpm --dry-run 2>/dev/null; then
    echo "✅ Test sudo OK"
else
    echo "⚠️  Test sudo échoué (peut être normal si PHP-FPM n'est pas installé)"
fi

# Vérifier la configuration SSH
echo ""
echo "🧪 Test de la configuration SSH..."
if sudo -u $DEPLOY_USER ssh -o StrictHostKeyChecking=no -o BatchMode=yes localhost echo "test" 2>/dev/null; then
    echo "✅ Configuration SSH OK"
else
    echo "ℹ️  Configuration SSH en place (test local non concluant)"
fi

echo ""
echo "======================================"
echo "✅ CONFIGURATION TERMINÉE"
echo "======================================"
echo ""
echo "Prochaines étapes :"
echo ""
echo "1. Sur GitHub, aller dans Settings → Secrets and variables → Actions"
echo ""
echo "2. Créer ces secrets :"
echo "   - SSH_PRIVATE_KEY  → Contenu de ~/.ssh/mcp_manager_deploy (clé privée)"
echo "   - SSH_HOST         → $DROPLET_IP"
echo "   - SSH_USER         → $DEPLOY_USER"
echo "   - SSH_PORT         → 22"
echo ""
echo "3. Tester la connexion depuis votre machine :"
echo "   ssh -i ~/.ssh/mcp_manager_deploy $DEPLOY_USER@$DROPLET_IP"
echo ""
echo "4. Faire un push sur main pour tester le déploiement :"
echo "   git push origin main"
echo ""