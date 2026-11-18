# 13. REST API Endpoint Specification

## 1. Authentication & Protocols
- **Format**: All payloads adhere strictly to `application/json; charset=utf-8`.
- **Authentication**:
  - Customer API: Stateful session cookies or Bearer Token (`Authorization: Bearer <sanctum_token>`).
  - Admin API: Admin Session or API Token with permission scopes.
  - Webhooks: Razorpay `X-Razorpay-Signature` HMAC header verification.
- **Common Status Codes**:
  - `200 OK`: Request succeeded.
  - `201 Created`: Resource created successfully.
  - `400 Bad Request`: Validation failure or semantic error.
  - `401 Unauthorized`: Missing or invalid authentication token.
  - `403 Forbidden`: Authenticated user lacks permission scope.
  - `404 Not Found`: Resource does not exist.
  - `422 Unprocessable Entity`: Input failed business rule validation (e.g., out of stock).
  - `429 Too Many Requests`: Rate limit reached.

---

## 2. Customer Storefront API Endpoints

### 2.1 Catalog & Search
| Method | Endpoint | Description | Auth Required |
|---|---|---|:---:|
| `GET` | `/api/v1/categories` | Retrieve hierarchical category tree. | No |
| `GET` | `/api/v1/products` | Paginated product listing with filters (`category`, `size`, `color`, `price_min`, `price_max`, `sort`). | No |
| `GET` | `/api/v1/products/{slug}` | Detailed product data with variant matrix, color image galleries, and size guide. | No |
| `GET` | `/api/v1/products/{id}/variants` | Query active variant stock availability given `size_id` and `color_id`. | No |
| `GET` | `/api/v1/search/suggest?q={term}`| Autocomplete predictive search suggestions. | No |

### 2.2 Cart & Coupon Management
| Method | Endpoint | Description | Auth Required |
|---|---|---|:---:|
| `GET` | `/api/v1/cart` | Get current active cart, line items, itemized discounts, and subtotal. | Session / Token |
| `POST` | `/api/v1/cart/items` | Add product variant to cart: `{ "variant_id": 142, "quantity": 1 }`. | Session / Token |
| `PUT` | `/api/v1/cart/items/{id}` | Update quantity or variant attributes: `{ "quantity": 2 }`. | Session / Token |
| `DELETE` | `/api/v1/cart/items/{id}` | Remove specific line item from cart. | Session / Token |
| `POST` | `/api/v1/cart/apply-coupon` | Validate & apply promo code: `{ "code": "SUMMER20" }`. | Session / Token |
| `POST` | `/api/v1/cart/remove-coupon` | Remove applied coupon from active cart session. | Session / Token |

### 2.3 Checkout & Payment
| Method | Endpoint | Description | Auth Required |
|---|---|---|:---:|
| `POST` | `/api/v1/checkout/reserve-stock` | Soft-reserve cart variant inventory for 15 minutes before payment. | Optional (Guest/User) |
| `POST` | `/api/v1/checkout/create-order` | Calculate final payable and create Razorpay Order ID. | Optional (Guest/User) |
| `POST` | `/api/v1/checkout/verify-payment`| Verify client Razorpay signature and confirm order. | Optional (Guest/User) |

#### Example: Initiate Razorpay Order Request & Response
```http
POST /api/v1/checkout/create-order
Content-Type: application/json

{
    "shipping_address_id": 12,
    "billing_address_id": 12,
    "payment_method": "RAZORPAY"
}
```
```json
{
    "success": true,
    "data": {
        "order_number": "ORD-20261117-9842",
        "razorpay_order_id": "order_Mxyz12345678",
        "amount_in_paise": 249900,
        "currency": "INR",
        "key_id": "rzp_live_xxxxxxxx",
        "customer_details": {
            "name": "Aarav Sharma",
            "email": "aarav@example.com",
            "phone": "+919876543210"
        }
    }
}
```

#### Example: Verify Payment Request & Response
```http
POST /api/v1/checkout/verify-payment
Content-Type: application/json

{
    "order_number": "ORD-20261117-9842",
    "razorpay_order_id": "order_Mxyz12345678",
    "razorpay_payment_id": "pay_Nabc56781234",
    "razorpay_signature": "9d8e7a6b5c4d3e2f1a0b..."
}
```
```json
{
    "success": true,
    "message": "Payment verified and order confirmed successfully.",
    "order": {
        "order_number": "ORD-20261117-9842",
        "status": "CONFIRMED",
        "total_amount": 2499.00
    }
}
```

---

## 3. Webhook Receiver Endpoint

| Method | Endpoint | Description | Verification Header |
|---|---|---|---|
| `POST` | `/api/v1/webhooks/razorpay` | Asynchronous Razorpay event dispatcher (`payment.captured`, `payment.failed`, `refund.processed`). | `X-Razorpay-Signature` |

---

## 4. Admin Management API Endpoints

### 4.1 Products & Catalog
- `GET /api/v1/admin/products`: Filterable product master list.
- `POST /api/v1/admin/products`: Create master product and generate variant matrix.
- `PUT /api/v1/admin/products/{id}`: Update product specifications, title, or pricing.
- `DELETE /api/v1/admin/products/{id}`: Soft-delete/archive product.
- `POST /api/v1/admin/products/{id}/images`: Upload and map images to colorways.

### 4.2 Inventory
- `GET /api/v1/admin/inventory`: SKU inventory table with stock, reserved, and threshold values.
- `POST /api/v1/admin/inventory/adjust`: Mutate stock quantity with mandatory reason code:
  ```json
  {
      "variant_id": 48,
      "quantity_delta": 25,
      "change_type": "PURCHASE_RECEIPT",
      "remarks": "Received Batch PO-2026-09"
  }
  ```
- `GET /api/v1/admin/inventory/movements`: View immutable audit log of inventory changes.

### 4.3 Orders & Fulfillment
- `GET /api/v1/admin/orders`: Query orders by state (`CONFIRMED`, `SHIPPED`, etc.).
- `GET /api/v1/admin/orders/{id}`: Order deep inspector with line items and payment state.
- `PUT /api/v1/admin/orders/{id}/status`: Transition status:
  ```json
  {
      "status": "SHIPPED",
      "carrier_name": "Blue Dart",
      "tracking_number": "BLUEDART-88991122"
  }
  ```

### 4.4 Returns & Refunds
- `GET /api/v1/admin/returns`: List pending return requests with customer images.
- `POST /api/v1/admin/returns/{id}/approve`: Mark return request approved for reverse pickup.
- `POST /api/v1/admin/returns/{id}/refund`: Process Razorpay refund and optionally restock item:
  ```json
  {
      "amount": 1499.00,
      "restock_inventory": true,
      "remarks": "QC Passed, tags intact."
  }
  ```

