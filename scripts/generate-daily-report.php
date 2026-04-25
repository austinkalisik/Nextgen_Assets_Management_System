<?php

declare(strict_types=1);

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$settings = DB::table('settings')->pluck('value', 'key');

$appName = (string) ($settings['system_name'] ?? 'Nextgen Assets Management System');
$companyName = (string) ($settings['company_name'] ?? 'Nextgen Technology');
$companyWebsite = (string) ($settings['company_website'] ?? 'https://nextgenpng.net/');
$generatedAt = now()->format('F j, Y g:i A');
$reportDate = now()->format('F j, Y');
$safeDate = now()->format('Y-m-d');
$reportTitle = 'Daily Development Report - '.$reportDate;

$completedWork = [
    [
        'area' => 'ERD',
        'outcome' => 'Regenerated graph.png from the live Laravel project using php artisan generate:erd and generated a matching text snapshot.',
    ],
    [
        'area' => 'Wireframe Documentation',
        'outcome' => 'Updated wireframe notes, ERD notes, and schema alignment notes so the project documentation matches the generated ERD and current Laravel + React structure.',
    ],
    [
        'area' => 'Bootstrap Automation',
        'outcome' => 'Added clone-and-run bootstrap scripts for Composer, PowerShell, and shell so the project is easier to set up after pulling from GitHub.',
    ],
    [
        'area' => 'Repository Readiness',
        'outcome' => 'Improved composer scripts, added a verification command, and updated VS Code tasks for easier local development.',
    ],
    [
        'area' => 'Docker Support',
        'outcome' => 'Added Dockerfile, docker-compose.yml, Docker env example, Apache config, and entrypoint script to support containerized startup.',
    ],
    [
        'area' => 'README',
        'outcome' => 'Rewrote the README to document GitHub clone flow, bootstrap, VS Code tasks, seeded users, Docker quick start, and verification steps.',
    ],
];

$fileHighlights = [
    ['file' => 'graph.png', 'purpose' => 'Regenerated live ERD image.'],
    ['file' => 'wireframe/erd-current.txt', 'purpose' => 'Generated text ERD snapshot from the live project.'],
    ['file' => 'wireframe/ERD - Nextgen Assets Management System.txt', 'purpose' => 'Human-readable ERD explanation aligned with the generated diagram.'],
    ['file' => 'wireframe/Schema Alignment Notes.txt', 'purpose' => 'Clarifies live generated ERD scope and legacy column visibility.'],
    ['file' => 'scripts/bootstrap.php', 'purpose' => 'One-command bootstrap for install, env setup, migrations, seeding, and build.'],
    ['file' => 'scripts/bootstrap.ps1', 'purpose' => 'Windows PowerShell bootstrap helper.'],
    ['file' => 'scripts/bootstrap.sh', 'purpose' => 'Shell bootstrap helper for non-Windows environments.'],
    ['file' => 'Dockerfile', 'purpose' => 'Container build definition for application deployment.'],
    ['file' => 'docker-compose.yml', 'purpose' => 'Local multi-container startup definition for app and MySQL.'],
    ['file' => 'README.md', 'purpose' => 'Updated clone, run, VS Code, Docker, and verification guide.'],
];

$verification = [
    ['check' => 'php artisan test', 'result' => 'Passed: 49 tests, 147 assertions.'],
    ['check' => 'npm run build', 'result' => 'Passed successfully.'],
    ['check' => 'composer verify', 'result' => 'Passed and confirmed the new verification flow works.'],
    ['check' => 'php -l scripts/bootstrap.php', 'result' => 'Passed with no syntax errors.'],
    ['check' => 'docker compose config', 'result' => 'Not validated on this machine because Docker is not installed here.'],
];

$caveats = [
    'Docker files were added, but runtime validation could not be completed on this machine because Docker is not installed.',
    'The generated ERD reflects the live local database/model state and may still show legacy columns that exist locally.',
    'The repository is easier to clone and run now, but production deployment still requires normal environment hardening such as HTTPS, real mail, proper server/process setup, and secrets management.',
];

$nextSteps = [
    'Push the updated repository to GitHub once the current worktree is reviewed and approved for commit.',
    'Optionally remove legacy schema columns so future generated ERDs are cleaner.',
    'Validate the new Docker flow on a machine with Docker installed.',
    'Prepare a focused deployment checklist for real production hosting.',
];

$html = view('reports.daily-report', [
    'reportTitle' => $reportTitle,
    'reportDate' => $reportDate,
    'generatedAt' => $generatedAt,
    'appName' => $appName,
    'companyName' => $companyName,
    'companyWebsite' => $companyWebsite,
    'completedWork' => $completedWork,
    'fileHighlights' => $fileHighlights,
    'verification' => $verification,
    'caveats' => $caveats,
    'nextSteps' => $nextSteps,
])->render();

$reportDirectory = public_path('reports');

if (! is_dir($reportDirectory)) {
    mkdir($reportDirectory, 0777, true);
}

$pdfPath = $reportDirectory.DIRECTORY_SEPARATOR."nextgen-daily-report-{$safeDate}.pdf";
$htmlPath = $reportDirectory.DIRECTORY_SEPARATOR."nextgen-daily-report-{$safeDate}.html";

file_put_contents($htmlPath, $html);

Pdf::loadHTML($html)
    ->setPaper('a4')
    ->save($pdfPath);

echo "HTML: {$htmlPath}".PHP_EOL;
echo "PDF: {$pdfPath}".PHP_EOL;
