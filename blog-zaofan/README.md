# blog-zaofan

WordPress (Argon) stack for https://blog.zaofan.org

## Backup

On the server:

```bash
/home/ubuntu/dev/projects/blog-zaofan/scripts/backup-config.sh
```

Output: `backups/blog-zaofan-config-*.tar.gz` (also `backups/latest`).

Includes Argon/menus/pages JSON, DB dump, custom theme images, compose, `.env`, nginx snippet.
