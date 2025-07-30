# Laravel Gaming Application - Docker Setup

## 🎮 Overview
This Laravel gaming application has been successfully containerized using Docker and Docker Compose. The setup includes all necessary services for a complete development environment.

## 📋 Services

### Core Application Services
- **app** - Laravel PHP-FPM application (PHP 8.2)
- **nginx** - Web server (Alpine)
- **database** - MySQL 8.0
- **redis** - Redis cache (Alpine)

### Development Services
- **vite** - Frontend development server (Node.js 20)
- **reverb** - Laravel WebSocket server

## 🚀 Quick Start

### Prerequisites
- Docker Desktop
- WSL2 (for Windows users)
- Git

### Starting the Application

1. **Start all services:**
   ```bash
   wsl docker-compose up -d
   ```

2. **Deploy the application (includes migrations, seeding, and optimization):**
   ```bash
   # Option 1: Using artisan command (recommended)
   wsl docker exec games_app php artisan app:deploy
   
   # Option 2: Using deployment script
   wsl docker exec games_app ./scripts/deploy.sh
   ```

3. **Access the application:**
   - Main application: http://localhost
   - Vite dev server: http://localhost:5173
   - WebSocket server: http://localhost:8080

### Stopping the Application
```bash
wsl docker-compose down
```

## 🚀 Deployment

The application includes automated deployment tools for setting up the environment:

### Deployment Commands

#### Option 1: Artisan Deploy Command (Recommended)
```bash
wsl docker exec games_app php artisan app:deploy
```

This command will:
- Run database migrations
- Seed essential data (roles, permissions, initial content)
- Clear and optimize caches
- Create storage symlinks

#### Option 2: Deployment Script
```bash
wsl docker exec games_app ./scripts/deploy.sh
```

Both methods are idempotent and safe to run multiple times.

## 🔧 Development Commands

### Laravel Commands
```bash
# Run artisan commands
wsl docker exec games_app php artisan [command]

# Install PHP dependencies
wsl docker exec games_app composer install

# Run tests
wsl docker exec games_app php artisan test
```

### Frontend Commands
```bash
# Install Node dependencies
wsl docker exec games_vite npm install

# Build assets
wsl docker exec games_vite npm run build
```

### Database Commands
```bash
# Run migrations
wsl docker exec games_app php artisan migrate

# Run seeders
wsl docker exec games_app php artisan db:seed

# Reset database
wsl docker exec games_app php artisan migrate:fresh --seed
```

## 📂 Docker Configuration Files

### Key Files
- `docker-compose.yml` - Multi-service orchestration
- `Dockerfile` - PHP-FPM container configuration
- `.dockerignore` - Build exclusions
- `nginx.conf` - Nginx web server configuration

### Volumes
- `vendor_data` - PHP dependencies shared between containers
- `node_modules_data` - Node.js dependencies shared between containers
- `games_db_data` - MySQL database persistence

## 🌐 Port Mappings

| Service | Internal Port | External Port | Description |
|---------|---------------|---------------|-------------|
| nginx | 80/443 | 80/443 | Web server |
| mysql | 3306 | 3306 | Database |
| redis | 6379 | 6379 | Cache |
| vite | 5173 | 5173 | Dev server |
| reverb | 8080 | 8080 | WebSockets |

## 🔍 Troubleshooting

### Container Issues
```bash
# Check container status
wsl docker ps

# View container logs
wsl docker logs [container_name]

# Restart a specific service
wsl docker restart [container_name]
```

### Permission Issues
If you encounter permission issues on Windows, ensure you're using WSL for Docker commands:
```bash
wsl docker-compose [command]
```

### Vite Development Server Issues
If you see `ERR_ADDRESS_INVALID` errors for Vite assets, the development server configuration should automatically use `localhost` for browser access while binding to `0.0.0.0` in the container.

If issues persist:
```bash
# Restart the Vite container
wsl docker restart games_vite

# Check Vite logs
wsl docker logs games_vite
```

### Database Connection
The application connects to the database using these credentials:
- Host: `database` (container name)
- Database: `games`
- Username: `games_user`
- Password: `password`

## 🎯 Features Included

### Laravel Features
- Laravel 12.x Framework
- Inertia.js for SPA functionality
- Laravel Socialite for authentication
- Spatie Laravel Permissions
- Laravel Reverb for WebSockets
- Laravel Dusk for browser testing

### Development Tools
- Vite for asset compilation
- Hot module replacement
- Redis caching
- MySQL database
- WebSocket support

## 🛠️ Customization

### Environment Variables
Create a `.env` file in the project root with your custom configuration:
```env
APP_NAME="Laravel Gaming App"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=games
DB_USERNAME=games_user
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Adding New Services
To add new services, update the `docker-compose.yml` file and rebuild:
```bash
wsl docker-compose build
wsl docker-compose up -d
```

## 📝 Notes

- The setup uses shared volumes for vendor and node_modules to optimize container startup times
- Laravel Dusk is installed for browser testing capabilities
- The Nginx configuration is optimized for Laravel applications
- All containers are connected via a dedicated Docker network

---

🎉 **Your Laravel Gaming Application is now fully containerized and ready for development!**
