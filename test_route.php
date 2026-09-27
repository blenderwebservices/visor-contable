<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

// Find the excel file
$doc = \App\Models\FileDocument::where('type', 'excel')->first();
if (!$doc) {
    echo "No excel file found\n";
    exit;
}

$request = \Illuminate\Http\Request::create('/documents/view/' . $doc->id, 'GET');
$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "File path: " . $doc->file_path . "\n";
