# responsive-design — Detailed Patterns & Worked Examples

## Core Capabilities

### 1. Container Queries
- Component-level responsiveness independent of viewport size.
- Container query units (`cqi`, `cqw`, `cqh`).
- Style queries for conditional component styling.
- Browser support fallbacks.

### 2. Fluid Typography & Spacing
- CSS `clamp()` for fluid scaling without media query bloat.
- Viewport-relative units (`vw`, `vh`, `dvh`).
- Fluid type scales with strict min/max bounds.
- Responsive spacing systems (`clamp(0.5rem, 1vw, 1.5rem)`).

### 3. Layout Patterns
- CSS Grid for 2D layouts (`grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))`).
- Flexbox for 1D distribution and alignment.
- Intrinsic layouts (content-based sizing).
- Subgrid for nested grid alignment.

### 4. Breakpoint Strategy
- Mobile-first media queries (`min-width`).
- Content-driven breakpoints over device-specific hardcoding.
- Design token integration.
- Feature queries (`@supports`).

---

## Quick Reference

### Breakpoint Scale (Bootstrap 5 & Tailwind Compatible)

```css
/* Mobile-first breakpoints */
/* Base: Mobile (< 576px / 640px) */
@media (min-width: 576px) {
  /* sm: Small devices / landscape phones */
}
@media (min-width: 768px) {
  /* md: Medium devices / tablets */
}
@media (min-width: 992px) {
  /* lg: Large devices / laptops */
}
@media (min-width: 1200px) {
  /* xl: Extra large / desktops */
}
@media (min-width: 1400px) {
  /* xxl: Ultra wide displays */
}
```

---

## Key Implementation Patterns

### Pattern 1: Container Queries (Component-Level Responsiveness)

```css
/* Define containment context on card wrapper */
.card-container {
  container-type: inline-size;
  container-name: card;
}

/* Responsive adjustment based on container width, NOT screen width */
@container card (min-width: 400px) {
  .card-body-wrapper {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 1rem;
  }
}

@container card (min-width: 600px) {
  .card-title {
    font-size: clamp(1.1rem, 4cqi, 1.75rem);
  }
}
```

### Pattern 2: Fluid Typography Scale

```css
:root {
  /* clamp(min, val, max) */
  --text-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
  --text-sm: clamp(0.875rem, 0.8rem + 0.375vw, 1rem);
  --text-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);
  --text-lg: clamp(1.125rem, 1rem + 0.625vw, 1.25rem);
  --text-xl: clamp(1.25rem, 1rem + 1.25vw, 1.5rem);
  --text-2xl: clamp(1.5rem, 1.25rem + 1.25vw, 2rem);
  --text-3xl: clamp(1.875rem, 1.5rem + 1.875vw, 2.5rem);
}
```

### Pattern 3: Responsive Data Tables (LIMS Logbook & Analytics)

```css
/* Responsive table wrapper for mobile devices */
.table-responsive-custom {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Card-view fallback for small mobile screens (< 576px) */
@media (max-width: 575.98px) {
  .table-card-mobile thead {
    display: none;
  }
  .table-card-mobile tbody tr {
    display: block;
    margin-bottom: 1rem;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 1rem;
    padding: 0.75rem;
  }
  .table-card-mobile td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    padding: 0.5rem 0;
  }
  .table-card-mobile td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #64748b;
    font-size: 0.85rem;
  }
}
```
