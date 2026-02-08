# HTML File Backups

This directory contains automatic backups of HTML files created when editing through the admin panel.

## Backup Format

Backups are saved with the following naming convention:
```
{filename}.{YYYY-MM-DD_HH-mm-ss}.backup
```

For example:
- `index.html.2026-02-07_14-30-45.backup`
- `about.html.2026-02-07_15-22-10.backup`

## Retention

Backups are created automatically every time you save an HTML file through the admin panel. You may want to periodically clean up old backups to save disk space.

## Restoring Backups

To restore a backup:
1. Locate the backup file you want to restore
2. Copy the content from the backup file
3. Replace the current file's content with the backup content

Or simply rename the backup file to replace the original:
```bash
cp backups/index.html.2026-02-07_14-30-45.backup index.html
```
