# 05. Customer Storefront & Admin Panel Sitemaps

## 1. Customer Storefront Sitemap & URL Architecture

```
/ (Homepage)
├── /collections
│   ├── /collections/:department-slug (e.g. /collections/men)
│   ├── /collections/:category-slug (e.g. /collections/shirts)
│   └── /collections/:subcategory-slug (e.g. /collections/linen-shirts)
├── /products/:product-slug (Product Details Page - PDP)
├── /search?q=:keyword
├── /cart (Dedicated Shopping Cart)
├── /checkout
│   ├── /checkout/shipping (Step 1: Address & Contact)
│   ├── /checkout/payment (Step 2: Razorpay Trigger)
│   └── /checkout/success/:order-id (Step 3: Confirmation Receipt)
├── /account
│   ├── /account/login
│   ├── /account/register
│   ├── /account/forgot-password
│   ├── /account/reset-password/:token
│   ├── /account/profile (Overview & Profile Edit)
│   ├── /account/addresses (Address Book)
│   ├── /account/orders (Order History)
│   │   ├── /account/orders/:order-id (Order Details & Tracking)
│   │   ├── /account/orders/:order-id/invoice.pdf (GST Invoice Download)
│   │   └── /account/orders/:order-id/return (Return/Exchange Request)
│   └── /account/wishlist (Saved Items)
└── /pages
    ├── /pages/about-us
    ├── /pages/contact
    ├── /pages/size-guide
    ├── /pages/shipping-policy
    ├── /pages/returns-exchanges
    ├── /pages/terms-and-conditions
    └── /pages/privacy-policy
```

---

## 2. Admin Panel Sitemap & URL Architecture

```
/admin/login (Admin Secure Sign-in)
/admin (Dashboard Overview)
├── /admin/analytics (Sales, Inventory, & Refund Reports)
│   └── /admin/analytics/export (CSV/Excel Generation)
│
├── /admin/catalog (Catalog Management)
│   ├── /admin/products (Product Listing with Stock Status)
│   │   ├── /admin/products/create (Product Builder & Variant Generator)
│   │   ├── /admin/products/:id/edit (Product Editor & Gallery)
│   │   └── /admin/products/:id/variants (SKU & Price Overrides)
│   ├── /admin/categories (Category Tree & Reordering)
│   ├── /admin/brands (Brand Management)
│   └── /admin/attributes (Sizes, Colors, Material Specifications)
│
├── /admin/inventory (Stock Control & Audit)
│   ├── /admin/inventory/adjustments (Manual Stock In / Stock Out)
│   ├── /admin/inventory/low-stock (Safety Threshold Alerts)
│   └── /admin/inventory/movements (Full Immutable Stock Audit Trail)
│
├── /admin/orders (Order Fulfillment & Processing)
│   ├── /admin/orders?status=:status (Filter by Pending, Confirmed, Shipped, etc.)
│   ├── /admin/orders/:id (Detailed Order Inspector & Fulfillment Action)
│   ├── /admin/orders/:id/invoice (Print GST Invoice)
│   ├── /admin/orders/:id/packing-slip (Print Warehouse Slip)
│   └── /admin/orders/bulk (Bulk Dispatch / Status Update)
│
├── /admin/returns (Returns & Exchanges)
│   ├── /admin/returns/requests (Pending Return Triage)
│   └── /admin/returns/:id (Inspection, Restock, & Refund Trigger)
│
├── /admin/customers (Customer Base)
│   ├── /admin/customers (Customer Listing with LTV & Order Counts)
│   └── /admin/customers/:id (Deep Profile, Addresses, Order History)
│
├── /admin/marketing (Promotions & Content)
│   ├── /admin/marketing/coupons (Promo Codes, Usage Caps, Rules)
│   ├── /admin/marketing/banners (Homepage Slider & Promo Grids)
│   └── /admin/marketing/pages (Static Policy CMS Editor)
│
└── /admin/system (Administration & Security)
    ├── /admin/system/roles (Dynamic Role & Permission Matrix)
    ├── /admin/system/users (Admin Staff Accounts)
    ├── /admin/system/settings (Global Store, Shipping, Tax, & Razorpay Keys)
    └── /admin/system/audit-logs (Admin Action Audit Ledger)
```

