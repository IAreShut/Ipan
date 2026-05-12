# LIMS — System Design Document

> **Logbook Internship Management System**
> Laravel 12 MVC — FYP Faculty of Computing
> Live: https://lim-system.my/

---

## 1. System Overview

LIMS is a web-based platform that digitises internship logbook management. Three actor roles interact with the system:

| Actor | Responsibilities |
|---|---|
| **Student** | Submit daily log entries with attachments, generate AI summaries, track weekly progress, set personal reminders |
| **Supervisor** | Review/approve/reject log entries, assign tasks to student groups, view analytics dashboard |
| **Admin** | Placeholder (Coming Soon) |

The system runs on a DigitalOcean Droplet (Ubuntu LEMP Stack), with Cloudinary for persistent file storage and Gemini API for AI-powered log summarisation.

---

## 2. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CLIENT LAYER (Browser)                        │
│  Blade Templates → Bootstrap 5 → jQuery/DataTables → SweetAlert2        │
│  AJAX Polling (30s) for real-time notification toasts                   │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │ HTTPS
                                ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         SERVER LAYER (Laravel 12)                       │
│                                                                         │
│  ┌──────────┐    ┌────────────────────────────────────┐                 │
│  │  Routes   │───▶│           Controllers               │                 │
│  │ (web.php) │    │  AuthController                    │                 │
│  │           │    │  Student\{Dashboard,LogEntry,       │                 │
│  │ / (public)│    │    Progress,Profile,Notification}   │                 │
│  │ /student/*│    │  Supervisor\{Dashboard,Review,      │                 │
│  │ /supervisor/*│  │    Task,Analytics,Profile}         │                 │
│  │ /admin/*  │    │  Controller (base)                  │                 │
│  └──────────┘    └────────────┬───────────────────────┘                 │
│                               │                                         │
│  ┌────────────────────────────┼────────────────────────────┐            │
│  │  Middleware                │        Notifications       │            │
│  │  - auth (built-in)         │  3 Classes + LimsDBChannel │            │
│  │  - No role middleware      │  Scheduled (5PM weekdays)  │            │
│  │  - HTTPS forced in prod    │  AJAX polling (30s)        │            │
│  └────────────────────────────┼────────────────────────────┘            │
│                               │                                         │
│  ┌──────────────────────┐     │    ┌────────────────────────┐           │
│  │  Channels             │     │    │  Providers             │           │
│  │  LimsDatabaseChannel  │     │    │  AppServiceProvider    │           │
│  │  (custom DB notifs)   │     │    │  (HTTPS forcing)       │           │
│  └──────────────────────┘     │    └────────────────────────┘           │
│                               ▼                                         │
│                    ┌─────────────────────┐                              │
│                    │   Eloquent Models    │                              │
│                    │  User, Internship,   │                              │
│                    │  LogEntry,           │                              │
│                    │  LogAttachment,      │                              │
│                    │  Notification, Task  │                              │
│                    └─────────┬───────────┘                              │
└──────────────────────────────┼──────────────────────────────────────────┘
                               │
         ┌─────────────────────┼─────────────────────┐
         ▼                     ▼                     ▼
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────┐
│  DATA LAYER     │  │  FILE STORAGE   │  │  EXTERNAL APIs      │
│                 │  │                 │  │                     │
│  MySQL (DO)     │  │  Cloudinary     │  │  Gemini 2.5 Flash   │
│  via Eloquent   │  │  (production)   │  │  Lite (AI summary)  │
│  ORM            │  │  + local/public │  │                     │
│                 │  │  (development)  │  │  Mailtrap SMTP      │
│                 │  │                 │  │  (email delivery)   │
└─────────────────┘  └─────────────────┘  └─────────────────────┘
```

**Architecture style:** Monolithic MVC with role-based route grouping. No microservices, no API layer. Server-rendered Blade views with AJAX for real-time interactivity.

---

## 3. Tech Stack & Decision Rationale

### 3.1 Backend

| Technology | Version | Why Chosen |
|---|---|---|
| **PHP** | 8.2+ | Required by Laravel 12; natively supported on Ubuntu 24.04 |
| **Laravel** | 12.x | Mature MVC framework with built-in auth, Eloquent ORM, migrations, queues, and artisan CLI |
| **Composer** | Latest | Standard PHP dependency manager |

### 3.2 Frontend

| Technology | Why Chosen |
|---|---|
| **Blade** (built-in) | Server-side rendering avoids SPA complexity; ideal for a multi-page CRUD app |
| **Bootstrap 5.3** | Consistent, responsive UI; `.card` and `.table` classes globally styled for premium SaaS look |
| **Tailwind CSS 4.0** | Utility classes for one-off custom styling (bundled via Vite) |
| **jQuery 3.7** | Simple DOM manipulation and AJAX — no need for React/Vue given page-level interactivity |
| **DataTables 1.13** | Production-grade table sorting, pagination, and search with minimal setup |
| **SweetAlert2 11** | Beautiful, accessible popups for alerts and confirmations |
| **Font Awesome 6.4** | Consistent icon set across all UI components |
| **Animate.css 4** | Subtle CSS animations for UI polish |

### 3.3 Build Tools

| Technology | Why Chosen |
|---|---|
| **Vite 7** | Fast HMR and build, Laravel Vite Plugin for seamless Blade integration |
| **Axios 1.11** | Promise-based HTTP client for AJAX (CSRF token auto-injection) |
| **Concurrently 9** | Runs `artisan serve`, `queue:listen`, `pail`, and `npm run dev` in one terminal |

### 3.4 External Services

| Service | Why Chosen |
|---|---|
| **Cloudinary** | Originally added for Heroku's ephemeral filesystem, retained on DO for offloading asset delivery and saving disk space. |
| **Gemini 2.5 Flash-Lite** | Cost-effective AI model for generating professional log summaries from student rough notes. Multimodal support allows image attachment context. |
| **MySQL** | Self-managed MySQL 8.0 on the DigitalOcean Droplet. |
| **Mailtrap** | SMTP sandbox for development email testing |
| **UI Avatars** | Free placeholder avatar generation API (no API key needed) |

---

## 4. Directory Structure

```
Ipan/
├── app/
│   ├── Channels/
│   │   └── LimsDatabaseChannel.php        # Custom DB notification channel
│   ├── Http/Controllers/
│   │   ├── AuthController.php             # Login, register, logout
│   │   ├── Controller.php                 # Abstract base controller
│   │   ├── Student/
│   │   │   ├── DashboardController.php    # Student stats + recent logs
│   │   │   ├── LogEntryController.php     # CRUD log entries + AI + attachments
│   │   │   ├── ProfileController.php      # Student profile + internship info
│   │   │   ├── ProgressController.php     # Weekly progress grid + drill-down
│   │   │   └── NotificationController.php # Notifications + personal reminders
│   │   └── Supervisor/
│   │       ├── DashboardController.php    # Supervisor stats + student list
│   │       ├── ReviewController.php       # Approve/reject log entries
│   │       ├── TaskController.php         # Assign tasks to student groups
│   │       ├── AnalyticsController.php    # Performance analytics
│   │       └── ProfileController.php      # Supervisor profile + groups
│   ├── Models/
│   │   ├── User.php                       # Auth user (student/supervisor/admin)
│   │   ├── Internship.php                 # Internship period details
│   │   ├── LogEntry.php                   # Daily log entries
│   │   ├── LogAttachment.php              # File attachments for logs
│   │   ├── Notification.php               # Custom notification records
│   │   └── Task.php                       # Tasks & personal reminders
│   ├── Notifications/
│   │   ├── DailyLogReminderNotification.php  # Scheduled 5PM weekday
│   │   ├── TaskSetNotification.php           # Supervisor assigns task
│   │   └── PersonalReminderNotification.php  # Student self-reminder
│   └── Providers/
│       └── AppServiceProvider.php         # HTTPS forcing in production
├── bootstrap/
│   ├── app.php                            # Application bootstrap
│   └── providers.php                      # Service provider registration
├── config/
│   ├── app.php, auth.php, database.php    # Standard Laravel configs
│   ├── gemini.php                         # Gemini API key + base URL
│   ├── filesystems.php                    # Cloudinary disk definition
│   └── services.php                       # Third-party service config
├── database/
│   ├── migrations/                        # 15 migration files
│   └── seeders/DatabaseSeeder.php         # Test data (4 user accounts)
├── public/
│   ├── css/                               # Page-specific stylesheets (15 files)
│   ├── js/                                # Page-specific scripts (13 files)
│   └── index.php                          # Application entry point
├── resources/
│   ├── css/app.css                        # Vite-bundled CSS (Tailwind)
│   ├── js/app.js                          # Vite-bundled JS
│   └── views/
│       ├── welcome.blade.php              # Landing page
│       ├── auth/login.blade.php           # Combined login + register
│       ├── layouts/
│       │   ├── master.blade.php           # Root layout (Bootstrap + JS libs)
│       │   └── app.blade.php              # Inner layout (sidebar + content)
│       ├── student/                       # 7 Blade views
│       └── supervisor/                    # 5 Blade views
├── routes/
│   ├── web.php                            # All 28 web routes
│   └── console.php                        # Scheduled task + inspire command
├── storage/
│   └── app/public/                        # Local file uploads (dev only)
├── package.json                          # NPM dependencies + dev scripts
├── composer.json                         # Composer dependencies + scripts
├── vite.config.js                        # Vite + Laravel plugin config
├── .env.example                          # Environment template
├── README.md                             # Setup + troubleshooting guide
└── DESIGN.md                             # This document
```

---

## 5. Module Design

### 5.1 Authentication Module

**Files:** `AuthController.php`, `auth/login.blade.php`, `layouts/master.blade.php`

**Login Flow:**
```
User enters email/matrix_id + password
        │
        ▼
AuthController@login()
  ├─ Detects input type (FILTER_VALIDATE_EMAIL)
  ├─ Authenticates via Auth::attempt()
  ├─ Regenerates session
  └─ Redirects by role:
       supervisor → /supervisor/dashboard
       admin      → /admin/dashboard
       default    → /student/dashboard
```

**Registration Flow:**
```
Student fills form (faculty → programme_code → class cascading dropdowns)
        │
        ▼
AuthController@register()
  ├─ Validates: matrix_id (numeric, unique), phone (+60 regex), email, password (min 8)
  ├─ Auto-assigns supervisor via User::matchesCriteria()
  │    └─ Normalises strings, checks student group against supervisor's groups JSON
  ├─ Creates User record with role='student'
  └─ Logs in & redirects to dashboard
```

**Key design decisions:**
- No email verification required (simplified for FYP scope)
- Dual login credential: email OR matrix_id
- Supervisor auto-matching at registration via `User::matchesCriteria()` — normalised string comparison (`faculty + programme_code + class` ↔ `groups[]`)
- No forgot-password flow implemented

### 5.2 Log Entry Module (Core)

**Files:** `Student/LogEntryController.php`, `student/log-entries.blade.php`, `student/log-entry-show.blade.php`

**Status Lifecycle:**
```
            ┌────────────┐
            │   DRAFT    │ ← Student saves as draft (editable)
            └─────┬──────┘
                  │ Submit button
                  ▼
            ┌────────────┐
            │  PENDING   │ ← Visible to supervisor for review
            └─────┬──────┘
        ┌─────────┴─────────┐
        ▼                   ▼
   ┌──────────┐      ┌──────────┐
   │ APPROVED │      │ REJECTED │ ← Supervisor action (rejection requires comment)
   └──────────┘      └──────────┘
```

**Create Flow:**
```
Student clicks "New Log Entry"
        │
        ▼
LogEntryController@store()
  ├─ Validates: entry_date, week_number, task_description, attachments (image, max 5MB)
  ├─ Auto-creates Internship if none exists (company_name='Not Set')
  ├─ DB::beginTransaction()
  ├─ Creates LogEntry (status=draft or pending)
  ├─ If has attachments:
  │    ├─ CLOUDINARY_URL set? → Upload to cloudinary, store secure_url
  │    └─ Not set?           → Store to storage/app/public/, store asset() URL
  └─ DB::commit()
        │
        ▼
  Redirect with flash message
```

**Edit constraint:** Only `draft` entries can be edited by the student. Once submitted (`pending`), the entry is locked.

**View authorisation:** A log entry can be viewed by its owner student OR that student's assigned supervisor.

**AI Summary Flow:**
```
Student clicks "Generate AI Summary" button (AJAX)
        │
        ▼
POST /student/ai-generate-summary
  ├─ Reads task_description from request
  ├─ Checks for attached images → base64 Blob objects
  ├─ Calls Gemini::generativeModel('models/gemini-2.5-flash-lite')
  │    └─ System prompt: "Act as professional internship student"
  │       - Use professional verbs (Assisted, Analyzed, Developed...)
  │       - Minimum 50 words, 1 paragraph
  │       - Malaysian basic English, informal tone
  │       - No greetings/sign-offs
  ├─ Returns JSON: { summary: "..." }
  └─ Error → JSON 500: { error: "..." }
```

**Week calculation:** `week_number = diffInWeeks(entry_date, internship.start_date) + 1`

### 5.3 Progress Tracking Module

**Files:** `Student/ProgressController.php`, `student/progress.blade.php`, `student/progress-week.blade.php`

```
ProgressController@index()
  ├─ Calculates total weeks from internship
  ├─ Groups log entries by week_number
  └─ Renders progress grid:
       Each week card shows: log count/5, status (Complete/In Progress/Incomplete)
       STATUS per card is derived from the aggregate of all 5 daily logs:
         All approved → Approved
         Any rejected → Rejected  
         All pending  → Pending
         Mixed        → In Progress

ProgressController@week($week)
  └─ Drills down into daily log entries for a specific week
       Each day card shows: date, task preview, image thumbnail, status badge
```

**Week status derivation:**
| Condition | Display |
|---|---|
| All 5 logs approved | Approved (green) |
| All 5 logs pending | Pending (yellow) |
| Any log rejected | Rejected (red) |
| No logs | Incomplete (grey) |
| Mixed statuses | In Progress (blue) |

### 5.4 Review Module

**Files:** `Supervisor/ReviewController.php`, `supervisor/review-logbook.blade.php`

```
ReviewController@index()
  └─ Lists all pending log entries from students under this supervisor
       Each entry card shows: student avatar, task description, attachments, timestamps

ReviewController@approve($id)
  └─ Sets status='approved', no comment required

ReviewController@reject($id)
  └─ Sets status='rejected', supervisor_comment is REQUIRED
       └─ Modal popup with textarea for mandatory rejection reason
```

**Authorisation:** Supervisor can only review entries belonging to their assigned students (`supervisor_id` matches).

### 5.5 Task & Reminder Module

**Files:** `Supervisor/TaskController.php`, `Student/NotificationController.php`, `supervisor/tasks.blade.php`, `student/notifications.blade.php`

**Task types:**
| Type | Created By | Assigned To | Trigger |
|---|---|---|---|
| `sv_task` | Supervisor | All students in selected groups | `TaskSetNotification` |
| `personal_reminder` | Student | Self | `PersonalReminderNotification` |

**Supervisor Task Assignment:**
```
Supervisor selects groups (checkboxes from supervisor's groups[] JSON)
  └─ Enter title + due date/time
        │
        ▼
TaskController@store()
  ├─ For each selected group:
  │    └─ Finds all students whose programme_code+class matches the group
  ├─ Creates Task record (type=sv_task) for each matching student
  └─ Sends TaskSetNotification (LimsDBChannel + mail) to each student
```

**Student Personal Reminder:**
```
Student clicks "Add Reminder" in notifications page
  └─ Modal: title + due date + time
        │
        ▼
NotificationController@storeReminder()
  ├─ Creates Task (type=personal_reminder, created_by=self)
  └─ Sends PersonalReminderNotification to self (LimsDBChannel + mail)
```

**FullCalendar integration:** The notifications page renders tasks as FullCalendar events for visual timeline view.

### 5.6 Notification Module

**Files:** `LimsDatabaseChannel.php`, 3 notification classes, `master.blade.php` (polling JS)

**Architecture:**
```
┌─────────────────────────────────────────────────────────────┐
│                  Notification Delivery                       │
│                                                             │
│  ┌──────────────────┐     ┌──────────────────┐              │
│  │  Scheduled        │     │  Event-Driven     │              │
│  │  (routes/console) │     │  (controllers)    │              │
│  │                   │     │                   │              │
│  │  Weekdays 5PM     │     │  Supervisor       │              │
│  │  → All students   │     │  assigns task     │              │
│  │  → DailyLogRemind │     │  → TaskSetNotif   │              │
│  │                   │     │                   │              │
│  │                   │     │  Student creates  │              │
│  │                   │     │  reminder         │              │
│  │                   │     │  → PersonalRemind │              │
│  └────────┬──────────┘     └────────┬─────────┘              │
│           │                         │                        │
│           └─────────┬───────────────┘                        │
│                     ▼                                        │
│           ┌─────────────────────┐                            │
│           │  Notification::via()│                            │
│           │  [LimsDBChannel,    │                            │
│           │   'mail']           │                            │
│           └─────────┬───────────┘                            │
│           ┌─────────┴───────────┐                            │
│           ▼                     ▼                            │
│   ┌───────────────┐    ┌───────────────┐                    │
│   │ LimsDBChannel  │    │  Mail Channel  │                    │
│   │ → notifications│    │  → Email (SMTP)│                    │
│   │   table        │    │  via Mailtrap   │                    │
│   └───────┬───────┘    └───────────────┘                    │
│           │                                                  │
│           ▼                                                  │
│   ┌───────────────────────────────────────┐                  │
│   │  Real-Time Delivery (Student only)    │                  │
│   │                                       │                  │
│   │  master.blade.php JS polls every 30s: │                  │
│   │  GET /student/notifications/unread    │                  │
│   │  → Returns: { count: N,              │                  │
│   │               notifications: [...] }  │                  │
│   │  → SweetAlert2 toast for new notifs   │                  │
│   └───────────────────────────────────────┘                  │
└─────────────────────────────────────────────────────────────┘
```

**Notification types (DB):** `info`, `warning`, `success`, `danger`

**Key design decisions:**
- Custom `LimsDatabaseChannel` instead of Laravel's built-in `DatabaseChannel` — uses own `notifications` table schema (simpler: no `notifiable_type`, `notifiable_id`, `data` JSON column)
- Dual delivery: all notifications go to both DB and email
- Polling (30s interval) instead of WebSockets — simpler for deployment without WebSockets overhead.
- Student-only polling: supervisor notifications are loaded on page refresh only

---

## 6. Database Schema

### 6.1 Entity-Relationship Diagram

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│   ┌──────────┐          ┌──────────────┐          ┌─────────────┐  │
│   │   users   │ 1──────N │ internships  │ 1──────N │ log_entries │  │
│   │          │          │              │          │             │  │
│   │ id (PK)  │          │ id (PK)      │          │ id (PK)     │  │
│   │ name     │          │ student_id   │          │ student_id  │  │
│   │ email    │          │ company_name │          │ internship_id│  │
│   │ password │          │ start_date   │          │ entry_date  │  │
│   │ role     │          │ end_date     │          │ week_number │  │
│   │          │          │ total_weeks  │          │ task_desc   │  │
│   └────┬─────┘          └──────────────┘          │ status      │  │
│        │                                          │ sv_comment  │  │
│        │ self-ref: supervisor_id                  └──────┬──────┘  │
│        │                                          │      │         │
│        │        ┌──────────────┐                   │      │ 1──N    │
│        ├──1──N─▶│ notifications│                   │      ▼         │
│        │        │              │                   │ ┌──────────┐  │
│        │        │ id (PK)      │                   │ │   log    │  │
│        │        │ user_id (FK) │                   │ │attachments│  │
│        │        │ title        │                   │ │          │  │
│        │        │ message      │                   │ │ id (PK)  │  │
│        │        │ type         │                   │ │log_entry │  │
│        │        │ is_read      │                   │ │  _id(FK) │  │
│        │        └──────────────┘                   │ │file_path │  │
│        │                                           │ │file_name │  │
│        │        ┌──────────────┐                   │ │file_type │  │
│        ├──1──N─▶│    tasks     │                   │ └──────────┘  │
│        │        │              │                   │               │
│        │        │ id (PK)      │                   └───────────────┘
│        │        │ user_id (FK) │
│        │        │ created_by   │──FK──▶ users.id
│        │        │ title        │
│        │        │ due_date     │
│        │        │ type         │
│        │        └──────────────┘
│        │
│   (supervisor_id FK references users.id — self-referencing)
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

### 6.2 Table Details

#### `users`
| Column | Type | Constraints | Notes |
|---|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY | |
| `name` | VARCHAR(255) | NOT NULL | |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Also used as login identifier |
| `email_verified_at` | TIMESTAMP | NULLABLE | Not enforced |
| `password` | VARCHAR(255) | NOT NULL | Hashed (bcrypt) |
| `role` | ENUM('student','supervisor','admin') | NOT NULL | Role-based routing |
| `matrix_id` | VARCHAR(255) | UNIQUE, NULLABLE | Student/Staff ID; alternate login |
| `employee_id` | VARCHAR(255) | NULLABLE | Supervisor-only field |
| `phone` | VARCHAR(255) | NULLABLE | Malaysian +60 format |
| `company` | VARCHAR(255) | NULLABLE | Student's internship company |
| `supervisor_id` | BIGINT | NULLABLE, FK→users.id ON DELETE SET NULL | Self-referencing |
| `faculty` | VARCHAR(255) | NULLABLE | |
| `class` | TEXT | NULLABLE | JSON-encoded array |
| `programme_code` | TEXT | NULLABLE | JSON-encoded array |
| `groups` | TEXT | NULLABLE | JSON array for supervisors only |
| `location` | VARCHAR(255) | NULLABLE | |
| `about` | TEXT | NULLABLE | |
| `avatar` | VARCHAR(255) | NULLABLE | URL (Cloudinary or local) |
| `remember_token` | VARCHAR(100) | NULLABLE | "Remember me" |
| `created_at`, `updated_at` | TIMESTAMP | | Eloquent timestamps |

**JSON fields** (`class`, `programme_code`, `groups`):
- Stored as TEXT but treated as JSON arrays via model accessors
- `getClassesAttribute()`, `getProgrammeCodesAttribute()`, `getGroupsAttribute()` handle both JSON arrays and legacy plain strings

#### `internships`
| Column | Type | Constraints |
|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY |
| `student_id` | BIGINT | FK→users.id ON DELETE CASCADE |
| `company_name` | VARCHAR(255) | NOT NULL, DEFAULT 'Not Set' |
| `company_address` | VARCHAR(255) | NULLABLE |
| `start_date` | DATE | NOT NULL |
| `end_date` | DATE | NOT NULL |
| `total_weeks` | INT | DEFAULT 12 |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `log_entries`
| Column | Type | Constraints |
|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY |
| `student_id` | BIGINT | FK→users.id ON DELETE CASCADE |
| `internship_id` | BIGINT | FK→internships.id ON DELETE CASCADE |
| `entry_date` | DATE | NOT NULL |
| `week_number` | INT | NOT NULL |
| `task_description` | TEXT | NOT NULL |
| `ai_summary` | TEXT | NULLABLE |
| `status` | VARCHAR(255) | DEFAULT 'draft'; values: draft/pending/approved/rejected |
| `supervisor_comment` | TEXT | NULLABLE |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `log_attachments`
| Column | Type | Constraints |
|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY |
| `log_entry_id` | BIGINT | FK→log_entries.id ON DELETE CASCADE |
| `file_path` | VARCHAR(255) | NOT NULL (full URL: Cloudinary or local) |
| `file_name` | VARCHAR(255) | NOT NULL |
| `file_type` | VARCHAR(255) | NULLABLE |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `notifications` (custom — NOT Laravel's built-in)
| Column | Type | Constraints |
|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY |
| `user_id` | BIGINT | FK→users.id ON DELETE CASCADE |
| `title` | VARCHAR(255) | NOT NULL |
| `message` | TEXT | NOT NULL |
| `type` | VARCHAR(255) | DEFAULT 'info' (info/warning/success/danger) |
| `is_read` | TINYINT(1) | DEFAULT 0 (boolean) |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `tasks`
| Column | Type | Constraints |
|---|---|---|
| `id` | BIGINT AUTO_INCREMENT | PRIMARY KEY |
| `user_id` | BIGINT | FK→users.id ON DELETE CASCADE |
| `created_by` | BIGINT | FK→users.id ON DELETE CASCADE |
| `title` | VARCHAR(255) | NOT NULL |
| `due_date` | DATETIME | NOT NULL |
| `type` | VARCHAR(255) | DEFAULT 'sv_task' (sv_task / personal_reminder) |
| `created_at`, `updated_at` | TIMESTAMP | |

> **Note:** This table was originally named `milestones` (migration 2026_03_03_205303). Renamed to `tasks` and `sv_milestone` type values updated to `sv_task` by migration 2026_05_04_202613.

### 6.3 Schema Evolution (Migration History)

| Migration | Purpose |
|---|---|
| 0001_01_01_000000 | Create `users`, `password_reset_tokens`, `sessions` |
| 0001_01_01_000001 | Create `cache`, `cache_locks` |
| 0001_01_01_000002 | Create `jobs`, `job_batches`, `failed_jobs` |
| 2026_01_31_150000 | Create `internships` |
| 2026_01_31_150001 | Create `log_entries` |
| 2026_01_31_150002 | Create `log_attachments` |
| 2026_01_31_150003 | Create `notifications` |
| 2026_01_31_174955 | Add `matrix_id` to users (after `name`) |
| 2026_01_31_191345 | Add `phone` to users (after `email`) |
| 2026_03_03_205303 | Create `milestones` (later renamed to `tasks`) |
| 2026_03_04_202928 | Add `faculty`, `class`, `programme_code`, `location`, `about`, `avatar` to users |
| 2026_05_01_102304 | Change `programme_code` to TEXT; add `employee_id` |
| 2026_05_01_111905 | Change `class` from string to TEXT |
| 2026_05_03_183400 | Add `groups` (TEXT) after `programme_code` |
| 2026_05_04_202613 | Rename `milestones` to `tasks`; update type values |

---

## 7. Design Patterns & Conventions

### 7.1 MVC with Controller-per-Role Pattern

```
Routes grouped by role prefix → Controllers per role → Views per role
  /student/*     → Student\{Dashboard,LogEntry,Progress,Profile,Notification}Controller
  /supervisor/*  → Supervisor\{Dashboard,Review,Task,Analytics,Profile}Controller
  /admin/*       → Placeholder (closure)
```

- Each role has its own controller namespace
- No shared controller methods between roles
- `AuthController` handles cross-cutting auth logic

### 7.2 Authorisation Pattern

**No role-based middleware.** All access control is inline in controllers:

```php
// Example: Check if student can edit this log entry
if ($logEntry->student_id !== Auth::id() || $logEntry->status !== 'draft') {
    abort(403);
}

// Example: Supervisor can only review their own students' entries
$entries = LogEntry::whereIn('student_id', 
    User::where('supervisor_id', Auth::id())->pluck('id')
)->where('status', 'pending')->paginate(...);
```

Rationale: The three-role system is simple enough that middleware would add unnecessary indirection. If more roles are added, introduce a `RoleMiddleware`.

### 7.3 Validation Pattern

**Inline in controllers** (no Form Request classes):

```php
$request->validate([
    'entry_date'        => 'required|date',
    'week_number'       => 'required|integer|min:1',
    'task_description'  => 'required|string',
    'attachments.*'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
]);
```

### 7.4 Response Pattern

| Context | Pattern |
|---|---|
| Web redirect | `redirect()->route('student.log-entries')->with('success', '...')` |
| Web error | `redirect()->back()->with('error', '...')->withInput()` |
| AJAX success | `response()->json(['summary' => $summary])` |
| AJAX error | `response()->json(['error' => '...'], 500)` |
| AJAX unread | `response()->json(['count' => N, 'notifications' => [...]])` |

### 7.5 Database Transaction Pattern

Used for multi-step writes (log entry + attachments):

```php
\DB::beginTransaction();
try {
    // create log entry
    // upload & create attachment records
    \DB::commit();
} catch (\Exception $e) {
    \DB::rollBack();
    return redirect()->back()->with('error', '...');
}
```

### 7.6 Dual-Path File Storage Pattern

```php
if (env('CLOUDINARY_URL')) {
    // PRODUCTION
    $uploaded = cloudinary()->uploadApi()->upload($file->getRealPath(), [
        'folder' => 'lims/log-attachments/' . $logEntry->id,
    ]);
    $path = $uploaded['secure_url'];
} else {
    // LOCAL DEVELOPMENT (persistent fs)
    $path = asset('storage/' . $file->store('log-attachments/' . $logEntry->id, 'public'));
}
```

**Cloudinary folder structure:**
- Log attachments: `lims/log-attachments/{logEntryId}/`
- Avatars: `lims/avatars/`

**Known tradeoff:** `env('CLOUDINARY_URL')` is called directly in controllers instead of `config('filesystems.cloudinary')`. This breaks if config is cached (`php artisan config:cache`). Future improvement: use config helper.

### 7.7 Phone Normalisation (Malaysian +60)

```php
$rawPhone = preg_replace('/[^0-9]/', '', $request->phone);  // strip non-digits
$rawPhone = ltrim($rawPhone, '0');                           // remove leading 0
$request->merge(['phone' => '+60' . $rawPhone]);             // prepend country code
```

### 7.8 Supervisor-Student Matching Algorithm

```php
// User.php → matchesCriteria()
$studentGroup = strtolower(str_replace(' ', '', $programmeCode . '-' . $class));
// e.g., "CS266-5C"

foreach ($supervisor->groups as $group) {
    $normalised = strtolower(str_replace(' ', '', $group));
    if ($normalised === $studentGroup) return true;
}
```

All strings are normalised (lowercased, spaces removed) for robust matching. The same logic powers the registration form's cascading dropdown (faculty → programme_code → class) via JSON-embedded supervisor criteria.

### 7.9 MySQL-Specific Query

```php
// Student dashboard — sorts drafts to top
LogEntry::where('student_id', $studentId)
    ->orderByRaw("FIELD(status, 'draft') DESC")
    ->latest()
    ->take(5)
    ->get();
```

`FIELD()` is MySQL-specific. Will NOT work on SQLite. Documented in `.clinerules` as a known constraint.

### 7.10 Internship Auto-Creation

If a student submits a log entry without an existing internship record:

```php
if (!$internship) {
    $internship = Internship::create([
        'student_id'   => $student->id,
        'company_name' => 'Not Set',
        'start_date'   => now(),
        'end_date'     => now()->addWeeks(12),
        'total_weeks'  => 12,
    ]);
}
```

### 7.11 UI Conventions

| Element | Convention |
|---|---|
| Cards | `.card` class (globally styled: `1.5rem` radius, soft shadow, `1px solid #cbd5e1` outline, blue hover border) |
| Tables | `.table` class (no vertical borders, uppercase light-grey headers, rounded pagination) |
| Buttons | `.btn-premium` with `rounded-pill` or `1rem` radius, always with icons |
| Layout base | All authenticated views extend `layouts/app.blade.php` → extends `layouts/master.blade.php` |

---

## 8. Data Flow Diagrams

### 8.1 Log Entry Creation & AI Summary Flow

```
┌──────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  STUDENT  │────▶│ LogEntry     │────▶│   DATABASE   │────▶│   RESPONSE   │
│  (Browser)│     │ Controller   │     │  (MySQL)     │     │  (Redirect)  │
└──────────┘     └──────┬───────┘     └──────────────┘     └──────────────┘
   │                    │
   │ Fill form:         │ Validate input
   │ - entry_date       │
   │ - task_description │ DB::beginTransaction()
   │ - upload images    │
   │                    ├─ Create LogEntry
   │ [Optional]         ├─ If attachments:
   │ Click "Generate    │    ├─ Cloudinary? → upload → secure_url
   │  AI Summary"       │    └─ Local?     → store  → asset() URL
   │        │           ├─ DB::commit()
   │        ▼           │
   │  ┌──────────────┐  │
   │  │  AJAX Call   │  │
   │  │  POST /ai-   │  │
   │  │  generate-   │──┤
   │  │  summary     │  │
   │  └──────┬───────┘  │
   │         │          │
   │         ▼          │
   │  ┌──────────────┐  │
   │  │  Gemini API  │  │
   │  │  Flash-Lite  │  │
   │  │              │  │
   │  │  System Prompt│  │
   │  │  + task_desc  │  │
   │  │  + images     │  │
   │  └──────┬───────┘  │
   │         │          │
   │         ▼          │
   │  JSON: {summary}  │
   │         │          │
   │         ▼          │
   │  Fill textarea    │
   │  with AI summary  │
   │         │          │
   │         ▼          │
   │  Click "Submit" ───┘
```

### 8.2 Registration & Supervisor Auto-Assignment Flow

```
┌──────────┐     ┌──────────────┐     ┌──────────────────┐     ┌──────────┐
│  STUDENT  │────▶│   Auth       │────▶│  User::matches   │────▶│ DATABASE │
│  (Browser)│     │  Controller  │     │  Criteria()      │     │ (MySQL)  │
└──────────┘     └──────────────┘     └──────────────────┘     └──────────┘
   │                    │                     │
   │ Registration form: │ Validate:           │
   │ - name             │ - matrix_id unique  │
   │ - email            │ - email unique      │ For each supervisor:
   │ - matrix_id        │ - phone +60 format   │   ┌────────────────────┐
   │ - phone            │ - password min 8     │   │ faculty matches?   │
   │ - faculty          │                      │   │ yes → check groups │
   │ - programme_code   │ Normalise phone      │   │ group = prog_code  │
   │ - class            │ to +60 format        │   │   + "-" + class    │
   │                    │                      │   │ normalise strings  │
   │ Cascading          │ Create user:         │   │ match found?       │
   │ dropdowns driven   │  role='student'      │   │ yes → assign this  │
   │ by supervisor      │  supervisor_id       │   │   supervisor_id    │
   │ criteria JSON      │   (auto-assigned)    │   └────────────────────┘
   │                    │                      │
   │                    │ Login + redirect     │
```

### 8.3 Notification Delivery Flow (Scheduled + Real-Time)

```
┌─────────────────────────────────────────────────────────────────┐
│                    SCHEDULED (PUSH)                              │
│                                                                 │
│  routes/console.php                                             │
│  Schedule::call() weekdays 17:00                                │
│       │                                                         │
│       ▼                                                         │
│  User::where('role','student')->get()                           │
│       │                                                         │
│       ▼                                                         │
│  $student->notify(new DailyLogReminderNotification())           │
│       │                                                         │
│       ├──▶ LimsDatabaseChannel::send()                          │
│       │         └──▶ notifications table (title, message, type) │
│       │                                                         │
│       └──▶ Mail Channel                                         │
│                └──▶ Mailtrap SMTP → student email               │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                    REAL-TIME (POLL, Student Only)                │
│                                                                 │
│  master.blade.php JS (setInterval 30s)                          │
│       │                                                         │
│       ▼                                                         │
│  GET /student/notifications/unread (AJAX)                       │
│       │                                                         │
│       ▼                                                         │
│  NotificationController@unread()                                │
│       ├──▶ Count unread notifications for Auth::user()          │
│       └──▶ Return JSON: { count: N, notifications: [...] }     │
│                │                                                │
│                ▼                                                │
│  JS receives response:                                          │
│       ├──▶ Update nav bell badge with count                     │
│       └──▶ If new notifications: SweetAlert2 toast popup        │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                    EVENT-DRIVEN (PUSH)                           │
│                                                                 │
│  Supervisor assigns task → TaskSetNotification                  │
│  Student sets reminder → PersonalReminderNotification           │
│       │                                                         │
│       └──▶ Same dual-channel delivery (DB + mail)               │
│            Picked up by polling on next 30s cycle               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Document Metadata

| Field | Value |
|---|---|
| **Document Version** | 1.0 |
| **Last Updated** | 2026-05-10 |
| **Maintained By** | LIMS Development Team |
| **Derived From** | `.clinerules` v1.0, `README.md`, codebase analysis |
| **Project Code** | Ipan (LIMS — Logbook Internship Management System) |
| **Framework** | Laravel 12.x |
| **Deployment** | Heroku (Eco Dyno) |
