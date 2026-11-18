# 06. User Role & Permission Matrix (RBAC)

## 1. Architectural Strategy
Rather than hardcoding static role checks (e.g., `if ($user->role == 'admin')`) across the codebase, the platform implements a **Dynamic Granular Permission Engine**:
1. **Permissions** represent atomic capabilities structured as `module.action` (e.g., `products.create`, `orders.ship`, `refunds.process`).
2. **Roles** are dynamic collections of permissions stored in the database (`roles`, `permissions`, `role_permissions`).
3. **Users** are assigned one or more roles.
4. Access checks are executed via middleware using semantic capability gates (e.g., `$this->authorize('orders.refund')`).

---

## 2. Standard Admin Persona Roles
- **Super Admin**: Complete platform ownership, credentials, financial control, and role configuration.
- **Store Admin**: General operational director with full access to catalog, orders, and promotions, but no access to system credentials or role permission changes.
- **Product Manager**: Catalog curation, variant matrix definition, pricing, discounts, and brand management.
- **Order Fulfillment Manager**: Processing confirmed orders, generating packing slips, updating AWB tracking, handling couriers.
- **Inventory Manager**: Physical stock receipt, batch adjustments, discrepancy write-offs, and warehouse audits.
- **Customer Support Agent**: View-only customer profile access, order tracking inquiries, return ticket review (no direct refund approval power).

---

## 3. Dynamic Permission Mapping Matrix

| Functional Module | Atomic Permission Code | Super Admin | Store Admin | Product Manager | Order Manager | Inventory Manager | Customer Support |
|---|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard** | `dashboard.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | `dashboard.financial_stats` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Catalog** | `products.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| | `products.create` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| | `products.edit` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| | `products.delete` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| | `categories.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Inventory** | `inventory.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| | `inventory.adjust` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| | `inventory.audit_trail` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| **Orders** | `orders.view` | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| | `orders.update_status` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| | `orders.pack_and_ship` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| | `orders.cancel` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Returns & Refunds** | `returns.view` | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| | `returns.approve` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| | `refunds.process` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Marketing** | `coupons.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| | `banners.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Customers** | `customers.view` | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| | `customers.edit_status` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Settings & Security**| `roles.manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | `settings.edit_credentials` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| | `audit_logs.view` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 4. Enforcement Architecture
Every administrative request passes through authorization middleware:
```php
// Example controller enforcement
public function processRefund(Request $request, int $orderId): JsonResponse 
{
    $this->authorize('refunds.process');
    
    // Proceed with validated Razorpay refund
}
```
All permission violations yield a strict `403 Forbidden` response and generate an audit security log.

