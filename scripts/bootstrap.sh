#!/usr/bin/env sh

set -eu

cd "$(dirname "$0")/.."

echo "Running: composer install"
composer install

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
  echo "Running: php artisan key:generate --ansi"
  php artisan key:generate --ansi
fi

echo "Running: php artisan storage:link --ansi --force"
php artisan storage:link --ansi --force

echo "Running: php artisan migrate --seed --force --ansi"
php artisan migrate --seed --force --ansi

echo "Running: php artisan optimize:clear --ansi"
php artisan optimize:clear --ansi

echo "Running: npm install"
npm install

echo "Running: npm run build"
npm run build

echo "Bootstrap complete."
