# 🎮 Games Hub - Laravel Gaming Platform

A modern, full-featured gaming platform built with Laravel 12, Vue.js 3, and Inertia.js. Features user authentication, role-based access control, leaderboards, and an admin dashboard with a beautiful purple gradient theme.

## ✨ Features

### 🎮 Gaming Features

- **Interactive Games Hub** with beautiful UI
- **Leaderboard System** with multiple timeframes (30/60/90 days, yearly, all-time)
- **Best Score Tracking** per player to ensure fair competition
- **Game Categories** and organization
- **Play Count Tracking** and statistics
- **Responsive Gaming Interface** optimized for all devices

### 🔐 Authentication & Security

- **User Registration/Login** with email verification
- **Social Authentication** (GitHub, Google, Facebook)
- **Role-Based Access Control** using Spatie Permissions
- **Password Reset** functionality
- **Email Verification** system
- **Secure Session Management**

### 👨‍💼 Admin Dashboard

- **Comprehensive Admin Panel** with role restrictions
- **User Management** with role assignments
- **Game Management** (CRUD operations)
- **Admin Settings** and configuration
- **Dashboard Statistics** and analytics
- **Admin-only Features** with permission-based access

### 🎨 Modern UI/UX

- **Purple Gradient Theme** for non-admin views
- **Glass Morphism Effects** with backdrop blur
- **Responsive Design** that works on all devices
- **Dark Theme** with beautiful gradients
- **Interactive Animations** and hover effects
- **Breadcrumb Navigation** for better UX

## 🛠️ Tech Stack

### Backend

- **Laravel 12** - Modern PHP framework
- **PHP 8.2+** - Latest PHP features
- **MySQL/SQLite** - Database support
- **Spatie Laravel Permission** - Role & permission management
- **Laravel Socialite** - Social authentication
- **Inertia.js** - Server-side rendering without APIs

### Frontend

- **Vue.js 3** - Progressive JavaScript framework
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS** - Utility-first CSS framework
- **Inertia.js** - Seamless SPA experience
- **Vite** - Fast build tool and dev server
- **ESLint & Prettier** - Code quality and formatting

### Development Tools

- **Composer** - PHP dependency management
- **NPM** - Node.js package management
- **PHPUnit** - PHP testing framework
- **Laravel Tinker** - Interactive REPL
- **Git** - Version control

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL or SQLite database

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/jekinney/games.git
   cd games
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install Node.js dependencies**

   ```bash
   npm install
   ```

4. **Environment setup**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**

   Edit `.env` file with your database credentials:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=games
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**

   ```bash
   php artisan migrate --seed
   ```

7. **Build frontend assets**

   ```bash
   npm run build
   ```

8. **Start the development server**

   ```bash
   php artisan serve
   ```

9. **Start the frontend dev server** (optional, for development)

   ```bash
   npm run dev
   ```

Visit `http://localhost:8000` to access the application.

## 🎯 Usage

### For Players

1. **Register/Login** to your account
2. **Browse Games** in the Games Hub
3. **Play Games** and compete for high scores
4. **View Leaderboards** to see rankings
5. **Track Your Progress** across different games

### For Admins

1. **Access Admin Dashboard** (requires admin role)
2. **Manage Users** and assign roles
3. **Create/Edit Games** in the system
4. **Configure Settings** and permissions
5. **Monitor Statistics** and user activity

## 📱 Screenshots & Features

### 🎮 Games Hub

- Beautiful purple gradient background
- Interactive game cards with hover effects
- Easy navigation and game selection

### 🏆 Leaderboards

- Multiple timeframe options (30/60/90 days, yearly, all-time)
- Best score per player tracking
- Beautiful table design with rankings
- User-specific highlighting

### 👨‍💼 Admin Panel

- Comprehensive statistics display
- User and game management interfaces
- Role-based access control
- Modern admin UI design

## 🧪 Testing

Run the comprehensive test suite:

```bash
# Run all tests
php artisan test

# Run with coverage (if configured)
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

### Test Coverage

- **55 tests** covering all major functionality
- **259 assertions** ensuring code quality
- **Authentication tests** for all auth flows
- **Admin dashboard tests** for access control
- **Feature tests** for core functionality
- **Role/Permission tests** for security

## 🔧 Development

### Code Quality

```bash
# Run ESLint
npm run lint

# Format code with Prettier
npm run format

# Check formatting
npm run format:check
```

### Build Process

```bash
# Development build
npm run dev

# Production build
npm run build

# Build with SSR support
npm run build:ssr
```

## 📁 Project Structure

```text
├── app/
│   ├── Http/Controllers/     # Laravel controllers
│   ├── Models/              # Eloquent models (User, Game, GameScore)
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── js/
│   │   ├── components/     # Vue.js components
│   │   ├── pages/         # Inertia.js pages
│   │   ├── layouts/       # Layout components
│   │   └── types/         # TypeScript definitions
│   └── css/               # Stylesheets
├── routes/
│   ├── web.php            # Web routes
│   └── auth.php           # Authentication routes
├── tests/
│   ├── Feature/           # Feature tests
│   └── Unit/              # Unit tests
└── public/                # Public assets
```

## 🔒 Security Features

- **CSRF Protection** on all forms
- **XSS Protection** with input sanitization
- **SQL Injection Prevention** using Eloquent ORM
- **Role-Based Access Control** with Spatie Permissions
- **Session Security** with secure cookies
- **Password Hashing** using Laravel's bcrypt
- **Email Verification** for new accounts

## 🌐 API Endpoints

### Authentication

- `POST /register` - User registration
- `POST /login` - User login
- `POST /logout` - User logout
- `POST /password/email` - Password reset request
- `POST /password/reset` - Password reset

### Games

- `GET /games` - List all games
- `GET /games/{slug}` - Get specific game
- `POST /games` - Create game (admin only)
- `PUT /games/{id}` - Update game (admin only)
- `DELETE /games/{id}` - Delete game (admin only)

### Leaderboards

- `GET /leaderboards` - All leaderboards overview
- `GET /leaderboards/{game}` - Game-specific leaderboard
- `POST /leaderboards/{game}/submit` - Submit score

### Admin

- `GET /admin` - Admin dashboard
- `GET /admin/users` - User management
- `GET /admin/settings` - Admin settings

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use TypeScript for all JavaScript code
- Write tests for new features
- Update documentation as needed
- Follow the existing code style

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel** for the amazing PHP framework
- **Vue.js** for the reactive frontend framework
- **Inertia.js** for seamless SPA experience
- **Tailwind CSS** for beautiful styling
- **Spatie** for the permission system
- **All contributors** who help improve this project

## 📞 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/jekinney/games/issues) page
2. Create a new issue with detailed information
3. Provide steps to reproduce any bugs
4. Include your environment details

---

Built with ❤️ using Laravel, Vue.js, and modern web technologies
