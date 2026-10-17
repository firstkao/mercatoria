# Deployment Checklist — Mercatoria MVP v1.4

## Environment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the HTTPS `APP_URL`.
- Keep the existing `APP_KEY`; never regenerate it for an existing production database.
- Configure MySQL, persistent cache/session storage, the public storage disk, and the three SMTP sender addresses.
- Configure Cloudflare HTTPS/Turnstile and GA4 via `/office/settings`.
- Use PHP extensions required by the Composer lock file (including GD and ZIP for spreadsheet/image dependencies).

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
5. On rollback, restore the matching database backup and uploaded files with the previous release; do not roll back schema changes independently.

## Hostinger scheduled tasks

- Run `php artisan schedule:run` every minute using the production PHP binary.
- If database queues are enabled, run `php artisan queue:work --stop-when-empty` every five minutes.
- Verify `php artisan schedule:list` after deployment. The application registers order reminders/expiry, spammer grace-period checks, and daily sitemap generation.
