# LIMS Project Architecture Wiki

This document provides a structural overview of the Internship Logbook Management System (LIMS) codebase, organized by functional communities detected via graph analysis.

## 🏗️ High-Level Architecture
The system follows a standard Laravel MVC structure, with a clear separation between PHP backend logic and JavaScript frontend helpers.

### 📊 Community Overview
| Community Name | Members | Language | Description |
| :--- | :---: | :---: | :--- |
| **student-controller** | 45 | PHP | Core logic for Auth, Student, and Supervisor controllers. |
| **student-location** | 36 | JS | Frontend helpers, location services, and interactive UI logic. |
| **migrations-up** | 34 | PHP | Database schema definitions and migrations. |
| **models-log** | 23 | PHP | Eloquent models representing the core domain entities. |
| **notifications-notification** | 14 | PHP | Application notifications (Email & Database). |
| **seeders-seeder** | 4 | PHP | Database seeding logic for initial setup. |
| **providers-app** | 3 | PHP | Laravel service providers for bootstrapping. |
| **factories-user** | 3 | PHP | Factories for generating test and dummy data. |
| **channels-limsdatabasechannel** | 2 | PHP | Custom notification delivery channels. |
| **feature-exampletest** | 2 | PHP | High-level feature tests. |
| **unit-exampletest** | 2 | PHP | Low-level unit tests. |

---

## 📂 Community Details

### 🛠️ Core Logic (student-controller)
Handles the main request-response cycle for all actors.
- **Location:** `app/Http/Controllers`
- **Key Files:** `AuthController.php`, `Student/DashboardController.php`, `Supervisor/TaskController.php`.

### 🌐 Frontend & UI (student-location)
Handles client-side interactivity, AJAX requests, and dynamic UI updates.
- **Location:** `public/js`
- **Key Files:** `public/js/student/profile.js`, `public/js/auth/login.js`, `public/js/ux-helpers.js`.

### 🗄️ Data Layer (models-log & migrations-up)
The backbone of the application's data structure.
- **Models:** `User.php`, `Internship.php`, `LogEntry.php`, `Task.php`.
- **Migrations:** Covers everything from `users` table to `tasks` and `supervisor_assignments`.

### 🔔 Communications (notifications-notification)
Manages the alerting system for daily logs, reminders, and task assignments.
- **Key Notifications:** `DailyLogReminderNotification`, `TaskSetNotification`.

---

> [!NOTE]
> This architecture map was auto-generated using **code-review-graph** analysis. Last updated: 2026-05-17.
