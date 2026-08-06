#!/usr/bin/env bash
set -euo pipefail
DIR="/home/ubuntu/dev/projects/blog-zaofan"
STAMP=$(date +%Y%m%d-%H%M%S)
OUT="$DIR/docs/exports"
mkdir -p "$OUT"
docker cp "$DIR/scripts/full-export.php" blog-zaofan-wp:/tmp/full-export.php
docker exec blog-zaofan-wp php /tmp/full-export.php
docker cp blog-zaofan-wp:/tmp/blog-zaofan-full-export.json /tmp/blog-zaofan-full-export.json
cp -a "$DIR/docker-compose.yml" /tmp/docker-compose.yml
cp -a "$DIR/.env.example" /tmp/.env.example 2>/dev/null || touch /tmp/.env.example
TAR="$OUT/blog-zaofan-config-$STAMP.tar.gz"
tar -czf "$TAR" -C /tmp blog-zaofan-full-export.json docker-compose.yml .env.example
cp -f /tmp/blog-zaofan-full-export.json "$OUT/blog-zaofan-full-export-latest.json"
cp -f "$TAR" "$OUT/blog-zaofan-config-latest.tar.gz"
echo "OK: $TAR"
echo "OK: $OUT/blog-zaofan-full-export-latest.json"
