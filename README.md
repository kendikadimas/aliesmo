# E-Commerce Backend

Single-store e-commerce platform built with Laravel 12 + Filament 4 + Vue 3 + Tailwind CSS v4.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.3+) |
| Admin Panel | Filament 4 |
| Auth (API) | Laravel Sanctum |
| Storefront | Vue 3 (Composition API) |
| CSS | Tailwind CSS v4 |
| Build | Vite + `@tailwindcss/vite` |
| Database | MySQL 8.0+ (SQLite for testing) |
| Payment | Midtrans Snap (swappable via interface) |

## Setup

```bash
# Clone and install
composer install
npm install

# Environment
cp .env.example .env
# Edit .env with your database and Midtrans credentials

# Generate key and migrate
php artisan key:generate
php artisan migrate --seed

# Build frontend assets
npm run build

# Serve
php artisan serve
```

Default admin login: `admin@example.com` / `password`

## Seeded Data

- 1 admin user, 1 customer user
- 5 product categories
- 30 products with varied stock levels
- 10 sample orders in various statuses

## API Endpoints

All under `/api/v1/`:

### Public
- `GET  /products` — paginated, filterable by `category`, `search`, sort by `sort`
- `GET  /products/{slug}`
- `GET  /categories`
- `POST /orders` — guest checkout, returns payment token
- `GET  /orders/{orderNumber}/status`
- `POST /payments/callback/midtrans` — webhook (no CSRF)

### Auth
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout` (auth:sanctum)
- `GET  /me/orders` (auth:sanctum)

## Admin Panel

Access `/admin` with admin credentials.

### Resources
- **Products** — CRUD with stock adjustment action (only way to modify stock)
- **Categories** — simple CRUD
- **Orders** — read-mostly, status transitions, CSV export
- **Stock Movements** — read-only audit log

### Dashboard Widgets
- Sales overview (revenue, orders, avg value, low stock)
- Revenue chart (last 30 days)
- Top-selling products

### Sales Report
Custom page with date range filter, summary stats, and CSV export.

## Testing Payment Flow (Midtrans Sandbox)

1. Set `MIDTRANS_IS_PRODUCTION=false` in `.env`
2. Get sandbox keys from [Midtrans Dashboard](https://account.midtrans.com)
3. Place an order via the API or storefront
4. You'll be redirected to Midtrans Snap sandbox
5. Use test card: `4811 1111 1111 1114` (any expiry, any CVV)
6. Webhook will flip the order status to `paid`

## Swapping Payment Gateway

1. Create a new class implementing `App\Contracts\PaymentGatewayInterface`
2. Place it in `app/Services/Gateways/`
3. Update the binding in `App\Services\PaymentGatewayServiceProvider`
4. No controllers or services need changes

## Running Tests

```bash
php artisan test
```

Key tests cover:
- Stock cannot go negative
- Order validation rejects insufficient stock
- Webhook signature verification rejects invalid payloads
- Stock reserved only on payment success, not order creation

## Out of Scope

This project does NOT include (unless requested as a paid add-on):
- Multi-vendor/marketplace
- Multi-currency / multi-language
- Wishlists / reviews / recommendations
- Discount codes / coupons
- Real-time notifications (websockets)
