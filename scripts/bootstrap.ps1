$ErrorActionPreference = "Stop"

Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "Running: composer install"
composer install

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch '(?m)^APP_KEY=base64:.+') {
    Write-Host "Running: php artisan key:generate --ansi"
    php artisan key:generate --ansi
}

Write-Host "Running: php artisan storage:link --ansi --force"
php artisan storage:link --ansi --force

Write-Host "Running: php artisan migrate --seed --force --ansi"
php artisan migrate --seed --force --ansi

Write-Host "Running: php artisan optimize:clear --ansi"
php artisan optimize:clear --ansi

Write-Host "Running: npm install"
npm install

Write-Host "Running: npm run build"
npm run build

Write-Host "Bootstrap complete."
