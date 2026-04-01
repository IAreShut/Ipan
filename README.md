# LIMS - Logbook Internship Management System
Sistem pengurusan logbook latihan industri berasaskan Laravel.

## Requirements

### Backend (Server-Side)

| Technology | Version | Role |
|------------|---------|------|
| **PHP** | 8.2+ | Main server-side programming language |
| **Laravel** | 12.x | Core PHP framework — routing, MVC, Eloquent ORM, authentication, middleware, migrations |
| **Composer** | Latest | Dependency manager for PHP packages |
| **Laravel Tinker** | 2.10+ | Interactive REPL for debugging and testing Eloquent queries |
| **Laravel Pail** | 1.2+ | Real-time log viewer for development |
| **Laravel Pint** | 1.24+ | Code style fixer (PHP CS Fixer) for formatting PHP code |
| **Laravel Sail** | 1.41+ | Docker-based development environment (optional) |

### Database

| Technology | Version | Role |
|------------|---------|------|
| **MySQL** | 5.7+ / 8.0 | Primary database for storing all system data |
| **XAMPP** | Latest | Local development server bundle (Apache + MySQL + PHP) |
| **Eloquent ORM** | (built-in Laravel) | Object-Relational Mapping — query the database using PHP classes & models |

### Frontend (Client-Side)

| Technology | Version | Role |
|------------|---------|------|
| **Blade** | (built-in Laravel) | Template engine — renders dynamic HTML from the server |
| **Bootstrap** | 5.3.0 | Main CSS framework — grid system, UI components, responsive layout |
| **Tailwind CSS** | 4.0 | Utility-first CSS framework (used via Vite build pipeline) |
| **Font Awesome** | 6.4.0 | Icon library — all icons in sidebars, buttons, and forms |
| **jQuery** | 3.7.0 | JavaScript library — DOM manipulation, event handling, AJAX calls |
| **DataTables** | 1.13.7 | jQuery plugin — sorting, pagination, and search for HTML tables |
| **SweetAlert2** | 11.x | Popup/dialog library — beautiful alerts for success, error, and confirmation |
| **Animate.css** | 4.x | CSS animation library — transitions and animations for UI elements |

### Build Tools & Dev Dependencies

| Technology | Version | Role |
|------------|---------|------|
| **Node.js** | 18+ | JavaScript runtime — required for frontend build tools |
| **NPM** | (with Node.js) | Package manager for frontend dependencies |
| **Vite** | 7.x | Build tool & dev server — fast HMR, CSS/JS bundling |
| **Laravel Vite Plugin** | 2.0 | Vite integration with Laravel — auto-refreshes Blade views on changes |
| **Axios** | 1.11+ | HTTP client — handles AJAX requests from the frontend |
| **Concurrently** | 9.x | Runs multiple commands simultaneously (artisan serve + vite dev) |

### Testing (Development)

| Technology | Version | Role |
|------------|---------|------|
| **PHPUnit** | 11.5+ | Unit & feature testing framework for PHP |
| **Mockery** | 1.6+ | Mocking library for unit tests |
| **Faker** | 1.23+ | Generates fake data for database seeding & testing |
| **Collision** | 8.6+ | Beautiful error reporting for CLI and testing output |

### External Services (CDN)

| Technology | Role |
|------------|------|
| **jsDelivr CDN** | Hosts Bootstrap, SweetAlert2, Animate.css, and DataTables assets |
| **cdnjs CDN** | Hosts Font Awesome icon assets |
| **jQuery CDN** | Hosts jQuery library |
| **UI Avatars API** | Generates avatar placeholders based on user names |

## Installation
### 1. Clone / Download Project
```bash
cd C:\Users\User\FYP System\Ipan
```
### 2. Install Dependencies
```bash
composer install
```
### 3. Setup Environment
Copy file `.env.example` ke `.env` (jika belum ada):
```bash
cp .env.example .env
```
### 4. Generate Application Key
```bash
php artisan key:generate
```
### 5. Setup Database
1. Buka **XAMPP Control Panel**
2. Start **MySQL**
3. Create database `lims_db` (atau guna phpMyAdmin)

Pastikan `.env` ada config berikut:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lims_db
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Run Migrations
```bash
php artisan migrate
```

## Running the Application

### Development Server (Recommended)
```bash
php artisan serve
```
Buka browser: **http://127.0.0.1:8000**

### Using XAMPP (Alternative)
1. Copy folder project ke `C:\xampp\htdocs\Ipan`
2. Start Apache & MySQL
3. Buka browser: **http://localhost/Ipan/public**

## Useful Commands

| Command | Description |
|---------|-------------|
| `php artisan serve` | Start development server |
| `php artisan migrate` | Run database migrations |
| `php artisan migrate:fresh` | Reset & re-run all migrations |
| `php artisan make:controller Name` | Create new controller |
| `php artisan make:model Name -m` | Create model with migration |
| `php artisan route:list` | List all routes |
| `php artisan cache:clear` | Clear application cache |

## Project Structure

```
Ipan/
├── app/                    # Application logic
│   ├── Http/Controllers/   # Controllers
│   └── Models/             # Eloquent models
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── public/                 # Public assets (entry point)
├── resources/
│   └── views/              # Blade templates
├── routes/
│   └── web.php             # Web routes
├── storage/                # Logs, cache, uploads
├── legacy_src/             # Original HTML files (reference)
└── .env                    # Environment configuration
```

## Database Tables

| Table | Description |
|-------|-------------|
| `users` | User accounts (Student/Supervisor/Admin) |
| `internships` | Internship period details |
| `log_entries` | Daily logbook entries |
| `log_attachments` | File attachments for logs |
| `notifications` | System notifications |

## Troubleshooting

### MySQL Connection Error / Shutdown Unexpectedly
- Pastikan MySQL running dalam XAMPP
- Check `.env` untuk database credentials

**Fix untuk error "MySQL shutdown unexpectedly" (XAMPP):**
Jika MySQL tiba-tiba crash dan tak boleh start:
1. Pergi ke folder `C:\xampp\mysql\`.
2. Rename folder `data` kepada `data_old`.
3. Buat salinan (copy) folder `backup` di tempat yang sama, dan rename salinan tersebut kepada `data`.
4. Buka folder `data_old`, **copy** folder database project anda sahaja (contoh: `lims_db`). *PENTING: Jangan copy folder `mysql`, `performance_schema`, atau `phpmyadmin`!*
5. **Paste** folder tersebut ke dalam folder `data` baru.
6. Dari folder `data_old`, copy file `ibdata1` dan replace file tersebut di dalam folder `data` baru.
7. Start semula MySQL di XAMPP Control Panel.

### 500 Internal Server Error
```bash
php artisan cache:clear
php artisan config:clear
```

### Permission Error (Storage)
```bash
chmod -R 775 storage bootstrap/cache
```

## Heroku Deployment

App URL: **https://lims-fyp-8a6cb0f71eca.herokuapp.com/**

### Setup (Sudah dilakukan)
- **Dyno:** Eco ($5/bulan dari GitHub Student credits)
- **Database:** JawsDB MySQL (kitefin)
- **Buildpacks:** Node.js + PHP

### Deploy / Update ke Heroku
Setiap kali buat changes dan nak update Heroku:
```bash
git add .
git commit -m "description of changes"
git push heroku main
```

### Heroku Useful Commands

| Command | Description |
|---------|-------------|
| `heroku logs --tail` | View live logs |
| `heroku run "php artisan migrate --force"` | Run migrations on Heroku |
| `heroku run "php artisan db:seed --force"` | Run seeders on Heroku |
| `heroku config` | View all environment variables |
| `heroku config:set KEY=value` | Set environment variable |
| `heroku restart` | Restart the app |
| `heroku open` | Open app in browser |

### Heroku Troubleshooting

**500 Error on Heroku:**
```bash
heroku logs --tail
```

**Database issues:**
```bash
heroku config:get JAWSDB_URL
heroku run "php artisan migrate:status"
```

**Clear cache on Heroku:**
```bash
heroku run "php artisan config:clear && php artisan cache:clear && php artisan view:clear"
```

---
© 2026 LIMS - Faculty of Computing
