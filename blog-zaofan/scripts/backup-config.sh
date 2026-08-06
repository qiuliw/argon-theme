#!/usr/bin/env bash
set -euo pipefail
DIR="/home/ubuntu/dev/projects/blog-zaofan"
STAMP=$(date +%Y%m%d-%H%M%S)
OUT="$DIR/backups/config-$STAMP"
mkdir -p "$OUT"
set -a; . "$DIR/.env"; set +a
docker cp "$DIR/scripts/backup-config.php" blog-zaofan-wp:/tmp/backup-config.php
docker exec blog-zaofan-wp php /tmp/backup-config.php
docker cp blog-zaofan-wp:/tmp/blog-zaofan-full-export.json "$OUT/blog-zaofan-full-export.json"
docker exec blog-zaofan-db mariadb-dump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" | gzip > "$OUT/db-wordpress.sql.gz"
cp -a "$DIR/docker-compose.yml" "$OUT/" 2>/dev/null || true
cp -a "$DIR/.env" "$OUT/.env"; chmod 600 "$OUT/.env"
docker exec blog-zaofan-wp bash -lc 'cd /var/www/html/wp-content/themes/argon && tar czf /tmp/argon-custom-assets.tgz assets/img/backgrounds assets/img/avatar 2>/dev/null || true'
docker cp blog-zaofan-wp:/tmp/argon-custom-assets.tgz "$OUT/argon-custom-assets.tgz" 2>/dev/null || true
docker exec blog-zaofan-wp wp plugin list --allow-root --format=json > "$OUT/plugins.json"
docker exec blog-zaofan-wp wp theme list --allow-root --format=json > "$OUT/themes.json"
sudo grep -n -A60 'blog.zaofan.org' /etc/nginx/conf.d/zaofan.org.conf > "$OUT/nginx-blog.zaofan.org.conf.txt" 2>/dev/null || true
echo "# backup $STAMP" > "$OUT/README.md"
tar -czf "$DIR/backups/blog-zaofan-config-$STAMP.tar.gz" -C "$DIR/backups" "config-$STAMP"
ln -sfn "blog-zaofan-config-$STAMP.tar.gz" "$DIR/backups/blog-zaofan-config-latest.tar.gz"
ln -sfn "config-$STAMP" "$DIR/backups/latest"
echo "OK $DIR/backups/blog-zaofan-config-$STAMP.tar.gz"
