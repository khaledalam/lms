#!/usr/bin/env bash
set -euo pipefail

# ----------------------------
# Pretty output helpers
# ----------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

say()   { printf "${BLUE}›${NC} %s\n" "$*"; }
ok()    { printf "${GREEN}✔${NC} %s\n" "$*"; }
warn()  { printf "${YELLOW}⚠${NC} %s\n" "$*"; }
fail()  { printf "${RED}✘${NC} %s\n" "$*"; exit 1; }

APP_SERVICE="${APP_SERVICE:-app}"
NODE_SERVICE="${NODE_SERVICE:-node}"

run_in() {
  docker compose exec -T "$APP_SERVICE" sh -lc "$*"
}

# ----------------------------
# 1) Build & start
# ----------------------------
say "Building containers..."
docker compose up -d --build
ok "Containers built & running"

# ----------------------------
# 2) Wait for PHP readiness
# ----------------------------
say "Waiting for PHP to boot..."
for i in $(seq 1 30); do
  if docker compose exec -T "$APP_SERVICE" php -v >/dev/null 2>&1; then
    ok "PHP ready"
    break
  fi
  sleep 1
  if [ "$i" = "30" ]; then
    fail "Timed out waiting for PHP container."
  fi
done

# ----------------------------
# 3) SQLite & permissions
# ----------------------------
say "Ensuring SQLite & permissions..."
run_in "mkdir -p database storage/logs bootstrap/cache"
run_in "test -f database/database.sqlite || touch database/database.sqlite"
run_in "chown -R www-data:www-data database storage bootstrap/cache"
ok "SQLite ready"

# ----------------------------
# 4) Composer install
# ----------------------------
say "Installing Composer deps..."
run_in "composer install --no-interaction --prefer-dist"
ok "Composer install complete"

# ----------------------------
# 5) App key & storage link
# ----------------------------
say "Generating app key & storage link..."
run_in "php artisan key:generate || true"
run_in "php artisan storage:link || true"
ok "App key & storage linked"

# ----------------------------
# 6) Migrations & seed
# ----------------------------
say "Running migrations & seeding..."
if run_in "php artisan migrate:fresh --seed"; then
  ok "Database migrated & seeded"
else
  warn "migrate:fresh failed (maybe production DB). Retrying with migrate --seed"
  run_in "php artisan migrate --seed" || fail "Migration failed"
  ok "Migrations complete"
fi

# ----------------------------
# 7) Frontend build (Vite)
# ----------------------------
say "Building frontend assets (Vite + Tailwind)..."
docker compose run --rm "$NODE_SERVICE" sh -lc "npm ci && npm run build"
ok "Frontend build finished"

# ----------------------------
# 8) Clear Laravel caches
# ----------------------------
say "Clearing Laravel caches..."
run_in "rm -f public/hot && php artisan view:clear && php artisan route:clear && php artisan config:clear"
ok "Laravel caches cleared"

# ----------------------------
# 9) Optimize
# ----------------------------
say "Optimizing..."
run_in "php artisan optimize || true"
ok "Optimization done"

# ----------------------------
# 10) Done!
# ----------------------------
ok "Setup complete! 🚀"
say "Visit ${YELLOW}http://localhost:8000${NC}"