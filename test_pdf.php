<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$path = App\Services\DocumentConverterService::convertToPdf('documents/01KXKAQS61F3YMYSCZBY0JT7NY.xlsx');
echo "RETURNED PATH: " . $path . "\n";
