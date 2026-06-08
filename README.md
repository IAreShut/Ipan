# LIMS — Logbook Internship Management System

Sistem pengurusan logbook latihan industri berasaskan Laravel untuk pelajar dan penyelia.

## System Overview

| Actor          | Responsibilities                                                                                                |
| -------------- | --------------------------------------------------------------------------------------------------------------- |
| **Student**    | Submit daily log entries with attachments, generate AI summaries, track weekly progress, set personal reminders |
| **Supervisor** | Review/approve/reject log entries, assign tasks to students, view analytics & performance dashboard             |
| **Admin**      | Placeholder (Coming Soon)                                                                                       |

**Live URL:** https://lim-system.my / https://lims-fyp-8a6cb0f71eca.herokuapp.com/

---

## Tech Stack

| Layer        | Technology                                                                                          |
| ------------ | --------------------------------------------------------------------------------------------------- |
| **Backend**  | PHP 8.2+, Laravel 12, Eloquent ORM, Composer                                                        |
| **Database** | MySQL 8.0 (Production: DigitalOcean / Development: XAMPP)                                           |
| **Frontend** | Blade, Bootstrap 5.3, Tailwind CSS 4, jQuery 3.7, DataTables 1.13, SweetAlert2 11, Font Awesome 6.4 |
| **Build**    | Node.js 18+, Vite 7, Laravel Vite Plugin 2.0, Axios, Concurrently                                   |
| **Cloud**    | Cloudinary (file storage), Gemini 2.5 Flash-Lite (AI summaries)                                     |
| **Email**    | Mailtrap SDK (local dev), Brevo SMTP (production)                                                   |
| **Server**   | DigitalOcean Droplet (Ubuntu 24.04, Nginx, PHP 8.3-FPM, MySQL 8.0)                                  |

---

## Installation

```bash
# 1. Clone & install
git clone https://github.com/IAreShut/Ipan.git
cd Ipan
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Database (XAMPP)
#    Start MySQL → Create database `lims_db`
#    Update .env: DB_DATABASE=lims_db, DB_USERNAME=root, DB_PASSWORD=

# 4. Migrate & seed
php artisan migrate
php artisan db:seed

# 5. Run development server
composer dev
# (Runs: artisan serve + queue:listen + pail + npm run dev)

# 6. Run queue and schedule
php artisan queue:work
php artisan schedule:work
```

**Test Accounts:**
| Role | Email | Password |
|---|---|---|
| Supervisor | supervisor@test.com | password123 |
| Student | student@test.com | password123 |
| Admin | admin@test.com | password123 |

---

## Architecture

```
CLIENT (Browser) ─── HTTPS ───▶ Laravel 12 Application ───▶ MySQL
                                   │
                              Controllers (per-role):
                                Auth, Student\{Dashboard,LogEntry,Progress,Profile,Notification},
                                Supervisor\{Dashboard,Review,Task,Analytics,Profile,AssignStudent}
                                   │
                              Eloquent Models:
                                User, Internship, LogEntry, LogAttachment,
                                Notification, Task, SupervisorAssignment
                                   │
                              Services:
                                Gemini API (AI summary), Cloudinary (files),
                                LimsDatabaseChannel + Mail (notifications)
```

**Architecture style:** Monolithic MVC, role-based route grouping, server-rendered Blade with AJAX interactivity. No API layer, no microservices.

---

## Module Summaries

### Authentication

- Login via email **or** matrix_id (auto-detect)
- Registration: strict pre-assignment check via `supervisor_assignments` table
- No email verification, no role middleware

### Log Entry (Core)

- Status lifecycle: `draft → pending → approved/rejected`
- Draft = editable, Pending = locked for supervisor review
- Attachments: Cloudinary (production) or local storage (development)
- AI summary: optional Gemini-generated professional summary from task description + images

### Review (Supervisor)

- Lists pending entries from assigned students only
- Approve (no comment) or Reject (mandatory feedback reason)

### Progress Tracking

- Weekly progress matrix grid (week 1–N)
- Status per week: Approved / Pending / Rejected / In Progress / Empty
- Drill-down into daily entries per week

### Task & Reminders

- Supervisor assigns tasks to **all** students (bulk)
- Student creates personal reminders
- Types: `sv_task` (supervisor) / `personal_reminder` (self)
- Notifications sent via database + email

### Notifications

- Custom `LimsDatabaseChannel` (not Laravel's default)
- Dual delivery: DB table + email
- Real-time polling (30s interval) via SweetAlert2 toasts
- Scheduled: Daily reminder every weekday at 5 PM

### Supervisor-Student Assignment

- Pre-assigned via `supervisor_assignments` lookup table (admin-managed)
- Registration auto-fills faculty/programme/class from assignment data
- AssignStudent page shows registered profiles + task metrics table
- At-risk detection: rejected logs or overdue tasks

---

## Design Patterns & Conventions

| Pattern                 | Implementation                                                                                                                               |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| **Controller-per-role** | Separate namespaces for Student & Supervisor controllers                                                                                     |
| **Authorisation**       | Inline checks in controllers (no role middleware)                                                                                            |
| **Validation**          | Inline in controllers (no Form Request classes)                                                                                              |
| **File upload**         | Dual-path: `env('CLOUDINARY_URL')` → Cloudinary / local storage                                                                              |
| **Transactions**        | `DB::beginTransaction/commit/rollback` for multi-step writes                                                                                 |
| **Phone format**        | `+60XXXXXXXXX` (Malaysian normalisation)                                                                                                     |
| **CSS/JS**              | Page-specific files in `public/css/` & `public/js/` (not Vite-bundled); only `resources/css/app.css` & `resources/js/app.js` go through Vite |

---

## Deployment

### DigitalOcean (Current)

```bash
cd /var/www/Ipan
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
sudo supervisorctl restart laravel-worker:*
```

### Useful Server Commands

```bash
systemctl restart nginx              # Web server
systemctl restart php8.3-fpm         # PHP process
sudo supervisorctl restart laravel-worker:*  # Queue worker
tail -f storage/logs/laravel.log     # View logs
```
