# CreativeItBari Website

Company website + admin panel for selling SaaS, one-time software, e-commerce solutions, web applications, hosting and domains.

**Stack:** Laravel 13 · Filament 5 (admin) · Tailwind CSS 4 · Alpine.js · MySQL/MariaDB · PHP 8.3+

## Local setup

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate   # then set DB_* in .env
php artisan migrate --seed      # creates tables, demo content and the admin user
php artisan storage:link
php artisan serve               # http://127.0.0.1:8000  ·  admin: /admin
```

> XAMPP's bundled PHP is 8.2; Laravel 13 needs PHP 8.3+, so use `php artisan serve` (Homebrew PHP) locally.

Run tests: `php artisan test`

## Where things live

| What | Where |
|---|---|
| Public pages | `resources/views/pages/*`, controllers in `app/Http/Controllers` |
| Layout, SEO meta, JSON-LD | `resources/views/components/layouts/app.blade.php` |
| Header / footer / cards | `resources/views/components/site/*`, `components/cards/*` |
| Theme colors, buttons, dark mode | `resources/css/app.css` |
| Admin panel | `app/Filament/*`, `app/Providers/Filament/AdminPanelProvider.php` |
| Site settings (logo, contact, hero, SEO…) | Admin → Settings → Site Settings (`setting('key')` helper) |
| Central payment hub (EPS) | `app/Services/Eps`, `app/Services/Payments`, `CheckoutController`, `Api/PaymentController`, Admin → Payments |
| **Payment integration guide** for other apps | [`docs/payment-gateway-integration.md`](docs/payment-gateway-integration.md) (also in Admin → Payments → Integration Guide) |

## Payment hub (EPS)

This site is the central payment gateway for our other apps (ERP, client sites). Apps create a checkout through a signed API, the customer pays on `/pay/{reference}` through EPS, and the app is told by webhook and by a signed return redirect.

1. Put the EPS credentials in `.env` (`EPS_*`; see `.env.example`). Leave `EPS_SANDBOX=true` until you have the live credentials and `EPS_BASE_URL`.
2. Admin → Payments → **Connected Apps** → *Connect an app* → copy its **API keys** into that app's `.env`.
3. Run the scheduler (`* * * * * php artisan schedule:run`). It runs `payments:reconcile` every 5 minutes, which checks payments EPS has not confirmed, expires old links and retries webhooks.

## Deploying to cPanel / shared hosting

1. Choose **PHP 8.3+** in cPanel → *Select PHP Version*; enable `pdo_mysql`, `intl`, `gd`, `zip`, `fileinfo`.
2. Upload the project (without `node_modules`) **outside** `public_html`, run `npm run build` locally first so `public/build` is included.
3. Point the domain's document root to the project's `public/` folder (or move `public/*` into `public_html` and fix paths in `index.php`).
4. Create the MySQL database, fill `.env` (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://creativeitbari.com`, DB and MAIL settings).
5. Via Terminal/SSH: `composer install --no-dev -o`, `php artisan migrate --force --seed`, `php artisan storage:link`, `php artisan optimize`.
   Add the cron job `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1` (payment reconciliation).
6. Submit `https://your-domain/sitemap.xml` in Google Search Console.
# creativeitbari-final-host
