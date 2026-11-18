# 17. Comprehensive Testing Strategy & Quality Assurance

## 1. Testing Pyramid & QA Philosophy
To guarantee zero-downtime releases and protect business revenue, the platform enforces an automated multi-tier testing strategy:

```
        / \
       /   \     E2E / Browser Tests (5%)
      /-----\    Cypress / Playwright (Full Checkout & Payment)
     /       \
    /---------\  Integration & Feature Tests (35%)
   /           \ HTTP Endpoints, DB Transactions, Webhook Idempotency
  /-------------\
 /               \ Unit Tests (60%)
/                 \ Pricing Engines, Stock Lock Math, Signature Hashers
-------------------
```

---

## 2. Test Suites & Focus Areas

### 2.1 Unit Testing (PHPUnit / Pest)
- **Coupon Calculation Logic**:
  - Test flat vs. percentage discounts.
  - Test maximum discount cap enforcement.
  - Test minimum spend threshold rejection.
  - Test first-order-only validation against previous order history.
- **Stock Math**:
  - Available-to-promise calculation: `stock_quantity - reserved_quantity`.
  - Prohibit negative quantities on decrement.
- **Razorpay Cryptographic Hashes**:
  - Assert HMAC-SHA256 calculation matches known test vectors.
  - Assert constant-time string comparison blocks timing attacks.

### 2.2 Feature & Integration Testing
- **Cart & Session Persistence**:
  - Verify guest cart items migrate to authenticated user cart on login without duplicating line items.
- **Order Placement Flow**:
  - Verify address persistence, shipping fee assignment, and tax calculation.
  - Verify draft order creation with `PENDING_PAYMENT` status.
- **Razorpay Webhook Handling**:
  - Inject simulated `payment.captured` webhook with valid HMAC header; assert order transitions to `CONFIRMED` and stock is deducted.
  - Inject identical webhook event twice; assert second call returns HTTP 200 without duplicate inventory deduction or duplicate emails.
  - Inject webhook with invalid signature; assert HTTP 401 Unauthorized response.

### 2.3 Concurrency & Race Condition Stress Testing
- **Flash Sale Stock Contention Simulation**:
  - Setup: Single apparel variant with `stock_quantity = 5`.
  - Action: Execute 50 concurrent simulated checkout requests using parallel HTTP workers (e.g. `k6` or `Guzzle Pool`).
  - Expected Outcome: Exactly 5 requests succeed in reserving stock; exactly 45 requests receive `422 Out of Stock` error. Total stock never drops below zero.
- **Checkout Expiration & Release**:
  - Assert scheduled cron command `ReleaseExpiredStockReservations` successfully restores reserved stock to active stock after 15 minutes of inactivity.

---

## 3. Automated Load & Stress Testing
- **Target Load Profile**:
  - Normal traffic: 100 requests/sec.
  - Peak seasonal sale: 1,500 requests/sec.
  - Concurrent active checkouts: 500 simultaneous users.
- **Tooling**: `k6` load scripts executing realistic browsing, filtering, adding to cart, and proceeding to checkout.
- **Pass Criteria**:
  - 95th percentile response time < 500ms under 1,000 req/sec.
  - Zero database deadlocks or unhandled exceptions.
  - Memory consumption of PHP-FPM workers stays within configured limits.

---

## 4. End-to-End (E2E) Acceptance Test Checklist

| Scenario | Expected Outcome | Verification |
|---|---|---|
| **E2E-01: Full Guest Purchase** | Guest browses shirt -> selects M/Navy -> adds to cart -> enters address -> completes test payment -> lands on confirmed receipt. | Order created, stock deducted, confirmation email dispatched. |
| **E2E-02: Color Swatch Switching** | User clicks "Olive" swatch on PDP. | Main gallery immediately switches to Olive photos; URL updates cleanly. |
| **E2E-03: Cart Size Modification** | User edits size from L to XL inside mini-cart. | Cart item updates without full page reload; stock re-checked. |
| **E2E-04: Coupon Limit Abuse** | User attempts to apply single-use coupon twice across two tabs. | Second attempt fails with clear error message. |
| **E2E-05: Admin Order Dispatch** | Admin inputs AWB "BLUEDART123" and marks shipped. | Customer receives email/SMS with tracking link; order status is `SHIPPED`. |
| **E2E-06: Customer Return Flow** | Customer requests return for delivered order -> uploads photo. | Return ticket appears in admin review queue; customer receives ticket ID. |

