# Tutiks POS Theme

Minimal WordPress theme tailored for a point‑of‑sale (POS) workflow.

## Installation
- WordPress 6.0+ and PHP 7.4+ are required.
- Copy the `tutiks-pos` directory into `wp-content/themes/`.
- In wp‑admin, go to Appearance → Themes and activate “Tutiks POS”.
- Create a page:
  - Title: “POS”
  - Slug: `pos`
  - Template: “POS Dashboard”
- Optional seed data:
  - Go to Products → Initialize Menu to create menu categories and products.
  - You may also create sample orders from the same screen.
- Recommended: set pretty permalinks in Settings → Permalinks.

## Features
- Custom Post Types
  - `product` (public) with price meta and thumbnails
  - `pos_order` (admin UI) storing order items, totals, payment method, date
- Taxonomy
  - `menu_category` (hierarchical) for organizing products
- POS Dashboard page template with searchable/filterable product grid and cart
- AJAX order saving with nonce validation
- Enhanced admin list for Sales (custom columns and sorting)
- Front‑end stack: Bootstrap 5, Font Awesome 6, SweetAlert2, Chart.js, DataTables

## Usage
- Manage products and menu categories in wp‑admin.
- Visit the POS page (`/pos`) to add items to cart and checkout.
- Saved orders appear under Sales (`pos_order`) with totals and item details.
- Optional config: set a QR code URL via the `tutiks_pos_qr_code_url` option if needed by your payment flow.


