# Cache refresh — examples

This project includes `admin/scripts/refresh_dashboard_cache.php` to warm API caches (crm_dashboard, invoice_trends).

Cron (every 10 minutes):

*/10 * * * * /usr/bin/php /path/to/workspaces/content_catalogz/admin/scripts/refresh_dashboard_cache.php >/dev/null 2>&1

Systemd timer (preferred on modern systems):
- Copy `admin/systemd/refresh-dashboard-cache.service` and `admin/systemd/refresh-dashboard-cache.timer` to `/etc/systemd/system/`
- Enable & start:
  - sudo systemctl daemon-reload
  - sudo systemctl enable --now refresh-dashboard-cache.timer

Notes:
- Ensure the `ExecStart` path matches your workspace location and `php` binary.
- The script calls local URLs (127.0.0.1:8081) — update if your site runs elsewhere.
