# 04. Complete Module Breakdown

## 1. Overview
The platform is organized into distinct, modular functional domains. Each module possesses a clearly bounded responsibility, clean input/output interfaces, and documented dependencies.

---

## 2. Customer Storefront Modules

```
                    +--------------------------------+
                    |      Customer Storefront       |
                    +---------------+----------------+
                                    |
     +--------------+---------------+--------------+--------------+
     |              |               |              |              |
+----+----+   +-----+----+    +-----+----+   +-----+----+   +-----+----+
| Catalog |   | Cart &   |    | Checkout |   | Customer |   | CMS &    |
| & Search|   | Wishlist |    | & Payment|   | Account  |   | Support  |
+---------+   +----------+    +----------+   +----------+   +----------+
```

### Module C1: Catalog, Navigation & Discovery
- **Sub-components**: Dynamic Header Megamenu, Breadcrumbs, Category Landing Grid, Product Listing Page (PLP), Quick-View Modal, Filter Drawer, Sort Dropdown.
- **Data Inputs**: Category slug, Search query string, Filter parameters (`size[]`, `color[]`, `price_min`, `price_max`, `sort`).
- **Data Outputs**: Paginated product list, active filter counts, facet counts, category metadata.
- **Dependencies**: Category Service, Product Query Service, Media Storage.

### Module C2: Product Details (PDP) & Variant Selection
- **Sub-components**: High-res Swiper/Zoom Gallery, Colorway Selector, Size Selector Buttons, Stock Availability Indicator, Interactive Measurement Guide, Customer Review List, Related Products Carousel.
- **Data Inputs**: Product slug, Selected color code, Selected size ID.
- **Data Outputs**: Active variant details (SKU, price, discount, real-time stock status), associated color gallery images.
- **Dependencies**: Variant Service, Inventory Cache, Review Service.

### Module C3: Cart & Wishlist Engine
- **Sub-components**: Slide-over Mini Cart Drawer, Dedicated Cart Page, Wishlist Manager, Variant Switcher Modal.
- **Data Inputs**: `variant_id`, `quantity`, session identifier (for guests) or `user_id` (authenticated).
- **Data Outputs**: Cart items breakdown, subtotal, discount total, tax estimate, shipping eligibility indicator.
- **Dependencies**: Session Store, Inventory Soft-Check Service.

### Module C4: Coupon & Discount Calculation Engine
- **Sub-components**: Coupon Input Box, Applied Discount Badge, Real-time Savings Breakdown.
- **Data Inputs**: `coupon_code`, current cart context (`subtotal`, `items`, `user_id`).
- **Data Outputs**: Applied discount amount, updated payable total, validation message/error.
- **Dependencies**: Promotion Rule Evaluator, User Order History.

### Module C5: Checkout & Razorpay Payment Integration
- **Sub-components**: Step 1: Customer Contact & Shipping Address; Step 2: Billing Address & Delivery Method; Step 3: Payment Selection & Razorpay Modal Trigger; Step 4: Order Confirmation Summary.
- **Data Inputs**: Address ID / new address form, selected payment method.
- **Data Outputs**: Razorpay Order ID, Signature verification request, finalized Order Confirmation.
- **Dependencies**: Tax Service, Shipping Calculation Service, Razorpay Client, Stock Reservation Service.

### Module C6: Customer Account & Order Tracking
- **Sub-components**: Registration/Login Form, Password Reset Flow, Address Book Manager, Order History Grid, Order Timeline Tracker, Return/Replacement Initiation Form, Invoice PDF Stream.
- **Data Inputs**: Order ID, tracking token, return reason & photo uploads.
- **Data Outputs**: Visual status stepper (Confirmed -> Shipped -> Out for Delivery -> Delivered), courier tracking URL.
- **Dependencies**: Authentication Service, Order Service, Courier Tracker.

---

## 3. Back-Office Admin Management Modules

```
                    +--------------------------------+
                    |       Admin Management         |
                    +---------------+----------------+
                                    |
     +--------------+---------------+--------------+--------------+
     |              |               |              |              |
+----+----+   +-----+----+    +-----+----+   +-----+----+   +-----+----+
| Catalog |   | Orders & |    | Customers|   | Mktg &   |   | Settings |
| & Stock |   | Logistics|    | & Support|   | Analytics|   | & RBAC   |
+---------+   +----------+    +----------+   +----------+   +----------+
```

### Module A1: Catalog & Variant Master Administration
- **Sub-components**: Master Product CRUD, 2D Variant Matrix Generator (Size × Color), Bulk Image Upload & Color Swatch Mapper, Brand & Category Hierarchy Editor.
- **Responsibilities**: Manage product titles, rich descriptions, wash-care tags, SEO slugs, and aggregate base prices.

### Module A2: Multi-SKU Inventory & Stock Ledger
- **Sub-components**: SKU Stock Adjustment Form, Bulk CSV Stock Importer, Low-Stock Alert Monitor, Real-time Stock Reservation Inspector, Immutable Stock Movement Audit Trail.
- **Responsibilities**: Provide real-time tracking of on-hand, reserved, and available-to-promise inventory.

### Module A3: Order Fulfillment & Life-cycle Management
- **Sub-components**: Order Processing Dashboard, Status Transition Workflow Engine, Packing Slip Generator, AWB & Courier Integration Form, Cancellation & Return Approval Manager.
- **Responsibilities**: Ensure seamless physical order fulfillment with validation checks preventing illegal status jumps.

### Module A4: Customer 360 & Relationship Management
- **Sub-components**: Customer Listing Table, Deep Profile Inspector (Lifetime Value, Average Order Value, Order History), Account Suspension Switch, Internal Admin Notes.
- **Responsibilities**: Centralize customer engagement history and audit communication.

### Module A5: Marketing, Coupons & CMS Management
- **Sub-components**: Advanced Coupon Rule Builder, Hero Banner Slider Manager, Static Policy Pages (About Us, Terms, Privacy, Size Guides), Announcement Bar Manager.
- **Responsibilities**: Enable marketing campaigns without requiring software code deployments.

### Module A6: Financial Analytics & Business Intelligence
- **Sub-components**: Executive KPI Cards (Gross Merchandise Value, Net Revenue, Units Sold), Sales by Category/SKU Charts, Return Rate Analytics, Payment Reconciliation Reports, Excel/CSV Exporter.
- **Responsibilities**: Provide actionable data intelligence for merchandising and stock replenishment.

### Module A7: Role-Based Access Control (RBAC) & System Settings
- **Sub-components**: Dynamic Role Permission Matrix Editor, Admin User Manager, System Audit Trail Viewer, Store Global Configuration (Taxes, Shipping flat fees, Free shipping thresholds, Gateway keys).
- **Responsibilities**: Enforce principle of least privilege across operational staff.

