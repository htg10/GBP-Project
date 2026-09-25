# ReviewFlow — Design System

**Last Updated:** 2026-09-25

---

## 1. Design Philosophy

- **Zero-build frontend** — all CSS/JS inline in Blade templates. No Vite, Webpack, Tailwind, or npm.
- **Server-rendered** — Blade templates with `@push('head')` for page-specific CSS and `@push('scripts')` for page-specific JS.
- **Consistent tokens** — CSS custom properties on `:root` for global theming. Dark mode via `[data-theme="dark"]`.
- **Plus Jakarta Sans** — primary typeface, loaded from Google Fonts (weights 400-800).
- **Blue accent** — `#4c6fff` as the primary brand color (named `--teal` for legacy reasons).

---

## 2. Color Palette

### Light Mode (`:root`)

| Token | Value | Usage |
|-------|-------|-------|
| `--ink` | `#1b2437` | Primary text |
| `--paper` | `#f4f6fb` | Page background |
| `--card` | `#ffffff` | Card/surface background |
| `--teal` | `#4c6fff` | Primary accent (blue) |
| `--teal-soft` | `#eaefff` | Primary accent background |
| `--teal-ink` | `#3452d1` | Primary accent text |
| `--amber` | `#f59e0b` | Warning, stars |
| `--amber-soft` | `#fef3d6` | Warning background |
| `--rose` | `#ef4757` | Error, danger, delete |
| `--rose-soft` | `#fde7e9` | Error background |
| `--line` | `#eaecf3` | Borders, dividers |
| `--muted` | `#7a8599` | Secondary text |
| `--green` | `#22c55e` | Success |
| `--green-soft` | `#dcfce7` | Success background |
| `--blue` | `#4c6fff` | Same as --teal |
| `--purple` | `#8b5cf6` | Purple accent |
| `--sky` | `#38bdf8` | Sky blue accent |
| `--pink` | `#ec4899` | Pink accent |
| `--shadow` | `0 1px 2px rgba(20,30,60,.04), 0 8px 28px rgba(20,30,60,.06)` | Card shadow |
| `--radius` | `16px` | Card border-radius |

### Dark Mode (`[data-theme="dark"]`)

| Token | Dark Value |
|-------|-----------|
| `--ink` | `#e6e9f0` |
| `--paper` | `#0e1420` |
| `--card` | `#161d2b` |
| `--teal` | `#6b8afd` |
| `--teal-soft` | `#1d2740` |
| `--teal-ink` | `#a9bcff` |
| `--rose` | `#f87171` |
| `--rose-soft` | `#2d1215` |
| `--line` | `#263041` |
| `--muted` | `#8b97a8` |
| `--green` | `#4ade80` |
| `--green-soft` | `#0f2a1a` |
| `--shadow` | `0 1px 2px rgba(0,0,0,.3), 0 8px 28px rgba(0,0,0,.35)` |

Dark mode additional overrides:
- Sidebar: `background: #141a22`, `border-color: #2a3340`
- Header bar: `background: #141a22`, `border-color: #2a3340`
- Inputs: `background: #1a2028`, `border-color: #2a3340`

Toggle mechanism: `data-theme` attribute on `<html>`, persisted in `localStorage` key `rf-theme`.

**Note:** Admin layout does NOT support dark mode.

---

## 3. Typography

| Element | Size | Weight | Notes |
|---------|------|--------|-------|
| Page heading (h1) | 23px | 700 | App layout |
| Card heading (h3) | 15-17px | 700 | |
| Body / table text | 13.5px | 500 | |
| Labels (`.lbl`) | 12.5px | 600 | Muted color |
| Nav items | 13.5px | 500 | |
| Badges | 11.5px | 600 | |
| Section kickers | 10.5px | 700 | Uppercase, letter-spacing .06em |
| KPI values | 25px | 700 | Stat cards |
| Gauge score | 42px | 800 | Health cockpit |
| Admin heading | 22px | 700-800 | Admin layout |

Font stack: `'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif`

---

## 4. Component Library

### Cards (`.card`)
```css
background: var(--card);
border: 1px solid var(--line);
border-radius: var(--radius); /* 16px */
padding: 18px;
box-shadow: var(--shadow);
```

### Buttons
- **Primary (`.btn`):** `var(--teal)` bg, white text, 10px 14px padding, 10px radius, 13.5px/600
- **Ghost (`.btn-ghost`):** white bg, 1px border, hovers to teal-soft
- **Icon (`.icon-btn`):** 32x32px, 9px radius, muted → teal on hover

### Badges (`.badge`)
- Pill shape: `border-radius: 999px`, 11.5px/600, 3px 10px padding
- Variants: `.teal`, `.amber`, `.rose`, `.gray`, `.dark`

### Alerts (`.alert`)
- 10px radius, 13.5px text, 11px 14px padding
- `.success` → teal-soft bg, teal-ink text
- `.error` → rose-soft bg, rose text
- `.info` → amber-soft bg

### Modals (`.modal-bg` + `.modal`)
- Overlay: `rgba(16,36,31,.4)`, `display: grid; place-items: center`
- Modal: max-width 440px, 18px radius, 24px padding, white bg
- Toggle: `.open` class on `.modal-bg`

### Tables
- Full width, `border-collapse`, 13.5px font
- Headers: 12px, muted, 600 weight, 12px 16px padding
- Cells: 12px 16px padding, top `1px solid var(--line)` border
- Mobile: `display: block; overflow-x: auto`

### Forms
- Inputs: full width, 10px 12px padding, 10px radius, `#fcfcfa` bg, 14px font
- Focus: `2px solid var(--teal)` outline
- Labels: `.lbl` class — 12.5px/600, muted, block display

### Stat Cards (`.stat`)
- Grid layout (4 columns default)
- 14px radius, 16px padding
- `.stat.accent` → teal-soft background
- Value: 25px/700, Label: 12px/muted

### Avatars
- 38x38px circle, teal-soft bg, teal-ink text, 13px/700 initials
- Can contain `<img>` tag

### Toggle Switches (`.rf-sw`)
- 44x24px pill, `.on` class for teal background
- Inner circle slides with `transform: translateX(20px)`

---

## 5. Layout Structure

### App Layout (`layouts/app.blade.php`)
```
┌─────────────────────────────────────────────┐
│ Header Bar (52px, sticky)                    │
│ [Google Status] [Credits] [🔔] [🌙] [⚙] [👤]│
├──────────┬──────────────────────────────────┤
│ Sidebar  │ Main Content                      │
│ (240px)  │ (max-width: 980px)               │
│ sticky   │ padding: 22px 28px                │
│          │                                    │
│ WORKSPACE│ @yield('content')                 │
│ nav items│                                    │
│          │                                    │
│ BILLING  │                                    │
│ (if plan)│                                    │
│          │                                    │
│ PROFILE  │                                    │
│ ADMIN    │                                    │
│ LOGOUT   │                                    │
└──────────┴──────────────────────────────────┘
```

### Admin Layout (`layouts/admin.blade.php`)
```
┌──────────┬──────────────────────────────────┐
│ Sidebar  │ Main Content                      │
│ (235px)  │ (max-width: 940px)               │
│          │ padding: 26px 30px                │
│ Overview │ @yield('content')                 │
│ Users    │                                    │
│ Plans    │                                    │
│ Credits  │                                    │
│          │                                    │
│ User view│                                    │
│ Sign out │                                    │
└──────────┴──────────────────────────────────┘
```

---

## 6. Responsive Breakpoints

| Breakpoint | App Layout | Admin Layout |
|-----------|------------|--------------|
| `≤ 1000px` | Overview grids 3→2 cols, sub-score 2 cols | — |
| `≤ 960px` | Sidebar → off-canvas drawer, topbar appears | Same |
| `≤ 900px` | Landing page: single column, hero visual hides | — |
| `≤ 820px` | Social: single column layout | — |
| `≤ 700px` | Overview grids → single column | — |
| `≤ 640px` | Admin card grids → single column | Same |
| `≤ 520px` | Stat grids → 2 cols, modal padding reduced | Same |

Mobile sidebar: slide-in from left (260px / 250px), with `rgba(16,36,31,.45)` overlay.

---

## 7. Sidebar Navigation Items

### App Sidebar — Workspace Section

| Label | Icon | Route Name | Notes |
|-------|------|-----------|-------|
| Overview | ▦ | `dashboard` | |
| One-Click Optimize | ⚡ | `optimize` | |
| AI Mode | ✦ | `ai` | |
| Clients | 🏢 | `clients` | Admin only |
| Reviews | ★ | `reviews` | |
| Insights | 📈 | `insights` | |
| Posts & Photos | 🖼 | `gbp-content` | |
| Google Audit | ◎ | `audit` | |
| Competitors | ⚔ | `competitors` | |
| Rank Checker | 📍 | `rank-checker` | |
| Social | ◈ | `social` | |
| Leads CRM | ◉ | `leads` | |
| WhatsApp | ☎ | `whatsapp` | |
| Ads Reports | ▤ | `ads` | |
| Keywords | 🔍 | `keywords` | |

### App Sidebar — Billing Section (shown only with active plan)

| Label | Icon | Route Name |
|-------|------|-----------|
| Credits | ⚡ | `credits` |
| Buy Credits | 🛒 | `buy-credits` |
| Billing & Invoices | 💳 | `client-billing` |
| Plans | ⬆ | `plans` |
| Billing Settings | ⚙ | `billing-settings` |

### Admin Sidebar

| Label | Icon | Route Name |
|-------|------|-----------|
| Overview | ▦ | `admin.overview` |
| Users | ◉ | `admin.users` |
| Plans | ▤ | `admin.plans` |
| Credit Packs | ⚡ | `admin.credit-packages` |

All icons are Unicode/emoji characters — no icon library.

---

## 8. Special UI Components

### Business Health Gauge (Overview)
- Pure SVG circular gauge, 160x160px
- Two `<circle>` elements: background (muted) + foreground (colored arc)
- Color: green (≥75), amber (≥50), red (<50)
- Animated with `requestAnimationFrame` transitioning `stroke-dashoffset`
- Center: score value (42px/800) + label text

### "Do Next" Queue (Overview)
- Numbered action rows with rank circles (teal bg)
- Impact badges: Critical (rose), High (amber), Medium (teal), Low (muted)
- Each row links to the relevant module route

### Plan-Required Modal (Global)
- Triggered by `window.handlePlanRequired(response)` on 402 responses
- Full-screen overlay with "Unlock AI Features" heading
- "View Plans & Upgrade" button → links to `/plans`
- "Maybe Later" dismiss button

### Sync Overlay
- Full-screen loading overlay with CSS spinner animation
- Shown during Google data sync operations

---

## 9. External Dependencies

| Resource | Source | Used On |
|----------|--------|---------|
| Plus Jakarta Sans | Google Fonts | All pages |
| Chart.js 4.4.1 | cdnjs.cloudflare.com | Overview, Insights |
| Razorpay Checkout v1 | checkout.razorpay.com | Plans, Buy Credits |

No other external CSS/JS libraries.

---

## 10. Auth Page Design

### Login
- Split-panel: left purple gradient (`#5b6ef5` → `#9b6ef0`) with floating animated cards, right white form
- Google OAuth button + email/password form
- Demo credentials shown at bottom

### Register
- Split-panel: left green gradient (`#0f6b5c` → `#a7f3d0`) with frosted-glass feature pills, right white form
- Business Name, Name, Email, Password fields
- Green gradient CTA button

### Landing Page (`welcome.blade.php`)
- Sticky glassmorphism navbar
- Hero with gradient text accent + floating dashboard mock
- Stats bar, How it Works (3 steps), Features grid (6 cards), Module strip (12 tiles)
- Pricing cards (Starter ₹999, Growth ₹2,999, Agency ₹9,999)
- CTA section + footer with policy links

---

## 11. Gradient Patterns

| Usage | Colors |
|-------|--------|
| Primary button hover | `#4c6fff` → `#6b8afd` |
| Brand logo | `#1b2437` → `#3452d1` |
| Hero accent | `#4c6fff` → `#8b5cf6` |
| KPI icons | Various per-icon gradients (blue, green, amber, purple) |
| Login panel | `#5b6ef5` → `#9b6ef0` |
| Register panel | `#0f6b5c` → `#a7f3d0` |
| Admin brand logo | `#1b2437` → `#3452d1` |

---

## 12. Currency & Locale

- Currency: Indian Rupees (₹ / INR)
- Timezone: Asia/Kolkata (IST)
- Date format: `d M Y` (e.g., "25 Sep 2026")
- Number format: Indian commas (`number_format()`)
