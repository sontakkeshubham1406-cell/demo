# 16. Security & Performance Engineering Checklist

## 1. Security Architecture & Threat Defense

### 1.1 OWASP Top 10 Mitigation Matrix
| Threat | Architectural Defense Mechanism |
|---|---|
| **A01: Broken Access Control** | Enforce dynamic middleware gates (`CheckRolePermission`) on every admin route. Deny-by-default routing. Validate that customer can only view orders where `user_id == auth()->id()`. |
| **A02: Cryptographic Failures** | Strict TLS 1.3 in transit. Passwords hashed using **Argon2id**. API secrets & gateway credentials encrypted at rest using AES-256-GCM (`APP_KEY`). |
| **A03: Injection (SQL/Command)** | 100% parameterized queries via PDO prepared statements. Zero raw query string interpolation. Input sanitization on all search strings. |
| **A04: Insecure Design** | Two-phase inventory commitment prevents overselling. Pessimistic row locking on checkout. Server-side authoritative price recalculation. |
| **A05: Security Misconfiguration** | `APP_DEBUG=false` in production. Directory indexing disabled. Strict HTTP Security Headers: `HSTS`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Content-Security-Policy`. |
| **A06: Vulnerable & Outdated Components** | Automated Dependabot audits. Zero unvetted third-party npm or Composer packages. Locked lockfiles (`composer.lock`, `package-lock.json`). |
| **A07: Identification & Auth Failures** | Rate limit login endpoints (max 5 attempts per 15 min per IP). Password minimum requirements (8+ chars, numbers, symbols). Secure session regeneration on authentication. |
| **A08: Software & Data Integrity Failures** | Strict HMAC-SHA256 signature verification for Razorpay payment callbacks and webhook events. Idempotent webhook tracking table. |
| **A09: Security Logging & Monitoring Failures** | All admin operations (price changes, status overrides, refunds, role updates) written to immutable `audit_logs` table with user ID, IP address, and payload delta. |
| **A10: Server-Side Request Forgery (SSRF)** | Restrict external HTTP client requests to validated whitelist URLs. Prohibit internal network IP fetches (`127.0.0.1`, `169.254.169.254`). |

### 1.2 PCI-DSS Compliance Posture
- **SAQ-A Scope**: The platform uses Razorpay Checkout (client-side modal / iframe).
- **Zero Card Data Storage**: No PAN (Primary Account Number), CVV, expiry dates, or bank credentials are ever received, processed, or stored on application servers.
- **Cardholder Data Flow**: Browser transmits card data directly to Razorpay's PCI-DSS Level 1 certified servers; the application only receives payment tokens (`razorpay_payment_id`) and status indicators.

### 1.3 Secure File Upload Controls
- Product images strictly validated for MIME type (`image/jpeg`, `image/png`, `image/webp`).
- Uploaded files are re-encoded and converted to WebP via server-side image processing, stripping all malicious EXIF/PHP payload headers.
- File storage paths randomized via UUIDs; execution permissions disabled on upload directories (`chmod 755`, no script execution).

---

## 2. High-Performance Optimization Strategy

### 2.1 Caching Hierarchy
```
[User Browser]
      │
      ▼
[Cloudflare CDN Edge] ── Cache Static Assets (CSS, JS, WebP Images) for 1 Year
      │
      ▼
[Nginx FastCGI Cache] ── Cache Anonymous PLP & PDP Pages for 5-15 Minutes
      │
      ▼
[Redis Cache Layer]   ── Cache Category Tree, Mega Menu, Store Settings
      │
      ▼
[MySQL Persistence]   ── Read-Replicas for Complex Reporting; Indexed Lookups
```

### 2.2 Database Query Optimization
- All foreign keys indexed.
- Composite index on `products(is_active, is_featured, created_at)` for instant homepage loading.
- Composite index on `products(category_id, is_active, base_price)` for faceted category filtering.
- Cursor-based or indexed offset pagination (`LIMIT 24 OFFSET x`) to avoid full-table scans.
- Strict prevention of N+1 query problems via eager loading:
  ```php
  $products = Product::with(['variants.size', 'variants.color', 'images', 'brand'])
      ->where('is_active', true)
      ->paginate(24);
  ```

### 2.3 Frontend & Core Web Vitals Optimization
- **Image Delivery**: Progressive `.webp` format served with responsive `srcset` attributes and native `loading="lazy"`.
- **CSS Delivery**: Critical CSS inlined in `<head>`; full Tailwind bundle minified to `< 15KB`.
- **JavaScript Execution**: Alpine.js loaded with `defer`. Third-party tracking scripts loaded via Web Workers or deferred after `DOMContentLoaded`.
- **SEO & Structured Data**: Dynamic OpenGraph tags, canonical links, XML sitemap auto-generation (`/sitemap.xml`), and schema.org `Product` JSON-LD metadata for Google Shopping snippets.

