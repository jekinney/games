# Games Hub Docker Environment

This directory contains Docker configuration files for running the Games Hub application in a containerized environment. This setup resolves WebSocket configuration conflicts and provides a consistent development environment.

## 🚀 Quick Start

### Prerequisites
- Docker Desktop installed and running
- Git (to clone/manage the repository)

### Setup

1. **Run the setup script:**

   **Windows:**
   ```bash
   .\setup-docker.bat
   ```

   **Linux/macOS:**
   ```bash
   chmod +x setup-docker.sh
   ./setup-docker.sh
   ```

2. **Access your application:**
   - **Web Application:** http://localhost
   - **WebSocket (Reverb):** ws://localhost:8080
   - **Database:** localhost:3306
   - **Redis:** localhost:6379

## 🐳 Services

### Core Services
- **app** - PHP 8.2 with Laravel application
- **nginx** - Web server with proxy configuration
- **database** - MySQL 8.0 database
- **redis** - Redis for caching and sessions

### Real-time Services
- **reverb** - Laravel Reverb WebSocket server
- **vite** - Frontend development server with HMR

## 🔧 Configuration

### Environment Variables
The setup uses `.env.docker` as a template. Key configurations:

```env
# Database
DB_HOST=database
DB_DATABASE=games
DB_USERNAME=games_user
DB_PASSWORD=password

# Redis
REDIS_HOST=redis

# WebSocket (Reverb)
REVERB_HOST=reverb
REVERB_PORT=8080
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
```

### WebSocket Configuration
- **Internal communication:** `reverb:8080` (between containers)
- **External access:** `localhost:8080` (from browser)
- **Nginx proxy:** Routes `/app` to Reverb container

## 📝 Common Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f reverb
docker-compose logs -f app
```

### Application Commands
```bash
# Run Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan queue:work

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Build assets
docker-compose exec app npm run dev
docker-compose exec app npm run build
```

### Database Operations
```bash
# Access database
docker-compose exec database mysql -u games_user -p games

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed
```

## 🎮 Game Development

### Adding New Games
1. Add game files to `public/games/`
2. Update the games controller/database
3. Games automatically inherit WebSocket configuration

### Real-time Features
- WebSocket connections automatically work
- No environment variable conflicts
- Consistent connection across all games

### Testing WebSocket
```javascript
// Test connection in browser console
const echo = new Echo({
    broadcaster: 'reverb',
    key: 'h1sh3zmuownfn2zuo48g',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

echo.channel('test').listen('TestEvent', (e) => {
    console.log('WebSocket working!', e);
});
```

## 🔧 Troubleshooting

### Container Issues
```bash
# Rebuild containers
docker-compose down --volumes
docker-compose build --no-cache
docker-compose up -d

# Check container status
docker-compose ps

# Access container shell
docker-compose exec app bash
```

### WebSocket Issues
```bash
# Check Reverb logs
docker-compose logs -f reverb

# Test WebSocket connectivity
docker-compose exec app php artisan reverb:ping

# Restart Reverb only
docker-compose restart reverb
```

### Database Issues
```bash
# Reset database
docker-compose exec app php artisan migrate:fresh --seed

# Check database connection
docker-compose exec app php artisan tinker
```

## 🚀 Production Deployment

For production deployment:

1. Update `.env` with production values
2. Use production-ready Dockerfile
3. Configure SSL certificates
4. Set up proper secret management
5. Use external database and Redis services

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Nginx Configuration](https://nginx.org/en/docs/)
