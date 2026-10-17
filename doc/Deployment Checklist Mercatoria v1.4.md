# Deployment Checklist — Mercatoria MVP v1.4

## Environment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the HTTPS `APP_URL`.
- Keep the existing `APP_KEY`; never regenerate it for an existing production database.
- Configure MySQL, persistent cache/session storage, the public storage disk, and the three SMTP sender addresses.
- Configure Cloudflare HTTPS/Turnstile and GA4 via `/office/settings`.
- Use PHP extensions required by the Composer lock file (including GD and ZIP for spreadsheet/image dependencies).

## Closed beta acceptance

- Deploy the release candidate to a production-like staging environment with a restorable database and uploaded-file backup.
- Select and brief a small beta cohort; use test accounts and never ask beta users to transfer real money during acceptance.
- Run `php artisan app:deployment-check`, `php artisan migrate:status`, and `php artisan schedule:list`. Resolve every preflight failure and confirm each scheduled task is registered before the beta starts.
- Exercise customer registration, email verification, profile completion, search/catalog, variant selection, cart, shipping calculation, checkout, immutable order snapshot, payment proof review, DP settlement, refund, and order completion.
- Exercise admin authorization, customer migration, spammer handling, content publishing, email-template test delivery, reports/CSV export, gift delivery, SEO metadata, robots, and sitemap.
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
- Verify `php artisan schedule:list` after deployment. Currently registered tasks are order pickup reminders, expired-order processing, spammer grace-period checks, and daily sitemap generation.
- The PRD also calls for log/notification retention cleanup, customer-state and tier refresh, Crush cleanup, cart reminders, coin-expiry notices, and birthday bonuses. These jobs are not currently registered in this release; do not claim those automations are live until their commands are implemented, tested, and scheduled.
