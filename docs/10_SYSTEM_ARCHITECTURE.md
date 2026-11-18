# 10. System Architecture & Topology

## 1. High-Level Architectural Pattern
The platform follows a **Clean Modular Layered Architecture** with distinct presentation boundaries, shared domain service logic, and strict repository-based data access.

```
+-------------------------------------------------------------------------------+
|                           EDGE & SECURITY LAYER                               |
|        Cloudflare CDN (DDoS, WAF, Edge Caching, TLS 1.3 Termination)          |
+-------------------------------------------------------------------------------+
                                        │
                                        ▼
+-------------------------------------------------------------------------------+
|                            WEB SERVER & REVERSE PROXY                         |
|        Nginx (Static Asset Serving, Gzip/Brotli, FastCGI Cache, SSL)          |
+-------------------------------------------------------------------------------+
                                        │
                                        ▼
+-------------------------------------------------------------------------------+
|                         APPLICATION ENGINE (PHP 8.2+)                         |
|  ┌───────────────────────────────┐         ┌───────────────────────────────┐  |
|  │      Storefront Context       │         │      Admin Portal Context     │  |
|  │  (SSR Blade + Alpine.js UI)   │         │  (SSR Blade + Admin Panels)   │  |
|  └──────────────┬────────────────┘         └───────────────┬───────────────┘  |
|                 │                                          │                  |
|                 ▼                                          ▼                  |
|  ┌─────────────────────────────────────────────────────────────────────────┐  |
|  │                           SHARED DOMAIN SERVICES                        │  |
|  │  - CatalogService      - CartService           - OrderFulfillmentService│  |
|  │  - InventoryLockService- PricingCouponEngine   - RazorpayPaymentAdapter │  |
|  │  - AddressService      - ReturnRefundService   - AuditLogger            │  |
|  └────────────────────────────────────┬────────────────────────────────────┘  |
|                                       │                                       |
|                                       ▼                                       |
|  ┌─────────────────────────────────────────────────────────────────────────┐  |
|  │                         DATA ACCESS REPOSITORY LAYER                    │  |
|  │             (Prepared Statements PDO / Active Record ORM)               │  |
|  └─────────────────────────────────────────────────────────────────────────┘  |
+---------------------------------------┬───────────────────────────────────────+
                                        │
        ┌───────────────────────────────┴───────────────────────────────┐
        ▼                                                               ▼
+────────────────---------------+                             +────────────────-+
|      PERSISTENCE STORAGE      |                             |   CACHE & LOCKS |
|  MySQL 8.0+ / MariaDB 10.6+   |                             |   Redis Store   |
|  - ACID Transaction Engine    |                             |   - Sessions    |
|  - Row-Level Locking          |                             |   - Cart Cache  |
|  - Foreign Key Integrity      |                             |   - Stock Locks |
+───────────────────────────────+                             +────────────────-+
```

---

## 2. Asynchronous Queue & Background Worker Architecture
To ensure customer response times remain `< 200ms`, all non-blocking operations are offloaded to an asynchronous background worker pool (managed by Redis + Supervisor):

```mermaid
flowchart LR
    A[Checkout Completed] --> B[Push to Redis Queue]
    B --> C[Background Worker]
    C --> D[Generate PDF GST Invoice]
    C --> E[Send Order Confirmation Email]
    C --> F[Send Dispatch SMS]
    C --> G[Sync Inventory to Analytics]
```

### Queue Channels
1. **`high-priority`**: Razorpay webhook processing, stock release timers, password reset emails.
2. **`default`**: Order confirmation emails, SMS dispatch notifications, GST invoice PDF generation.
3. **`reports`**: Nightly CSV sales exports, inventory reconciliation checks.

---

## 3. Media & File Storage Architecture
Clothing eCommerce is highly image-intensive.
1. **Upload Pipeline**: Admin uploads high-resolution photography (`PNG`/`JPEG`).
2. **Processing**: Background worker strips EXIF data, generates progressive **WebP** variants in three standard dimensions:
   - Thumbnail: `150x200`
   - Card/Listing: `600x800`
   - High-Res Zoom: `1200x1600`
3. **Storage**: Assets are streamed to S3-compatible cloud storage (Cloudflare R2 / AWS S3 / MinIO).
4. **Delivery**: Served through global CDN edge cache with immutable `Cache-Control: public, max-age=31536000` headers.

---

## 4. Webhook Processing Topology (Razorpay)
```mermaid
sequenceDiagram
    autonumber
    participant RZ as Razorpay Gateway
    participant EP as /api/webhooks/razorpay
    participant DB as MySQL DB
    participant Q as Redis Queue
    participant W as Queue Worker

    RZ->>EP: POST Event (Signature Header)
    EP->>EP: Compute HMAC-SHA256 with Secret
    alt Invalid Signature
        EP-->>RZ: 401 Unauthorized
    else Valid Signature
        EP->>DB: Check if event_id exists in payment_webhooks
        alt Already Processed (Idempotency)
            EP-->>RZ: 200 OK (Skip duplicate)
        else Fresh Event
            EP->>DB: Insert event_id (is_processed = 0)
            EP->>Q: Dispatch ProcessWebhookJob
            EP-->>RZ: 200 OK (Fast Ack < 100ms)
            Q->>W: Consume Job
            W->>DB: Update order status & deduct inventory
            W->>DB: Mark payment_webhooks is_processed = 1
        end
    end
```

