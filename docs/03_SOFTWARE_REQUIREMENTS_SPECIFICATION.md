# 03. Software Requirements Specification (SRS)

## 1. Introduction
### 1.1 Purpose
This Software Requirements Specification (SRS) establishes the technical, operational, and performance requirements for the clothing eCommerce platform. It follows IEEE Std 830-1998 guidelines to serve as the definitive contract between stakeholders and engineering teams.

### 1.2 Scope
The software system comprises:
- **Client Tier**: Responsive Web Storefront (Mobile & Desktop).
- **Admin Tier**: Back-Office Web Administration Console.
- **Application Tier**: Modern Modular PHP Service Engine exposing RESTful APIs and Blade/Server-Rendered UI with Alpine.js reactive components.
- **Data Tier**: Relational Database Management System (MySQL 8.0+ / MariaDB 10.6+) and In-Memory Data Store (Redis) for session and caching.
- **External Integration Tier**: Razorpay Payment Gateway, Courier Logistics APIs, Transactional Mail/SMS Gateways, Cloud Object Storage (S3-compatible).

---

## 2. Overall Description

### 2.1 Product Perspective
```
+-------------------------------------------------------------+
|                     Client Presentation                     |
|  [ Mobile Browser ]  [ Desktop Browser ]  [ Admin Console ] |
+-------------------------------------------------------------+
                              | HTTPS (TLS 1.3)
                              v
+-------------------------------------------------------------+
|             Reverse Proxy & Edge (Nginx + Cloudflare)       |
|    - SSL Termination   - WAF   - Rate Limiting   - Static CDN|
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|                 Application Engine (PHP 8.2+)               |
|  - Routing & Middleware (Auth, CSRF, Permissions)           |
|  - Business Domain Services (Cart, Order, Inventory, Promo) |
|  - Payment Adapter (Razorpay SDK + Webhook Verifier)        |
+-------------------------------------------------------------+
               |                               |
               v                               v
+-----------------------------+  +----------------------------+
|  Cache & Lock Layer (Redis) |  | Relational DB (MySQL 8.0)  |
|  - Session state            |  | - ACID transactions        |
|  - Cart caching             |  | - Row-level locking        |
|  - Stock reservation locks  |  | - Immutable audit logs     |
+-----------------------------+  +----------------------------+
```

### 2.2 User Characteristics
1. **End Consumers**: Varying tech literacy; expect instantaneous page loading, intuitive thumb-friendly mobile checkout, transparent pricing, and visual feedback for apparel colors and sizing.
2. **Operations & Fulfillment Staff**: High-frequency order fulfillment operators requiring barcode-friendly interfaces, bulk order state updates, and rapid label generation.
3. **Merchandising & Catalog Managers**: Product creators needing bulk image upload, variant matrix generation, and seasonal discount configuration.
4. **Super Administrators**: Complete platform oversight, financial ledger auditing, user role configuration, and gateway credential administration.

---

## 3. External Interface Requirements

### 3.1 User Interfaces
- Responsive UI adhering to mobile-first responsive breakpoints:
  - Mobile Small: `320px` to `480px`
  - Tablet: `768px` to `1024px`
  - Desktop: `1280px` to `1920px+`
- Cross-browser compatibility: Chrome (latest 3 versions), Safari (iOS 15+ & macOS), Firefox, Edge.

### 3.2 Software Interfaces
| Service | Protocol | Role / Responsibility |
|---|---|---|
| **Razorpay Standard SDK** | HTTPS / JSON / Webhooks | Order creation, payment capture, verification, refunds. |
| **Transactional Email (SMTP / SES / Resend)** | TLS SMTP / REST API | Order receipts, reset links, status dispatches. |
| **Transactional SMS / WhatsApp** | REST API | OTP verification, delivery tracking links. |
| **Cloud Storage (AWS S3 / Cloudflare R2 / MinIO)** | S3 API | Secure, redundant storage for product imagery and invoices. |
| **PDF Generation Engine (Dompdf / Snappy)** | Server-side binary/library | Dynamic GST-compliant invoice and packing slip generation. |

### 3.3 Communications Interfaces
- All client-to-server and server-to-external communication must strictly use **HTTPS with TLS 1.3** (minimum TLS 1.2).
- Webhook callbacks must be authenticated using **HMAC-SHA256 signature verification** against the pre-shared secret.

---

## 4. System Attributes & Constraints

### 4.1 Reliability & Fault Tolerance
- **Transactional Atomicity**: All checkout operations (order item insertion, payment record initialization, stock reservation deduction) must execute inside ACID database transactions.
- **Webhook Idempotency**: Each inbound webhook must log its unique event ID (`event_id`). Repeated incoming events with the same ID must immediately return HTTP `200 OK` without executing duplicate database mutations.

### 4.2 Security Constraints
- **Zero Cardholder Data Storage**: No credit/debit card numbers, CVVs, or bank credentials shall ever touch application memory or database storage.
- **Password Storage**: Passwords hashed strictly using **Argon2id** (memory cost: 65536 KB, time cost: 4, parallelism: 1) or **Bcrypt** with minimum work factor 12.
- **SQL Injection Prevention**: 100% of database queries executed via parameterized prepared statements (PDO). Zero string-concatenated SQL queries permitted.
- **Session Security**: Session cookies marked `HttpOnly`, `Secure`, and `SameSite=Lax`.

### 4.3 Performance Requirements
- **Server Response Time (TTFB)**: Server-rendered page TTFB < 200ms on cached routes, < 450ms on dynamic cart/checkout routes.
- **Database Query Latency**: 95th percentile query execution time < 50ms; all filtering columns (price, category, size, color) covered by composite indexes.
- **Media Optimization**: All product images automatically converted to modern `.webp` format in multiple resolutions (thumbnail `150x200`, listing `600x800`, zoom `1200x1600`).

