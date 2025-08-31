#!/bin/bash

# MCP Manager Deployment Script for DigitalOcean
# Usage: ./deploy.sh [staging|production]

set -e

# Configuration
ENVIRONMENT=${1:-staging}
PROJECT_DIR="/var/www/mcp-manager"
BACKUP_DIR="/var/backups/mcp-manager"
DEPLOY_USER="deploy"
GITHUB_REPO="git@github.com:your-username/mcp-manager.git"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Check if running as deploy user
if [ "$USER" != "$DEPLOY_USER" ]; then
    log_error "This script must be run as the $DEPLOY_USER user"
fi

# Determine branch based on environment
if [ "$ENVIRONMENT" == "production" ]; then
    BRANCH="main"
    ENV_FILE=".env.production"
else
    BRANCH="staging"
    ENV_FILE=".env.staging"
fi

log_info "Starting deployment for $ENVIRONMENT environment (branch: $BRANCH)"

# Create backup
log_info "Creating backup..."
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR/$TIMESTAMP

# Backup database
log_info "Backing up database..."
cd $PROJECT_DIR
php artisan backup:run --only-db --filename=db_backup_$TIMESTAMP.sql || log_warning "Database backup failed"

# Backup current code
log_info "Backing up current code..."
tar -czf $BACKUP_DIR/$TIMESTAMP/code_backup.tar.gz --exclude=node_modules --exclude=vendor --exclude=storage $PROJECT_DIR || log_warning "Code backup failed"

# Enable maintenance mode
log_info "Enabling maintenance mode..."
php artisan down --message="System upgrade in progress. We'll be back shortly!" --retry=60

# Pull latest code
log_info "Pulling latest code from $BRANCH branch..."
cd $PROJECT_DIR
git fetch origin
git reset --hard origin/$BRANCH

# Check if environment file exists
if [ ! -f "$PROJECT_DIR/$ENV_FILE" ]; then
    log_error "Environment file $ENV_FILE not found!"
fi

# Copy environment file
log_info "Updating environment configuration..."
cp $ENV_FILE .env

# Install/update Composer dependencies
log_info "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install/update NPM dependencies and build assets
log_info "Installing NPM dependencies..."
npm ci --production

log_info "Building frontend assets..."
npm run build

# Run database migrations
log_info "Running database migrations..."
php artisan migrate --force

# Clear and optimize caches
log_info "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Clear old cache
php artisan cache:clear

# Set proper permissions
log_info "Setting file permissions..."
chown -R $DEPLOY_USER:www-data $PROJECT_DIR
find $PROJECT_DIR -type f -exec chmod 644 {} \;
find $PROJECT_DIR -type d -exec chmod 755 {} \;
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Restart services
log_info "Restarting services..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart mcp-manager-worker:*
sudo supervisorctl restart mcp-manager-scheduler
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm

# Health check
log_info "Running health check..."
sleep 5
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://your-domain.com/health)

if [ "$HTTP_CODE" -eq 200 ]; then
    log_info "Health check passed!"
else
    log_warning "Health check returned HTTP $HTTP_CODE"
fi

# Disable maintenance mode
log_info "Disabling maintenance mode..."
php artisan up

# Send deployment notification
if [ "$ENVIRONMENT" == "production" ]; then
    curl -X POST https://hooks.slack.com/services/YOUR/WEBHOOK/URL \
        -H 'Content-Type: application/json' \
        -d "{\"text\":\"✅ Production deployment completed successfully!\"}" 2>/dev/null || true
fi

# Clean old backups (keep last 7 days)
log_info "Cleaning old backups..."
find $BACKUP_DIR -type d -mtime +7 -exec rm -rf {} + 2>/dev/null || true

log_info "Deployment completed successfully!"

# Display application info
php artisan about

echo ""
echo "========================================="
echo "Deployment Summary:"
echo "Environment: $ENVIRONMENT"
echo "Branch: $BRANCH"
echo "Timestamp: $TIMESTAMP"
echo "Backup location: $BACKUP_DIR/$TIMESTAMP"
echo "========================================="