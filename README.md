# DEMO — Haute Couture eCommerce Platform

Welcome to **DEMO**, an enterprise-grade, modern, and minimalist luxury clothing eCommerce platform handcrafted with **PHP**, **Tailwind CSS**, **Alpine.js**, and integrated with **Razorpay Payment Gateway**.

---

## ✨ Features & Architecture

- **Storefront & Catalog**:
  - Editorial Luxury Design (Playfair Display & Plus Jakarta Sans typography)
  - Interactive Mega Menu & Multi-Level Dynamic Apparel Categories (Men, Women, Kids, Accessories)
  - Rich Product Pages with multi-angle galleries, interactive size selectors, and color swatches
  - Dynamic interactive Size Guide Modal with cm/inches toggling
  - Slide-out Cart Drawer with instant real-time subtotal calculations
  - Full-featured Cart & Checkout with dynamic coupon engine (e.g., `FIRST10`, `LUXE1000`)
  - Dual-mode Razorpay Payment simulation & webhook verification
  - User Authentication, Registration, and Order Tracking

- **Executive Admin Portal (`/admin`)**:
  - Live Sales Analytics, Revenue, and Inventory Metrics
  - Product Catalog Management (Variants, SKUs, Inventory, Multi-Images)
  - Order Management & Processing (Pending -> Confirmed -> Shipped -> Delivered)
  - Printable Haute Couture Tax Invoices
  - Inventory Stock Alerts & Movement Audit Logs
  - Coupon Code Creation & Usage Analytics

---

## 🚀 Quick Start (Local Development)

### Prerequisites
- PHP 8.1+ with SQLite / MySQL extensions enabled.

### Running Locally
You can double-click `run.bat` or run:
```bash
php -S 127.0.0.1:8000 -t public
```

- **Storefront URL**: [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Admin Panel URL**: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

### Demo Credentials
| Role | Email | Password |
|---|---|---|
| **Super Admin** | `admin@demo.com` | `Admin@123` |
| **Catalog Manager** | `manager@demo.com` | `Manager@123` |
| **Customer** | `customer@example.com` | `Customer@123` |

*(Quick 1-click Auto-Fill buttons are available on login pages).*

---

## ☁️ Deployment

- **Vercel**: Configured via `vercel.json` with the `vercel-php` serverless runtime and auto-syncing SQLite database.
- **GitHub Repository**: [https://github.com/sontakkeshubham1406-cell/demo](https://github.com/sontakkeshubham1406-cell/demo)
