# 15. UI/UX Design System & Fashion Aesthetic Guide

## 1. Design Philosophy: Modern Minimalist Luxury
Fashion eCommerce requires an editorial, high-end visual language that elevates apparel photography without visual noise. The design focuses on:
- **Clean Whitespace & Asymmetric Grids**: Ample breathing room to spotlight garment textures, cuts, and silhouettes.
- **Mobile-First Ergonomics**: All critical interactive targets (Add to Cart, Size chips, Filter triggers) located in the lower "thumb zone".
- **Visual Micro-Interactions**: Smooth color swatch switching, quick-view slide-overs, image zoom, and subtle drawer transitions.

---

## 2. Design Tokens & Color Palette

### 2.1 Primary & Accent Color Palette
```css
:root {
  /* Editorial Monochromes */
  --color-canvas-bg: #FAF9F6;       /* Alabaster / Warm White background */
  --color-surface-white: #FFFFFF;   /* Clean white for product cards & modals */
  --color-text-primary: #111111;    /* Near-black / Obsidian for high-contrast typography */
  --color-text-secondary: #555555;  /* Deep neutral gray for descriptions & metadata */
  --color-text-muted: #888888;      /* Light muted gray for placeholders & breadcrumbs */
  --color-border-subtle: #E5E5E5;   /* Crisp 1px borders */
  
  /* Brand Luxury Accents */
  --color-brand-primary: #0F172A;   /* Midnight Slate - Main action color */
  --color-brand-accent: #C5A880;    /* Muted Champagne Gold - Badges, accents, highlights */
  
  /* Semantic Status Colors */
  --color-success: #15803D;         /* Forest green - In-stock, orders confirmed */
  --color-warning: #B45309;         /* Amber - Low stock alerts */
  --color-danger: #B91C1C;          /* Crimson - Form errors, cancellations */
}
```

### 2.2 Typography Scale
- **Headings & Editorial Display**: `Playfair Display` or `Cinzel` (Serif, elegant, premium fashion feel).
- **Body, Navigation & Data Tables**: `Inter` or `Plus Jakarta Sans` (Sans-serif, razor-sharp legibility at small sizes).
- **Type Scale**:
  - `Hero Display`: 48px / 1.15 line-height (Mobile: 32px)
  - `Section Title`: 28px / 1.25 line-height
  - `Product Title`: 16px / 1.4 line-height (Medium 500)
  - `Price Text`: 16px / Bold 700
  - `Body Regular`: 14px / 1.5 line-height
  - `Meta / Captions`: 12px / Uppercase / Letter spacing 0.08em

---

## 3. Core Component Specifications

### 3.1 Apparel Product Card (PLP)
- **Aspect Ratio**: 3:4 portrait ratio (optimal for full-body garment modeling).
- **Hover / Touch Interaction**: 
  - Desktop: Hover smoothly swaps to secondary model angle image; displays "Quick View" and "Wishlist" icon.
  - Mobile: Swipe left/right on card swaps image angle directly in the feed.
- **Card Content Anatomy**:
  1. Product Image with subtle discount tag (`-30% OFF` in muted champagne badge).
  2. Heart icon in top-right corner (Wishlist toggle).
  3. Brand label (Uppercase 11px).
  4. Garment Name (14px, 2-line clamp).
  5. Colorway dot swatches (e.g. Black, Olive, Khaki). Clicking swatch updates the card image instantly.
  6. Price block: `₹1,299` (Bold) with struck-through `₹1,999` original price and discount percentage.

### 3.2 Product Details Page (PDP) Layout
- **Desktop (Split View)**:
  - Left column (60% width): Sticky double-column image grid or full-height vertical photo gallery.
  - Right column (40% width): Sticky purchase panel containing Title, Star rating, Price, Fabric details, Color picker chips, Size buttons, Interactive Size Chart link, "Add to Cart" and "Buy Now" CTA buttons, Collapsible Accordions (Fabric & Care, Shipping & Returns).
- **Mobile**:
  - Full-width swipeable image carousel with indicator dots.
  - Sticky bottom purchase bar with selected variant summary and prominent "Add to Cart" CTA.

### 3.3 Interactive Size Guide Modal
- Table toggle between **Inches** and **Centimeters**.
- Columns: `Size`, `Chest`, `Waist`, `Shoulder`, `Garment Length`.
- Graphical illustration displaying "How to Measure Your Body".

### 3.4 Buttons & CTA Styles
- **Primary CTA ("Add to Cart" / "Pay Now")**: Solid `#0F172A`, full-width on mobile, 48px touch height, uppercase, letter-spaced, sharp or subtle `2px` rounded corners.
- **Secondary CTA ("Buy Now")**: Outlined 1.5px `#0F172A` with white background.
- **Size Chip Button**: 42x42px square or pill. Active state: Black background with white text. Disabled/Out-of-Stock state: Struck-through with diagonal line and muted gray text.

---

## 4. Admin Panel UI & Dashboard Experience
- **Layout Architecture**: Fixed collapsible left sidebar navigation (`#0F172A` deep navy), high-contrast white content area, and top action bar with search, notification bell, and user avatar.
- **Data Tables**: Zebra-striping disabled; crisp 1px light gray borders, sticky header row during vertical scroll, inline status badges (`CONFIRMED` in emerald green, `PENDING` in amber, `SHIPPED` in cobalt blue).
- **Form Design**: Multi-step wizard tabs for product creation:
  - Tab 1: General Info & Categorization
  - Tab 2: Media & Color Mapping
  - Tab 3: Variant Matrix (2D table for Size × Color SKUs)
  - Tab 4: SEO & Shipping Weights

