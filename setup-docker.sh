#!/bin/bash

# Games Hub Docker Setup Script
echo "🎮 Setting up Games Hub Docker Environment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop and try again."
    exit 1
fi

# Check if .env file exists, if not copy from .env.docker
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker template..."
    cp .env.docker .env
    echo "⚠️  Please update your APP_KEY and other sensitive values in .env"
else
    echo "✅ .env file already exists"
fi

# Build and start the containers
echo "🏗️  Building Docker containers..."
docker-compose down --volumes 2>/dev/null
docker-compose build --no-cache

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install dependencies and set up application
echo "📦 Installing dependencies..."
docker-compose exec app composer install
docker-compose exec app npm install

# Generate application key if needed
echo "🔐 Generating application key..."
docker-compose exec app php artisan key:generate

# Run database migrations
echo "🗄️  Running database migrations..."
docker-compose exec app php artisan migrate --force

# Create storage link
echo "🔗 Creating storage link..."
docker-compose exec app php artisan storage:link

# Build frontend assets
echo "🎨 Building frontend assets..."
docker-compose exec app npm run build

# Set permissions
echo "🔧 Setting permissions..."
docker-compose exec app chown -R sail:sail /var/www/storage
docker-compose exec app chown -R sail:sail /var/www/bootstrap/cache

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Your Games Hub is now running at:"
echo "🌐 Web: http://localhost"
echo "📡 WebSocket (Reverb): ws://localhost:8080"
echo "🗄️  Database: localhost:3306"
echo "🔴 Redis: localhost:6379"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f                 # View all logs"
echo "  docker-compose logs -f reverb          # View WebSocket logs"
echo "  docker-compose exec app php artisan    # Run artisan commands"
echo "  docker-compose down                    # Stop containers"
echo "  docker-compose up -d                   # Start containers"
echo ""
