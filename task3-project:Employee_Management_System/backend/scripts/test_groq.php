<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new App\Services\AI\ResumeAnalysisService();

try {
    $result = $svc->analyzeRawTextForVacancy(
        "Senior PHP developer with 5 years experience in Laravel, REST APIs, MySQL, and unit testing.",
        "We seek a Laravel developer with 3+ years experience building REST APIs, MySQL, and automated tests."
    );

    echo "OK\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
