#!/bin/bash

# Automated test for LaraGrape package installation in a fresh Laravel app

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 0. Remove previous test install
if [ -d test-laragrape ]; then
    echo "[0/7] Removing existing test-laragrape..."
    rm -rf test-laragrape
fi

# 1. Create a fresh Laravel project
echo "[1/7] Creating fresh Laravel project..."
composer create-project laravel/laravel test-laragrape --no-interaction
cd test-laragrape || exit 1

# 2. Require the LaraGrape package from local path (parent directory = package root)
# Path repos use composer.json "version" (e.g. 1.5.0), not dev-Development — use ^1.5 locally.
# For Packagist only: LARAGRAPE_CONSTRAINT=dev-Development ./test_laragrape_install.sh
echo "[2/7] Requiring LaraGrape package from local path..."
PACKAGE_ROOT="$(cd .. && pwd)"
LARAGRAPE_CONSTRAINT="${LARAGRAPE_CONSTRAINT:-^1.5}"
composer config repositories.laragrape-local path "$PACKAGE_ROOT"
composer require "streats22/laragrape:${LARAGRAPE_CONSTRAINT}" --with-all-dependencies --no-interaction

# 3. Run the setup command (Filament, portfolio module, LaraGrape\ → App\, migrate, seed)
echo "[3/7] Running LaraGrape setup command..."
php artisan laragrape:setup --migrate --all --force --no-interaction

# 4. Run the seeders (setup may already seed; this ensures host DatabaseSeeder runs)
echo "[4/7] Running database seeders..."
php artisan db:seed --no-interaction

# 5. Install and build frontend assets
echo "[5/7] Installing and building frontend assets..."
npm install --silent
npm run build

# 6. Smoke checks
echo "[6/7] Running smoke checks..."
php artisan route:list --name=portfolio.show --no-interaction >/dev/null
grep -q 'LARAGRAPE_PORTFOLIO=true' .env || { echo "FAIL: LARAGRAPE_PORTFOLIO not enabled in .env"; exit 1; }
HOME_CODE=$(curl -s -o /dev/null -w '%{http_code}' http://test-laragrape.test 2>/dev/null || echo "000")
PORTFOLIO_CODE=$(curl -s -o /dev/null -w '%{http_code}' http://test-laragrape.test/portfolio 2>/dev/null || echo "000")
echo "  Homepage HTTP: ${HOME_CODE}"
echo "  Portfolio page HTTP: ${PORTFOLIO_CODE}"
if [ "$HOME_CODE" != "200" ] || [ "$PORTFOLIO_CODE" != "200" ]; then
    echo "FAIL: Expected HTTP 200 for homepage and /portfolio"
    exit 1
fi

# 7. Done
echo "[7/7] Install complete."
echo "  Site:    http://test-laragrape.test"
echo "  Admin:   http://test-laragrape.test/admin"
echo "  Create admin user: php artisan make:filament-user"
