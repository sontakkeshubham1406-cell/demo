# 11. Technology Stack Evaluation & Architectural Decision

## 1. Executive Summary
The client has expressed a clear preference for a **PHP + MySQL/MariaDB** foundation. Based on extensive eCommerce benchmarks, development velocity, security posture, and production maintainability, this evaluation assesses framework options, frontend toolings, database choices, and peripheral services.

---

## 2. Backend Stack Evaluation

### Option A: Laravel 11 (PHP 8.2+) — **RECOMMENDED**
- **Pros**:
  - Built-in secure authentication scaffolding, CSRF protection, and Argon2id password hashing.
  - Eloquent ORM with native transaction management and pessimistic row locking (`lockForUpdate()`).
  - Native Redis queue integration (Laravel Horizon/Queue workers) for asynchronous email, webhook handling, and PDF invoice generation.
  - Official Razorpay PHP SDK compatibility (`razorpay/razorpay`).
  - Active ecosystem, continuous security patches, and battle-tested in high-volume eCommerce.
- **Cons**: Slightly higher memory footprint than raw micro-frameworks (mitigated by OPcache and PHP 8.2 JIT).

### Option B: Custom Modern Native PHP MVC (Vanilla PHP 8.2 + PDO + Composer)
- **Pros**: Extremely low footprint; zero framework lock-in.
- **Cons**: Requires building security primitives from scratch (session fixation guards, CSRF token middleware, queue workers, ORM migrations, audit logging). Higher risk of introducing security vulnerabilities and significantly higher maintenance cost.

> **Decision**: **PHP 8.2+ with Laravel 11** is selected. It honors the user's PHP requirement while providing enterprise-grade security, rapid development, and native queueing.

---

## 3. Database Layer Evaluation

### MySQL 8.0+ vs. MariaDB 10.6+
- **MySQL 8.0**: Superior JSON function performance (used for storing frozen shipping/billing snapshots), robust native row-level locking, and exact parity with cloud managed services (AWS RDS, DigitalOcean Managed DB).
- **MariaDB 10.6**: Open-source purity; excellent thread pool implementation.

> **Decision**: **MySQL 8.0+** (or MariaDB 10.6+ interchangeably via standard InnoDB dialect). All tables use the `utf8mb4_unicode_ci` character set to support full Unicode text and emojis.

---

## 4. Frontend & Styling Architecture

### CSS Framework: Tailwind CSS v3 vs. Bootstrap 5
| Dimension | Tailwind CSS v3 | Bootstrap 5 |
|---|---|---|
| **Design Flexibility** | Total customizability; ideal for luxury, minimalist fashion branding. | Generic "Bootstrap look"; requires heavy CSS overrides to appear unique. |
| **Bundle Size** | Purged down to `< 15KB` in production. | `~60KB` base CSS + JS bundle. |
| **Mobile Breakpoints** | Utility-first responsive design (`sm`, `md`, `lg`, `xl`). | 12-column grid. |

> **Decision**: **Tailwind CSS v3** is selected for custom luxury brand aesthetic and minimal production bundle size.

### Client Interactivity: Alpine.js vs. React/Vue SPA
- Full SPA (React/Vue) adds hydration overhead, complex state management, and hurts SEO unless paired with Next.js/Nuxt SSR (introducing a secondary Node.js runtime).
- **Blade + Alpine.js**: Gives instantaneous server-side rendering for optimal Google SEO indexing, with ultra-lightweight (`~12KB`) reactive components for color switching, size selection, mini-cart slide-overs, and image zooms.

> **Decision**: **Laravel Blade Templates + Alpine.js** (Server-Side Rendered for SEO + Reactive Micro-interactions).

---

## 5. Integration Stack Summary

| Domain | Recommended Technology | Justification |
|---|---|---|
| **Programming Language** | **PHP 8.2 / 8.3** | High-performance JIT, strict typing, modern attributes. |
| **Backend Framework** | **Laravel 11.x** | Enterprise MVC, Eloquent ORM, built-in queues & auth. |
| **Primary Database** | **MySQL 8.0+ (InnoDB)** | Relational integrity, row-locking, JSON document support. |
| **Cache & Queue Broker**| **Redis 7.x** | Sub-millisecond sessions, cart caching, stock reservation locks. |
| **Styling Engine** | **Tailwind CSS v3** | Bespoke fashion design tokens, utility-first responsiveness. |
| **Client Interactivity**| **Alpine.js v3** | Declarative micro-reactivity without SPA bloat. |
| **Payment Gateway** | **Razorpay Standard SDK** | Official India compliance, UPI, Cards, NetBanking, Webhooks. |
| **PDF Invoice Generator**| **Barryvdh/Laravel-DomPDF** | Server-side GST-compliant tax invoice and packing slip generation. |
| **Image Processing** | **Intervention Image v3 (WebP)** | Dynamic resizing, compression, progressive WebP delivery. |
| **Object File Storage** | **S3-compatible (Cloudflare R2 / AWS S3)** | Highly durable, zero-egress or low-cost media asset hosting. |
| **Transactional Email** | **Amazon SES / Mailgun / Resend** | 99.5%+ inbox deliverability for order confirmations and resets. |
| **Web Server** | **Nginx 1.24+ (FastCGI)** | High-concurrency event-driven reverse proxy and SSL termination. |

