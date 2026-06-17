#!/usr/bin/env bash
# Web entrypoint for Render. Runs release-time tasks, then starts Apache.
set -e

# Default PORT for local docker runs; Render sets this automatically.
export PORT="${PORT:-8080}"

echo "==> Caching config / routes / views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations"
php artisan migrate --force

# storage:link is a no-op if files live on S3; harmless on a persistent disk.
php artisan storage:link 2>/dev/null || true

echo "==> Starting Apache on :$PORT"
exec apache2-foreground
