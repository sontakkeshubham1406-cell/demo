# 02. Functional Requirements Document (FRD)

## 1. Document Overview
This document specifies all functional requirements for the **Customer Storefront** and the **Back-Office Admin Panel**. Every requirement is given a unique identifier (`FR-CUST-xxx` for customer features, `FR-ADM-xxx` for admin features) to facilitate traceability across testing and implementation.

---

## 2. Customer Storefront Functional Requirements

### 2.1 Navigation & Discovery
- **FR-CUST-001 (Dynamic Megamenu)**: The system shall render a multi-tier navigation menu populated dynamically from active categories (Department -> Category -> Subcategory / Collection).
- **FR-CUST-002 (Predictive Search)**: The search bar shall provide auto-suggest results as the customer types (minimum 3 characters), displaying matching product titles, category suggestions, and small thumbnail previews.
- **FR-CUST-003 (Faceted Filtering)**: The product listing page (PLP) shall allow filtering by:
  - Size (XS, S, M, L, XL, etc.)
  - Color (visual color swatches)
  - Price Range (interactive slider or min-max inputs)
  - Category / Subcategory
  - Discount percentage (e.g., 20% and above)
  - Availability (In Stock only)
- **FR-CUST-004 (Multi-Criteria Sorting)**: The PLP shall support sorting products by:
  - Newest Arrivals (default)
  - Price: Low to High
  - Price: High to Low
  - Popularity / Best Sellers
  - Customer Rating (highest to lowest)

### 2.2 Product Details & Variant Selection
- **FR-CUST-005 (Color-Switched Gallery)**: When a customer clicks a color swatch on the Product Details Page (PDP), the image gallery shall dynamically switch to showcase photos of the garment in that specific color.
- **FR-CUST-006 (Size Selection & Out-of-Stock Alert)**: Sizes shall display as selectable buttons. If a specific size for the selected color has `0` inventory, it shall display as crossed out/disabled, with an optional "Notify Me" trigger.
- **FR-CUST-007 (Interactive Size Guide)**: The PDP shall feature a modal displaying exact garment measurements (chest, waist, hip, length) in both centimeters and inches, with instructions on how to measure.
- **FR-CUST-008 (Real-time Stock Urgency)**: If available stock for the selected variant is below the low-stock threshold (e.g., `< 5 items`), display a warning: *"Hurry, only 3 left in stock!"*.

### 2.3 Cart & Wishlist Management
- **FR-CUST-009 (Dual Cart Support)**: The system shall support guest carts via secure session tokens. Upon login or account creation, items in the guest cart shall automatically merge into the user's persistent database cart.
- **FR-CUST-010 (Quantity Adjustment & Limit Checks)**: Customers can increment or decrement item quantities in the cart up to a maximum limit (e.g., 5 units per variant or available stock, whichever is lower).
- **FR-CUST-011 (Variant Modification in Cart)**: Customers can change the selected size or color of an item directly from the cart drawer/page without deleting and re-browsing.
- **FR-CUST-012 (Wishlist Toggle)**: Authenticated users can save products to their personal wishlist with one-click toggling from cards or PDP.

### 2.4 Coupon Engine & Price Calculation
- **FR-CUST-013 (Coupon Code Validation)**: The checkout and cart shall allow inputting discount promo codes. The engine evaluates:
  - Expiration timestamp.
  - Minimum cart subtotal requirement.
  - First-time buyer condition (checks user order history).
  - Maximum cap on percentage discounts.
  - Category or SKU inclusions/exclusions.
- **FR-CUST-014 (Automatic Promotion Stacking Rules)**: The system shall prevent invalid discount stacking; coupon discounts apply on pre-tax subtotal after catalog promotional discounts unless marked as non-combinable.

### 2.5 Checkout & Order Placement
- **FR-CUST-015 (Address Book Management)**: Customers can save multiple delivery addresses (Home, Office), set a default shipping address, and specify a separate billing address if required.
- **FR-CUST-016 (Pin Code / Zip Code Delivery Validation)**: The checkout shall validate delivery availability against the delivery pin code and display estimated delivery dates.
- **FR-CUST-017 (Temporary Stock Reservation)**: When proceeding to payment, the system reserves the cart's variant items for 15 minutes. A countdown timer displays on the checkout screen.
- **FR-CUST-018 (Payment Initiation)**: The checkout generates a secure Razorpay Order ID on the backend and initiates the Razorpay modal on the client.

### 2.6 Customer Account, Tracking & Post-Purchase
- **FR-CUST-019 (Self-Service Order Cancellation)**: Customers can cancel an order directly from their account dashboard as long as the order status is `PENDING` or `CONFIRMED` (before `PACKED` / `SHIPPED`).
- **FR-CUST-020 (Return / Replacement Request)**: Within 7 days of delivery, customers can initiate a return or replacement for eligible items by selecting the reason (Wrong Size, Defective, Disliked Fabric) and uploading photos.
- **FR-CUST-021 (Tax Invoice Download)**: Customers can download a GST-compliant PDF invoice for any completed order.

---

## 3. Back-Office Admin Panel Functional Requirements

### 3.1 Product & Catalog Management
- **FR-ADM-001 (Multi-Variant Product Builder)**: Admins can create a master product and generate a 2D variant matrix (Colors × Sizes) with unique SKU codes, individual barcode strings, and distinct pricing per variant.
- **FR-ADM-002 (Media Manager & Color Mapping)**: Upload multiple high-resolution images per product, drag-and-drop to reorder, set primary thumbnail, and assign images to specific color variants.
- **FR-ADM-003 (Category Tree Management)**: Unlimited nesting support for categories, with drag-and-drop hierarchy reordering, banner image uploads, and SEO metadata configuration.

### 3.2 Inventory & Stock Control
- **FR-ADM-004 (Variant-Level Stock Adjustment)**: Admins can manually adjust stock for any SKU with mandatory reason tagging (`PO Receipt`, `Damage/Write-off`, `Stock Count Correction`, `Return Restock`).
- **FR-ADM-005 (Low Stock Alert Dashboard)**: Real-time filterable dashboard showing SKUs falling below their defined safety threshold.
- **FR-ADM-006 (Inventory Audit Trail)**: An immutable ledger recording timestamp, user ID, previous quantity, delta (+/-), new quantity, and reason code for every single stock mutation.

### 3.3 Order Fulfillment & Shipping
- **FR-ADM-007 (Fulfillment Workflow Stages)**: Admins move orders through controlled state transitions: `CONFIRMED` -> `PACKING` -> `SHIPPED` -> `DELIVERED`.
- **FR-ADM-008 (Shipment Dispatch & Tracking Linkage)**: Input shipping carrier name, Tracking Number / AWB, and dispatch timestamp, automatically triggering tracking notifications to the customer.
- **FR-ADM-009 (Bulk Order Actions)**: Bulk print packing slips, shipping labels, and tax invoices.

### 3.4 Returns, Replacements & Refund Management
- **FR-ADM-010 (Return Request Triage)**: Admins review customer return requests, inspect uploaded proof images, and approve/reject with notes.
- **FR-ADM-011 (Automated Razorpay Refund Trigger)**: Upon inspecting returned merchandise, the admin clicks "Issue Refund", which executes a server-to-server Razorpay Refund API call and logs the transaction ID.

### 3.5 Marketing, CMS & Customer Management
- **FR-ADM-012 (Dynamic Coupon Creator)**: Full UI for creating percentage or flat coupons with granular constraints (date range, total usage limit, per-customer usage limit, category eligibility).
- **FR-ADM-013 (Visual Banner & Section CMS)**: Upload and schedule homepage hero banners, promotional grids, and brand story sections with custom click-through URLs.
- **FR-ADM-014 (Customer 360 View)**: Comprehensive customer profile showing lifetime order value (LTV), total orders, active returns, saved addresses, and customer support notes.

### 3.6 Role-Based Access Control (RBAC) & Audit Logs
- **FR-ADM-015 (Granular Permission Assignment)**: Super Admins can define custom roles and assign read/write/delete permissions per module.
- **FR-ADM-016 (System Audit Log)**: Automatic logging of critical admin actions (price changes, role edits, refund approvals, stock adjustments) with user ID, IP address, and payload diff.

