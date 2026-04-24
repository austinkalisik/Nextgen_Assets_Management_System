<?php

declare(strict_types=1);

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$settings = DB::table('settings')->pluck('value', 'key');

$appName = (string) ($settings['system_name'] ?? 'NextGen Assets');
$tagline = (string) ($settings['system_tagline'] ?? 'Management System');
$companyName = (string) ($settings['company_name'] ?? 'NextGen Technology');
$companyWebsite = (string) ($settings['company_website'] ?? 'https://nextgenpng.net/');
$supportEmail = (string) ($settings['support_email'] ?? 'support@nextgenpng.net');
$generatedAt = now()->format('F j, Y g:i A');

$html = view('reports.system-demo-report', [
    'appName' => $appName,
    'tagline' => $tagline,
    'companyName' => $companyName,
    'companyWebsite' => $companyWebsite,
    'supportEmail' => $supportEmail,
    'generatedAt' => $generatedAt,
])->render();

$reportDirectory = public_path('reports');

if (! is_dir($reportDirectory)) {
    mkdir($reportDirectory, 0777, true);
}

$pdfPath = $reportDirectory . DIRECTORY_SEPARATOR . 'nextgen-asset-management-system-demo-report.pdf';
$htmlPath = $reportDirectory . DIRECTORY_SEPARATOR . 'nextgen-asset-management-system-demo-report.html';

file_put_contents($htmlPath, $html);

Pdf::loadHTML($html)
    ->setPaper('a4')
    ->save($pdfPath);

echo "HTML: {$htmlPath}" . PHP_EOL;
echo "PDF: {$pdfPath}" . PHP_EOL;
