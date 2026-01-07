#!/bin/bash
# Unified Director Office Management System - Deployment Script
# Target Server: 10.10.200.22
# Deploy Path: /var/www/html/udoms/

set -e

APP_DIR="/var/www/html/udoms"
DB_NAME="udoms"
DB_USER="udoms_app"
DB_PASSWORD="UDOMS_SecureP@ss2026"

echo "================================================"
echo "Unified Director Office Management System"
echo "Deployment Script"
echo "================================================"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root or with sudo"
    exit 1
fi

echo "[1/9] Installing system dependencies..."
if command -v dnf &> /dev/null; then
    dnf install -y php php-fpm php-pgsql php-mbstring php-xml php-zip php-gd php-curl php-bcmath php-intl php-tokenizer php-json
    dnf install -y nginx postgresql-server postgresql-contrib redis nodejs npm git
elif command -v apt &> /dev/null; then
    apt update
    apt install -y php php-fpm php-pgsql php-mbstring php-xml php-zip php-gd php-curl php-bcmath php-intl
    apt install -y nginx postgresql redis-server nodejs npm git
fi

echo "[2/9] Initialize PostgreSQL if needed..."
if command -v postgresql-setup &> /dev/null; then
    postgresql-setup --initdb 2>/dev/null || true
fi
systemctl enable postgresql
systemctl start postgresql

echo "[3/9] Creating database and user..."
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';" 2>/dev/null || echo "User may already exist"
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;" 2>/dev/null || echo "Database may already exist"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"

# Update pg_hba.conf for md5 authentication if needed
PG_HBA=$(sudo -u postgres psql -t -c "SHOW hba_file" | tr -d ' ')
if ! grep -q "md5" "$PG_HBA" 2>/dev/null; then
    echo "host    all             all             127.0.0.1/32            md5" >> "$PG_HBA"
    echo "host    all             all             ::1/128                 md5" >> "$PG_HBA"
    systemctl restart postgresql
fi

echo "[4/9] Creating application directory..."
mkdir -p $APP_DIR
rm -rf $APP_DIR/*

echo "[5/9] Copying application files..."
cp -r . $APP_DIR/
cd $APP_DIR

echo "[6/9] Installing dependencies..."
# Install Composer if not present
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

echo "[7/9] Configuring application..."
cp .env.example .env

# Update .env file with actual values
sed -i "s/APP_ENV=local/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
sed -i "s/APP_URL=http:\/\/localhost:8000/APP_URL=http:\/\/10.10.200.22/" .env
sed -i "s/DB_DATABASE=udoms/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=udoms_app/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=your_secure_password/DB_PASSWORD=$DB_PASSWORD/" .env

php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "[8/9] Configuring Nginx..."
cat > /etc/nginx/conf.d/udoms.conf << 'EOF'
server {
    listen 80;
    server_name 10.10.200.22 localhost;
    root /var/www/html/udoms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;
    gzip_disable "MSIE [1-6]\.";

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Remove default nginx config if exists
rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

echo "[9/9] Setting permissions and starting services..."
# Determine web server user
if id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "www-data" &>/dev/null; then
    WEB_USER="www-data"
else
    WEB_USER="apache"
fi

chown -R $WEB_USER:$WEB_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Update php-fpm socket path for Ubuntu/Debian if needed
if [ -f /etc/php/*/fpm/pool.d/www.conf ]; then
    PHP_FPM_SOCK=$(find /run/php -name "*.sock" 2>/dev/null | head -1)
    if [ -n "$PHP_FPM_SOCK" ]; then
        sed -i "s|unix:/run/php-fpm/www.sock|unix:$PHP_FPM_SOCK|g" /etc/nginx/conf.d/udoms.conf
    fi
fi

# Start services
systemctl enable nginx
systemctl enable php-fpm 2>/dev/null || systemctl enable php*-fpm 2>/dev/null || true
systemctl enable redis 2>/dev/null || systemctl enable redis-server 2>/dev/null || true

systemctl restart php-fpm 2>/dev/null || systemctl restart php*-fpm 2>/dev/null || true
systemctl restart redis 2>/dev/null || systemctl restart redis-server 2>/dev/null || true
systemctl restart nginx

# Test nginx config
nginx -t

echo ""
echo "================================================"
echo "Deployment Complete!"
echo "================================================"
echo ""
echo "Application URL: http://10.10.200.22/"
echo ""
echo "Default Credentials (CSIR-SERC Lab):"
echo "  Director: director@serc.res.in / password"
echo "  PA: pa@serc.res.in / password"
echo "  Admin: admin@serc.res.in / password"
echo ""
echo "IMPORTANT: Please change the default passwords!"
echo ""
echo "Next Steps:"
echo "1. Update $APP_DIR/.env with your mail configuration"
echo "2. Configure SSL certificate (recommended)"
echo "3. Change database password in production"
echo "4. Set up backup schedule"
echo ""
