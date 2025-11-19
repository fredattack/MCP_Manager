#!/bin/bash

# Script d'installation de Docker et Docker Compose sur Ubuntu
# À exécuter une seule fois sur le serveur de production

set -e

echo "🐳 Installation de Docker et Docker Compose..."

# Mise à jour des paquets
sudo apt-get update

# Installation des dépendances
sudo apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Ajout de la clé GPG officielle de Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Configuration du repository Docker
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Installation de Docker Engine
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Ajout de l'utilisateur actuel au groupe docker
sudo usermod -aG docker $USER

# Démarrage et activation de Docker
sudo systemctl enable docker
sudo systemctl start docker

echo "✅ Docker installé avec succès!"
echo "🔄 Veuillez vous déconnecter et vous reconnecter pour que les changements de groupe prennent effet."
echo ""
echo "Vérification de l'installation:"
docker --version
docker compose version
