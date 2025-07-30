#!/bin/bash

# Laravel Deployment Script for Docker
# This script runs essential Laravel commands during deployment

set -e

echo "🚀 Starting Laravel deployment..."

# Wait for database connection
echo "⏳ Waiting for database connection..."
until php artisan migrate:status > /dev/null 2>&1; do
    echo "Waiting for database..."
    sleep 2
done
echo "✅ Database is ready"

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Seed essential data (roles, permissions, default content)
echo "🌱 Seeding essential database data..."
php artisan db:seed --class=RolePermissionSeeder --force

# Clear and optimize caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache configuration for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Deployment completed successfully!"
