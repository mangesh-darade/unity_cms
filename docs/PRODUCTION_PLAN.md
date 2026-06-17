# Unity Clinical Laboratory — Production & Theme Plan

**Project:** Unity CMS (`unity_cms`)  
**Lab:** Unity Clinical Laboratory, Maharashtra, India  
**Contact:** +91 98507 00268  
**Last updated:** June 2026  
**Purpose:** Full checklist to make the website fully attractive, professional, pathology-focused, and production-ready.

---

## Table of contents

1. [Executive summary](#1-executive-summary)
2. [Current state](#2-current-state)
3. [Phase 0 — Must do before go-live (P0)](#3-phase-0--must-do-before-go-live-p0)
4. [Phase 1 — Trust & content accuracy (P1)](#4-phase-1--trust--content-accuracy-p1)
5. [Phase 2 — Theme & UX polish](#5-phase-2--theme--ux-polish)
6. [Phase 3 — Conversion & features](#6-phase-3--conversion--features)
7. [Phase 4 — SEO & marketing](#7-phase-4--seo--marketing)
8. [Phase 5 — Admin & operations](#8-phase-5--admin--operations)
9. [Page-by-page review](#9-page-by-page-review)
10. [Admin panel map](#10-admin-panel-map)
11. [Theme & color guide](#11-theme--color-guide)
12. [Technical production checklist](#12-technical-production-checklist)
13. [Content copy checklist](#13-content-copy-checklist)
14. [File reference](#14-file-reference)

---

## 1. Executive summary

| Area | Score | Status |
|------|-------|--------|
| Design / theme | 8/10 | Strong navy + teal medical look; minor polish needed |
| Content | 7/10 | Good pathology copy; fix location & trust claims |
| Features | 9/10 | Tests, packages, booking, reports, reviews, locations, GA4 |
| Admin CMS | 8.5/10 | Very complete; dense for non-technical users |
| Production readiness | 6.5/10 | Blocked by password, address, email, NABL wording |

**Goal:** Launch a trustworthy Maharashtra pathology lab website that converts visitors into bookings and report downloads.

**Brand line:** *Accurate Diagnostics. Trusted Results.*  
**Visual feel:** Clean white lab + precise teal instrumentation + dark navy trust.

---

## 2. Current state

### What is already built

**Public website**
- Homepage with CMS-driven sections (hero, services, packages, equipment, gallery, FAQs, contact, testimonials + review form)
- Pages: About, Services (search + filter), Packages, Gallery, Blog, Contact, Collection, Download Report, Locations, Privacy, Terms
- APIs: booking, inquiry, review, services catalog, report OTP/download
- SEO: sitemap, robots.txt, RSS feed, JSON-LD schema, local city pages
- Tracking: GA4 events (`booking_submit`, `inquiry_submit`, `review_submit`)

**Admin panel**
- Dashboard with CRM stats + GA4 realtime
- Bookings, Patients, Reports upload, Inquiries
- GA4 Analytics (realtime + historical)
- CMS with 15 tabs (general, header, footer, pages, sections, tests, packages, FAQs, testimonials, locations, gallery, blog, menu, marketing)
- Settings (password, notifications, captcha, OTP)

**Theme**
- `css/style.css` — base layout and components
- `css/premium.css` — hero, cards, animations, responsive
- `css/admin.css` — admin UI
- Fonts: Plus Jakarta Sans, Outfit, Inter

### Known gaps (from audit)

- Default database seed may still contain **Gurugram** data if `production_seed.php` was not run
- **NABL Certified** vs **NABL Aligned** wording is inconsistent in fallbacks
- Default admin password unchanged
- Google Maps embed may show Maharashtra region, not exact lab pin
- No favicon / apple-touch-icon documented
- No individual test detail pages (SEO)
- Blog uses `?id=` instead of slug URLs
- Package booking URLs use stripped names (fragile matching)

---

## 3. Phase 0 — Must do before go-live (P0)

> Complete all items before pointing a real domain at the site.

| # | Task | Owner | How |
|---|------|-------|-----|
| 0.1 | Run production seed | Dev | `php tools/production_seed.php` from project root (WAMP PHP path) |
| 0.2 | Change admin password | Admin | `/admin/` → Settings → change from default `admin` / `admin_password_123` |
| 0.3 | Fix NABL wording site-wide | Content | Use **NABL Aligned** only (unless real NABL certificate exists). Update CMS → General hero tagline, footer badges, About badges |
| 0.4 | Set exact lab address | Content | CMS → General: full street, landmark, city, pincode (not only "Maharashtra, India") |
| 0.5 | Fix Google Maps embed | Content | CMS → General / Contact: paste exact lab location embed URL |
| 0.6 | Verify phone & WhatsApp | Content | +91 98507 00268 everywhere (header, footer, forms, schema) |
| 0.7 | Set up real email | Dev | Configure SMTP; set `mail_from_email`, `notify_admin_email` in Settings |
| 0.8 | Test end-to-end booking | QA | Submit collection form → check admin email + customer thank-you |
| 0.9 | Test report flow | QA | Register patient → upload PDF → download with OTP |
| 0.10 | Hide `/tools/` on production | Dev | Block web access via `.htaccess` or remove from public server |
| 0.11 | Enable HTTPS | Hosting | SSL certificate + uncomment HTTPS redirect in `.htaccess` |
| 0.12 | Switch database to MySQL | Dev | `includes/config.local.php`: `DB_DRIVER=mysql` + credentials |

**Exit criteria:** No default passwords, no Gurugram references, truthful accreditation text, working email on booking.

---

## 4. Phase 1 — Trust & content accuracy (P1)

| # | Task | Details |
|---|------|---------|
| 1.1 | Replace demo testimonials | Admin → Testimonials: approve only real patient reviews |
| 1.2 | About page credentials | Add pathologist name, qualification, registration number in Page Blocks |
| 1.3 | Upload certificate images | Gallery: NABL/ISO/registration scans (if applicable); link from About |
| 1.4 | Real team photos | Replace initials-only team cards with staff photos |
| 1.5 | Social media links | CMS → Marketing: real Facebook/Instagram URLs or hide empty icons |
| 1.6 | Founding year | Confirm **ESTD 2026** is correct; update footer badge if not |
| 1.7 | Location disclaimer | Locations pages: add "Service availability varies by pincode — call to confirm" if not statewide |
| 1.8 | Legal review | Privacy Policy & Terms reviewed for Indian healthcare / patient data (DPDP 2023) |
| 1.9 | Rate card visibility | Add rate card image (already in gallery) to homepage or packages with clear CTA |
| 1.10 | Remove unverified claims | No "ISO Certified" or "Free doctor consultation" unless verified (seed already deactivates some) |

---

## 5. Phase 2 — Theme & UX polish

### 5.1 Color palette (recommended)

| Role | Color | Usage |
|------|-------|--------|
| Primary | `#0f172a` (navy) | Headers, footer, top bar |
| Accent | `#0d9488` (teal) | CTAs, icons, links, trust |
| Accent light | `#14b8a6` | Hover states, gradients |
| Background | `#f8fafc` | Alternate sections |
| Clinical tint | `#e8f4f8` | Info panels, form backgrounds |
| Text | `#334155` / `#64748b` | Body / muted |
| Success CTA | `#059669` | WhatsApp, confirm actions |
| Warning | `#f59e0b` | Fasting notices only |

Keep existing `premium.css` gradient hero; optionally shift primary to `#0b3d5c` for deeper clinical blue.

### 5.2 Layout improvements

| # | Item | Priority | Implementation notes |
|---|------|----------|----------------------|
| 2.1 | Mobile sticky bar | High | Fixed bottom: Call \| WhatsApp \| Book Test |
| 2.2 | Trust ribbon under hero | High | `90+ Tests \| Home Collection 6 AM \| Reports 6–12 hrs \| Sysmex & Orbit` |
| 2.3 | Favicon + app icon | High | Add `favicon.ico`, `apple-touch-icon.png` from lab logo |
| 2.4 | Menu hamburger icon | Medium | Replace `☰` with Font Awesome bars in `header.php` |
| 2.5 | Unified card shadows | Medium | Same border-radius/shadow on services + packages cards |
| 2.6 | Featured package styling | Medium | Teal left border + "RECOMMENDED" badge (partially exists) |
| 2.7 | Sunday hours highlight | Low | Fri–Sat: show Sunday hours chip in top bar |
| 2.8 | OG image 1200×630 | High | Branded share image in CMS → Marketing |

### 5.3 Typography

- **Display:** Plus Jakarta Sans / Outfit — keep
- **Body:** Inter / Plus Jakarta Sans — keep
- Avoid adding more font families

### 5.4 Photography rules

- Use only real lab photos from `/images/gallery/web/` and `/images/akshay_*`
- Hero: blood collection or signboard (current)
- Avoid generic stock doctors

---

## 6. Phase 3 — Conversion & features

| # | Feature | Benefit | Complexity |
|---|---------|---------|------------|
| 3.1 | Home service cards → direct book link | Faster conversion | Low — link to `collection.php?test=slug` |
| 3.2 | Collection time slot field | Better scheduling | Medium — DB + admin + form |
| 3.3 | Pincode / area checker | Sets expectations | Medium — optional API or static list |
| 3.4 | Package compare table | Helps package choice | Low — static HTML on packages page |
| 3.5 | Rate card PDF download | Transparency | Low — upload PDF, link from header/footer |
| 3.6 | Sample report screenshot on download page | Reduces support calls | Low |
| 3.7 | Report status (pending/ready) | Patient clarity | Medium |
| 3.8 | Auto WhatsApp/SMS on report upload | Patient satisfaction | Medium — MSG91 in Settings |
| 3.9 | Corporate / doctor referral page | B2B leads | Medium — new page + inquiry type |
| 3.10 | Marathi tagline (optional) | Local trust in MH | Low — CMS text field |

---

## 7. Phase 4 — SEO & marketing

| # | Task | Details |
|---|------|---------|
| 4.1 | GA4 Measurement ID | CMS → Digital Marketing: `G-XXXXXXXX` |
| 4.2 | GA4 admin dashboard | Property ID + service account JSON on `/admin/analytics.php` |
| 4.3 | Google Search Console | Verify domain; submit `sitemap.php` |
| 4.4 | Google Business Profile | Match NAP (name, address, phone) with website |
| 4.5 | Individual test pages | `test.php?slug=cbc` for long-tail SEO |
| 4.6 | Blog slug URLs | `/blog/cbc-report-explained` instead of `?id=3` |
| 4.7 | Fix package book URLs | Use package `id` or `slug` in query string |
| 4.8 | 4+ new blog posts | Maharashtra-focused: monsoon panel, pre-surgery tests, etc. |
| 4.9 | Facebook Pixel / GTM | If running ads — CMS → Marketing |
| 4.10 | Looker Studio embed | Optional full dashboard in admin analytics |

---

## 8. Phase 5 — Admin & operations

### Daily workflow

1. Check **Dashboard** — new bookings, inquiries, GA4 live users
2. **Bookings** — confirm pending, update status
3. **Inquiries** — reply, mark handled
4. **Testimonials** — approve/reject website reviews
5. **Reports** — upload PDFs when ready; patient gets notification

### Admin improvements (backlog)

| # | Improvement |
|---|-------------|
| 5.1 | Dashboard: pending reviews count + quick action buttons |
| 5.2 | Bookings: export CSV, date filter, assign phlebotomist |
| 5.3 | Patients: unified view (bookings + reports) |
| 5.4 | Inquiries: internal notes + "replied" flag |
| 5.5 | CMS: split tabs into Content / Catalog / Marketing groups |
| 5.6 | CMS: page preview button |
| 5.7 | CMS: media library (upload picker vs raw paths) |
| 5.8 | Fix tab label typo: "health Blog" → "Health Blog" |

---

## 9. Page-by-page review

| Page | URL | Status | Top action |
|------|-----|--------|------------|
| Home | `index.php` | Good | Trust ribbon + rate card CTA |
| About | `about.php` | Good | Real credentials + certificates |
| Services | `services.php` | Excellent | Add test detail pages (later) |
| Packages | `packages.php` | Good | Compare table + fix book URLs |
| Collection | `collection.php` | Excellent | Time slot + pincode note |
| Download | `download.php` | Good | Sample report preview |
| Contact | `contact.php` | Good | Exact map pin |
| Gallery | `gallery.php` | Good | Rate card highlight |
| Blog | `blog.php` | Good | Slug URLs |
| Locations | `locations.php` | Good | Pincode disclaimer |
| Location city | `location.php?city=` | Good | Unique content per city |
| Privacy | `privacy.php` | OK | Legal review |
| Terms | `terms.php` | OK | Legal review |

### Homepage sections (CMS order)

1. Hero  
2. Why Choose Us  
3. Services (9 tests preview)  
4. Packages  
5. Equipment  
6. Home Collection CTA  
7. Gallery  
8. Testimonials + review form  
9. FAQs  
10. Contact  
11. Download Report  

Manage order: **Admin → CMS → Home Sections**

---

## 10. Admin panel map

| Menu | File | Purpose |
|------|------|---------|
| Dashboard | `admin/index.php` | Overview, GA4 realtime |
| GA4 Analytics | `admin/analytics.php` | Live + historical analytics |
| Bookings | `admin/bookings.php` | Home collection requests |
| Patients | `admin/patients.php` | Patient registry |
| Upload Reports | `admin/reports.php` | PDF upload per patient |
| Inquiries | `admin/inquiries.php` | Contact form inbox |
| CMS Settings | `admin/cms.php` | All website content |
| Settings | `admin/settings.php` | Password, email, SMS, security |

### CMS tabs (`admin/cms.php`)

| Tab | Manages |
|-----|---------|
| General Settings | Site name, logo, contact, hero, offer banner |
| Website Header | Top bar, logo size, social |
| Website Footer | About text, hours, badges |
| Site Pages | Per-page SEO titles & descriptions |
| Home Sections | Show/hide/reorder homepage blocks |
| Section Items | Why choose us cards, etc. |
| Page Blocks | About intro, team, badges |
| Digital Marketing | GA4, GTM, pixels, schema, SEO meta |
| Pathology Tests | Full test catalog (~90+ tests) |
| Health Packages | Package CRUD |
| FAQ Accordion | Homepage FAQs |
| Testimonials | Approve website reviews |
| Service Locations | City SEO pages |
| Gallery & Equipment | Photos and machines |
| Health Blog | Articles |
| Navigation Menu | Header links + CTA button |

---

## 11. Theme & color guide

### Pathology lab design principles

1. **Trust first** — real photos, accurate accreditation, visible phone number  
2. **Clarity** — prices, sample type, fasting rules on every test  
3. **Action** — Book, Call, WhatsApp always one tap away on mobile  
4. **Calm palette** — white space, teal accents, no loud reds except warnings  
5. **Consistency** — same card style, icons, button labels across pages  

### Button labels (standardize)

| Action | Label |
|--------|-------|
| Primary | Book Home Test |
| Secondary | Download Report |
| Contact | Call Now / WhatsApp |
| Catalog | Browse All Tests |
| Package | Book Appointment |

### CSS files to edit for theme changes

- `css/style.css` — variables in `:root`, base components  
- `css/premium.css` — hero, cards, animations, page-specific polish  
- `css/admin.css` — admin only  

---

## 12. Technical production checklist

### Security (done / verify)

- [x] CSRF on forms  
- [x] Admin login rate limiting  
- [x] Captcha on public forms  
- [x] Report OTP option  
- [x] `.htaccess` blocks `database.sqlite`  
- [x] `storage/ga4/` denied via `.htaccess`  
- [ ] Change default admin password  
- [ ] HTTPS enforced  
- [ ] `APP_DEBUG` false in production  
- [ ] Block `/tools/` on public server  

### Hosting setup

```php
// includes/config.local.php (create on server)
<?php
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'unity_cms');
define('DB_USER', '...');
define('DB_PASS', '...');
define('BASE_URL', 'https://www.yourdomain.com/');
define('APP_DEBUG', false);
```

### PHP extensions required

- `pdo_mysql` or `pdo_sqlite`  
- `curl` (GA4 API)  
- `openssl` (GA4 JWT)  
- `mbstring` (optional, code uses `substr` fallback)  

### Post-deploy tests

| Test | Expected |
|------|----------|
| Homepage loads all sections | No PHP errors |
| Book collection form | Email to admin + DB row |
| Contact inquiry | Email + admin inbox |
| Review submit | Pending in testimonials tab |
| Report download | OTP + PDF if uploaded |
| Sitemap | `sitemap.php` lists all pages |
| Mobile menu | Opens/closes, no layout break |
| GA4 realtime | Shows user when browsing site |

---

## 13. Content copy checklist

### Contact & NAP (must match everywhere)

| Field | Value (verify) |
|-------|----------------|
| Business name | Unity Clinical Laboratory |
| Phone | +91 98507 00268 |
| WhatsApp | 919850700268 |
| Email | info@unityclinicallab.com (must be live) |
| Region | Maharashtra, India |
| Address | _[Fill exact street + pincode]_ |

### Hero copy (recommended)

- **Tagline:** NABL Aligned Laboratory & Diagnostic Center  
- **Headline:** Accurate Diagnostics. Trusted Results.  
- **Subheadline:** Advanced blood, urine and health testing with fast, reliable reports. Correct testing is the first step to correct treatment.  

### Trust badges (footer / hero)

- NABL ALIGNED (not Certified unless accredited)  
- ESTD [real year]  
- Reports in 6–12 hrs  
- Home Collection Available  

### Offer banner (example)

> Home Sample Collection from 6:00 AM \| CBC from ₹200 \| Packages from ₹530

---

## 14. File reference

| Area | Key files |
|------|-----------|
| Homepage | `index.php`, `includes/sections/*.php` |
| Theme | `css/style.css`, `css/premium.css` |
| Header/footer | `includes/header.php`, `includes/footer.php` |
| CMS data | `includes/db.php`, `tools/production_seed.php` |
| Booking | `collection.php`, `api/book.php`, `includes/booking_helpers.php` |
| Reports | `download.php`, `api/report-otp.php`, `api/download-report.php` |
| Reviews | `includes/sections/testimonials.php`, `api/review.php` |
| SEO | `includes/marketing_helpers.php`, `sitemap.php`, `robots.php` |
| Locations | `locations.php`, `location.php`, `includes/locations_data.php` |
| GA4 | `includes/ga4_analytics.php`, `admin/analytics.php` |
| Admin CMS | `admin/cms.php`, `admin/includes/cms_*_tab.php` |
| JS | `js/main.js`, `admin/js/ga4-realtime.js` |

---

## Suggested timeline

| Week | Focus | Key deliverables |
|------|-------|------------------|
| **Week 1** | Phase 0 + Phase 1 | Seed data, password, address, map, NABL fix, real testimonials |
| **Week 2** | Phase 2 + Phase 3 (quick wins) | Sticky mobile bar, trust ribbon, favicon, rate card CTA, direct book links |
| **Week 3** | Phase 4 | GA4 live, Search Console, 2 blog posts, Business Profile |
| **Week 4** | Phase 5 + polish | Admin workflow training, SMTP/SMS, legal sign-off, go-live |

---

## Sign-off

| Role | Name | Date | Signed |
|------|------|------|--------|
| Lab owner | | | |
| Content | | | |
| Developer | | | |
| Go-live approval | | | |

---

*This document should be updated as tasks are completed. Mark items in your issue tracker or add checkboxes when implementing.*
