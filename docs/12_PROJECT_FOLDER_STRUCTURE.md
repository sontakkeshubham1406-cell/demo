# 12. Project Folder & File Structure

## 1. Architectural Directory Layout
The folder structure follows modern domain-driven and modular standards. Storefront controllers, Admin controllers, API controllers, and shared Core domain services are organized with clean separation of concerns.

```
clothing-ecommerce/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ReleaseExpiredStockReservations.php  # Scheduled cron to release 15-min stock locks
│   │       └── GenerateDailySalesReport.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                              # Back-Office Admin Management
│   │   │   │   ├── Auth/
│   │   │   │   │   └── AdminAuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── VariantController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── ReturnRefundController.php
│   │   │   │   ├── CouponController.php
│   │   │   │   ├── BannerController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── RolePermissionController.php
│   │   │   │   └── SettingController.php
│   │   │   │
│   │   │   ├── Storefront/                         # Customer Facing Storefront
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── ProductCatalogController.php
│   │   │   │   ├── CartController.php
│   │   │   │   ├── CheckoutController.php
│   │   │   │   ├── PaymentCallbackController.php
│   │   │   │   ├── CustomerAccountController.php
│   │   │   │   ├── OrderTrackingController.php
│   │   │   │   ├── ReturnExchangeController.php
│   │   │   │   └── StaticPageController.php
│   │   │   │
│   │   │   └── Webhooks/                           # Secure Inbound Webhook Receivers
│   │   │       └── RazorpayWebhookController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── VerifyCsrfToken.php
│   │   │   ├── CheckRolePermission.php             # Dynamic RBAC Gate
│   │   │   ├── VerifyRazorpayWebhookSignature.php   # HMAC-SHA256 signature verifier
│   │   │   └── EnforceSecureHeaders.php
│   │   │
│   │   └── Requests/                               # Form Request Validations
│   │       ├── Admin/
│   │       │   ├── StoreProductRequest.php
│   │       │   ├── UpdateInventoryRequest.php
│   │       │   └── ProcessRefundRequest.php
│   │       └── Storefront/
│   │           ├── AddToCartRequest.php
│   │           ├── ApplyCouponRequest.php
│   │           └── InitiateCheckoutRequest.php
│   │
│   ├── Models/                                     # Eloquent Relational Entities (32 Tables)
│   │   ├── User.php
│   │   ├── UserAddress.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── ProductImage.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Size.php
│   │   ├── Color.php
│   │   ├── Inventory.php
│   │   ├── InventoryMovement.php
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Wishlist.php
│   │   ├── WishlistItem.php
│   │   ├── Coupon.php
│   │   ├── CouponUsage.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Payment.php
│   │   ├── PaymentWebhook.php
│   │   ├── Shipment.php
│   │   ├── ReturnRequest.php
│   │   ├── Refund.php
│   │   ├── Review.php
│   │   ├── Banner.php
│   │   ├── Notification.php
│   │   ├── Setting.php
│   │   └── AuditLog.php
│   │
│   ├── Services/                                   # Core Shared Domain Logic
│   │   ├── CatalogService.php
│   │   ├── CartService.php
│   │   ├── CheckoutService.php
│   │   ├── InventoryLockService.php                # Redis/Pessimistic locking logic
│   │   ├── CouponPricingEngine.php
│   │   ├── Payments/
│   │   │   ├── PaymentGatewayInterface.php
│   │   │   └── RazorpayServiceAdapter.php          # Razorpay API client & signature verifier
│   │   ├── OrderFulfillmentService.php
│   │   ├── InvoicePdfService.php                   # DomPDF generator
│   │   └── AuditLogService.php
│   │
│   └── Jobs/                                       # Asynchronous Background Workers
│       ├── ProcessRazorpayWebhookJob.php
│       ├── SendOrderConfirmationEmailJob.php
│       ├── GenerateInvoicePdfJob.php
│       └── OptimizeProductImageJob.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── razorpay.php                                # Keys, secrets, webhook secrets
│   └── filesystems.php                             # S3/R2 bucket settings
│
├── database/
│   ├── migrations/                                 # Complete 32 database migration scripts
│   └── seeders/
│       ├── RolePermissionSeeder.php                # Seed initial RBAC permissions & Super Admin
│       ├── ApparelTaxonomySeeder.php               # Seed Men, Women, Kids, Shirts, Jeans
│       ├── SizeColorSeeder.php                     # Seed XS-3XL & 20 apparel colorways
│       └── SystemSettingSeeder.php
│
├── public/
│   ├── css/
│   ├── js/
│   └── index.php                                   # Single point of entry
│
├── resources/
│   ├── css/
│   │   └── app.css                                 # Tailwind directives
│   ├── js/
│   │   └── app.js                                  # Alpine.js initialization
│   └── views/
│       ├── layouts/
│       │   ├── storefront.blade.php                # Customer base layout (Header, Nav, Footer)
│       │   └── admin.blade.php                     # Admin base layout (Sidebar, Topbar)
│       ├── storefront/
│       │   ├── home/index.blade.php
│       │   ├── catalog/
│       │   │   ├── index.blade.php                 # PLP with filters
│       │   │   └── show.blade.php                  # PDP with variant selector & size guide
│       │   ├── cart/index.blade.php
│       │   ├── checkout/index.blade.php
│       │   └── account/
│       │       ├── orders.blade.php
│       │       └── tracking.blade.php
│       └── admin/
│           ├── dashboard/index.blade.php
│           ├── products/
│           │   ├── index.blade.php
│           │   └── create.blade.php                # 2D Variant matrix builder
│           ├── inventory/index.blade.php
│           └── orders/
│               ├── index.blade.php
│               └── show.blade.php                  # Order inspector & fulfillment actions
│
├── routes/
│   ├── web.php                                     # Storefront customer web routes
│   ├── admin.php                                   # Authenticated Admin panel routes
│   └── api.php                                     # REST API & Webhook routes
│
├── storage/
│   ├── app/
│   │   └── invoices/                               # Generated PDF invoices
│   └── logs/
│
├── tests/
│   ├── Unit/
│   │   ├── CouponPricingEngineTest.php
│   │   └── InventoryLockServiceTest.php
│   └── Feature/
│       ├── CheckoutFlowTest.php
│       └── RazorpayWebhookTest.php
│
├── .env.example
├── composer.json
├── package.json
└── tailwind.config.js
```

