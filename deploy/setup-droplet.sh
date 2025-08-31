#!/bin/bash

# Initial DigitalOcean Droplet Setup Script for MCP Manager
# Run this script as root on a fresh Ubuntu 22.04 droplet

set -e

# Configuration
DEPLOY_USER="deploy"
PROJECT_NAME="mcp-manager"
PROJECT_DIR="/var/www/$PROJECT_NAME"
DOMAIN="your-domain.com"
GITHUB_REPO="git@github.com:your-username/mcp-manager.git"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Update system
log_info "Updating system packages..."
apt-get update
apt-get upgrade -y

# Install essential packages
log_info "Installing essential packages..."
apt-get install -y \
    curl \
    git \
    unzip \
    software-properties-common \
    ufw \
    fail2ban \
    htop \
    redis-tools \
    postgresql-client

# Configure firewall
log_info "Configuring firewall..."
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# Create deploy user
log_info "Creating deploy user..."
if ! id "$DEPLOY_USER" &>/dev/null; then
    adduser --disabled-password --gecos "" $DEPLOY_USER
    usermod -aG sudo $DEPLOY_USER
    usermod -aG www-data $DEPLOY_USER
fi

# Install PHP 8.2
log_info "Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-xml \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-redis \
    php8.2-intl \
    php8.2-soap

# Configure PHP
log_info "Configuring PHP..."
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Install Composer
log_info "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Node.js 20 LTS
log_info "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Install Nginx
log_info "Installing Nginx..."
apt-get install -y nginx

# Install Supervisor
log_info "Installing Supervisor..."
apt-get install -y supervisor

# Install Certbot for SSL
log_info "Installing Certbot..."
apt-get install -y certbot python3-certbot-nginx

# Create project directory
log_info "Creating project directory..."
mkdir -p $PROJECT_DIR
chown -R $DEPLOY_USER:www-data $PROJECT_DIR

# Setup Git SSH key for deploy user
log_info "Setting up SSH key for deploy user..."
sudo -u $DEPLOY_USER ssh-keygen -t ed25519 -f /home/$DEPLOY_USER/.ssh/id_ed25519 -N ""

echo ""
echo "========================================="
echo "Add this SSH key to your GitHub repository as a deploy key:"
echo ""
cat /home/$DEPLOY_USER/.ssh/id_ed25519.pub
echo ""
echo "========================================="
echo "Press Enter to continue after adding the key..."
read

# Clone repository
log_info "Cloning repository..."
sudo -u $DEPLOY_USER git clone $GITHUB_REPO $PROJECT_DIR

# Setup Nginx configuration
log_info "Configuring Nginx..."
cp $PROJECT_DIR/deploy/nginx.conf /etc/nginx/sites-available/$PROJECT_NAME
sed -i "s/your-domain.com/$DOMAIN/g" /etc/nginx/sites-available/$PROJECT_NAME
ln -sf /etc/nginx/sites-available/$PROJECT_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx

# Setup Supervisor
log_info "Configuring Supervisor..."
cp $PROJECT_DIR/deploy/supervisor.conf /etc/supervisor/conf.d/$PROJECT_NAME.conf
supervisorctl reread
supervisorctl update

# Setup SSL with Let's Encrypt
log_info "Setting up SSL certificate..."
certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos -m admin@$DOMAIN

# Create environment file
log_info "Creating environment file..."
cd $PROJECT_DIR
sudo -u $DEPLOY_USER cp .env.production.example .env
sudo -u $DEPLOY_USER php artisan key:generate

# Install dependencies
log_info "Installing application dependencies..."
sudo -u $DEPLOY_USER composer install --no-dev --optimize-autoloader
sudo -u $DEPLOY_USER npm ci --production

# Build assets
log_info "Building frontend assets..."
sudo -u $DEPLOY_USER npm run build

# Set permissions
log_info "Setting file permissions..."
chown -R $DEPLOY_USER:www-data $PROJECT_DIR
find $PROJECT_DIR -type f -exec chmod 644 {} \;
find $PROJECT_DIR -type d -exec chmod 755 {} \;
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Create backup directory
log_info "Creating backup directory..."
mkdir -p /var/backups/$PROJECT_NAME
chown $DEPLOY_USER:$DEPLOY_USER /var/backups/$PROJECT_NAME

# Setup cron for Laravel scheduler
log_info "Setting up cron for Laravel scheduler..."
(crontab -u $DEPLOY_USER -l 2>/dev/null; echo "* * * * * cd $PROJECT_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -u $DEPLOY_USER -

# Setup log rotation
log_info "Configuring log rotation..."
cat > /etc/logrotate.d/$PROJECT_NAME << EOF
$PROJECT_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 $DEPLOY_USER www-data
    sharedscripts
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
EOF

# Configure fail2ban for Nginx
log_info "Configuring fail2ban..."
cat > /etc/fail2ban/jail.local << EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
EOF

systemctl restart fail2ban

# System optimization
log_info "Optimizing system..."
echo "fs.file-max = 65535" >> /etc/sysctl.conf
echo "net.core.somaxconn = 65535" >> /etc/sysctl.conf
echo "net.ipv4.tcp_max_syn_backlog = 65535" >> /etc/sysctl.conf
sysctl -p

# Create health check endpoint script
log_info "Creating health check script..."
cat > /usr/local/bin/health-check << 'EOF'
#!/bin/bash
curl -f http://localhost/health || exit 1
EOF
chmod +x /usr/local/bin/health-check

# Final instructions
echo ""
echo "========================================="
echo "SETUP COMPLETED!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Edit /var/www/$PROJECT_NAME/.env with your configuration:"
echo "   - Database credentials (DigitalOcean Managed Database)"
echo "   - Redis credentials (DigitalOcean Managed Redis)"
echo "   - MCP Server credentials"
echo "   - Mail settings"
echo "   - Other service credentials"
echo ""
echo "2. Run database migrations:"
echo "   sudo -u $DEPLOY_USER php $PROJECT_DIR/artisan migrate --force"
echo ""
echo "3. Clear and cache configuration:"
echo "   sudo -u $DEPLOY_USER php $PROJECT_DIR/artisan config:cache"
echo "   sudo -u $DEPLOY_USER php $PROJECT_DIR/artisan route:cache"
echo "   sudo -u $DEPLOY_USER php $PROJECT_DIR/artisan view:cache"
echo ""
echo "4. Restart services:"
echo "   systemctl restart php8.2-fpm"
echo "   systemctl restart nginx"
echo "   supervisorctl restart all"
echo ""
echo "5. Test your application:"
echo "   https://$DOMAIN"
echo ""
echo "========================================="
echo "Server Information:"
echo "Deploy User: $DEPLOY_USER"
echo "Project Directory: $PROJECT_DIR"
echo "PHP Version: $(php -v | head -n 1)"
echo "Node Version: $(node -v)"
echo "Nginx Version: $(nginx -v 2>&1)"
echo "========================================="