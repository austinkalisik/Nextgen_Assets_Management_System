#!/usr/bin/env sh

set -eu

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.docker.example .env
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
  php artisan key:generate --ansi
fi

php artisan storage:link --ansi --force || true
php artisan migrate --seed --force --ansi
php artisan optimize:clear --ansi

exec "$@"
