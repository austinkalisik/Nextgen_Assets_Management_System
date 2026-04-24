<?php

declare(strict_types=1);

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$settings = DB::table('settings')->pluck('value', 'key');

$appName = (string) ($settings['system_name'] ?? 'Nextgen Assets Management System');
$companyName = (string) ($settings['company_name'] ?? 'Nextgen Technology');
$companyWebsite = (string) ($settings['company_website'] ?? 'https://nextgenpng.net/');
$generatedAt = now()->format('F j, Y g:i A');
$reportDate = now()->format('F j, Y');
$safeDate = now()->format('Y-m-d');
$reportTitle = 'Full Daily Development Report - ' . $reportDate;

$objectives = [
    'Verify the current ERD against the live schema.',
    'Regenerate the visual ERD from the project itself.',
    'Align wireframe and schema notes with the generated ERD.',
    'Improve clone-and-run experience for GitHub users.',
    'Add setup automation, Docker support, and updated documentation.',
    'Re-verify that the application still passes tests and frontend build checks.',
];

$completedAreas = [
    [
        'area' => 'ERD Verification',
        'work' => 'Compared graph.png against current migrations, models, and schema behavior.',
        'outcome' => 'Confirmed where the previous ERD matched, where it was outdated, and what needed regeneration.',
    ],
    [
        'area' => 'ERD Regeneration',
        'work' => 'Ran php artisan generate:erd to regenerate graph.png and produced a generated text snapshot.',
        'outcome' => 'The project now has a fresh generated ERD that reflects the live operational model state.',
    ],
    [
        'area' => 'Documentation Alignment',
        'work' => 'Updated ERD notes, schema alignment notes, and wireframe/design documents.',
        'outcome' => 'The documentation now references the generated ERD properly and explains scope/legacy-column realities clearly.',
    ],
    [
        'area' => 'Bootstrap Automation',
        'work' => 'Added bootstrap scripts and improved Composer scripts for onboarding.',
        'outcome' => 'A new developer has a much easier pull-and-run path with composer bootstrap and verification helpers.',
    ],
    [
        'area' => 'Developer Experience',
        'work' => 'Updated VS Code tasks and README with clearer setup/run steps.',
        'outcome' => 'The repo is easier to use directly in VS Code and easier to follow after cloning from GitHub.',
    ],
    [
        'area' => 'Docker Scaffolding',
        'work' => 'Added Dockerfile, docker-compose.yml, Apache config, entrypoint, and Docker env example.',
        'outcome' => 'The project now has a containerized startup option ready for a later Docker validation pass.',
    ],
    [
        'area' => 'Verification',
        'work' => 'Ran test/build verification after the repository-level changes.',
        'outcome' => 'The application still passes the Laravel test suite and frontend production build.',
    ],
];

$technicalSections = [
    [
        'title' => '4.1 ERD and Schema Work',
        'points' => [
            'Regenerated graph.png from the live project instead of relying on an older manual diagram.',
            'Generated wireframe/erd-current.txt as a raw machine-produced ERD snapshot for comparison and auditability.',
            'Documented that the generated ERD is operational-model focused and does not represent every framework/support table.',
            'Recorded that some legacy columns still appear because they exist in the live local schema.',
        ],
    ],
    [
        'title' => '4.2 Documentation and Reporting Work',
        'points' => [
            'Updated ERD explanation notes so they now reflect the generated graph.png output.',
            'Added schema alignment notes to avoid future confusion between generated ERD output and idealized business documentation.',
            'Updated wireframe notes and design notes to mention the generated ERD snapshot and current application structure.',
            'Prepared reusable daily report generation templates so reporting can be repeated more easily.',
        ],
    ],
    [
        'title' => '4.3 Bootstrap / Onboarding Work',
        'points' => [
            'Improved composer setup flow to include storage link, migrations with seeders, cache clear, and frontend build.',
            'Added a dedicated composer bootstrap command for simpler onboarding.',
            'Added cross-platform bootstrap scripts for PHP, PowerShell, and shell environments.',
            'Added a composer verify command to group test and build verification.',
        ],
    ],
    [
        'title' => '4.4 Developer Workflow Improvements',
        'points' => [
            'Added a VS Code bootstrap task for easier first-time setup.',
            'Added a VS Code verify task so developers can quickly run health checks.',
            'Rewrote the README to give one clearer GitHub clone-to-run path instead of scattered manual steps.',
        ],
    ],
    [
        'title' => '4.5 Docker Work',
        'points' => [
            'Added Dockerfile for app container build.',
            'Added docker-compose.yml for app and MySQL startup.',
            'Added Apache virtual host config and entrypoint logic.',
            'Added Docker environment example for easier container startup.',
        ],
    ],
];

$fileHighlights = [
    ['file' => 'graph.png', 'purpose' => 'Fresh generated ERD image from the live project.'],
    ['file' => 'wireframe/erd-current.txt', 'purpose' => 'Raw text ERD snapshot generated from the same source.'],
    ['file' => 'wireframe/ERD - Nextgen Assets Management System.txt', 'purpose' => 'Updated ERD explanation file aligned with generated output.'],
    ['file' => 'wireframe/Schema Alignment Notes.txt', 'purpose' => 'New note describing generated ERD scope and legacy-column visibility.'],
    ['file' => 'wireframe/Detailed ASCII Wireframes.txt', 'purpose' => 'Updated to reference current ERD alignment.'],
    ['file' => 'wireframe/Detailed Design Spec.txt', 'purpose' => 'Updated to reference current ERD alignment.'],
    ['file' => 'wireframe/low-fidelity wireframe.txt', 'purpose' => 'Updated to reference current ERD alignment.'],
    ['file' => 'scripts/bootstrap.php', 'purpose' => 'Main bootstrap automation for repository setup.'],
    ['file' => 'scripts/bootstrap.ps1', 'purpose' => 'Windows bootstrap helper.'],
    ['file' => 'scripts/bootstrap.sh', 'purpose' => 'Shell bootstrap helper.'],
    ['file' => 'Dockerfile', 'purpose' => 'Container build recipe.'],
    ['file' => 'docker-compose.yml', 'purpose' => 'Local container startup orchestration.'],
    ['file' => '.env.docker.example', 'purpose' => 'Docker environment starter file.'],
    ['file' => '.vscode/tasks.json', 'purpose' => 'Updated with bootstrap and verify tasks.'],
    ['file' => 'README.md', 'purpose' => 'Rewritten for easier GitHub clone, setup, Docker, and verification flow.'],
    ['file' => 'resources/views/reports/daily-full-report.blade.php', 'purpose' => 'Full daily report PDF template.'],
    ['file' => 'scripts/generate-full-daily-report.php', 'purpose' => 'Generator for a fuller daily report PDF.'],
];

$stateSummary = [
    [
        'topic' => 'ERD',
        'state' => 'Regenerated from the live project and documented more clearly.',
    ],
    [
        'topic' => 'Documentation',
        'state' => 'README and wireframe notes now better match current repo reality.',
    ],
    [
        'topic' => 'Clone / Onboarding',
        'state' => 'Improved through bootstrap scripts and clearer instructions.',
    ],
    [
        'topic' => 'VS Code usability',
        'state' => 'Improved through dedicated bootstrap and verify tasks.',
    ],
    [
        'topic' => 'Docker readiness',
        'state' => 'Scaffolded but not runtime-validated on this machine.',
    ],
    [
        'topic' => 'Application health',
        'state' => 'Verified with passing Laravel tests and successful frontend production build.',
    ],
];

$verification = [
    ['check' => 'ERD generation', 'result' => 'graph.png regenerated successfully with php artisan generate:erd.'],
    ['check' => 'Generated ERD text output', 'result' => 'wireframe/erd-current.txt generated successfully.'],
    ['check' => 'PHP syntax check', 'result' => 'scripts/bootstrap.php passed php -l.'],
    ['check' => 'Laravel test suite', 'result' => 'Passed: 49 tests, 147 assertions.'],
    ['check' => 'Frontend production build', 'result' => 'Passed via npm run build.'],
    ['check' => 'Composer verify command', 'result' => 'Passed and validated the new grouped verification flow.'],
    ['check' => 'Docker runtime validation', 'result' => 'Not completed because Docker is not installed on this machine.'],
];

$caveats = [
    'The generated ERD reflects the live local schema/model state, which can still include legacy columns that remain in the local database.',
    'graph.png is a generated operational-model ERD and not a full inventory of every database table such as framework support tables.',
    'Docker support has been added but not executed here due missing Docker runtime on this machine.',
    'The Git worktree still contains broader ongoing project changes, so GitHub push/commit scope should be reviewed carefully before publishing.',
];

$nextSteps = [
    'Review the full worktree and prepare a clean commit strategy before pushing to GitHub.',
    'Validate the new Docker flow on a Docker-enabled machine.',
    'Optionally clean legacy database columns so future generated ERDs are cleaner.',
    'Continue improving production-readiness documentation and deployment guidance.',
];

$html = view('reports.daily-full-report', [
    'reportTitle' => $reportTitle,
    'reportDate' => $reportDate,
    'generatedAt' => $generatedAt,
    'appName' => $appName,
    'companyName' => $companyName,
    'companyWebsite' => $companyWebsite,
    'objectives' => $objectives,
    'completedAreas' => $completedAreas,
    'technicalSections' => $technicalSections,
    'fileHighlights' => $fileHighlights,
    'stateSummary' => $stateSummary,
    'verification' => $verification,
    'caveats' => $caveats,
    'nextSteps' => $nextSteps,
])->render();

$reportDirectory = public_path('reports');

if (! is_dir($reportDirectory)) {
    mkdir($reportDirectory, 0777, true);
}

$pdfPath = $reportDirectory . DIRECTORY_SEPARATOR . "nextgen-full-daily-report-{$safeDate}.pdf";
$htmlPath = $reportDirectory . DIRECTORY_SEPARATOR . "nextgen-full-daily-report-{$safeDate}.html";

file_put_contents($htmlPath, $html);

Pdf::loadHTML($html)
    ->setPaper('a4')
    ->save($pdfPath);

echo "HTML: {$htmlPath}" . PHP_EOL;
echo "PDF: {$pdfPath}" . PHP_EOL;
