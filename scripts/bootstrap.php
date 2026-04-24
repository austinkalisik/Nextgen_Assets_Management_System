<?php

declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

run('composer install');

if (! file_exists('.env')) {
    copy('.env.example', '.env');
    out('Created .env from .env.example');
}

if (! hasAppKey('.env')) {
    run('php artisan key:generate --ansi');
}

run('php artisan storage:link --ansi --force');
run('php artisan migrate --seed --force --ansi');
run('php artisan optimize:clear --ansi');
run('npm install');
run('npm run build');

out('Bootstrap complete.');

function hasAppKey(string $path): bool
{
    if (! file_exists($path)) {
        return false;
    }

    $contents = file_get_contents($path);

    return $contents !== false && preg_match('/^APP_KEY=base64:.+/m', $contents) === 1;
}

function run(string $command): void
{
    out("Running: {$command}");
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "Command failed: {$command}\n");
        exit($exitCode);
    }
}

function out(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}
