# LIMS Architecture

High-level structural overview of the Logbook Internship Management System.

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          CLIENT (Browser)                                │
│  Blade + Bootstrap 5 + jQuery + DataTables + SweetAlert2 + FullCalendar  │
│  ─── Real-time notification polling (30s)                               │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │ HTTPS
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                       LARAVEL 12 APPLICATION                             │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  Routing (web.php)                                              │   │
│  │  / (public), /student/* (auth), /supervisor/* (auth), /admin/*  │   │
│  └──────────────────────────┬───────────────────────────────────────┘   │
│                             ▼                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  Controllers (per-role)                                         │   │
│  │                                                                  │   │
│  │  Auth ──▶ login, register, checkAssignment                       │   │
│  │  Student ──▶ Dashboard, LogEntry, Progress, Profile, Notification │   │
│  │  Supervisor ──▶ Dashboard, Review, Task, Analytics, Profile,     │   │
│  │                  AssignStudent                                   │   │
│  └──────────────────────────┬───────────────────────────────────────┘   │
│                             ▼                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  Services & Channels                                            │   │
│  │  - Notification: LimsDatabaseChannel + Mail                     │   │
│  │  - AI: Gemini API (gemini-2.5-flash-lite)                       │   │
│  │  - File: Cloudinary (production) / local (development)          │   │
│  │  - Queue: Database driver (async email delivery)                │   │
│  └──────────────────────────┬───────────────────────────────────────┘   │
│                             ▼                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │  Eloquent Models                                               │   │
│  │  User ────► Internship ────► LogEntry ────► LogAttachment       │   │
│  │    │                                                            │   │
│  │    ├──► Notification                                            │   │
│  │    └──► Task ────► SupervisorAssignment                         │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         DATA LAYER                                       │
│  ┌──────────────┐  ┌──────────────────┐  ┌──────────────────────────┐  │
│  │  MySQL 8.0   │  │  Cloudinary      │  │  Gemini API (Google)     │  │
│  │  (DO Droplet)│  │  (file storage)  │  │  (AI summaries)          │  │
│  └──────────────┘  └──────────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

## Entity Relationship Summary

```
User (role: student/supervisor/admin)
├── supervisor_id → User (self-ref)
├── hasMany: Internship
├── hasMany: LogEntry
├── hasMany: Notification
├── hasMany: Task (user_id)
└── hasMany: Task (created_by)

Internship (belongsTo User)
└── hasMany: LogEntry

LogEntry (belongsTo User, belongsTo Internship)
└── hasMany: LogAttachment

LogAttachment (belongsTo LogEntry)

Notification (belongsTo User)

Task (belongsTo User, belongsTo User creator)

SupervisorAssignment (standalone lookup)
```

## Module Interaction Flow

```
Authentication Flow:
  Student enters matrix_id
    → AJAX /check-assignment
    → supervisor_assignments table lookup
    → Auto-fill faculty/programme/class (readonly)
    → Register → create User with supervisor_id

Log Entry Flow:
  Student submits entry_date + task_description + images
    → LogEntryController@store
    → DB::transaction()
      → Create/Get Internship
      → Create LogEntry (draft/pending)
      → Upload attachments (Cloudinary/local)
    → Redirect to show page

Review Flow:
  Supervisor reviews pending entries
    → ReviewController@approve → status=approved
    → ReviewController@reject → status=rejected + comment

Task Assignment Flow:
  Supervisor sets title + due_date
    → TaskController@store
    → Create Task for ALL students under supervisor
    → Notify each student (DB + email)

AI Summary Flow:
  Student clicks "Generate AI Summary"
    → POST /student/ai-generate-summary (AJAX)
    → Gemini::generateContent(task_desc + images)
    → Return JSON {summary: "..."}
    → Populate textarea (editable before submit)

Notification Flow:
  System/supervisor/student triggers notification
    → Notification::via() [LimsDatabaseChannel, mail]
    → LimsDatabaseChannel → notifications table
    → Mail channel → queue → Mailtrap (dev) / Brevo (prod)
```

## Key Design Decisions

| Decision | Rationale |
|---|---|
| **No role middleware** | Simpler auth; all checks are inline in controllers |
| **No Form Request classes** | Validation stays in controllers for direct context |
| **Custom notification table** | Simpler schema than Laravel's polymorphic default |
| **Blade + AJAX instead of SPA** | Faster development, no API layer needed for FYP scope |
| **Polling instead of WebSockets** | Zero infrastructure overhead; 30s interval is sufficient |
| **Dual-path file storage** | Cloudinary for prod, local for dev (no env dependency) |
| **Pre-assigned supervisor lookup** | Avoids cascading dropdown complexity; admin-managed |
