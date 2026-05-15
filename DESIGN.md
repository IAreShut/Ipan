# LIMS — Design System

> **Logbook Internship Management System**
> Rujukan utama untuk UI/UX — semua pembangunan komponen dan halaman MESTI mengikut spesifikasi di bawah.

---

## 1. Color Palette

### 1.1 Brand Colors

| Token | CSS Variable | HEX | Usage |
|-------|-------------|-----|-------|
| Primary | `--primary-color` | `#1E40AF` | Butang utama, sidebar logo, link, hover border card |
| Accent | `--accent-color` | `#3B82F6` | Spinner, progress bar, gradient AI button |
| Primary Dark | — | `#1e3a8a` | `.btn-primary-custom:hover`, `.btn-premium-primary` bg |
| Darker Navy | — | `#172554` | `.btn-premium-primary:hover` bg |

### 1.2 Semantic Colors

| Token | HEX | Usage |
|-------|-----|-------|
| Success | `#10B981` | Status `approved`, badge hijau, SweetAlert2 success |
| Danger | `#EF4444` | Status `rejected`, badge merah, logout link, SweetAlert2 error |
| Warning | `#F59E0B` | Status `pending`, badge kuning, SweetAlert2 warning |
| Info | `#3B82F6` | Notifikasi info |

### 1.3 Neutral / Background

| Token | HEX | Usage |
|-------|-----|-------|
| Light BG | `#F3F4F6` | Body background (global) |
| Card BG | `#ffffff` | Semua card, sidebar, modal |
| Soft BG | `#F8FAFC` | Input fields, task content box, student list items |
| Table Header | `#F8FAFC` | Table `thead` background |
| Dark Text | `#1F2937` | Body text, headings |
| Muted Text | `#6B7280` | `text-muted`, labels, secondary info |
| Table Text | `#334155` | Table body text |
| Table Header Text | `#64748B` | Table header text (uppercase) |

### 1.4 Border Colors

| Token | HEX | Usage |
|-------|-----|-------|
| Card Border | `#cbd5e1` | Card outline (default state) |
| Soft Border | `#e2e8f0` | Table rows, card header/footer, input border on focus |
| Light Border | `#E5E7EB` | Sidebar divider, progress bar bg, timeline |

### 1.5 Status Badge Colors

| Status | BG | Text |
|--------|----|------|
| Approved | `#D1FAE5` | `#065F46` |
| Rejected | `#FEE2E2` | `#991B1B` |
| Pending | `#FEF3C7` | `#92400E` |
| Draft | `#f1f5f9` | `#64748b` |

### 1.6 Soft Icon Backgrounds

| Icon Variant | BG | Text |
|-------------|-----|------|
| Primary | `#EFF6FF` | `#1e3a8a` |
| Success | `#ecfdf5` | `#10b981` |
| Warning | `#fffbeb` | `#f59e0b` |
| Danger | `#fef2f2` | `#ef4444` |

---

## 2. Typography

### 2.1 Font Family

Font utama: **`'Inter', sans-serif`**

Dimuat melalui Google Fonts (`@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')`) di `public/css/style.css`.

Fallback: `ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'`.

### 2.2 Heading Sizes

| Element | Weight | Notes |
|---------|--------|-------|
| `<h1>` hingga `<h6>` | `font-weight: 600` | Bootstrap default sizes |
| `.h2` dalam dashboard header | `fw-bold` | Saiz `2rem` (Bootstrap) |
| `.h4` sidebar logo | `fw-bold`, `mb-0` | |

### 2.3 Body Text

| Element | Size | Weight | Color |
|---------|------|--------|-------|
| Body | Bootstrap default (`1rem`) | — | `#1F2937` |
| Table body | `0.95rem` | — | `#334155` |
| Table header | `0.85rem` | `600` | `#64748b` (uppercase) |
| Stat value | `2.25rem` | `800` | `#0f172a` |
| Stat label | `0.85rem` | `600` | `#64748b` (uppercase) |
| Small muted | `0.75rem`‑`0.85rem` | `500` | `#64748b` |

### 2.4 Link Styles

| Variant | Style |
|---------|-------|
| Nav link (sidebar) | `#6B7280`, hover `#1E40AF` with `#EFF6FF` bg + right border |
| Danger link (logout) | `#dc3545` (Bootstrap default) |
| Navy link | `#1E3A8A`, `fw-600` |

---

## 3. Components

### 3.1 Buttons

#### 3.1.1 `.btn-primary-custom`

| State | Style |
|-------|-------|
| Default | `background: #1E40AF`, `color: white`, `border-color: #1E40AF` |
| Hover | `background: #1e3a8a`, `color: white` |

#### 3.1.2 `.btn-premium` (base class)

- `padding: 0.75rem 1.5rem`
- `border-radius: 1rem`
- `font-weight: 600`
- `display: flex`, `align-items: center`, `gap: 0.5rem`

| Variant | Default | Hover |
|---------|---------|-------|
| `.btn-premium-primary` | `background: #1e3a8a`, `color: white` | `background: #172554`, `translateY(-1px)`, `box-shadow` |
| `.btn-premium-outline` | `background: white`, `color: #1e3a8a`, `border: 1px solid #1e3a8a` | `background: #EFF6FF`, `translateY(-1px)` |

#### 3.1.3 `.btn-action-icon`

- `width: 32px`, `height: 32px`
- `border-radius: 50%`
- `background: #f1f5f9`, `color: #64748b`
- Hover: `background: #EFF6FF`, `color: #1e3a8a`

#### 3.1.4 `.btn-ai-generate`

- `background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 100%)`
- `border-radius: 50px`
- `padding: 0.6rem 1.4rem`, `font-weight: 600`
- `box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4)`
- Hover: `translateY(-2px)`, shadow lebih besar
- Active: `translateY(0)`
- Icon `i` animasi `sparkle` (scale + rotate)

#### 3.1.5 Buttons Lain

| Class | Style |
|-------|-------|
| `.btn-navy-link` | `color: #1E3A8A`, `fw-600`, `text-decoration: none`, gap `0.5rem` |
| `.btn-back-nav` | `border-radius: 0.5rem`, border `1px solid #E5E7EB`, `box-shadow`, hover `bg: #F9FAFB` |
| `.btn-close-custom` | `32x32` circle, `border: 1px solid #E5E7EB`, `border-radius: 50%` |
| `.btn-thumb-delete` | `18x18` circle, `bg: rgba(239,68,68,0.9)`, hidden until hover |

### 3.2 Input Fields

#### 3.2.1 `.form-control-custom`

| State | Style |
|-------|-------|
| Default | `background: #f8fafc`, `border: 1px solid transparent`, `border-radius: 0.75rem`, `padding: 0.75rem 1rem`, `font-size: 0.9rem`, `color: #334155` |
| Focus | `background: #fff`, `border-color: #cbd5e1`, `box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.05)` |
| Disabled | `background: #f8fafc`, `opacity: 1` |

#### 3.2.2 `.upload-zone`

| State | Style |
|-------|-------|
| Default | `border: 2px dashed #c8d1dc`, `padding: 2rem`, `border-radius: 12px`, `background: #f8fafc` |
| Hover | `border-color: #6366f1`, `background: #eef2ff` |
| Drag active | `border-color: #6366f1`, `background: #e0e7ff`, `box-shadow: 0 0 0 4px rgba(99,102,241,0.15)` |

#### 3.2.3 DataTables Search

| State | Style |
|-------|-------|
| Default | `border: 1px solid #cbd5e1`, `border-radius: 2rem`, `padding: 0.5rem 1rem` |
| Focus | `border-color: var(--primary-color)`, `box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1)` |

#### 3.2.4 Labels

- `.form-label-custom`: `font-size: 0.85rem`, `font-weight: 500`, `color: #475569`, `margin-bottom: 0.4rem`

### 3.3 Cards

#### 3.3.1 Card Global (`style.css`)

| Property | Value |
|----------|-------|
| Background | `#ffffff` |
| Border radius | `1.5rem` |
| Border | `1px solid #cbd5e1` |
| Box shadow | `0 10px 25px rgba(0, 0, 0, 0.03)` |
| Hover | `translateY(-3px)`, `border-color: #1E40AF`, `box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05)` |
| Transition | `0.2s ease` (transform, box-shadow, border-color) |
| Overflow | `hidden` |
| Margin bottom | `1.5rem` |

Classes yang mendapat gaya ini: `.card`, `.card-custom`, `.profile-card`, `.detail-card`, `.auth-card`

#### 3.3.2 Card Variants

| Class | Special Properties |
|-------|-------------------|
| `.stat-card` | `padding: 1.5rem`, `display: flex`, `justify-content: space-between` |
| `.premium-card` | Sama dengan global card (student dashboard) |
| `.detail-card` | `box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05)`, `padding: 1.5rem` (log entry show) |
| `.activity-card` | `border-radius: 1.25rem`, `padding: 1.5rem`, `margin-bottom: 1rem`, animasi `slideUpFadeIn` |
| `.week-card` | `border: 2px solid transparent`, hover `border-color: #1E3A8A`, `translateY(-5px)` |
| `.stat-card-modern` | `border-radius: 12px`, `padding: 20px`, `border: 1px solid #e2e8f0` |

#### 3.3.3 Card Sub-elements

| Element | Style |
|---------|-------|
| `.card-header` / `.card-footer` | `background: transparent`, `border-bottom: 1px solid #e2e8f0` |
| `.profile-card-header` | `display: flex`, `justify-content: space-between`, `margin-bottom: 1.5rem` |
| `.profile-card-title` | `font-size: 1.1rem`, `fw-600`, `color: #1e293b` |
| `.profile-card-subtitle` | `font-size: 0.85rem`, `color: #64748b` |

### 3.4 Alerts & Modals

#### 3.4.1 Flash Messages (SweetAlert2)

Flash messages dihantar dari Laravel controller melalui `<meta>` tags (dibaca oleh `ux-helpers.js`):

| Type | SweetAlert2 Config |
|------|-------------------|
| Success | `icon: 'success'`, `confirmButtonColor: '#10B981'`, `timer: 4000`, animasi `fadeInDown` |
| Error | `icon: 'error'`, `confirmButtonColor: '#EF4444'` |
| Validation | `icon: 'warning'`, `confirmButtonColor: '#F59E0B'`, `html`: `<ul>` errors |
| Toast (real-time notif) | `toast: true`, `position: 'top-end'`, `timer: 10000`, animasi `fadeInRight` |

#### 3.4.2 Meta Tag Pattern

```blade
@if(session('success'))
    <meta name="flash-success" content="{{ session('success') }}">
@endif
@if(session('error'))
    <meta name="flash-error" content="{{ session('error') }}">
@endif
@if($errors->any())
    <meta name="flash-validation" content="{{ json_encode($errors->all()) }}">
@endif
```

#### 3.4.3 Controller Flash Pattern

```php
// Success redirect
redirect()->route('...')->with('success', '...')

// Error redirect
redirect()->back()->with('error', '...')->withInput()
```

---

## 4. Layout Rules

### 4.1 Framework

| Layer | Peranan |
|-------|---------|
| **Bootstrap 5.3** | Framework utama — grid system, responsive layout, components (offcanvas, nav, card, table) |
| **Tailwind CSS 4.0** | Utiliti kelas untuk one-off styling (via Vite build) |
| **Custom CSS** | Gaya khusus sistem — terletak di `public/css/*.css` |

### 4.2 Layout Structure

```
┌──────────────────────────────────────────────┐
│  master.blade.php (root layout)               │
│  ├── Bootstrap/DataTables/SweetAlert2 CDN     │
│  ├── Global spinner overlay                   │
│  └── @yield('content')                        │
│                                               │
│  app.blade.php (inner layout)                 │
│  ├── Sidebar (offcanvas-lg)                   │
│  │   ├── Logo (LIMS)                          │
│  │   ├── User profile card                    │
│  │   └── Nav links + Logout                   │
│  └── Main content                             │
│      ├── Mobile header (d-lg-none)            │
│      ├── Desktop header (d-none d-lg-flex)    │
│      └── @yield('main-content')               │
└──────────────────────────────────────────────┘
```

### 4.3 Sidebar

| Property | Value |
|----------|-------|
| Width | `260px` (`--sidebar-width`) |
| Background | `white` |
| Box shadow | `2px 0 10px rgba(0,0,0,0.05)` |
| Position (lg+) | `fixed`, `left: 0`, `height: 100vh`, `z-index: 1000` |
| Mobile (<992px) | Offcanvas (Bootstrap `offcanvas-lg`) |
| Nav link padding | `0.75rem 1.5rem` |
| Nav link active | `color: #1E40AF`, `bg: #EFF6FF`, `border-right: 3px solid #1E40AF` |

### 4.4 Main Content

| Property | Value |
|----------|-------|
| Margin-left (lg+) | `260px` |
| Padding | `2rem` |
| Mobile (<992px) | `margin-left: 0`, `padding: 1rem` |

### 4.5 Responsive Breakpoints

| Breakpoint | Behavior |
|-----------|----------|
| `>=992px` (lg) | Sidebar fixed, main content with margin |
| `<992px` | Sidebar jadi offcanvas, main content full width |

### 4.6 Standard Spacing

| Use Case | Value |
|----------|-------|
| Card margin-bottom | `1.5rem` |
| Card padding (stat-card) | `1.5rem` |
| Main content padding (desktop) | `2rem` |
| Main content padding (mobile) | `1rem` |
| Sidebar logo padding | `1.5rem` |
| Nav link padding (Y) | `0.75rem` |
| Grid gap (info-grid) | `1.5rem` |
| Flex gap (nav items) | `0.75rem` |
| Image thumb gap | `6px`‑`12px` |
| Component gap (buttons) | `0.5rem` |

---

## 5. Naming Convention

### 5.1 Pattern

Gaya: **kebab-case dengan BEM modifiers**

```
.block-element—state
.block__element—modifier
```

### 5.2 Examples

| Category | Examples |
|----------|----------|
| **Status badges** | `.badge-status-approved`, `.badge-status-rejected`, `.badge-status-pending`, `.badge-status.draft` |
| **Buttons** | `.btn-primary-custom`, `.btn-premium-primary`, `.btn-premium-outline`, `.btn-action-icon`, `.btn-ai-generate`, `.btn-close-custom`, `.btn-back-nav`, `.btn-navy-link` |
| **Cards** | `.premium-card`, `.stat-card`, `.profile-card`, `.detail-card`, `.auth-card`, `.activity-card`, `.week-card` |
| **Forms** | `.form-control-custom`, `.form-label-custom`, `.form-group-custom` |
| **Icons/Stats** | `.stat-icon-wrapper`, `.stat-label`, `.stat-value`, `.icon-primary`, `.icon-success`, `.icon-warning`, `.icon-danger` |
| **Upload** | `.upload-zone`, `.upload-zone.drag-active`, `.image-preview-grid`, `.preview-item`, `.btn-preview-remove` |
| **Attachments** | `.attachment-thumbnails`, `.thumb-wrapper`, `.attachment-thumb`, `.thumb-delete-form`, `.btn-thumb-delete` |
| **Timeline** | `.timeline`, `.timeline-item`, `.drill-down-content` |
| **Sidebar** | `.sidebar`, `.sidebar-logo`, `.main-content` |
| **Auth** | `.auth-container`, `.auth-card` |
| **Utilities** | `.text-primary-custom`, `.bg-primary-custom`, `.text-lims-navy`, `.bg-lims-light`, `.rounded-2xl`, `.shadow-soft`, `.line-clamp-2` |
| **Badges/Tags** | `.badge-status`, `.status-badge`, `.tag-badge`, `.tag-item`, `.tag-remove`, `.tag-input` |
| **Profile** | `.avatar-wrapper`, `.avatar-upload-btn`, `.info-grid`, `.info-item`, `.info-icon`, `.info-content` |
| **Details** | `.detail-meta-item`, `.section-title`, `.task-content-box`, `.feedback-box` |

### 5.3 Modifier Rules

- Status modifiers guna class terpisah (bukan `--modifier`): `.badge-status.approved`
- Hover/Focus states guna pseudo-class CSS
- Dark/light variants guna prefix: `.btn-premium-primary`, `.btn-premium-outline`

### 5.4 CSS Variables

Gunakan `:root` CSS custom properties untuk konsistensi:

```css
:root {
    --primary-color: #1E40AF;
    --accent-color: #3B82F6;
    --success-color: #10B981;
    --danger-color: #EF4444;
    --warning-color: #F59E0B;
    --light-bg: #F3F4F6;
    --text-dark: #1F2937;
    --text-muted: #6B7280;
    --sidebar-width: 260px;
}
```

---

## Document Metadata

| Field | Value |
|-------|-------|
| **Document Version** | 2.0 |
| **Last Updated** | 2026-05-16 |
| **Purpose** | UI Design System & Visual Conventions |
| **Source** | `public/css/*.css`, `resources/views/layouts/*.blade.php`, `public/js/*.js` |
| **Project** | LIMS — Logbook Internship Management System |
