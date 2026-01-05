#!/bin/bash
# Director Office Management System - Deployment Script
# For Rocky Linux / AlmaLinux / RHEL 9.x

set -e

APP_DIR="/var/www/director-office"
DB_NAME="director_office"
DB_USER="director_app"

echo "================================================"
echo "Director Office Management System Deployment"
echo "================================================"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root or with sudo"
    exit 1
fi

echo "[1/8] Installing system dependencies..."
dnf install -y php php-fpm php-pgsql php-mbstring php-xml php-zip php-gd php-curl php-bcmath php-intl
dnf install -y nginx postgresql-server redis nodejs npm

echo "[2/8] Initializing PostgreSQL..."
postgresql-setup --initdb 2>/dev/null || true
systemctl enable postgresql
systemctl start postgresql

echo "[3/8] Creating database and user..."
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD 'your_secure_password';" 2>/dev/null || true
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;" 2>/dev/null || true

echo "[4/8] Copying application files..."
mkdir -p $APP_DIR
cp -r . $APP_DIR/
chown -R nginx:nginx $APP_DIR

echo "[5/8] Installing application dependencies..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader
npm install
npm run build

echo "[6/8] Configuring application..."
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[7/8] Configuring Nginx..."
cat > /etc/nginx/conf.d/director-office.conf << 'EOF'
server {
    listen 80;
    server_name director.serc.res.in;
    root /var/www/director-office/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

echo "[8/8] Starting services..."
systemctl enable nginx php-fpm redis
systemctl restart nginx php-fpm redis

# Set permissions
chown -R nginx:nginx $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

echo ""
echo "================================================"
echo "Deployment Complete!"
echo "================================================"
echo ""
echo "Default Credentials:"
echo "  Director: director@serc.res.in / password"
echo "  PA: pa@serc.res.in / password"
echo "  Admin: admin@serc.res.in / password"
echo ""
echo "Next Steps:"
echo "1. Update /var/www/director-office/.env with your configuration"
echo "2. Configure SSL certificate"
echo "3. Update database password"
echo ""
