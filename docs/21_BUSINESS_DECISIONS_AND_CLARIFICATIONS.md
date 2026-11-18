# 21. Business Decisions & Missing Requirements for Approval

Before proceeding with application development, the following **9 critical business decisions** must be evaluated and approved. No silent assumptions have been made in the architecture; default recommendations are provided for each.

---

### Decision 1: Cash on Delivery (COD) Support & Policy
- **Context**: While Razorpay handles prepaid transactions (UPI, Cards, NetBanking), 40-60% of fashion eCommerce in India still requests Cash on Delivery.
- **Options**:
  1. *(Recommended)* **Prepaid Only at Launch**: Launch exclusively with Razorpay online payments. Reduces RTO (Return to Origin) logistics losses and eliminates fake/bogus orders.
  2. **Prepaid + COD with Restrictions**: Support COD with a minimum order value (e.g., ₹499), a maximum cap (e.g., ₹3,000), and a non-refundable COD convenience fee (e.g., ₹50) plus OTP verification before order confirmation.
  3. **Unrestricted COD**: Standard COD for all orders.
- **Refund Policy if COD is chosen**: How should refunds be issued for returned COD orders? (A) Direct NEFT/IMPS bank transfer after customer submits IFSC/account details, or (B) Store credit / wallet vouchers?

---

### Decision 2: Stock Reservation Lock Duration
- **Context**: When a customer clicks "Proceed to Payment", the system temporarily holds the SKU stock so another shopper cannot buy it while the customer is completing payment.
- **Options**:
  1. *(Recommended)* **15 Minutes**: Standard e-commerce duration giving adequate time for UPI PIN entry or bank OTP delays without excessively locking inventory during high-demand drops.
  2. **10 Minutes**: Aggressive timeout suited for high-heat flash sales.
  3. **20 Minutes**: Relaxed timeout for high-value orders.

---

### Decision 3: Guest Checkout vs. Mandatory Account Registration
- **Context**: Requiring registration creates checkout friction, but guest checkouts complicate order tracking and return self-service.
- **Options**:
  1. *(Recommended)* **Seamless Auto-Account Creation**: Customer completes checkout with Name, Phone, and Email. The server automatically creates a customer account in the background, associates the order, and emails a secure one-click magic login link or temporary password.
  2. **True Guest Checkout**: Customer orders without an account; order is tracked solely via Order ID + Phone number lookup.
  3. **Mandatory Pre-Registration**: Customer must sign up and verify email/mobile before reaching the checkout screen.

---

### Decision 4: GST & Tax Calculation Strategy
- **Context**: Indian apparel tax law historically applies differential GST:
  - Apparel sale price $\le$ ₹1,000: **5% GST**
  - Apparel sale price $>$ ₹1,000: **12% GST**
- **Options**:
  1. *(Recommended)* **Tax-Inclusive Display (B2C Standard)**: Product catalog prices (e.g., ₹1,499) are displayed inclusive of GST to prevent sticker shock. The invoice automatically calculates and itemizes CGST/SGST (intra-state) or IGST (inter-state) on the backend.
  2. **Tax-Exclusive Display**: Prices are listed without tax, and 5%/12% GST is added as a separate line item at checkout.

---

### Decision 5: Shipping Fee Structure
- **Context**: Shipping fees heavily influence cart abandonment.
- **Options**:
  1. *(Recommended)* **Threshold-Based Free Shipping**: Free shipping on orders above ₹999; flat ₹79 shipping fee for orders below ₹999.
  2. **Flat Shipping**: Flat fee (e.g., ₹50 or ₹99) on all orders regardless of cart value.
  3. **100% Free Shipping**: Shipping costs baked entirely into garment base prices.

---

### Decision 6: Return, Replacement & Reverse Logistics Model
- **Context**: Apparel typically experiences a 15-25% return/exchange rate primarily due to sizing.
- **Options**:
  1. *(Recommended)* **7-Day Return/Exchange Window with Doorstep Reverse Pickup**: Customer requests return or size exchange within 7 days of delivery. Courier picks up item, returns to warehouse, and once QC passes, replacement is dispatched or Razorpay refund is issued.
  2. **Exchange Only (No Cash Refunds)**: Returns are only permissible for size/color exchanges or store credit vouchers.
  3. **Customer Self-Ship**: Customer ships the item back at their own expense to warehouse address before refund is processed.
- **Non-Returnable Items**: Should categories like Innerwear, Face Masks, or Clear-out Discount Items be marked strictly non-returnable?

---

### Decision 7: Logistics & Courier Integration
- **Context**: Managing tracking numbers and dispatch dispatches.
- **Options**:
  1. *(Recommended)* **Stage 1 Manual AWB Entry + Stage 2 Automated Aggregator**: In the initial phase, admin manually pastes carrier name (e.g., Blue Dart, Delhivery) and AWB tracking number. In Phase 8, integrate an automated courier API (e.g., Shiprocket, Delhivery Direct, or Pickrr) for 1-click label printing and live tracking webhooks.
  2. **Full Automated Courier Integration from Day 1**: Select a specific logistics API partner immediately (requires pre-existing courier commercial contract).

---

### Decision 8: Transactional SMS & OTP Provider
- **Context**: Order confirmation SMS, OTP verification, and dispatch updates.
- **Options**:
  1. *(Recommended)* **Fast2SMS / MSG91 (India DLT Compliant)**: Cost-effective, high delivery rates for Indian mobile networks.
  2. **Twilio**: Global enterprise standard, higher per-SMS cost.
  3. **Email Only (SES/Mailgun) Initially**: Eliminate SMS integration costs at launch; communicate order status strictly via email.

---

### Decision 9: Target Market & Multi-Currency Scope
- **Context**: Domestic vs. Global commerce.
- **Options**:
  1. *(Recommended)* **Domestic First (India / INR)**: Tailor checkout, GST invoices, Razorpay UPI/Cards, and Indian pin codes for India. Modular database already supports multi-currency columns for future expansion.
  2. **International Multi-Currency from Day 1**: Enable multi-currency conversion (USD, EUR, GBP), international shipping duties, and Razorpay International Payment activation.

