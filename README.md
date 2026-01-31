# LIMS - Logbook Internship Management System

Sistem pengurusan logbook latihan industri berasaskan Laravel.

## Requirements

- PHP 8.1+
- Composer
- MySQL (XAMPP recommended)
- Node.js (optional, untuk frontend build)

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

### MySQL Connection Error
- Pastikan MySQL running dalam XAMPP
- Check `.env` untuk database credentials

### 500 Internal Server Error
```bash
php artisan cache:clear
php artisan config:clear
```

### Permission Error (Storage)
```bash
chmod -R 775 storage bootstrap/cache
```

---
© 2024 LIMS - Faculty of Computing
