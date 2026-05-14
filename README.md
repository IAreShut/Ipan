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

### External APIs & Integrations

| Technology | Package | Role |
|------------|---------|------|
| **Gemini API** | `google-gemini-php/laravel` | Generate AI summaries for supervisor reviews and feedback |
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Cloud storage for persistent image uploads (log attachments & avatars) on DigitalOcean |

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
| `supervisor_assignments` | Pre-assigned supervisor-student lookup table |

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

## DigitalOcean Deployment

App URL: **https://lim-system.my/**

### Setup Server
- **Server:** Ubuntu 24.04 LTS (Droplet)
- **Stack:** Nginx, MySQL 8.0, PHP 8.3-FPM
- **Domain:** `lim-system.my`
- **SSL:** Let's Encrypt (Certbot)

### Deploy / Update ke Server
Setiap kali buat changes, push ke GitHub dulu:
```bash
git add .
git commit -m "description of changes"
git push origin main
```

Kemudian, di Web Console DigitalOcean (Server), jalankan:
```bash
cd /var/www/Ipan
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:clear
php artisan view:clear
```

### Cara Pantas (Gunakan Script Deployment)
Jika anda telah menyediakan fail `deploy.sh` di dalam `/var/www/Ipan`, anda hanya perlu jalankan:
```bash
cd /var/www/Ipan
./deploy.sh
```

### Server Useful Commands

| Command | Description |
|---------|-------------|
| `tail -f storage/logs/laravel.log` | View live Laravel logs |
| `systemctl restart nginx` | Restart Nginx web server |
| `systemctl restart php8.3-fpm` | Restart PHP processor |
| `mysql -u lims_user -p` | Masuk ke database MySQL |
| `nano .env` | Edit environment variables |

### Server Troubleshooting

**500 Internal Server Error:**
Biasanya isu permission. Jalankan:
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:clear
```

---
© 2026 LIMS - Faculty of Computing
