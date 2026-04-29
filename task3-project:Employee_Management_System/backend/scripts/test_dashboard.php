<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dashboardService = new App\Services\Recruitment\RecruitmentDashboardService();

try {
    $metrics = $dashboardService->getDashboardMetrics();
    echo "Dashboard Metrics:\n";
    echo json_encode($metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "\nSUCCESS: All ranking and dashboard queries executed successfully\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
