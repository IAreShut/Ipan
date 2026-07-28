# ==============================================================================
# LIMS DOCKERFILE & PRODUCTION CHEATSHEET
# ==============================================================================
# 
# --- 1. CONNECT TO AZURE SERVER VIA SSH ---
# ssh azureuser@40.80.80.249
#
# --- 2. DOCKER COMMANDS (Run inside /var/www/Ipan) ---
# Start Containers:       sudo docker compose up -d --build
# Stop Containers:        sudo docker compose down
# Check Status:           sudo docker compose ps
# View Logs:              sudo docker compose logs -f
#
# --- 3. LARAVEL COMMANDS INSIDE DOCKER ---
# Clear Cache:            sudo docker exec -it lims-app php artisan config:clear
# Fix Permissions:        sudo docker exec -it lims-app chmod -R 777 storage bootstrap/cache
# Storage Link:           sudo docker exec -it lims-app php artisan storage:link
# Run Migrations:         sudo docker exec -it lims-app php artisan migrate --force
# Enter App Container:    sudo docker exec -it lims-app bash
#
# --- 4. DATABASE DUMP & IMPORT ---
# Import Database Dump:   sudo docker exec -i lims-db mysql -u root -plims-system lims_db < /tmp/lims_db.sql
# Export Database Dump:   sudo docker exec lims-db mysqldump -u root -plims-system lims_db > /tmp/backup.sql
#
# --- 5. HOW TO UPDATE AZURE SERVER AFTER NEW GIT COMMIT ---
# Step 1: ssh azureuser@40.80.80.249
# Step 2: cd /var/www/Ipan && git pull origin main
# Step 3: sudo docker compose restart app
# ==============================================================================

FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
