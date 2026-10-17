# Deployment Checklist — Mercatoria MVP v1.4

## Environment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the HTTPS `APP_URL`.
- Keep the existing `APP_KEY`; never regenerate it for an existing production database.
- Configure MySQL, persistent cache/session storage, the public storage disk, and the three SMTP sender addresses.
- Configure Cloudflare HTTPS and set `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` in the production environment. Login, registration, and password-reset POSTs fail closed in production if Turnstile is unavailable.
- Configure `GEOIP_DATABASE_PATH` to a current, privately stored MaxMind GeoLite2 Country `.mmdb` file. Keep it outside source control; lookups are local and visitor IPs are not sent to a Geo-IP provider. Configure `TRUSTED_PROXIES` with only the actual proxy CIDRs (never `*`) so share attribution uses the visitor IP safely. Share rewards remain disabled and logged if the database is unavailable.
- Configure GA4 and SEO via `/office/settings`; configure SMTP senders for verification, admin OTP, and scheduled customer notifications.
- Use PHP extensions required by the Composer lock file (including GD and ZIP for spreadsheet/image dependencies).

## Closed beta acceptance

- Deploy the release candidate to a production-like staging environment with a restorable database and uploaded-file backup.
- Select and brief a small beta cohort; use test accounts and never ask beta users to transfer real money during acceptance.
- Run `php artisan app:deployment-check`, `php artisan migrate:status`, and `php artisan schedule:list`. Resolve every preflight failure and confirm each scheduled task is registered before the beta starts.
- Exercise customer registration and referral attribution, email verification, profile completion, search/catalog, variant selection, cart, shipping calculation, checkout with coins (maximum 5%) or a valid voucher (never both), release of unused coin/voucher discounts when an unpaid order fails, immutable order snapshot, payment proof review, DP settlement, refund, order completion, coin/referral rewards, wallet history, and share links from Indonesian and foreign IPs (only one 5-coin reward per Indonesian IP and product link).
- Exercise admin authorization and email OTP, customer migration, spammer handling, product/order/customer search, audit-log filters/CSV exports, voucher CRUD, announcement and hero-slide publishing, email-template test delivery, reports/CSV export, gift delivery, SEO metadata, robots, and sitemap.
- Repeat the critical customer and admin journeys on mobile and desktop; verify email delivery, notification links, and browser-console/network errors.
- Record reproducible defects with severity, affected route/account role, steps, expected/actual result, and evidence. Do not proceed with unresolved critical/high-severity issues involving account access, payment/order integrity, customer data, or security.
- Re-run the complete automated test suite and production asset build against the exact release candidate after the last beta fix; obtain product-owner approval before public release.

## Release

1. Back up the database and uploaded files.
2. Deploy the application code and run:

   ```sh
   composer install --no-dev --optimize-autoloader
   php artisan app:deployment-check
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan sitemap:generate
   ```

3. Ensure the web server document root points to `public/` and writable access is limited to `storage/` and `bootstrap/cache/`.
4. Check `https://mercatoria.id/up`, storefront pages, `/robots.txt`, `/sitemap.xml`, checkout, SMTP delivery, and the admin sign-in flow.
5. Monitor application errors, failed jobs, SMTP delivery, payment-proof queue, order transitions, admin logs, and sitemap generation closely during the initial public-release window.
6. Roll back on sustained service errors, broken checkout/payment/order integrity, data exposure, or failed recovery paths. Restore the matching database backup and uploaded files with the previous release; do not roll back schema changes independently.

## Hostinger scheduled tasks

- Run `php artisan schedule:run` every minute using the production PHP binary.
- If database queues are enabled, run `php artisan queue:work --stop-when-empty` every five minutes.
- Verify `php artisan schedule:list` after deployment. Registered jobs include order pickup reminders and expiry, daily log/notification retention, customer-state and tier refresh, Crush cleanup, cart reminders, coin expiry and expiry notices, birthday bonuses, spammer grace-period checks, and sitemap generation.
- Confirm cart reminders repeat every 30 minutes only while the cart has items; coin notifications cover H-30/H-7/H-1; birthday rewards grant 5,000 coins plus a 30-day Rp25,000 voucher (minimum purchase Rp100,000).
- Closed beta and public launch are operational release gates, not code changes. Do not mark Sprint 11/12 complete until beta acceptance, defect resolution, production deployment, and product-owner approval have actually occurred.
